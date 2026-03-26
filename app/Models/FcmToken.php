<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FcmToken extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'token',
        'device_name',
        'device_os',
        'app_version',
    ];

    /**
     * Get the user that owns the FCM token.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Find a token by its value
     *
     * @param string $token
     * @return FcmToken|null
     */
    public static function findByToken(string $token): ?self
    {
        return static::where('token', $token)->first();
    }
}
