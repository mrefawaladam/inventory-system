<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class SecurityLogService
{
    /**
     * Log security events
     */
    public static function log(string $event, array $context = []): void
    {
        $user = auth()->user();
        
        Log::channel('security')->info($event, [
            'user_id' => $user?->id,
            'user_email' => $user?->email ?? 'guest',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'url' => request()->fullUrl(),
            'method' => request()->method(),
            'timestamp' => now()->toIso8601String(),
            ...$context,
        ]);
    }

    /**
     * Log failed login attempts
     */
    public static function logFailedLogin(string $email, string $reason = 'Invalid credentials'): void
    {
        self::log('Failed login attempt', [
            'email' => $email,
            'reason' => $reason,
        ]);
    }

    /**
     * Log successful login
     */
    public static function logSuccessfulLogin(): void
    {
        self::log('Successful login');
    }

    /**
     * Log logout
     */
    public static function logLogout(): void
    {
        self::log('User logout');
    }

    /**
     * Log permission denied
     */
    public static function logPermissionDenied(string $permission, string $resource = null): void
    {
        self::log('Permission denied', [
            'permission' => $permission,
            'resource' => $resource,
        ]);
    }

    /**
     * Log suspicious activity
     */
    public static function logSuspiciousActivity(string $activity, array $context = []): void
    {
        self::log('Suspicious activity detected', [
            'activity' => $activity,
            ...$context,
        ]);
    }

    /**
     * Log data access
     */
    public static function logDataAccess(string $resource, string $action, $resourceId = null): void
    {
        self::log('Data access', [
            'resource' => $resource,
            'action' => $action,
            'resource_id' => $resourceId,
        ]);
    }
}

