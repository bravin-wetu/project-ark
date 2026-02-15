<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Jenssegers\Agent\Agent;

class UserSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'ip_address',
        'user_agent',
        'device_type',
        'browser',
        'platform',
        'last_activity_at',
        'is_current',
    ];

    protected $casts = [
        'last_activity_at' => 'datetime',
        'is_current' => 'boolean',
    ];

    /**
     * The user this session belongs to
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Create or update a session for the user
     */
    public static function track(User $user): self
    {
        $userAgent = request()->userAgent();
        $ipAddress = request()->ip();

        // Parse user agent for device info
        $deviceInfo = self::parseUserAgent($userAgent);

        // Check for existing session from same IP and browser
        $session = self::where('user_id', $user->id)
            ->where('ip_address', $ipAddress)
            ->where('browser', $deviceInfo['browser'])
            ->first();

        if ($session) {
            $session->update([
                'last_activity_at' => now(),
                'is_current' => true,
            ]);
        } else {
            // Mark all other sessions as not current
            self::where('user_id', $user->id)->update(['is_current' => false]);

            $session = self::create([
                'user_id' => $user->id,
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
                'device_type' => $deviceInfo['device_type'],
                'browser' => $deviceInfo['browser'],
                'platform' => $deviceInfo['platform'],
                'last_activity_at' => now(),
                'is_current' => true,
            ]);
        }

        return $session;
    }

    /**
     * Parse user agent string for device information
     */
    protected static function parseUserAgent(?string $userAgent): array
    {
        $result = [
            'device_type' => 'desktop',
            'browser' => 'Unknown',
            'platform' => 'Unknown',
        ];

        if (!$userAgent) {
            return $result;
        }

        // Simple browser detection
        if (stripos($userAgent, 'Chrome') !== false && stripos($userAgent, 'Edg') === false) {
            $result['browser'] = 'Chrome';
        } elseif (stripos($userAgent, 'Firefox') !== false) {
            $result['browser'] = 'Firefox';
        } elseif (stripos($userAgent, 'Safari') !== false && stripos($userAgent, 'Chrome') === false) {
            $result['browser'] = 'Safari';
        } elseif (stripos($userAgent, 'Edg') !== false) {
            $result['browser'] = 'Edge';
        } elseif (stripos($userAgent, 'MSIE') !== false || stripos($userAgent, 'Trident') !== false) {
            $result['browser'] = 'Internet Explorer';
        }

        // Simple platform detection
        if (stripos($userAgent, 'Windows') !== false) {
            $result['platform'] = 'Windows';
        } elseif (stripos($userAgent, 'Mac OS') !== false || stripos($userAgent, 'Macintosh') !== false) {
            $result['platform'] = 'macOS';
        } elseif (stripos($userAgent, 'Linux') !== false && stripos($userAgent, 'Android') === false) {
            $result['platform'] = 'Linux';
        } elseif (stripos($userAgent, 'Android') !== false) {
            $result['platform'] = 'Android';
            $result['device_type'] = 'mobile';
        } elseif (stripos($userAgent, 'iPhone') !== false) {
            $result['platform'] = 'iOS';
            $result['device_type'] = 'mobile';
        } elseif (stripos($userAgent, 'iPad') !== false) {
            $result['platform'] = 'iOS';
            $result['device_type'] = 'tablet';
        }

        // Detect mobile/tablet
        if (stripos($userAgent, 'Mobile') !== false && $result['device_type'] === 'desktop') {
            $result['device_type'] = 'mobile';
        } elseif (stripos($userAgent, 'Tablet') !== false) {
            $result['device_type'] = 'tablet';
        }

        return $result;
    }

    /**
     * Get device icon
     */
    public function getDeviceIconAttribute(): string
    {
        return match ($this->device_type) {
            'mobile' => 'M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z',
            'tablet' => 'M12 18h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z',
            default => 'M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
        };
    }

    /**
     * Clean up old sessions
     */
    public static function cleanup(int $daysOld = 30): int
    {
        return self::where('last_activity_at', '<', now()->subDays($daysOld))->delete();
    }

    /**
     * Invalidate all sessions for a user except current
     */
    public static function invalidateOtherSessions(User $user, ?int $exceptSessionId = null): int
    {
        $query = self::where('user_id', $user->id);
        
        if ($exceptSessionId) {
            $query->where('id', '!=', $exceptSessionId);
        }

        return $query->delete();
    }
}
