<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationSetting extends Model
{
    protected $fillable = [
        'user_id',
        // Email preferences
        'email_requisition_submitted',
        'email_requisition_approved',
        'email_requisition_rejected',
        'email_po_created',
        'email_po_approved',
        'email_po_sent',
        'email_goods_received',
        'email_budget_threshold',
        'email_stock_low',
        'email_asset_maintenance',
        // App preferences
        'app_requisition_submitted',
        'app_requisition_approved',
        'app_requisition_rejected',
        'app_po_created',
        'app_po_approved',
        'app_po_sent',
        'app_goods_received',
        'app_budget_threshold',
        'app_stock_low',
        'app_asset_maintenance',
        // Digest
        'digest_frequency',
        'digest_time',
    ];

    protected $casts = [
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
    ];

    /**
     * Get the user that owns the settings
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if a specific notification type is enabled for a channel
     */
    public function isEnabled(string $type, string $channel = 'app'): bool
    {
        $field = "{$channel}_{$type}";
        return $this->$field ?? true; // Default to true if field doesn't exist
    }

    /**
     * Get default settings for a new user
     */
    public static function getDefaults(): array
    {
        return [
            'email_requisition_submitted' => true,
            'email_requisition_approved' => true,
            'email_requisition_rejected' => true,
            'email_po_created' => true,
            'email_po_approved' => true,
            'email_po_sent' => true,
            'email_goods_received' => true,
            'email_budget_threshold' => true,
            'email_stock_low' => true,
            'email_asset_maintenance' => true,
            'app_requisition_submitted' => true,
            'app_requisition_approved' => true,
            'app_requisition_rejected' => true,
            'app_po_created' => true,
            'app_po_approved' => true,
            'app_po_sent' => true,
            'app_goods_received' => true,
            'app_budget_threshold' => true,
            'app_stock_low' => true,
            'app_asset_maintenance' => true,
            'digest_frequency' => 'none',
            'digest_time' => '08:00:00',
        ];
    }
}
