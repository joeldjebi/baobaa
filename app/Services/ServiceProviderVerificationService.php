<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Enums\VerificationStatus;
use App\Models\PortalAccessRequest;
use App\Models\ServiceProviderProfile;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * SAP decision on a service provider: approving makes the profile and its services public
 * and opens the provider portal; rejecting hides them. A pending access request is closed either way.
 */
class ServiceProviderVerificationService
{
    public function approve(ServiceProviderProfile $profile, User $reviewer): void
    {
        DB::transaction(function () use ($profile, $reviewer): void {
            $profile->forceFill([
                'verification_status' => VerificationStatus::Verified,
                'verified_by' => $reviewer->id,
                'verified_at' => now(),
            ])->save();

            $profile->user?->grantPortal(UserRole::ServiceProvider);

            $this->closePendingRequest($profile, $reviewer, 'approved', null);
        });
    }

    public function reject(ServiceProviderProfile $profile, User $reviewer, ?string $note = null): void
    {
        DB::transaction(function () use ($profile, $reviewer, $note): void {
            $profile->forceFill([
                'verification_status' => VerificationStatus::Rejected,
                'verified_by' => null,
                'verified_at' => null,
            ])->save();

            $this->closePendingRequest($profile, $reviewer, 'rejected', $note);
        });
    }

    private function closePendingRequest(ServiceProviderProfile $profile, User $reviewer, string $status, ?string $note): void
    {
        PortalAccessRequest::query()
            ->where('user_id', $profile->user_id)
            ->where('requested_role', UserRole::ServiceProvider)
            ->where('status', 'pending')
            ->update([
                'status' => $status,
                'decision_note' => $note,
                'reviewed_by' => $reviewer->id,
                'reviewed_at' => now(),
            ]);
    }
}
