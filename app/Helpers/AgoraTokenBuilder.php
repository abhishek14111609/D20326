<?php

namespace App\Helpers;

class AgoraTokenBuilder
{
    // Token versions
    private static $version = "007";
    
    // Role types
    private static $roleAttendee = 0; // For RTM (no publishing)
    private static $rolePublisher = 1; // For RTC (can publish)
    private static $roleSubscriber = 2; // For RTC (can only subscribe)
    private static $roleAdmin = 3; // For RTM (admin privileges)

    /**
     * Generate RTC token for audio/video calls
     */
    public static function buildTokenWithUid($appId, $appCertificate, $channelName, $uid, $role, $expireTimeInSeconds = 3600)
    {
        $token = new AccessToken($appId, $appCertificate, $channelName, $uid);
        
        // Add RTC privileges
        $token->addPrivilege(AccessToken::PRIVILEGE_JOIN_CHANNEL, $expireTimeInSeconds);
        
        if ($role == self::$rolePublisher) {
            $token->addPrivilege(AccessToken::PRIVILEGE_PUBLISH_AUDIO_STREAM, $expireTimeInSeconds);
            $token->addPrivilege(AccessToken::PRIVILEGE_PUBLISH_VIDEO_STREAM, $expireTimeInSeconds);
            $token->addPrivilege(AccessToken::PRIVILEGE_PUBLISH_DATA_STREAM, $expireTimeInSeconds);
        }
        
        return $token->build();
    }

    /**
     * Generate RTM token for signaling
     */
    public static function buildToken($appId, $appCertificate, $userId, $expireTimeInSeconds = 3600)
    {
        $token = new AccessToken($appId, $appCertificate, '', $userId);
        $token->addPrivilege(AccessToken::PRIVILEGE_LOGIN, $expireTimeInSeconds);
        return $token->build();
    }

    /**
     * Generate a simple token (legacy method)
     */
    public static function generateToken($appId, $appCertificate, $channelName, $uid, $expireTimeInSeconds)
    {
        return self::buildTokenWithUid($appId, $appCertificate, $channelName, $uid, self::$rolePublisher, $expireTimeInSeconds);
    }
}

/**
 * AccessToken class for generating Agora tokens
 */
class AccessToken
{
    public const VERSION = "007";
    public const VERSION_LENGTH = 3;
    
    // Privileges
    public const PRIVILEGE_JOIN_CHANNEL = 1;
    public const PRIVILEGE_PUBLISH_AUDIO_STREAM = 2;
    public const PRIVILEGE_PUBLISH_VIDEO_STREAM = 3;
    public const PRIVILEGE_PUBLISH_DATA_STREAM = 4;
    public const PRIVILEGE_RTMP_STREAMING = 5;
    public const PRIVILEGE_LOGIN = 101;
    
    private $appId;
    private $appCertificate;
    private $channelName;
    private $uid;
    private $message = [];
    
    public function __construct($appId, $appCertificate, $channelName, $uid)
    {
        $this->appId = $appId;
        $this->appCertificate = $appCertificate;
        $this->channelName = $channelName;
        $this->uid = $uid;
    }
    
    public function addPrivilege($privilege, $expireTimeInSeconds)
    {
        $this->message[$privilege] = time() + $expireTimeInSeconds;
        return $this;
    }
    
    public function build()
    {
        // Sort privileges by key
        ksort($this->message);
        
        // Build message content
        $content = [
            'app_id' => $this->appId,
            'channel_name' => $this->channelName,
            'uid' => $this->uid,
            'message' => $this->message
        ];
        
        // Serialize and sign the content
        $msg = json_encode($content);
        $signature = hash_hmac('sha256', $msg, $this->appCertificate, true);
        
        // Combine everything
        $versionLength = 3;
        $signature = base64_encode($signature);
        $content = base64_encode($msg);
        
        return self::VERSION . $signature . $content;
    }
}
