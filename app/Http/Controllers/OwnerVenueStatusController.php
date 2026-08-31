<?php

namespace App\Http\Controllers;

use App\Enums\VenueStatus;
use App\Models\OwnerProfile;
use App\Models\Venue;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OwnerVenueStatusController extends Controller
{
    public function update(Request $request, Venue $venue): RedirectResponse
    {
        $ownerProfile = $this->ownerProfile($request);

        abort_unless((int) $venue->owner_profile_id === (int) $ownerProfile->id, 403);

        $validated = $request->validate([
            'action' => ['required', Rule::in(['activate', 'disable', 'archive'])],
        ]);

        $status = match ($validated['action']) {
            'activate' => VenueStatus::Published,
            'archive' => VenueStatus::Archived,
            default => VenueStatus::Suspended,
        };

        $venue->update([
            'status' => $status,
            'published_at' => $status === VenueStatus::Published ? now() : $venue->published_at,
        ]);

        return back()->with('venue_status', match ($validated['action']) {
            'activate' => 'Votre espace est actif et visible par les clients.',
            'archive' => 'Votre espace est archivé.',
            default => 'Votre espace est désactivé.',
        });
    }

    private function ownerProfile(Request $request): OwnerProfile
    {
        return $request->user()->ownerProfile()->firstOrFail();
    }
}
