<?php

namespace App\Models;

use App\Enums\UserRole;
use App\Enums\UserStatus;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'phone', 'role', 'portal_roles', 'status', 'email_verified_at', 'password', 'last_login_at'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'role' => UserRole::class,
            'portal_roles' => 'array',
            'status' => UserStatus::class,
            'password' => 'hashed',
        ];
    }

    public function ownerProfile(): HasOne
    {
        return $this->hasOne(OwnerProfile::class);
    }

    public function serviceProviderProfile(): HasOne
    {
        return $this->hasOne(ServiceProviderProfile::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'client_id');
    }

    public function eventProjects(): HasMany
    {
        return $this->hasMany(EventProject::class, 'client_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'payer_id');
    }

    public function sentBookingMessages(): HasMany
    {
        return $this->hasMany(BookingMessage::class, 'sender_id');
    }

    public function venueReviews(): HasMany
    {
        return $this->hasMany(VenueReview::class, 'client_id');
    }

    public function portalAccessRequests(): HasMany
    {
        return $this->hasMany(PortalAccessRequest::class);
    }

    public function isSap(): bool
    {
        return $this->hasPortal(UserRole::Sap);
    }

    public function hasPortal(UserRole|string $role): bool
    {
        $roleValue = $role instanceof UserRole ? $role->value : $role;

        if ($this->role?->value === $roleValue) {
            return true;
        }

        return in_array($roleValue, $this->portal_roles ?: [], true);
    }

    public function grantPortal(UserRole|string $role): void
    {
        $roleValue = $role instanceof UserRole ? $role->value : $role;
        $roles = collect($this->portal_roles ?: [])
            ->push($this->role?->value)
            ->push($roleValue)
            ->filter()
            ->unique()
            ->values()
            ->all();

        $this->forceFill(['portal_roles' => $roles])->save();
    }
}
