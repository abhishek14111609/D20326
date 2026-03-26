<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserProfile extends Model
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'name',
        'mobile',
        'bio',
        'dob',
        'gender',
        'location',
		'latitude',
		'longitude',
        'interest',
        'hobby',
        'gallery_images',
        // Duo/Couple registration fields
        'couple_name',
        'partner1_name',
        'partner1_email',
        'partner1_mobile',
        'partner1_photo',
        'partner1_bio',
        'partner1_gender',
        'partner1_dob',
        'partner1_location',
        'partner1_interest',
        'partner1_hobby',
        'partner2_name',
        'partner2_email',
        'partner2_mobile',
        'partner2_photo',
        'partner2_bio',
        'partner2_gender',
        'partner2_dob',
        'partner2_location',
        'partner2_interest',
        'partner2_hobby',
        'registration_type',
        'is_couple',
        'relationship_status',
        'languages',
        'occupation',
		'looking_for',
		'ethnicity',
		'address',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'dob' => 'date',
        'partner1_dob' => 'date',
        'partner2_dob' => 'date',
        'location' => 'array',
        'partner1_location' => 'array',
        'partner2_location' => 'array',
        'interest' => 'array',
        'partner1_interest' => 'array',
        'partner2_interest' => 'array',
        'hobby' => 'array',
        'partner1_hobby' => 'array',
        'partner2_hobby' => 'array',
        'gallery_images' => 'array',
        'is_couple' => 'boolean',
        'languages' => 'array',
        'occupation' => 'string',
		'looking_for' => 'string',
		'ethnicity' => 'string',
		'address' => 'string',
    ];

    /**
     * Get the user that owns the profile.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the user's age based on date of birth.
     */
    public function getAgeAttribute(): ?int
    {
        return $this->dob ? $this->dob->age : null;
    }

    /**
     * Get partner 1's age based on date of birth.
     */
    public function getPartner1AgeAttribute(): ?int
    {
        return $this->partner1_dob ? $this->partner1_dob->age : null;
    }

    /**
     * Get partner 2's age based on date of birth.
     */
    public function getPartner2AgeAttribute(): ?int
    {
        return $this->partner2_dob ? $this->partner2_dob->age : null;
    }
}
