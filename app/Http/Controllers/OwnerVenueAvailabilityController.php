<?php

namespace App\Http\Controllers;

use App\Models\VenueAvailability;
use Carbon\CarbonImmutable;
use Carbon\CarbonPeriod;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class OwnerVenueAvailabilityController extends Controller
{
    /**
     * Open or block every day of a period for one of the owner's venues.
     */
    public function store(Request $request): RedirectResponse
    {
        $ownerProfile = $request->user()->ownerProfile;

        abort_unless($ownerProfile, 404);

        $validated = $request->validate([
            'venue_id' => ['required', 'integer', Rule::exists('venues', 'id')->where('owner_profile_id', $ownerProfile->id)],
            'status' => ['required', Rule::in(['available', 'blocked'])],
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date', 'before_or_equal:'.now()->addYears(2)->toDateString()],
            'starts_at' => ['nullable', 'date_format:H:i', 'required_with:ends_at'],
            'ends_at' => ['nullable', 'date_format:H:i', 'required_with:starts_at', 'after:starts_at'],
            'block_reason' => ['nullable', 'string', 'max:255'],
        ]);

        $period = CarbonPeriod::create(
            CarbonImmutable::parse($validated['start_date']),
            CarbonImmutable::parse($validated['end_date'] ?? $validated['start_date']),
        );

        abort_if($period->count() > 366, 422, 'La période ne peut pas dépasser un an.');

        $startsAt = $validated['starts_at'] ?? '00:00';
        $endsAt = $validated['ends_at'] ?? '23:59';

        DB::transaction(function () use ($period, $validated, $startsAt, $endsAt): void {
            foreach ($period as $date) {
                VenueAvailability::query()->updateOrCreate([
                    'venue_id' => $validated['venue_id'],
                    'available_date' => $date->toDateString(),
                    'starts_at' => $startsAt,
                    'ends_at' => $endsAt,
                ], [
                    'status' => $validated['status'],
                    'block_reason' => $validated['status'] === 'blocked' ? ($validated['block_reason'] ?? null) : null,
                ]);
            }
        });

        $daysCount = $period->count();
        $statusLabel = $validated['status'] === 'blocked' ? 'bloqué' : 'ouvert';

        return back()->with('calendar_status', $daysCount.' jour'.($daysCount > 1 ? 's' : '').' '.$statusLabel.($daysCount > 1 ? 's' : '').' dans votre calendrier.');
    }

    public function destroy(Request $request, VenueAvailability $venueAvailability): RedirectResponse
    {
        $ownerProfile = $request->user()->ownerProfile;

        abort_unless($ownerProfile && $venueAvailability->venue?->owner_profile_id === $ownerProfile->id, 404);

        $venueAvailability->delete();

        return back()->with('calendar_status', 'Créneau retiré du calendrier.');
    }
}
