<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\PortalLoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PortalAuthenticatedSessionController extends Controller
{
    public function create(Request $request, string $portal): View
    {
        $role = $this->roleForPortal($portal);

        abort_if($role === null, 404);

        if ($request->filled('redirect') && $this->isLocalRedirect($request->string('redirect')->toString())) {
            $request->session()->put('url.intended', $request->string('redirect')->toString());
        }

        return view('auth.portal-login', [
            'portal' => $portal,
            'role' => $role,
            'meta' => $this->metaForRole($role),
        ]);
    }

    public function store(PortalLoginRequest $request, string $portal): RedirectResponse
    {
        $role = $this->roleForPortal($portal);

        abort_if($role === null, 404);

        $request->ensureIsNotRateLimited();

        if (! Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            $request->hitRateLimiter();

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        $user = $request->user();

        if (! $user->hasPortal($role)) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            $request->hitRateLimiter();

            throw ValidationException::withMessages([
                'email' => 'Ce compte ne correspond pas à cet espace BAOBAA.',
            ]);
        }

        $request->clearRateLimiter();
        $request->session()->regenerate();
        $user->forceFill(['last_login_at' => now()])->save();

        return redirect()->intended(route($this->dashboardRouteName($role)));
    }

    public function destroy(Request $request): RedirectResponse
    {
        $portal = $this->portalForRole($request->user()?->role);

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('portal.login', ['portal' => $portal ?? 'client']);
    }

    private function roleForPortal(string $portal): ?UserRole
    {
        return match ($portal) {
            'sap' => UserRole::Sap,
            'proprietaire' => UserRole::Owner,
            'prestataire' => UserRole::ServiceProvider,
            'client' => UserRole::Client,
            default => null,
        };
    }

    private function portalForRole(?UserRole $role): ?string
    {
        return match ($role) {
            UserRole::Sap => 'sap',
            UserRole::Owner => 'proprietaire',
            UserRole::ServiceProvider => 'prestataire',
            UserRole::Client => 'client',
            default => null,
        };
    }

    private function dashboardRouteName(UserRole $role): string
    {
        return match ($role) {
            UserRole::Sap => 'sap.dashboard',
            UserRole::Admin => 'sap.dashboard',
            UserRole::Owner => 'owner.dashboard',
            UserRole::ServiceProvider => 'service-provider.dashboard',
            UserRole::Client => 'client.dashboard',
        };
    }

    private function isLocalRedirect(string $redirectUrl): bool
    {
        $applicationHost = parse_url(config('app.url'), PHP_URL_HOST);
        $redirectHost = parse_url($redirectUrl, PHP_URL_HOST);

        return $redirectHost === null || $redirectHost === $applicationHost || str_starts_with($redirectUrl, url('/'));
    }

    /**
     * @return array{label: string, title: string, subtitle: string}
     */
    private function metaForRole(UserRole $role): array
    {
        return match ($role) {
            UserRole::Sap => [
                'label' => 'Administration BAOBAA',
                'title' => 'Pilotez la plateforme BAOBAA',
                'subtitle' => 'Commissions, validations, abonnements et sécurité depuis un espace central.',
            ],
            UserRole::Owner => [
                'label' => 'Espace propriétaire',
                'title' => 'Publiez et gérez vos espaces',
                'subtitle' => 'Suivez vos salles, disponibilités, demandes, paiements et reversements.',
            ],
            UserRole::ServiceProvider => [
                'label' => 'Espace prestataire',
                'title' => 'Gérez vos services événementiels',
                'subtitle' => 'Publiez vos offres, gérez les demandes et développez votre visibilité sur BAOBAA.',
            ],
            UserRole::Client => [
                'label' => 'Espace client',
                'title' => 'Réservez le bon espace',
                'subtitle' => 'Retrouvez vos réservations, paiements et événements à venir.',
            ],
            UserRole::Admin => [
                'label' => 'Administration',
                'title' => 'Administrez BAOBAA',
                'subtitle' => 'Validez, modérez et accompagnez les utilisateurs de la plateforme.',
            ],
        };
    }
}
