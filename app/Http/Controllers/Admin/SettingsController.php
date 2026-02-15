<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class SettingsController extends Controller
{
    /**
     * Display the settings dashboard
     */
    public function index()
    {
        $settingsGrouped = SystemSetting::getAllGrouped();

        return view('admin.settings.index', compact('settingsGrouped'));
    }

    /**
     * Show general settings form
     */
    public function general()
    {
        $settings = SystemSetting::getGroup(SystemSetting::GROUP_GENERAL);

        return view('admin.settings.general', compact('settings'));
    }

    /**
     * Update general settings
     */
    public function updateGeneral(Request $request)
    {
        $validated = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'company_logo' => ['nullable', 'image', 'max:2048'],
        ]);

        $oldCompanyName = SystemSetting::getValue(SystemSetting::KEY_COMPANY_NAME);
        
        SystemSetting::setValue(
            SystemSetting::KEY_COMPANY_NAME,
            $validated['company_name'],
            SystemSetting::GROUP_GENERAL,
            'string'
        );

        if ($oldCompanyName !== $validated['company_name']) {
            ActivityLog::logSettingChange(
                SystemSetting::KEY_COMPANY_NAME,
                $oldCompanyName,
                $validated['company_name']
            );
        }

        // Handle logo upload
        if ($request->hasFile('company_logo')) {
            $path = $request->file('company_logo')->store('settings', 'public');
            $oldLogo = SystemSetting::getValue(SystemSetting::KEY_COMPANY_LOGO);
            
            SystemSetting::setValue(
                SystemSetting::KEY_COMPANY_LOGO,
                $path,
                SystemSetting::GROUP_GENERAL,
                'string'
            );

            ActivityLog::logSettingChange(
                SystemSetting::KEY_COMPANY_LOGO,
                $oldLogo,
                $path
            );
        }

        return back()->with('success', 'General settings updated successfully.');
    }

    /**
     * Show currency settings form
     */
    public function currency()
    {
        $settings = SystemSetting::getGroup(SystemSetting::GROUP_CURRENCY);

        // Available currencies
        $currencies = [
            'UGX' => 'Ugandan Shilling (UGX)',
            'USD' => 'US Dollar (USD)',
            'EUR' => 'Euro (EUR)',
            'GBP' => 'British Pound (GBP)',
            'KES' => 'Kenyan Shilling (KES)',
            'TZS' => 'Tanzanian Shilling (TZS)',
            'RWF' => 'Rwandan Franc (RWF)',
        ];

        return view('admin.settings.currency', compact('settings', 'currencies'));
    }

    /**
     * Update currency settings
     */
    public function updateCurrency(Request $request)
    {
        $validated = $request->validate([
            'default_currency' => ['required', 'string', 'max:3'],
            'exchange_rate_usd' => ['required', 'numeric', 'min:0'],
        ]);

        foreach ($validated as $key => $value) {
            $oldValue = SystemSetting::getValue($key);
            
            SystemSetting::setValue(
                $key,
                $value,
                SystemSetting::GROUP_CURRENCY,
                is_numeric($value) ? 'decimal' : 'string'
            );

            if ($oldValue != $value) {
                ActivityLog::logSettingChange($key, $oldValue, $value);
            }
        }

        return back()->with('success', 'Currency settings updated successfully.');
    }

    /**
     * Show fiscal year settings form
     */
    public function fiscal()
    {
        $settings = SystemSetting::getGroup(SystemSetting::GROUP_FISCAL);

        return view('admin.settings.fiscal', compact('settings'));
    }

    /**
     * Update fiscal year settings
     */
    public function updateFiscal(Request $request)
    {
        $validated = $request->validate([
            'fiscal_year_start' => ['required', 'date_format:m-d'],
            'fiscal_year_end' => ['required', 'date_format:m-d'],
        ]);

        foreach ($validated as $key => $value) {
            $oldValue = SystemSetting::getValue($key);
            
            SystemSetting::setValue(
                $key,
                $value,
                SystemSetting::GROUP_FISCAL,
                'string'
            );

            if ($oldValue != $value) {
                ActivityLog::logSettingChange($key, $oldValue, $value);
            }
        }

        return back()->with('success', 'Fiscal year settings updated successfully.');
    }

    /**
     * Show procurement settings form
     */
    public function procurement()
    {
        $settings = SystemSetting::getGroup(SystemSetting::GROUP_PROCUREMENT);

        return view('admin.settings.procurement', compact('settings'));
    }

    /**
     * Update procurement settings
     */
    public function updateProcurement(Request $request)
    {
        $validated = $request->validate([
            'low_stock_threshold' => ['required', 'integer', 'min:0'],
            'po_prefix' => ['required', 'string', 'max:10'],
            'rfq_prefix' => ['required', 'string', 'max:10'],
            'req_prefix' => ['required', 'string', 'max:10'],
        ]);

        foreach ($validated as $key => $value) {
            $oldValue = SystemSetting::getValue($key);
            $type = is_int($value) ? 'integer' : 'string';
            
            SystemSetting::setValue(
                $key,
                $value,
                SystemSetting::GROUP_PROCUREMENT,
                $type
            );

            if ($oldValue != $value) {
                ActivityLog::logSettingChange($key, $oldValue, $value);
            }
        }

        return back()->with('success', 'Procurement settings updated successfully.');
    }

    /**
     * Show notification settings form
     */
    public function notifications()
    {
        $settings = SystemSetting::getGroup(SystemSetting::GROUP_NOTIFICATIONS);

        return view('admin.settings.notifications', compact('settings'));
    }

    /**
     * Update notification settings
     */
    public function updateNotifications(Request $request)
    {
        $validated = $request->validate([
            'email_notifications_enabled' => ['boolean'],
            'low_stock_alerts_enabled' => ['boolean'],
            'budget_alerts_enabled' => ['boolean'],
            'approval_reminders_enabled' => ['boolean'],
            'reminder_frequency_days' => ['integer', 'min:1', 'max:30'],
        ]);

        foreach ($validated as $key => $value) {
            $oldValue = SystemSetting::getValue($key);
            $type = is_bool($value) || in_array($key, ['email_notifications_enabled', 'low_stock_alerts_enabled', 'budget_alerts_enabled', 'approval_reminders_enabled']) 
                ? 'boolean' 
                : 'integer';
            
            SystemSetting::setValue(
                $key,
                $value,
                SystemSetting::GROUP_NOTIFICATIONS,
                $type
            );

            if ($oldValue != $value) {
                ActivityLog::logSettingChange($key, $oldValue, $value);
            }
        }

        return back()->with('success', 'Notification settings updated successfully.');
    }

    /**
     * Show email settings form
     */
    public function email()
    {
        $settings = SystemSetting::getGroup(SystemSetting::GROUP_EMAIL);

        return view('admin.settings.email', compact('settings'));
    }

    /**
     * Update email settings
     */
    public function updateEmail(Request $request)
    {
        $validated = $request->validate([
            'mail_from_address' => ['required', 'email'],
            'mail_from_name' => ['required', 'string', 'max:255'],
            'mail_host' => ['nullable', 'string', 'max:255'],
            'mail_port' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'mail_username' => ['nullable', 'string', 'max:255'],
            'mail_password' => ['nullable', 'string', 'max:255'],
            'mail_encryption' => ['nullable', 'in:tls,ssl,null'],
        ]);

        foreach ($validated as $key => $value) {
            $oldValue = SystemSetting::getValue($key);
            $isEncrypted = in_array($key, ['mail_password']);
            
            $setting = SystemSetting::firstOrNew(['key' => $key]);
            $setting->group = SystemSetting::GROUP_EMAIL;
            $setting->type = 'string';
            $setting->is_encrypted = $isEncrypted;
            
            if ($isEncrypted) {
                $setting->value = $value ? \Illuminate\Support\Facades\Crypt::encryptString($value) : null;
            } else {
                $setting->value = $value;
            }
            
            $setting->updated_by = Auth::id();
            $setting->save();

            Cache::forget("setting_{$key}");

            if ($oldValue != $value) {
                ActivityLog::logSettingChange(
                    $key,
                    $isEncrypted ? '[hidden]' : $oldValue,
                    $isEncrypted ? '[hidden]' : $value
                );
            }
        }

        Cache::forget('settings_group_' . SystemSetting::GROUP_EMAIL);

        return back()->with('success', 'Email settings updated successfully.');
    }

    /**
     * Test email configuration
     */
    public function testEmail(Request $request)
    {
        $validated = $request->validate([
            'test_email' => ['required', 'email'],
        ]);

        try {
            \Illuminate\Support\Facades\Mail::raw(
                'This is a test email from the WeTu Procurement System to verify your email configuration.',
                function ($message) use ($validated) {
                    $message->to($validated['test_email'])
                        ->subject('Test Email - WeTu Procurement System');
                }
            );

            return back()->with('success', 'Test email sent successfully to ' . $validated['test_email']);
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to send test email: ' . $e->getMessage());
        }
    }

    /**
     * Show approval settings form
     */
    public function approvals()
    {
        $settings = SystemSetting::getGroup(SystemSetting::GROUP_APPROVAL);

        return view('admin.settings.approvals', compact('settings'));
    }

    /**
     * Update approval settings
     */
    public function updateApprovals(Request $request)
    {
        $validated = $request->validate([
            'auto_approve_below' => ['nullable', 'numeric', 'min:0'],
            'require_quotes_above' => ['nullable', 'numeric', 'min:0'],
            'min_quotes_required' => ['integer', 'min:1', 'max:10'],
            'approval_timeout_days' => ['integer', 'min:1', 'max:90'],
        ]);

        foreach ($validated as $key => $value) {
            $oldValue = SystemSetting::getValue($key);
            $type = is_int($value) ? 'integer' : 'decimal';
            
            SystemSetting::setValue(
                $key,
                $value,
                SystemSetting::GROUP_APPROVAL,
                $type
            );

            if ($oldValue != $value) {
                ActivityLog::logSettingChange($key, $oldValue, $value);
            }
        }

        return back()->with('success', 'Approval settings updated successfully.');
    }

    /**
     * Clear application cache
     */
    public function clearCache()
    {
        Cache::flush();

        ActivityLog::log(
            'cache_cleared',
            'Application cache was cleared',
            ['cleared_by' => Auth::user()->name]
        );

        return back()->with('success', 'Application cache cleared successfully.');
    }

    /**
     * Seed default settings
     */
    public function seedDefaults()
    {
        SystemSetting::seedDefaults();

        ActivityLog::log(
            'settings_seeded',
            'Default settings were seeded',
            ['seeded_by' => Auth::user()->name]
        );

        return back()->with('success', 'Default settings seeded successfully.');
    }
}
