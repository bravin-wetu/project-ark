<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\AuditLog;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RoleManagementController extends Controller
{
    /**
     * All available permissions in the system
     */
    protected array $availablePermissions = [
        // Dashboard
        'dashboard.view' => 'View Dashboard',

        // Requisitions
        'requisitions.view' => 'View Requisitions',
        'requisitions.create' => 'Create Requisitions',
        'requisitions.edit' => 'Edit Requisitions',
        'requisitions.delete' => 'Delete Requisitions',
        'requisitions.approve' => 'Approve Requisitions',

        // RFQs
        'rfqs.view' => 'View RFQs',
        'rfqs.create' => 'Create RFQs',
        'rfqs.edit' => 'Edit RFQs',
        'rfqs.delete' => 'Delete RFQs',
        'rfqs.approve' => 'Approve RFQs',

        // Purchase Orders
        'purchase_orders.view' => 'View Purchase Orders',
        'purchase_orders.create' => 'Create Purchase Orders',
        'purchase_orders.edit' => 'Edit Purchase Orders',
        'purchase_orders.delete' => 'Delete Purchase Orders',
        'purchase_orders.approve' => 'Approve Purchase Orders',

        // Goods Receipt
        'goods_receipt.view' => 'View Goods Receipt',
        'goods_receipt.create' => 'Receive Goods',
        'goods_receipt.edit' => 'Edit Goods Receipt',

        // Vendors
        'vendors.view' => 'View Vendors',
        'vendors.create' => 'Create Vendors',
        'vendors.edit' => 'Edit Vendors',
        'vendors.delete' => 'Delete Vendors',

        // Items & Inventory
        'items.view' => 'View Items',
        'items.create' => 'Create Items',
        'items.edit' => 'Edit Items',
        'items.delete' => 'Delete Items',
        'inventory.manage' => 'Manage Inventory',

        // Projects
        'projects.view' => 'View Projects',
        'projects.create' => 'Create Projects',
        'projects.edit' => 'Edit Projects',
        'projects.delete' => 'Delete Projects',

        // Budgets
        'budgets.view' => 'View Budgets',
        'budgets.create' => 'Create Budgets',
        'budgets.edit' => 'Edit Budgets',
        'budgets.delete' => 'Delete Budgets',
        'budgets.approve_revisions' => 'Approve Budget Revisions',
        'budgets.lock' => 'Lock/Unlock Budgets',

        // Departments
        'departments.view' => 'View Departments',
        'departments.create' => 'Create Departments',
        'departments.edit' => 'Edit Departments',
        'departments.delete' => 'Delete Departments',

        // Reports
        'reports.view' => 'View Reports',
        'reports.export' => 'Export Reports',

        // Admin
        'admin.users' => 'Manage Users',
        'admin.roles' => 'Manage Roles',
        'admin.settings' => 'Manage Settings',
        'admin.audit_logs' => 'View Audit Logs',
        'admin.activity_logs' => 'View Activity Logs',

        // Notifications
        'notifications.view' => 'View Notifications',
        'notifications.manage' => 'Manage Notifications',
    ];

    /**
     * Display a listing of roles
     */
    public function index()
    {
        $roles = Role::withCount('users')
            ->orderBy('name')
            ->get();

        return view('admin.roles.index', compact('roles'));
    }

    /**
     * Show the form for creating a new role
     */
    public function create()
    {
        $permissions = $this->getGroupedPermissions();
        
        return view('admin.roles.create', compact('permissions'));
    }

    /**
     * Store a newly created role
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:roles', 'regex:/^[a-z0-9-]+$/'],
            'description' => ['nullable', 'string', 'max:500'],
            'permissions' => ['array'],
            'permissions.*' => ['string', Rule::in(array_keys($this->availablePermissions))],
            'is_active' => ['boolean'],
        ]);

        $role = Role::create([
            'name' => $validated['name'],
            'slug' => $validated['slug'],
            'description' => $validated['description'] ?? null,
            'permissions' => $validated['permissions'] ?? [],
            'is_active' => $validated['is_active'] ?? true,
        ]);

        // Log audit
        AuditLog::log(AuditLog::ACTION_CREATED, $role, null, $role->toArray());

        ActivityLog::log(
            'role_created',
            "Role '{$role->name}' was created",
            ['role_id' => $role->id]
        );

        return redirect()
            ->route('admin.roles.show', $role)
            ->with('success', 'Role created successfully.');
    }

    /**
     * Display the specified role
     */
    public function show(Role $role)
    {
        $role->loadCount('users');
        
        // Get users with this role
        $users = User::whereHas('roles', function ($q) use ($role) {
            $q->where('roles.id', $role->id);
        })->take(20)->get();

        // Map permissions to their labels
        $permissionLabels = collect($role->permissions ?? [])
            ->mapWithKeys(fn ($p) => [$p => $this->availablePermissions[$p] ?? $p]);

        $allPermissions = $this->getGroupedPermissions();

        return view('admin.roles.show', compact('role', 'users', 'permissionLabels', 'allPermissions'));
    }

    /**
     * Show the form for editing the specified role
     */
    public function edit(Role $role)
    {
        $permissions = $this->getGroupedPermissions();
        
        return view('admin.roles.edit', compact('role', 'permissions'));
    }

    /**
     * Update the specified role
     */
    public function update(Request $request, Role $role)
    {
        // Prevent editing the admin role's core permissions
        $isAdminRole = $role->slug === Role::ADMIN;

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('roles')->ignore($role->id), 'regex:/^[a-z0-9-]+$/'],
            'description' => ['nullable', 'string', 'max:500'],
            'permissions' => ['array'],
            'permissions.*' => ['string', Rule::in(array_keys($this->availablePermissions))],
            'is_active' => ['boolean'],
        ]);

        $oldValues = $role->toArray();

        // For admin role, always keep full permissions
        $permissions = $isAdminRole ? ['*'] : ($validated['permissions'] ?? []);

        $role->update([
            'name' => $validated['name'],
            'slug' => $isAdminRole ? Role::ADMIN : $validated['slug'], // Prevent changing admin slug
            'description' => $validated['description'] ?? null,
            'permissions' => $permissions,
            'is_active' => $isAdminRole ? true : ($validated['is_active'] ?? true), // Admin always active
        ]);

        // Log audit
        AuditLog::log(AuditLog::ACTION_UPDATED, $role, $oldValues, $role->toArray());

        ActivityLog::log(
            'role_updated',
            "Role '{$role->name}' was updated",
            ['role_id' => $role->id, 'changes' => array_diff_assoc($role->toArray(), $oldValues)]
        );

        return redirect()
            ->route('admin.roles.show', $role)
            ->with('success', 'Role updated successfully.');
    }

    /**
     * Remove the specified role
     */
    public function destroy(Role $role)
    {
        // Prevent deletion of system roles
        $systemRoles = [Role::ADMIN, Role::STAFF];
        if (in_array($role->slug, $systemRoles)) {
            return back()->with('error', 'System roles cannot be deleted.');
        }

        // Check if role has users
        if ($role->users()->count() > 0) {
            return back()->with('error', 'Cannot delete role with assigned users. Please reassign users first.');
        }

        $roleData = $role->toArray();
        $roleName = $role->name;

        $role->delete();

        // Log audit
        AuditLog::log(AuditLog::ACTION_DELETED, $role, $roleData, null);

        ActivityLog::log(
            'role_deleted',
            "Role '{$roleName}' was deleted",
            ['role_data' => $roleData]
        );

        return redirect()
            ->route('admin.roles.index')
            ->with('success', 'Role deleted successfully.');
    }

    /**
     * Toggle role active status
     */
    public function toggleStatus(Role $role)
    {
        // Prevent deactivating admin role
        if ($role->slug === Role::ADMIN) {
            return back()->with('error', 'Cannot deactivate the administrator role.');
        }

        $role->update(['is_active' => !$role->is_active]);

        $status = $role->is_active ? 'activated' : 'deactivated';

        ActivityLog::log(
            'role_updated',
            "Role '{$role->name}' was {$status}",
            ['role_id' => $role->id]
        );

        return back()->with('success', "Role {$status} successfully.");
    }

    /**
     * Clone a role
     */
    public function clone(Role $role)
    {
        $newRole = Role::create([
            'name' => $role->name . ' (Copy)',
            'slug' => $role->slug . '-copy-' . time(),
            'description' => $role->description,
            'permissions' => $role->permissions,
            'is_active' => false, // Start as inactive
        ]);

        ActivityLog::log(
            'role_created',
            "Role '{$newRole->name}' was cloned from '{$role->name}'",
            ['source_role_id' => $role->id, 'new_role_id' => $newRole->id]
        );

        return redirect()
            ->route('admin.roles.edit', $newRole)
            ->with('success', 'Role cloned successfully. Please update the name and slug.');
    }

    /**
     * Bulk assign permission to multiple roles
     */
    public function bulkAssignPermission(Request $request)
    {
        $validated = $request->validate([
            'permission' => ['required', 'string', Rule::in(array_keys($this->availablePermissions))],
            'role_ids' => ['required', 'array'],
            'role_ids.*' => ['exists:roles,id'],
        ]);

        $roles = Role::whereIn('id', $validated['role_ids'])
            ->where('slug', '!=', Role::ADMIN) // Skip admin role
            ->get();

        $affectedCount = 0;

        foreach ($roles as $role) {
            $permissions = $role->permissions ?? [];
            if (!in_array($validated['permission'], $permissions)) {
                $permissions[] = $validated['permission'];
                $role->update(['permissions' => $permissions]);
                $affectedCount++;
            }
        }

        ActivityLog::log(
            ActivityLog::TYPE_BULK_ACTION,
            "Permission '{$validated['permission']}' assigned to {$affectedCount} roles",
            ['permission' => $validated['permission'], 'role_ids' => $validated['role_ids']]
        );

        return back()->with('success', "Permission assigned to {$affectedCount} role(s).");
    }

    /**
     * Get permissions grouped by category
     */
    protected function getGroupedPermissions(): array
    {
        $grouped = [];

        foreach ($this->availablePermissions as $key => $label) {
            $parts = explode('.', $key);
            $group = ucfirst($parts[0]);
            
            $grouped[$group][$key] = $label;
        }

        return $grouped;
    }

    /**
     * API: Get all available permissions
     */
    public function apiPermissions()
    {
        return response()->json([
            'permissions' => $this->availablePermissions,
            'grouped' => $this->getGroupedPermissions(),
        ]);
    }
}
