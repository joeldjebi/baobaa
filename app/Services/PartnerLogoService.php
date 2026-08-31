<?php

namespace App\Services;

use App\Models\OwnerProfile;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class PartnerLogoService
{
    public function store(OwnerProfile $profile, UploadedFile $logo, ?string $altText = null): OwnerProfile
    {
        $disk = 'wasabi';
        $directory = 'partners/'.$profile->public_uuid.'/logo';
        $extension = $logo->getClientOriginalExtension() ?: 'png';
        $path = $logo->storeAs($directory, Str::uuid().'.'.$extension, [
            'disk' => $disk,
            'visibility' => 'private',
        ]);

        if ($profile->logo_disk && $profile->logo_path) {
            Storage::disk($profile->logo_disk)->delete($profile->logo_path);
        }

        $profile->forceFill([
            'logo_disk' => $disk,
            'logo_path' => $path,
            'logo_alt_text' => $altText ?: $profile->business_name,
        ])->save();

        return $profile;
    }

    public function temporaryUrl(OwnerProfile $profile, int $minutes = 45): ?string
    {
        if (! $profile->logo_path) {
            return null;
        }

        if (Str::startsWith($profile->logo_path, ['http://', 'https://'])) {
            return $profile->logo_path;
        }

        if (($profile->logo_disk ?: 'public') === 'wasabi') {
            try {
                return Storage::disk('wasabi')->temporaryUrl(
                    $profile->logo_path,
                    now()->addMinutes($minutes)
                );
            } catch (Throwable $exception) {
                Log::warning('Unable to generate Wasabi partner logo URL.', [
                    'owner_profile_id' => $profile->id,
                    'path' => $profile->logo_path,
                    'message' => $exception->getMessage(),
                ]);

                return asset('images/baobaa.jpg');
            }
        }

        return Storage::disk($profile->logo_disk ?: 'public')->url($profile->logo_path);
    }
}
