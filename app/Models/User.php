<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        "nama",
        "username",
        "email",
        "role",
        "status",
        "foto_profil",
        "password",
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = ["password", "remember_token"];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            "email_verified_at" => "datetime",
            "password" => "hashed",
        ];
    }

    /**
     * Get the profile photo URL attribute.
     */
    public function getProfilePhotoUrlAttribute()
    {
        if ($this->foto_profil) {
            return asset("storage/profile-photos/" . $this->foto_profil);
        }

        // Return default avatar if no photo
        return asset("assets/image/logo/ptkmp-logo.png");
    }

    /**
     * Check if user has a specific role.
     */
    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    /**
     * Check if user is a Direktur.
     */
    public function isDirektur(): bool
    {
        return $this->role === "direktur";
    }

    /**
     * Check if user is Marketing.
     */
    public function isMarketing(): bool
    {
        return $this->role === "marketing";
    }

    /**
     * Check if user is Manager Purchasing.
     */
    public function isManagerPurchasing(): bool
    {
        return $this->role === "manager_purchasing";
    }

    /**
     * Check if user is Staff Purchasing.
     */
    public function isStaffPurchasing(): bool
    {
        return $this->role === "staff_purchasing";
    }

    /**
     * Check if user is in Purchasing team (manager or staff).
     */
    public function isPurchasing(): bool
    {
        return in_array($this->role, [
            "manager_purchasing",
            "staff_purchasing",
        ]);
    }

    /**
     * Check if user is Manager Accounting.
     */
    public function isManagerAccounting(): bool
    {
        return $this->role === "manager_accounting";
    }

    /**
     * Check if user is Staff Accounting.
     */
    public function isStaffAccounting(): bool
    {
        return $this->role === "staff_accounting";
    }

    /**
     * Check if user is in Accounting team (manager or staff).
     */
    public function isAccounting(): bool
    {
        return in_array($this->role, [
            "manager_accounting",
            "staff_accounting",
        ]);
    }

    /**
     * Check if user can verify penawaran (only direktur).
     */
    public function canVerifyPenawaran(): bool
    {
        return $this->isDirektur();
    }
}
