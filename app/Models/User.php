<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Builder;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable implements HasMedia
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, InteractsWithMedia, HasRoles;

        /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        // Add global scope to exclude blocked users
        static::addGlobalScope('excludeBlocked', function (Builder $builder) {
    if (auth()->check()) {
        // Temporarily remove blocking conditions for testing
        $builder->where('status', 'active'); // Example of a simplified condition
    }
});


    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
	
    protected $fillable = [
        'name',
        'email',
        'avatar',
        'status',
        'last_seen',
        'registration_type',
        'gender',
        'is_couple',
        'couple_name',
        'device_token',
        'device_type',
        'login_type',
        'last_login_ip',
        'last_login_at',
		'deleted_at',
    ];
    
    /**
     * Update the user's last seen timestamp
     *
     * @return void
     */
    public function updateLastSeen()
    {
        $this->last_seen = now();
        $this->save();
    }
    
    /**
     * Get all of the user's custom tokens.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function userTokens()
    {
        return $this->hasMany(\App\Models\UserToken::class);
    }
    
    /**
     * Get all payments for the user.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

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
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
			'last_login_at' => 'datetime',
        ];
    }

    /**
     * Get the user's profile.
     */
    public function profile()
    {
        return $this->hasOne(UserProfile::class);
    }

    /**
     * Get the user's custom tokens.
     */
    public function customTokens()
    {
        return $this->hasMany(Token::class);
    }

    /**
     * Get the user's latest chat message.
     */
    public function latestChat()
    {
       return $this->hasOne(Chat::class, function ($query) {
            $query->where('sender_id', $this->id)
                  ->orWhere('receiver_id', $this->id);
        })->latest();
    }

    /**
     * Get all of the sent messages for the user.
     */
    public function sentMessages()
    {
        return $this->hasMany(Chat::class, 'sender_id');
    }

    /**
     * Get all of the received messages for the user.
     */
    public function receivedMessages()
    {
        return $this->hasMany(Chat::class, 'receiver_id');
    }

    /**
     * Get all of the swipes where this user is the swiper.
     */
    public function sentSwipes()
    {
        return $this->hasMany(Swipe::class, 'swiper_id');
    }

    /**
     * Get all of the swipes where this user is the one being swiped on.
     */
    public function receivedSwipes()
    {
        return $this->hasMany(Swipe::class, 'swiped_id');
    }

    /**
     * Get all of the user's memberships.
     */
    public function userMemberships()
    {
        return $this->hasMany(UserMembership::class, 'user_id');
    }
    
    /**
     * @deprecated Use userMemberships() instead
     */
    public function memberships()
    {
        return $this->userMemberships();
    }

    /**
     * Get the user's active membership.
     */
    public function activeMembership()
    {
        return $this->hasOne(UserMembership::class, 'user_id')
            ->where('status', 'active')
            ->where('ends_at', '>=', now())
            ->latest();
    }

    /**
     * Register the media collections
     */
    /**
     * Get all competitions the user has participated in.
     */
    public function competitions()
    {
        return $this->belongsToMany(Competition::class, 'competition_participants')
            ->withPivot(['status', 'submission', 'score', 'rank'])
            ->withTimestamps();
    }

    /**
     * Register the media collections
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('profile_image')
             ->singleFile()
             ->useDisk('public');
             
        $this->addMediaCollection('gallery')
             ->useDisk('public');
    }

    /**
     * Get the URL to the user's profile photo.
     *
     * @return string|null
     */
    public function getProfilePhotoUrlAttribute()
    {
        return $this->getFirstMediaUrl('profile_image');
    }

    /**
     * Get the URL to the user's cover photo.
     *
     * @return string|null
     */
    public function getCoverPhotoUrlAttribute()
    {
        // If you have a separate collection for cover photos, use that instead
        // return $this->getFirstMediaUrl('cover_image');
        return null; // Return null if you don't have cover photos
    }

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['profile_photo_url', 'cover_photo_url'];

    /**
     * Users that this user has blocked
     */
    public function blockedUsers()
    {
        return $this->belongsToMany(
            User::class,
            'blocked_users',
            'user_id',
            'blocked_user_id'
        )
        ->withPivot(['created_at'])
        ->select([
            'users.id as user_id',
            'users.name',
            'users.email',
            'users.avatar',
            'users.status',
            'users.created_at as user_created_at',
            'blocked_users.user_id as pivot_user_id',
            'blocked_users.blocked_user_id as pivot_blocked_user_id',
            'blocked_users.created_at as pivot_created_at',
        ]);
    }

    /**
     * Users who have blocked this user
     */
    public function blockedBy()
    {
        return $this->belongsToMany(
            User::class, 
            'blocked_users', 
            'blocked_user_id', 
            'user_id'
        )->withPivot(['created_at'])
         ->select([
             'users.id as user_id', 
             'users.name', 
             'users.email', 
             'users.avatar',
             'users.status',
             'users.created_at as user_created_at',
             'blocked_users.user_id as pivot_user_id',
             'blocked_users.blocked_user_id as pivot_blocked_user_id',
             'blocked_users.created_at as pivot_created_at'
         ]);
    }

    /**
     * Check if this user is blocked by another user
     */
    public function isBlockedBy(User $user): bool
    {
        return $this->blockedBy()->where('user_id', $user->id)->exists();
    }

    /**
     * Check if this user has blocked another user
     */
    public function hasBlocked(User $user): bool
    {
        return $this->blockedUsers()->where('blocked_user_id', $user->id)->exists();
    }

    /**
     * Get all of the user's notifications.
     */
    public function notifications()
    {
        return $this->hasMany(Notification::class, 'user_id');
    }

    /**
     * Get the unread notifications
     */
    public function unreadNotifications()
    {
        return $this->notifications()->whereNull('read_at');
    }
	
	public function fcmTokens()
    {
        return $this->hasMany(FcmToken::class);
    }

    /**
     * Add or update an FCM token for the user.
     *
     * @param string $token
     * @param string|null $deviceName
     * @param string|null $deviceOs
     * @param string|null $appVersion
     * @return FcmToken
     */
    public function addFcmToken(
        string $token, 
        ?string $deviceName = null, 
        ?string $deviceOs = null, 
        ?string $appVersion = null
    ): FcmToken {
        // Check if token already exists for any user
        $existingToken = FcmToken::where('token', $token)->first();
        
        if ($existingToken) {
            // If token exists for a different user, delete it
            if ($existingToken->user_id !== $this->id) {
                $existingToken->delete();
            } else {
                // Token exists for this user, update it
                $existingToken->update([
                    'device_name' => $deviceName ?? $existingToken->device_name,
                    'device_os' => $deviceOs ?? $existingToken->device_os,
                    'app_version' => $appVersion ?? $existingToken->app_version,
                ]);
                return $existingToken;
            }
        }

        // Create new token
        return $this->fcmTokens()->create([
            'token' => $token,
            'device_name' => $deviceName,
            'device_os' => $deviceOs,
            'app_version' => $appVersion,
        ]);
    }

    /**
     * Remove an FCM token for the user.
     *
     * @param string $token
     * @return bool
     */
    public function removeFcmToken(string $token): bool
    {
        return $this->fcmTokens()
            ->where('token', $token)
            ->delete() > 0;
    }

    /**
     * Remove all FCM tokens for the user.
     *
     * @return bool
     */
    public function clearFcmTokens(): bool
    {
        return $this->fcmTokens()->delete() > 0;
    }
	
public function walletTransactions()
{
    return $this->hasMany(WalletTransaction::class);
}

public function sentGiftTransactions()
{
    return $this->hasMany(GiftTransaction::class, 'sender_id');
}
}
