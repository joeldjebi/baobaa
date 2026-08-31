<?php

namespace App\Http\Controllers;

use App\Models\Venue;
use App\Models\VenueMedia;
use App\Services\VenueImageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OwnerVenueMediaController extends Controller
{
    public function __construct(private readonly VenueImageService $venueImageService) {}

    public function destroy(Request $request, Venue $venue, VenueMedia $venueMedia): JsonResponse|RedirectResponse
    {
        $ownerProfile = $request->user()->ownerProfile;

        abort_unless($ownerProfile && $venue->owner_profile_id === $ownerProfile->id, 404);
        abort_unless($venueMedia->venue_id === $venue->id, 404);

        $this->venueImageService->delete($venueMedia);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Média retiré avec succès.',
                'media_id' => $venueMedia->id,
            ]);
        }

        return back()->with('draft_status', 'Média retiré avec succès.');
    }
}
