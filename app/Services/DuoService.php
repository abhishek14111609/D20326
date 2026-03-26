<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DuoService
{
    /**
     * Get user's partner
     */
    public function getPartner(User $user): ?User
    {
        if (!$user->partner_mobile) {
            return null;
        }
        
        return User::where('mobile', $user->partner_mobile)->first();
    }

    /**
     * Invite partner for duo account
     */
    public function invitePartner(User $user, string $partnerMobile, string $partnerName): array
    {
        // Check if user is already in a duo
        if ($user->partner_mobile) {
            throw new \Exception('You already have a partner');
        }
        
        // Check if partner mobile is already registered
        $existingUser = User::where('mobile', $partnerMobile)->first();
        if ($existingUser) {
            throw new \Exception('This mobile number is already registered');
        }
        
        // Generate invitation code
        $invitationCode = strtoupper(Str::random(8));
        
        // Store invitation in cache for 24 hours
        cache()->put("duo_invitation_{$invitationCode}", [
            'inviter_id' => $user->id,
            'partner_mobile' => $partnerMobile,
            'partner_name' => $partnerName,
            'created_at' => now()
        ], now()->addHours(24));
        
        // Update user with partner info
        $user->update([
            'partner_mobile' => $partnerMobile,
            'partner_name' => $partnerName
        ]);
        
        // TODO: Send SMS invitation to partner
        \Log::info("Duo invitation sent to {$partnerMobile}: Use code {$invitationCode} to join");
        
        return [
            'invitation_id' => $invitationCode,
            'masked_mobile' => $this->maskMobile($partnerMobile)
        ];
    }

    /**
     * Accept partner invitation
     */
    public function acceptInvitation(User $user, string $invitationCode): array
    {
        $invitation = cache()->get("duo_invitation_{$invitationCode}");
        
        if (!$invitation) {
            throw new \Exception('Invalid or expired invitation code');
        }
        
        // Check if user's mobile matches the invitation
        if ($user->mobile !== $invitation['partner_mobile']) {
            throw new \Exception('This invitation is not for your mobile number');
        }
        
        // Get the inviter
        $inviter = User::find($invitation['inviter_id']);
        if (!$inviter) {
            throw new \Exception('Inviter not found');
        }
        
        // Update both users to link them as partners
        $user->update([
            'partner_mobile' => $inviter->mobile,
            'partner_name' => $inviter->name,
            'registration_type' => 'duo'
        ]);
        
        $inviter->update([
            'partner_mobile' => $user->mobile,
            'partner_name' => $user->name,
            'registration_type' => 'duo'
        ]);
        
        // Remove invitation from cache
        cache()->forget("duo_invitation_{$invitationCode}");
        
        return [
            'partner' => $inviter
        ];
    }

    /**
     * Remove partner from duo account
     */
    public function removePartner(User $user, string $password): bool
    {
        // Verify password for security
        if (!Hash::check($password, $user->password)) {
            throw new \Exception('Invalid password');
        }
        
        if (!$user->partner_mobile) {
            throw new \Exception('You do not have a partner');
        }
        
        // Find partner
        $partner = User::where('mobile', $user->partner_mobile)->first();
        
        // Remove partner relationship from both users
        $user->update([
            'partner_mobile' => null,
            'partner_name' => null,
            'registration_type' => 'single'
        ]);
        
        if ($partner) {
            $partner->update([
                'partner_mobile' => null,
                'partner_name' => null,
                'registration_type' => 'single'
            ]);
        }
        
        return true;
    }

    /**
     * Check if users are partners
     */
    public function arePartners(User $user1, User $user2): bool
    {
        return $user1->partner_mobile === $user2->mobile && 
               $user2->partner_mobile === $user1->mobile;
    }

    /**
     * Get duo status
     */
    public function getDuoStatus(User $user): array
    {
        $partner = $this->getPartner($user);
        
        return [
            'is_duo' => $user->registration_type === 'duo',
            'has_partner' => !is_null($partner),
            'partner' => $partner,
            'partner_mobile' => $user->partner_mobile,
            'partner_name' => $user->partner_name
        ];
    }

    /**
     * Mask mobile number for privacy
     */
    private function maskMobile(string $mobile): string
    {
        $length = strlen($mobile);
        if ($length <= 4) {
            return str_repeat('*', $length);
        }
        
        return substr($mobile, 0, 2) . str_repeat('*', $length - 4) . substr($mobile, -2);
    }
}
