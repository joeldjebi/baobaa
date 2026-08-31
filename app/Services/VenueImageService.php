<?php

namespace App\Services;

use App\Models\Venue;
use App\Models\VenueMedia;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class VenueImageService
{
    public function store(Venue $venue, UploadedFile $image, bool $isPrimary = false, ?string $altText = null): VenueMedia
    {
        $disk = 'wasabi';
        $directory = 'venues/'.$venue->id.'/images';
        $extension = $image->getClientOriginalExtension() ?: 'jpg';
        $path = $image->storeAs($directory, Str::uuid().'.'.$extension, [
            'disk' => $disk,
            'visibility' => 'private',
        ]);

        if ($isPrimary) {
            $venue->media()->update(['is_primary' => false]);
        }

        return $venue->media()->create([
            'type' => 'image',
            'disk' => $disk,
            'path' => $path,
            'alt_text' => $altText,
            'is_primary' => $isPrimary,
            'sort_order' => (int) $venue->media()->max('sort_order') + 1,
            'moderation_status' => 'pending',
        ]);
    }

    public function temporaryUrl(VenueMedia $media, int $minutes = 45): string
    {
        if (Str::startsWith($media->path, ['http://', 'https://'])) {
            return $media->path;
        }

        if (($media->disk ?: 'public') === 'wasabi') {
            try {
                return Storage::disk('wasabi')->temporaryUrl(
                    $media->path,
                    now()->addMinutes($minutes)
                );
            } catch (Throwable $exception) {
                Log::warning('Unable to generate Wasabi venue image URL.', [
                    'venue_media_id' => $media->id,
                    'venue_id' => $media->venue_id,
                    'path' => $media->path,
                    'message' => $exception->getMessage(),
                ]);

                return asset('images/baobaa.jpg');
            }
        }

        return Storage::disk($media->disk ?: 'public')->url($media->path);
    }
}
