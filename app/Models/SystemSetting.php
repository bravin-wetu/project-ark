<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;

class SystemSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'group',
        'key',
        'value',
        'type',
        'description',
        'is_encrypted',
        'updated_by',
    ];

    protected $casts = [
        'is_encrypted' => 'boolean',
    ];

    // Setting groups
    const GROUP_GENERAL = 'general';
    const GROUP_EMAIL = 'email';
    const GROUP_FISCAL = 'fiscal';
    const GROUP_CURRENCY = 'currency';
    const GROUP_NOTIFICATIONS = 'notifications';
    const GROUP_PROCUREMENT = 'procurement';
    const GROUP_APPROVAL = 'approval';

    // Common setting keys
    const KEY_COMPANY_NAME = 'company_name';
    const KEY_COMPANY_LOGO = 'company_logo';
    const KEY_DEFAULT_CURRENCY = 'default_currency';
    const KEY_FISCAL_YEAR_START = 'fiscal_year_start';
    const KEY_FISCAL_YEAR_END = 'fiscal_year_end';
    const KEY_EXCHANGE_RATE_USD = 'exchange_rate_usd';
    const KEY_LOW_STOCK_THRESHOLD = 'low_stock_threshold';
    const KEY_PO_PREFIX = 'po_prefix';
    const KEY_RFQ_PREFIX = 'rfq_prefix';
    const KEY_REQ_PREFIX = 'req_prefix';

    /**
     * User who last updated this setting
     */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get a setting value by key with caching
     */
    public static function getValue(string $key, mixed $default = null): mixed
    {
        return Cache::remember("setting_{$key}", 3600, function () use ($key, $default) {
            $setting = self::where('key', $key)->first();
            
            if (!$setting) {
                return $default;
            }

            $value = $setting->is_encrypted 
                ? Crypt::decryptString($setting->value) 
                : $setting->value;

            return self::castValue($value, $setting->type);
        });
    }

    /**
     * Get all settings in a group
     */
    public static function getGroup(string $group): array
    {
        return Cache::remember("settings_group_{$group}", 3600, function () use ($group) {
            $settings = self::where('group', $group)->get();
            $result = [];

            foreach ($settings as $setting) {
                $value = $setting->is_encrypted 
                    ? Crypt::decryptString($setting->value) 
                    : $setting->value;
                
                $result[$setting->key] = self::castValue($value, $setting->type);
            }

            return $result;
        });
    }

    /**
     * Set a setting value
     */
    public static function setValue(string $key, mixed $value, ?string $group = null, ?string $type = null): self
    {
        $setting = self::firstOrNew(['key' => $key]);
        
        if ($group) {
            $setting->group = $group;
        }
        
        if ($type) {
            $setting->type = $type;
        }

        // Convert value based on type
        if ($setting->type === 'json' && is_array($value)) {
            $value = json_encode($value);
        } elseif ($setting->type === 'boolean') {
            $value = $value ? '1' : '0';
        } else {
            $value = (string) $value;
        }

        if ($setting->is_encrypted) {
            $value = Crypt::encryptString($value);
        }

        $setting->value = $value;
        $setting->updated_by = auth()->id();
        $setting->save();

        // Clear cache
        Cache::forget("setting_{$key}");
        if ($setting->group) {
            Cache::forget("settings_group_{$setting->group}");
        }

        return $setting;
    }

    /**
     * Cast value to appropriate type
     */
    protected static function castValue(mixed $value, string $type): mixed
    {
        return match ($type) {
            'integer' => (int) $value,
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'json' => json_decode($value, true),
            'float', 'decimal' => (float) $value,
            default => $value,
        };
    }

    /**
     * Get all settings grouped
     */
    public static function getAllGrouped(): array
    {
        $settings = self::all();
        $grouped = [];

        foreach ($settings as $setting) {
            $value = $setting->is_encrypted 
                ? '[encrypted]' 
                : self::castValue($setting->value, $setting->type);
            
            $grouped[$setting->group][$setting->key] = [
                'id' => $setting->id,
                'value' => $value,
                'type' => $setting->type,
                'description' => $setting->description,
                'is_encrypted' => $setting->is_encrypted,
            ];
        }

        return $grouped;
    }

    /**
     * Seed default settings
     */
    public static function seedDefaults(): void
    {
        $defaults = [
            // General settings
            ['group' => self::GROUP_GENERAL, 'key' => self::KEY_COMPANY_NAME, 'value' => 'WeTu Organization', 'type' => 'string', 'description' => 'Organization name displayed across the system'],
            ['group' => self::GROUP_GENERAL, 'key' => self::KEY_COMPANY_LOGO, 'value' => null, 'type' => 'string', 'description' => 'Path to organization logo'],
            
            // Currency settings
            ['group' => self::GROUP_CURRENCY, 'key' => self::KEY_DEFAULT_CURRENCY, 'value' => 'UGX', 'type' => 'string', 'description' => 'Default currency for transactions'],
            ['group' => self::GROUP_CURRENCY, 'key' => self::KEY_EXCHANGE_RATE_USD, 'value' => '3700', 'type' => 'decimal', 'description' => 'Exchange rate: 1 USD to local currency'],
            
            // Fiscal settings
            ['group' => self::GROUP_FISCAL, 'key' => self::KEY_FISCAL_YEAR_START, 'value' => '01-01', 'type' => 'string', 'description' => 'Fiscal year start date (MM-DD)'],
            ['group' => self::GROUP_FISCAL, 'key' => self::KEY_FISCAL_YEAR_END, 'value' => '12-31', 'type' => 'string', 'description' => 'Fiscal year end date (MM-DD)'],
            
            // Procurement settings
            ['group' => self::GROUP_PROCUREMENT, 'key' => self::KEY_LOW_STOCK_THRESHOLD, 'value' => '10', 'type' => 'integer', 'description' => 'Minimum stock level before alerts'],
            ['group' => self::GROUP_PROCUREMENT, 'key' => self::KEY_PO_PREFIX, 'value' => 'PO-', 'type' => 'string', 'description' => 'Purchase Order number prefix'],
            ['group' => self::GROUP_PROCUREMENT, 'key' => self::KEY_RFQ_PREFIX, 'value' => 'RFQ-', 'type' => 'string', 'description' => 'RFQ number prefix'],
            ['group' => self::GROUP_PROCUREMENT, 'key' => self::KEY_REQ_PREFIX, 'value' => 'REQ-', 'type' => 'string', 'description' => 'Requisition number prefix'],
        ];

        foreach ($defaults as $setting) {
            self::firstOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
