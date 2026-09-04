@php
    use App\Enums\UserRole;

    $user = auth()->user();
@endphp

@if ($user)
    <details {{ $attributes->merge(['class' => 'baobaa-account-menu relative z-[1000]']) }}>
        <summary class="grid size-11 cursor-pointer list-none place-items-center rounded-full bg-white text-[#151821] shadow-sm ring-1 ring-[#e2e9f8] transition hover:bg-[#f4f7ff] focus:outline-none focus:ring-4 focus:ring-[#2f6bff]/15 [&::-webkit-details-marker]:hidden" aria-label="Menu compte">
            <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/></svg>
        </summary>
        <div class="absolute right-0 z-[1000] mt-2 w-64 overflow-hidden rounded-2xl border border-[#dce6f7] bg-white p-2 text-sm font-extrabold shadow-2xl shadow-[#173e7a]/16 ring-1 ring-white/80">
            <div class="px-3 py-2">
                <p class="truncate text-[#07152f]">{{ $user->name }}</p>
                <p class="truncate text-xs font-bold text-[#7d8aa7]">{{ $user->email }}</p>
            </div>

            @if ($user->hasPortal(UserRole::Client))
                <a href="{{ route('client.dashboard') }}" class="block rounded-xl px-3 py-2 text-[#52617b] hover:bg-[#f2f7ff] hover:text-[#2f6bff]">Dashboard client</a>
                <a href="{{ route('event-composer.create') }}" class="block rounded-xl px-3 py-2 text-[#52617b] hover:bg-[#f2f7ff] hover:text-[#2f6bff]">Composer un événement</a>
                <a href="{{ route('client.reservations') }}" class="block rounded-xl px-3 py-2 text-[#52617b] hover:bg-[#f2f7ff] hover:text-[#2f6bff]">Historique des réservations</a>
                <a href="{{ route('client.payments') }}" class="block rounded-xl px-3 py-2 text-[#52617b] hover:bg-[#f2f7ff] hover:text-[#2f6bff]">Historique des paiements</a>
                <a href="{{ route('client.profile') }}" class="block rounded-xl px-3 py-2 text-[#52617b] hover:bg-[#f2f7ff] hover:text-[#2f6bff]">Profil et sécurité</a>
            @else
                <form method="POST" action="{{ route('portals.client.enable') }}">
                    @csrf
                    <button class="w-full rounded-xl px-3 py-2 text-left text-[#52617b] hover:bg-[#f2f7ff] hover:text-[#2f6bff]">Demander l’accès client</button>
                </form>
            @endif

            @if ($user->hasPortal(UserRole::Owner))
                <a href="{{ route('owner.dashboard') }}" class="block rounded-xl px-3 py-2 text-[#52617b] hover:bg-[#f2f7ff] hover:text-[#2f6bff]">Dashboard propriétaire</a>
                <a href="{{ route('owner.sponsorships') }}" class="block rounded-xl px-3 py-2 text-[#52617b] hover:bg-[#f2f7ff] hover:text-[#2f6bff]">Sponsoriser mes espaces</a>
            @else
                <a href="{{ auth()->check() ? route('portals.owner.request.form') : route('owner.register') }}" class="block rounded-xl px-3 py-2 text-[#52617b] hover:bg-[#f2f7ff] hover:text-[#2f6bff]">Devenir partenaire vérifié</a>
            @endif

            @if ($user->hasPortal(UserRole::Sap))
                <a href="{{ route('sap.dashboard') }}" class="block rounded-xl px-3 py-2 text-[#52617b] hover:bg-[#f2f7ff] hover:text-[#2f6bff]">Dashboard SAP</a>
                <a href="{{ route('sap.portal-requests') }}" class="block rounded-xl px-3 py-2 text-[#52617b] hover:bg-[#f2f7ff] hover:text-[#2f6bff]">Validations SAP</a>
            @endif

            <form method="POST" action="{{ route('logout') }}" class="mt-1 border-t border-[#edf2fb] pt-1">
                @csrf
                <button class="w-full rounded-xl px-3 py-2 text-left text-[#b42318] hover:bg-[#fff6f6]">Déconnexion</button>
            </form>
        </div>
    </details>

    @once
        <script>
            document.addEventListener('click', (event) => {
                document.querySelectorAll('.baobaa-account-menu[open]').forEach((menu) => {
                    if (! menu.contains(event.target)) {
                        menu.removeAttribute('open');
                    }
                });
            });

            document.addEventListener('toggle', (event) => {
                if (! event.target.matches('.baobaa-account-menu') || ! event.target.open) {
                    return;
                }

                document.querySelectorAll('.baobaa-account-menu[open]').forEach((menu) => {
                    if (menu !== event.target) {
                        menu.removeAttribute('open');
                    }
                });
            }, true);
        </script>
    @endonce
@else
    <a href="{{ route('portal.login', ['portal' => 'client']) }}" {{ $attributes->merge(['class' => 'grid size-11 place-items-center rounded-full bg-white text-[#151821] shadow-sm ring-1 ring-[#e2e9f8] transition hover:bg-[#f4f7ff]']) }} aria-label="Compte client">
        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/></svg>
    </a>
@endif
