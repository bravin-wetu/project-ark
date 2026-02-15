<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    use HasFactory;

    const UPDATED_AT = null; // Only track created_at

    protected $fillable = [
        'user_id',
        'type',
        'description',
        'properties',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected $casts = [
        'properties' => 'array',
        'created_at' => 'datetime',
    ];

    // Activity types
    const TYPE_LOGIN = 'login';
    const TYPE_LOGOUT = 'logout';
    const TYPE_LOGIN_FAILED = 'login_failed';
    const TYPE_PASSWORD_CHANGE = 'password_change';
    const TYPE_PASSWORD_RESET = 'password_reset';
    const TYPE_ROLE_ASSIGNED = 'role_assigned';
    const TYPE_ROLE_REMOVED = 'role_removed';
    const TYPE_USER_CREATED = 'user_created';
    const TYPE_USER_UPDATED = 'user_updated';
    const TYPE_USER_DELETED = 'user_deleted';
    const TYPE_USER_ACTIVATED = 'user_activated';
    const TYPE_USER_DEACTIVATED = 'user_deactivated';
    const TYPE_SETTING_CHANGED = 'setting_changed';
    const TYPE_BULK_ACTION = 'bulk_action';
    const TYPE_EXPORT = 'export';
    const TYPE_IMPORT = 'import';

    /**
     * User who performed the activity
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Log an activity
     */
    public static function log(
        string $type,
        string $description,
        ?array $properties = null,
        ?User $user = null
    ): self {
        return self::create([
            'user_id' => $user?->id ?? auth()->id(),
            'type' => $type,
            'description' => $description,
            'properties' => $properties,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
        ]);
    }

    /**
     * Log a login event
     */
    public static function logLogin(User $user, bool $success = true): self
    {
        return self::log(
            $success ? self::TYPE_LOGIN : self::TYPE_LOGIN_FAILED,
            $success 
                ? "User {$user->name} logged in successfully" 
                : "Failed login attempt for {$user->email}",
            ['user_email' => $user->email],
            $success ? $user : null
        );
    }

    /**
     * Log a logout event
     */
    public static function logLogout(User $user): self
    {
        return self::log(
            self::TYPE_LOGOUT,
            "User {$user->name} logged out",
            null,
            $user
        );
    }

    /**
     * Log a setting change
     */
    public static function logSettingChange(string $key, mixed $oldValue, mixed $newValue): self
    {
        return self::log(
            self::TYPE_SETTING_CHANGED,
            "Setting '{$key}' was changed",
            [
                'key' => $key,
                'old_value' => $oldValue,
                'new_value' => $newValue,
            ]
        );
    }

    /**
     * Log a role assignment
     */
    public static function logRoleAssigned(User $targetUser, Role $role): self
    {
        return self::log(
            self::TYPE_ROLE_ASSIGNED,
            "Role '{$role->name}' assigned to {$targetUser->name}",
            [
                'target_user_id' => $targetUser->id,
                'target_user_name' => $targetUser->name,
                'role_id' => $role->id,
                'role_name' => $role->name,
            ]
        );
    }

    /**
     * Log a role removal
     */
    public static function logRoleRemoved(User $targetUser, Role $role): self
    {
        return self::log(
            self::TYPE_ROLE_REMOVED,
            "Role '{$role->name}' removed from {$targetUser->name}",
            [
                'target_user_id' => $targetUser->id,
                'target_user_name' => $targetUser->name,
                'role_id' => $role->id,
                'role_name' => $role->name,
            ]
        );
    }

    /**
     * Get human-readable type label
     */
    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            self::TYPE_LOGIN => 'Login',
            self::TYPE_LOGOUT => 'Logout',
            self::TYPE_LOGIN_FAILED => 'Failed Login',
            self::TYPE_PASSWORD_CHANGE => 'Password Changed',
            self::TYPE_PASSWORD_RESET => 'Password Reset',
            self::TYPE_ROLE_ASSIGNED => 'Role Assigned',
            self::TYPE_ROLE_REMOVED => 'Role Removed',
            self::TYPE_USER_CREATED => 'User Created',
            self::TYPE_USER_UPDATED => 'User Updated',
            self::TYPE_USER_DELETED => 'User Deleted',
            self::TYPE_USER_ACTIVATED => 'User Activated',
            self::TYPE_USER_DEACTIVATED => 'User Deactivated',
            self::TYPE_SETTING_CHANGED => 'Setting Changed',
            self::TYPE_BULK_ACTION => 'Bulk Action',
            self::TYPE_EXPORT => 'Export',
            self::TYPE_IMPORT => 'Import',
            default => ucfirst(str_replace('_', ' ', $this->type)),
        };
    }

    /**
     * Get type color for badges
     */
    public function getTypeColorAttribute(): string
    {
        return match ($this->type) {
            self::TYPE_LOGIN => 'green',
            self::TYPE_LOGOUT => 'gray',
            self::TYPE_LOGIN_FAILED => 'red',
            self::TYPE_PASSWORD_CHANGE, self::TYPE_PASSWORD_RESET => 'yellow',
            self::TYPE_ROLE_ASSIGNED, self::TYPE_ROLE_REMOVED => 'purple',
            self::TYPE_USER_CREATED => 'blue',
            self::TYPE_USER_DELETED => 'red',
            self::TYPE_USER_ACTIVATED => 'green',
            self::TYPE_USER_DEACTIVATED => 'orange',
            self::TYPE_SETTING_CHANGED => 'indigo',
            default => 'gray',
        };
    }

    /**
     * Scope for filtering by type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope for filtering by user
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope for filtering by date range
     */
    public function scopeDateRange($query, $start, $end)
    {
        return $query->whereBetween('created_at', [$start, $end]);
    }
}
