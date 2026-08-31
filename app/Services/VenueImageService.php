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
        return $this->storeImage($venue, $image, $isPrimary, $altText);
    }

    public function storeImage(Venue $venue, UploadedFile $image, bool $isPrimary = false, ?string $altText = null): VenueMedia
    {
        return $this->storeMedia($venue, $image, 'image', $isPrimary, $altText);
    }

    public function storeVideo(Venue $venue, UploadedFile $video, ?string $altText = null): VenueMedia
    {
        return $this->storeMedia($venue, $video, 'video', false, $altText);
    }

    private function storeMedia(Venue $venue, UploadedFile $file, string $type, bool $isPrimary = false, ?string $altText = null): VenueMedia
    {
        $disk = 'wasabi';
        $directory = 'venues/'.$venue->id.'/'.$type.'s';
        $extension = $this->extension($file, $type);
        $path = $file->storeAs($directory, Str::uuid().'.'.$extension, [
            'disk' => $disk,
            'visibility' => 'private',
        ]);

        if ($type !== 'image') {
            $isPrimary = false;
        }

        if ($isPrimary && $type === 'image') {
            $venue->media()->where('type', 'image')->update(['is_primary' => false]);
        }

        return $venue->media()->create([
            'type' => $type,
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

    public function delete(VenueMedia $media): void
    {
        $venue = $media->venue;
        $wasPrimaryImage = $media->type === 'image' && $media->is_primary;

        if (! Str::startsWith($media->path, ['http://', 'https://'])) {
            Storage::disk($media->disk ?: 'public')->delete($media->path);
        }

        $media->delete();

        if (! $wasPrimaryImage || ! $venue) {
            return;
        }

        $venue->media()
            ->where('type', 'image')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->first()
            ?->update(['is_primary' => true]);
    }

    private function extension(UploadedFile $file, string $type): string
    {
        $extension = $file->extension();

        if ($extension) {
            return $extension;
        }

        return $type === 'video' ? 'mp4' : 'jpg';
    }
}
