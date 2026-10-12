<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductMedia;
use App\Models\Venue;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class ProductImageService
{
    public function store(Product $product, UploadedFile $image): ProductMedia
    {
        $path = $image->storeAs(
            'products/'.$product->id,
            Str::uuid().'.'.($image->extension() ?: 'jpg'),
            ['disk' => 'wasabi', 'visibility' => 'private'],
        );

        return $product->media()->create([
            'disk' => 'wasabi',
            'path' => $path,
            'alt_text' => $product->name,
            'is_primary' => ! $product->media()->exists(),
            'sort_order' => (int) $product->media()->max('sort_order') + 1,
        ]);
    }

    public function temporaryUrl(ProductMedia $media, int $minutes = 45): string
    {
        if (Str::startsWith($media->path, ['http://', 'https://'])) {
            return $media->path;
        }

        if ($media->disk === 'wasabi') {
            try {
                return Storage::disk('wasabi')->temporaryUrl($media->path, now()->addMinutes($minutes));
            } catch (Throwable $exception) {
                Log::warning('Unable to generate Wasabi product image URL.', [
                    'product_media_id' => $media->id,
                    'message' => $exception->getMessage(),
                ]);

                return Venue::placeholderImageUrl();
            }
        }

        return Storage::disk($media->disk ?: 'public')->url($media->path);
    }

    public function delete(ProductMedia $media): void
    {
        $product = $media->product;
        $wasPrimary = $media->is_primary;

        if (! Str::startsWith($media->path, ['http://', 'https://'])) {
            Storage::disk($media->disk ?: 'public')->delete($media->path);
        }

        $media->delete();

        if ($wasPrimary && $product) {
            $product->media()->first()?->update(['is_primary' => true]);
        }
    }
}
