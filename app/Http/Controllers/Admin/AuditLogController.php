<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    /**
     * Display audit logs (model-level changes)
     */
    public function auditLogs(Request $request)
    {
        $query = AuditLog::with('user')
            ->latest('created_at');

        // Filter by user
        if ($userId = $request->get('user_id')) {
            $query->where('user_id', $userId);
        }

        // Filter by action
        if ($action = $request->get('action')) {
            $query->where('action', $action);
        }

        // Filter by model type
        if ($modelType = $request->get('model_type')) {
            $query->where('auditable_type', $modelType);
        }

        // Filter by date range
        if ($startDate = $request->get('start_date')) {
            $query->whereDate('created_at', '>=', $startDate);
        }

        if ($endDate = $request->get('end_date')) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        $logs = $query->paginate(50)->withQueryString();

        // Get filter options
        $users = User::orderBy('name')->get(['id', 'name']);
        $actions = [
            AuditLog::ACTION_CREATED => 'Created',
            AuditLog::ACTION_UPDATED => 'Updated',
            AuditLog::ACTION_DELETED => 'Deleted',
            AuditLog::ACTION_APPROVED => 'Approved',
            AuditLog::ACTION_REJECTED => 'Rejected',
            AuditLog::ACTION_SUBMITTED => 'Submitted',
            AuditLog::ACTION_ACTIVATED => 'Activated',
            AuditLog::ACTION_CLOSED => 'Closed',
        ];
        $modelTypes = $this->getAuditableTypes();

        // Stats
        $stats = [
            'today' => AuditLog::whereDate('created_at', today())->count(),
            'this_week' => AuditLog::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'this_month' => AuditLog::whereMonth('created_at', now()->month)->count(),
        ];

        return view('admin.logs.audit', compact('logs', 'users', 'actions', 'modelTypes', 'stats'));
    }

    /**
     * Display activity logs (system-wide events)
     */
    public function activityLogs(Request $request)
    {
        $query = ActivityLog::with('user')
            ->latest('created_at');

        // Filter by user
        if ($userId = $request->get('user_id')) {
            $query->where('user_id', $userId);
        }

        // Filter by type
        if ($type = $request->get('type')) {
            $query->where('type', $type);
        }

        // Filter by date range
        if ($startDate = $request->get('start_date')) {
            $query->whereDate('created_at', '>=', $startDate);
        }

        if ($endDate = $request->get('end_date')) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        // Search in description
        if ($search = $request->get('search')) {
            $query->where('description', 'like', "%{$search}%");
        }

        $logs = $query->paginate(50)->withQueryString();

        // Get filter options
        $users = User::orderBy('name')->get(['id', 'name']);
        $types = [
            ActivityLog::TYPE_LOGIN => 'Login',
            ActivityLog::TYPE_LOGOUT => 'Logout',
            ActivityLog::TYPE_LOGIN_FAILED => 'Failed Login',
            ActivityLog::TYPE_PASSWORD_CHANGE => 'Password Changed',
            ActivityLog::TYPE_PASSWORD_RESET => 'Password Reset',
            ActivityLog::TYPE_ROLE_ASSIGNED => 'Role Assigned',
            ActivityLog::TYPE_ROLE_REMOVED => 'Role Removed',
            ActivityLog::TYPE_USER_CREATED => 'User Created',
            ActivityLog::TYPE_USER_UPDATED => 'User Updated',
            ActivityLog::TYPE_USER_DELETED => 'User Deleted',
            ActivityLog::TYPE_USER_ACTIVATED => 'User Activated',
            ActivityLog::TYPE_USER_DEACTIVATED => 'User Deactivated',
            ActivityLog::TYPE_SETTING_CHANGED => 'Setting Changed',
            ActivityLog::TYPE_BULK_ACTION => 'Bulk Action',
            ActivityLog::TYPE_EXPORT => 'Export',
            ActivityLog::TYPE_IMPORT => 'Import',
        ];

        // Stats
        $stats = [
            'logins_today' => ActivityLog::ofType(ActivityLog::TYPE_LOGIN)
                ->whereDate('created_at', today())->count(),
            'failed_logins_today' => ActivityLog::ofType(ActivityLog::TYPE_LOGIN_FAILED)
                ->whereDate('created_at', today())->count(),
            'activities_today' => ActivityLog::whereDate('created_at', today())->count(),
        ];

        return view('admin.logs.activity', compact('logs', 'users', 'types', 'stats'));
    }

    /**
     * Show a single audit log entry
     */
    public function showAuditLog(AuditLog $auditLog)
    {
        $auditLog->load('user');

        // Try to load the related model if it still exists
        $relatedModel = null;
        if ($auditLog->auditable_id) {
            try {
                $relatedModel = $auditLog->auditable;
            } catch (\Exception $e) {
                // Model may have been deleted
            }
        }

        return view('admin.logs.audit-show', compact('auditLog', 'relatedModel'));
    }

    /**
     * Show a single activity log entry
     */
    public function showActivityLog(ActivityLog $activityLog)
    {
        $activityLog->load('user');

        return view('admin.logs.activity-show', compact('activityLog'));
    }

    /**
     * Export audit logs
     */
    public function exportAuditLogs(Request $request)
    {
        $query = AuditLog::with('user')
            ->latest('created_at');

        // Apply same filters as index
        if ($userId = $request->get('user_id')) {
            $query->where('user_id', $userId);
        }
        if ($action = $request->get('action')) {
            $query->where('action', $action);
        }
        if ($startDate = $request->get('start_date')) {
            $query->whereDate('created_at', '>=', $startDate);
        }
        if ($endDate = $request->get('end_date')) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        $logs = $query->get();

        $filename = 'audit_logs_' . now()->format('Y-m-d_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($logs) {
            $file = fopen('php://output', 'w');
            
            fputcsv($file, [
                'ID', 'User', 'Action', 'Model Type', 'Model ID', 
                'IP Address', 'Notes', 'Created At'
            ]);

            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->id,
                    $log->user?->name ?? 'System',
                    $log->action,
                    class_basename($log->auditable_type),
                    $log->auditable_id,
                    $log->ip_address,
                    $log->notes,
                    $log->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        ActivityLog::log(ActivityLog::TYPE_EXPORT, "Audit logs exported ({$logs->count()} records)");

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export activity logs
     */
    public function exportActivityLogs(Request $request)
    {
        $query = ActivityLog::with('user')
            ->latest('created_at');

        // Apply filters
        if ($userId = $request->get('user_id')) {
            $query->where('user_id', $userId);
        }
        if ($type = $request->get('type')) {
            $query->where('type', $type);
        }
        if ($startDate = $request->get('start_date')) {
            $query->whereDate('created_at', '>=', $startDate);
        }
        if ($endDate = $request->get('end_date')) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        $logs = $query->get();

        $filename = 'activity_logs_' . now()->format('Y-m-d_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($logs) {
            $file = fopen('php://output', 'w');
            
            fputcsv($file, [
                'ID', 'User', 'Type', 'Description', 
                'IP Address', 'Created At'
            ]);

            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->id,
                    $log->user?->name ?? 'System',
                    $log->type,
                    $log->description,
                    $log->ip_address,
                    $log->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        ActivityLog::log(ActivityLog::TYPE_EXPORT, "Activity logs exported ({$logs->count()} records)");

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get list of auditable model types
     */
    protected function getAuditableTypes(): array
    {
        return AuditLog::select('auditable_type')
            ->distinct()
            ->pluck('auditable_type')
            ->mapWithKeys(function ($type) {
                return [$type => class_basename($type)];
            })
            ->toArray();
    }

    /**
     * Dashboard summary for logs
     */
    public function dashboard()
    {
        // Recent audit logs
        $recentAuditLogs = AuditLog::with('user')
            ->latest('created_at')
            ->take(10)
            ->get();

        // Recent activity logs
        $recentActivityLogs = ActivityLog::with('user')
            ->latest('created_at')
            ->take(10)
            ->get();

        // Activity by type (last 7 days)
        $activityByType = ActivityLog::selectRaw('type, COUNT(*) as count')
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('type')
            ->orderByDesc('count')
            ->get();

        // Audit by action (last 7 days)
        $auditByAction = AuditLog::selectRaw('action, COUNT(*) as count')
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('action')
            ->orderByDesc('count')
            ->get();

        // Top active users (last 7 days)
        $topActiveUsers = ActivityLog::selectRaw('user_id, COUNT(*) as activity_count')
            ->where('created_at', '>=', now()->subDays(7))
            ->whereNotNull('user_id')
            ->groupBy('user_id')
            ->orderByDesc('activity_count')
            ->take(10)
            ->with('user:id,name')
            ->get();

        // Failed logins (last 24 hours)
        $failedLogins = ActivityLog::ofType(ActivityLog::TYPE_LOGIN_FAILED)
            ->where('created_at', '>=', now()->subDay())
            ->latest('created_at')
            ->take(10)
            ->get();

        return view('admin.logs.dashboard', compact(
            'recentAuditLogs',
            'recentActivityLogs',
            'activityByType',
            'auditByAction',
            'topActiveUsers',
            'failedLogins'
        ));
    }
}
