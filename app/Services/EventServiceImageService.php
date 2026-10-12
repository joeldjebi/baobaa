<?php

namespace App\Services;

use App\Models\EventService;
use App\Models\EventServiceMedia;
use App\Models\Venue;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class EventServiceImageService
{
    public function store(EventService $eventService, UploadedFile $image): EventServiceMedia
    {
        $path = $image->storeAs(
            'event-services/'.$eventService->id,
            Str::uuid().'.'.($image->extension() ?: 'jpg'),
            ['disk' => 'wasabi', 'visibility' => 'private'],
        );

        return $eventService->media()->create([
            'disk' => 'wasabi',
            'path' => $path,
            'alt_text' => $eventService->name,
            'is_primary' => ! $eventService->media()->exists(),
            'sort_order' => (int) $eventService->media()->max('sort_order') + 1,
        ]);
    }

    public function temporaryUrl(EventServiceMedia $media, int $minutes = 45): string
    {
        if (Str::startsWith($media->path, ['http://', 'https://'])) {
            return $media->path;
        }

        if ($media->disk === 'wasabi') {
            try {
                return Storage::disk('wasabi')->temporaryUrl($media->path, now()->addMinutes($minutes));
            } catch (Throwable $exception) {
                Log::warning('Unable to generate Wasabi event service image URL.', [
                    'event_service_media_id' => $media->id,
                    'message' => $exception->getMessage(),
                ]);

                return Venue::placeholderImageUrl();
            }
        }

        return Storage::disk($media->disk ?: 'public')->url($media->path);
    }

    public function delete(EventServiceMedia $media): void
    {
        $eventService = $media->eventService;
        $wasPrimary = $media->is_primary;

        if (! Str::startsWith($media->path, ['http://', 'https://'])) {
            Storage::disk($media->disk ?: 'public')->delete($media->path);
        }

        $media->delete();

        if ($wasPrimary && $eventService) {
            $eventService->media()->first()?->update(['is_primary' => true]);
        }
    }
}
