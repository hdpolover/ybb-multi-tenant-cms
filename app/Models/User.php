<?php

namespace App\Models;

use App\Traits\TenantAware;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasUuids, TenantAware, HasRoles;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'tenant_id',
        'name',
        'email',
        'password',
        'avatar_url',
        'preferences',
        'is_active',
        'last_login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'preferences' => 'array',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the posts created by this user
     */
    public function createdPosts()
    {
        return $this->hasMany(Post::class, 'created_by');
    }

    /**
     * Get the posts updated by this user
     */
    public function updatedPosts()
    {
        return $this->hasMany(Post::class, 'updated_by');
    }

    /**
     * Check if user is active
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Get user preference
     */
    public function getPreference(string $key, $default = null)
    {
        return data_get($this->preferences, $key, $default);
    }

    /**
     * Set user preference
     */
    public function setPreference(string $key, $value): void
    {
        $preferences = $this->preferences ?? [];
        data_set($preferences, $key, $value);
        $this->preferences = $preferences;
    }

    /**
     * Update last login timestamp
     */
    public function updateLastLogin(): void
    {
        $this->update(['last_login_at' => now()]);
    }

    /**
     * Check if user has admin access to tenant
     */
    public function canAccessTenantAdmin(): bool
    {
        return $this->isActive() && 
               $this->hasAnyRole(['TenantOwner', 'Admin', 'Editor', 'Author', 'SEO', 'Moderator', 'Analyst']);
    }

    /**
     * Get the guard name for Spatie permissions
     */
    public function getDefaultGuardName(): string
    {
        return 'web';
    }
}