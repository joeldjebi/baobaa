<?php

namespace App\Console\Commands;

use App\Models\OwnerProfile;
use App\Models\VenueMedia;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Throwable;

#[Signature('app:diagnose-wasabi-images {--venue= : Venue slug or ID to test a specific venue image.}')]
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

        $venueMediaQuery = VenueMedia::query()
            ->whereNotNull('path')
            ->orderByDesc('id');

        if ($this->option('venue')) {
            $venueMediaQuery->whereHas('venue', function ($query): void {
                $query->where('slug', $this->option('venue'))
                    ->orWhere('id', $this->option('venue'));
            });
        } else {
            $venueMediaQuery->where('disk', 'wasabi');
        }

        $venueMedia = $venueMediaQuery->first();

        $partnerLogo = OwnerProfile::query()
            ->where('logo_disk', 'wasabi')
            ->whereNotNull('logo_path')
            ->orderByDesc('id')
            ->first();

        $this->components->info('Database records');
        $this->line('Venue images on Wasabi: '.VenueMedia::query()->where('disk', 'wasabi')->count());
        $this->line('Partner logos on Wasabi: '.OwnerProfile::query()->where('logo_disk', 'wasabi')->whereNotNull('logo_path')->count());

        $path = $venueMedia?->path ?? $partnerLogo?->logo_path;
        $disk = $venueMedia?->disk ?? $partnerLogo?->logo_disk ?? 'wasabi';

        if (! $path) {
            $this->components->warn('No Wasabi media path found in database.');

            return self::SUCCESS;
        }

        $this->newLine();
        $this->components->info('Testing media path');
        $this->line('Disk: '.$disk);
        $this->line($path);

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            $this->line('Direct external URL: yes');
            $this->line('Temporary URL generated: not needed');

            return self::SUCCESS;
        }

        try {
            $exists = Storage::disk($disk)->exists($path);
            $this->line('Object exists: '.($exists ? 'yes' : 'no'));

            $temporaryUrl = Storage::disk($disk)->temporaryUrl($path, now()->addMinutes(10));
            $this->line('Temporary URL generated: yes');
            $this->line($temporaryUrl);
        } catch (Throwable $exception) {
            $this->components->error('Wasabi test failed: '.$exception->getMessage());

            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
