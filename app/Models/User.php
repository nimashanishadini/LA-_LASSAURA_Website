<?php

namespace App\Models;

use App\Services\EmailVerification;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    public function sendEmailVerificationNotification(): void
    {
        app(EmailVerification::class)->send($this);
    }

    public function canAccessAdmin(): bool
    {
        return in_array($this->role, ['owner', 'manager', 'reception', 'finance', 'housekeeping', 'editor'], true);
    }

    public function canManageRooms(): bool
    {
        return in_array($this->role, ['owner', 'manager', 'editor'], true);
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
