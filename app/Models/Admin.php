<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Permission\Traits\HasPermissions;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Illuminate\Support\Facades\Log;

class Admin extends Authenticatable implements HasMedia
{
    use HasFactory, Notifiable, SoftDeletes, HasRoles, HasPermissions, InteractsWithMedia;
    
    /**
     * The guard name for the model.
     *
     * @var string
     */
    protected $guard_name = 'admin';
    
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];
    
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'last_login_at' => 'datetime',
    ];
    
    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['avatar_url'];
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'status',
        'last_login_at',
        'last_login_ip',
    ];
    
    /**
     * Register the media collections.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('avatars')
             ->singleFile()
             ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/gif'])
             ->useDisk('public');
    }

    /**
     * Get the URL of the admin's avatar.
     *
     * @return string
     */
    public function getAvatarUrlAttribute()
    {
        return $this->hasMedia('avatars') 
            ? $this->getFirstMediaUrl('avatars')
            : asset('assets/img/avatars/default-avatar.png');
    }

    /**
     * Check if admin has specific permission
     * Override the default hasPermissionTo method to ensure proper guard is used
     */
    public function hasPermissionTo($permission, $guardName = null): bool
    {
        try {
            // Ensure we have a guard name
            $guardName = $guardName ?: $this->getDefaultGuardName();
            
            // Handle string permission name
            if (is_string($permission)) {
                $permissionModel = app(\Spatie\Permission\Contracts\Permission::class);
                if (!method_exists($permissionModel, 'findByName')) {
                    \Log::error('Permission model does not have findByName method');
                    return false;
                }
                
                try {
                    $permission = $permissionModel->findByName($permission, $guardName);
                } catch (\Exception $e) {
                    \Log::error('Error finding permission by name', [
                        'permission' => $permission,
                        'error' => $e->getMessage()
                    ]);
                    return false;
                }
            }
            
            // Handle permission ID
            if (is_int($permission)) {
                $permissionModel = app(\Spatie\Permission\Contracts\Permission::class);
                if (!method_exists($permissionModel, 'findById')) {
                    \Log::error('Permission model does not have findById method');
                    return false;
                }
                
                try {
                    $permission = $permissionModel->findById($permission, $guardName);
                } catch (\Exception $e) {
                    \Log::error('Error finding permission by ID', [
                        'permission_id' => $permission,
                        'error' => $e->getMessage()
                    ]);
                    return false;
                }
            }
            
            // If no valid permission found, return false
            if (!$permission) {
                return false;
            }
            
            // Check direct permissions first
            if (method_exists($this, 'hasDirectPermission') && $this->hasDirectPermission($permission)) {
                return true;
            }
            
            // Then check permissions via roles
            if (method_exists($this, 'hasPermissionViaRole')) {
                return $this->hasPermissionViaRole($permission);
            }
            
            return false;
            
        } catch (\Exception $e) {
            \Log::error('Error in hasPermissionTo', [
                'permission' => is_object($permission) ? get_class($permission) : $permission,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Check if admin has specific role
     * Override the default hasRole method to ensure proper guard is used
     */
    public function hasRole($roles, string $guardName = null): bool
    {
        // Debug: Log the input
        \Log::info('hasRole called with:', [
            'roles' => $roles,
            'guardName' => $guardName,
            'roles_relation_exists' => isset($this->relations['roles']),
            'roles_type' => gettype($this->roles),
        ]);

        // Ensure we have a guard name
        $guardName = $guardName ?: $this->getDefaultGuardName();
        
        // Get roles - handle case where roles might be a string
        $userRoles = $this->roles;
        
        // If roles is a string, convert it to an array
        if (is_string($userRoles)) {
            $userRoles = [$userRoles];
        } 
        // If it's a collection, convert to array of names
        elseif (is_object($userRoles) && method_exists($userRoles, 'pluck')) {
            $userRoles = $userRoles->pluck('name')->toArray();
        }
        // If it's already an array, make sure it's an array of role names
        elseif (is_array($userRoles)) {
            $userRoles = array_map(function($role) {
                return is_object($role) ? $role->name : $role;
            }, $userRoles);
        }
        
        // If no roles exist, return false
        if (empty($userRoles)) {
            \Log::info('No roles found for user', ['user_id' => $this->id]);
            return false;
        }
        
        // Handle string role name
        if (is_string($roles)) {
            \Log::info('Checking string role', ['role_to_check' => $roles]);
            $result = in_array($roles, (array)$userRoles);
            \Log::info('String role check result', ['role' => $roles, 'result' => $result]);
            return $result;
        }
        
        // Handle role ID (if needed)
        if (is_int($roles)) {
            // If we have role IDs, we'd check against them here
            // For now, we'll return false as we're working with role names
            return false;
        }
        
        // Handle Role model instance
        if ($roles instanceof \Spatie\Permission\Contracts\Role) {
            return in_array($roles->name, (array)$userRoles);
        }
        
        // Handle array of roles
        if (is_array($roles)) {
            if (empty($roles)) {
                return false;
            }
            
            foreach ($roles as $role) {
                if (in_array($role, (array)$userRoles)) {
                    return true;
                }
            }
            
            return false;
        }
        
        // Handle collection of roles
        if ($roles instanceof \Illuminate\Support\Collection) {
            $roleNames = $roles->pluck('name')->toArray();
            return count(array_intersect($roleNames, (array)$userRoles)) > 0;
        }
        
        return false;
    }

    /**
     * Check if admin is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
