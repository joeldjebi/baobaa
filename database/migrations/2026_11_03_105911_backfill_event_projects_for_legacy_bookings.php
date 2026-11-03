<?php

use App\Models\Booking;
use App\Services\EventProjectService;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Bookings created before event projects existed have no project, so clients could not see them
     * in their reservations list: create the missing project and venue item for each of them.
     */
    public function up(): void
    {
        $eventProjectService = app(EventProjectService::class);

        Booking::query()
            ->whereNull('event_project_item_id')
            ->with('venue')
            ->chunkById(100, function ($bookings) use ($eventProjectService): void {
                $bookings->each(fn (Booking $booking) => $eventProjectService->ensureVenueBookingItem($booking));
            });
    }

    public function down(): void
    {
        // The created projects are regular data and are kept.
    }
};
