<?php

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Enums\VenueStatus;
use App\Models\EventService;
use App\Models\User;
use App\Models\Venue;
use App\Models\VenueReview;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

#[Signature('baobaa:purge-demo-data {--force : Apply the changes instead of only listing them.} {--domain=baobaa.local : E-mail domain used by demonstration accounts.}')]
#[Description('Hide demonstration accounts, venues, services and fake reviews from the public platform.')]
class PurgeDemoData extends Command
{
    /**
     * Execute the console command.
     *
     * Demonstration content is archived or suspended instead of deleted, so it can be restored if needed.
     */
    public function handle(): int
    {
        $demoUsers = User::query()
            ->where('email', 'like', '%@'.$this->option('domain'))
            ->where('role', '!=', UserRole::Sap->value)
            ->get();

        $demoUserIds = $demoUsers->modelKeys();

        $demoVenues = Venue::query()
            ->whereHas('ownerProfile', fn (Builder $query) => $query->whereIn('user_id', $demoUserIds))
            ->where('status', '!=', VenueStatus::Archived->value);

        $demoServices = EventService::query()
            ->whereHas('serviceProviderProfile', fn (Builder $query) => $query->whereIn('user_id', $demoUserIds))
            ->where('status', '!=', VenueStatus::Archived->value);

        $demoReviews = VenueReview::query()
            ->whereIn('client_id', $demoUserIds)
            ->where('status', '!=', 'rejected');

        $this->components->info('Données de démonstration détectées');
        $this->table(['Élément', 'Nombre'], [
            ['Comptes de démonstration actifs', $demoUsers->where('status', '!=', UserStatus::Suspended)->count()],
            ['Espaces à archiver', (clone $demoVenues)->count()],
            ['Services à archiver', (clone $demoServices)->count()],
            ['Faux avis à retirer', (clone $demoReviews)->count()],
        ]);

        if (! $this->option('force')) {
            $this->components->warn('Aucune modification appliquée. Relancez avec --force pour masquer ces données.');

            return self::SUCCESS;
        }

        DB::transaction(function () use ($demoUserIds, $demoVenues, $demoServices, $demoReviews): void {
            $demoVenues->update(['status' => VenueStatus::Archived->value]);
            $demoServices->update(['status' => VenueStatus::Archived->value]);
            $demoReviews->update(['status' => 'rejected', 'approved_at' => null]);

            User::query()
                ->whereIn('id', $demoUserIds)
                ->update(['status' => UserStatus::Suspended->value]);

            $this->syncVenueRatings();
        });

        $this->components->info('Les données de démonstration ne sont plus visibles publiquement.');

        return self::SUCCESS;
    }

    /**
     * Replace stored ratings with values computed from approved reviews only.
     */
    private function syncVenueRatings(): void
    {
        Venue::query()
            ->withPublicRating()
            ->each(fn (Venue $venue) => $venue->forceFill([
                'average_rating' => round((float) $venue->public_rating, 2),
                'reviews_count' => (int) $venue->public_reviews_count,
            ])->saveQuietly());
    }
}
