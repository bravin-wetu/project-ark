<?php

namespace App\Http\Controllers;

use App\Models\NotificationSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Display notifications inbox
     */
    public function index()
    {
        $notifications = Auth::user()
            ->notifications()
            ->latest()
            ->paginate(20);

        return view('notifications.index', compact('notifications'));
    }

    /**
     * Get notifications as JSON (for AJAX/dropdown)
     */
    public function getNotifications(Request $request)
    {
        $limit = $request->input('limit', 10);
        
        $notifications = Auth::user()
            ->notifications()
            ->latest()
            ->take($limit)
            ->get()
            ->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'type' => class_basename($notification->type),
                    'data' => $notification->data,
                    'read_at' => $notification->read_at,
                    'created_at' => $notification->created_at->diffForHumans(),
                    'created_at_full' => $notification->created_at->format('M d, Y H:i'),
                ];
            });

        $unreadCount = Auth::user()->unreadNotifications()->count();

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $unreadCount,
        ]);
    }

    /**
     * Mark a notification as read
     */
    public function markAsRead(string $id)
    {
        $notification = Auth::user()
            ->notifications()
            ->where('id', $id)
            ->first();

        if ($notification) {
            $notification->markAsRead();
        }

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Notification marked as read.');
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        Auth::user()->unreadNotifications->markAsRead();

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'All notifications marked as read.');
    }

    /**
     * Delete a notification
     */
    public function destroy(string $id)
    {
        Auth::user()
            ->notifications()
            ->where('id', $id)
            ->delete();

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Notification deleted.');
    }

    /**
     * Clear all read notifications
     */
    public function clearRead()
    {
        Auth::user()
            ->notifications()
            ->whereNotNull('read_at')
            ->delete();

        return back()->with('success', 'Read notifications cleared.');
    }

    /**
     * Display notification settings page
     */
    public function settings()
    {
        $settings = Auth::user()->getNotificationSettings();
        
        return view('notifications.settings', compact('settings'));
    }

    /**
     * Update notification settings
     */
    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            // Email preferences
            'email_requisition_submitted' => 'boolean',
            'email_requisition_approved' => 'boolean',
            'email_requisition_rejected' => 'boolean',
            'email_po_created' => 'boolean',
            'email_po_approved' => 'boolean',
            'email_po_sent' => 'boolean',
            'email_goods_received' => 'boolean',
            'email_budget_threshold' => 'boolean',
            'email_stock_low' => 'boolean',
            'email_asset_maintenance' => 'boolean',
            // App preferences
            'app_requisition_submitted' => 'boolean',
            'app_requisition_approved' => 'boolean',
            'app_requisition_rejected' => 'boolean',
            'app_po_created' => 'boolean',
            'app_po_approved' => 'boolean',
            'app_po_sent' => 'boolean',
            'app_goods_received' => 'boolean',
            'app_budget_threshold' => 'boolean',
            'app_stock_low' => 'boolean',
            'app_asset_maintenance' => 'boolean',
            // Digest
            'digest_frequency' => 'in:none,daily,weekly',
            'digest_time' => 'date_format:H:i',
        ]);

        // Convert checkbox values (present = true, absent = false)
        $settingsData = [];
        $booleanFields = [
            'email_requisition_submitted', 'email_requisition_approved', 'email_requisition_rejected',
            'email_po_created', 'email_po_approved', 'email_po_sent',
            'email_goods_received', 'email_budget_threshold', 'email_stock_low', 'email_asset_maintenance',
            'app_requisition_submitted', 'app_requisition_approved', 'app_requisition_rejected',
            'app_po_created', 'app_po_approved', 'app_po_sent',
            'app_goods_received', 'app_budget_threshold', 'app_stock_low', 'app_asset_maintenance',
        ];

        foreach ($booleanFields as $field) {
            $settingsData[$field] = $request->has($field);
        }

        $settingsData['digest_frequency'] = $validated['digest_frequency'] ?? 'none';
        $settingsData['digest_time'] = $validated['digest_time'] ?? '08:00';

        $settings = Auth::user()->getNotificationSettings();
        $settings->update($settingsData);

        return back()->with('success', 'Notification settings updated.');
    }
}
