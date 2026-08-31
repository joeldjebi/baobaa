<?php

namespace App\Console\Commands;

use App\Models\OwnerProfile;
use App\Models\VenueMedia;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Throwable;

#[Signature('app:diagnose-wasabi-images')]
#[Description('Diagnose Wasabi configuration and signed image URLs.')]
class DiagnoseWasabiImages extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $diskConfig = config('filesystems.disks.wasabi', []);

        $this->components->info('Wasabi configuration');
        $this->table(['Key', 'Value'], [
            ['driver', $diskConfig['driver'] ?? null],
            ['region', $diskConfig['region'] ?? null],
            ['bucket', $diskConfig['bucket'] ?? null],
            ['endpoint', $diskConfig['endpoint'] ?? null],
            ['url', $diskConfig['url'] ?? null],
            ['path style', ($diskConfig['use_path_style_endpoint'] ?? false) ? 'true' : 'false'],
            ['access key loaded', filled($diskConfig['key'] ?? null) ? 'yes' : 'no'],
            ['secret key loaded', filled($diskConfig['secret'] ?? null) ? 'yes' : 'no'],
        ]);

        $venueMedia = VenueMedia::query()
            ->where('disk', 'wasabi')
            ->whereNotNull('path')
            ->orderByDesc('id')
            ->first();

        $partnerLogo = OwnerProfile::query()
            ->where('logo_disk', 'wasabi')
            ->whereNotNull('logo_path')
            ->orderByDesc('id')
            ->first();

        $this->components->info('Database records');
        $this->line('Venue images on Wasabi: '.VenueMedia::query()->where('disk', 'wasabi')->count());
        $this->line('Partner logos on Wasabi: '.OwnerProfile::query()->where('logo_disk', 'wasabi')->whereNotNull('logo_path')->count());

        $path = $venueMedia?->path ?? $partnerLogo?->logo_path;

        if (! $path) {
            $this->components->warn('No Wasabi media path found in database.');

            return self::SUCCESS;
        }

        $this->newLine();
        $this->components->info('Testing media path');
        $this->line($path);

        try {
            $exists = Storage::disk('wasabi')->exists($path);
            $this->line('Object exists: '.($exists ? 'yes' : 'no'));

            $temporaryUrl = Storage::disk('wasabi')->temporaryUrl($path, now()->addMinutes(10));
            $this->line('Temporary URL generated: yes');
            $this->line($temporaryUrl);
        } catch (Throwable $exception) {
            $this->components->error('Wasabi test failed: '.$exception->getMessage());

            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
