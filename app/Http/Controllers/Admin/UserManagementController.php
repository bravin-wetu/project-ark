<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Department;
use App\Models\Hub;
use App\Models\Role;
use App\Models\User;
use App\Models\UserSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserManagementController extends Controller
{
    /**
     * Display a listing of users
     */
    public function index(Request $request)
    {
        $query = User::query()
            ->with(['department', 'hub', 'roles'])
            ->withCount(['requisitions', 'purchaseOrders']);

        // Search
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('employee_id', 'like', "%{$search}%");
            });
        }

        // Filter by department
        if ($departmentId = $request->get('department_id')) {
            $query->where('department_id', $departmentId);
        }

        // Filter by hub
        if ($hubId = $request->get('hub_id')) {
            $query->where('hub_id', $hubId);
        }

        // Filter by role
        if ($roleId = $request->get('role_id')) {
            $query->whereHas('roles', function ($q) use ($roleId) {
                $q->where('roles.id', $roleId);
            });
        }

        // Filter by status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        // Sorting
        $sortField = $request->get('sort', 'name');
        $sortDirection = $request->get('direction', 'asc');
        $query->orderBy($sortField, $sortDirection);

        $users = $query->paginate(20)->withQueryString();

        // Get filter options
        $departments = Department::orderBy('name')->get();
        $hubs = Hub::orderBy('name')->get();
        $roles = Role::where('is_active', true)->orderBy('name')->get();

        // Stats
        $stats = [
            'total_users' => User::count(),
            'active_users' => User::where('is_active', true)->count(),
            'inactive_users' => User::where('is_active', false)->count(),
            'new_this_month' => User::whereMonth('created_at', now()->month)->count(),
        ];

        return view('admin.users.index', compact('users', 'departments', 'hubs', 'roles', 'stats'));
    }

    /**
     * Show the form for creating a new user
     */
    public function create()
    {
        $departments = Department::orderBy('name')->get();
        $hubs = Hub::orderBy('name')->get();
        $roles = Role::where('is_active', true)->orderBy('name')->get();

        return view('admin.users.create', compact('departments', 'hubs', 'roles'));
    }

    /**
     * Store a newly created user
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'employee_id' => ['nullable', 'string', 'max:50', 'unique:users'],
            'phone' => ['nullable', 'string', 'max:20'],
            'job_title' => ['nullable', 'string', 'max:100'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'hub_id' => ['nullable', 'exists:hubs,id'],
            'roles' => ['array'],
            'roles.*' => ['exists:roles,id'],
            'is_active' => ['boolean'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'employee_id' => $validated['employee_id'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'job_title' => $validated['job_title'] ?? null,
            'department_id' => $validated['department_id'] ?? null,
            'hub_id' => $validated['hub_id'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        // Assign roles
        if (!empty($validated['roles'])) {
            $user->roles()->sync($validated['roles']);
        }

        // Log activity
        ActivityLog::log(
            ActivityLog::TYPE_USER_CREATED,
            "User {$user->name} was created",
            ['user_id' => $user->id, 'email' => $user->email]
        );

        return redirect()
            ->route('admin.users.show', $user)
            ->with('success', 'User created successfully.');
    }

    /**
     * Display the specified user
     */
    public function show(User $user)
    {
        $user->load(['department', 'hub', 'roles']);

        // Get user's recent activity
        $recentActivity = ActivityLog::where('user_id', $user->id)
            ->latest('created_at')
            ->take(20)
            ->get();

        // Get user's sessions
        $sessions = UserSession::where('user_id', $user->id)
            ->orderByDesc('last_activity_at')
            ->take(10)
            ->get();

        // Get statistics
        $stats = [
            'requisitions_count' => $user->requisitions()->count(),
            'purchase_orders_count' => $user->purchaseOrders()->count(),
            'pending_approvals' => 0, // Would need Approval model
            'last_login' => ActivityLog::where('user_id', $user->id)
                ->where('type', ActivityLog::TYPE_LOGIN)
                ->latest('created_at')
                ->first()?->created_at,
        ];

        return view('admin.users.show', compact('user', 'recentActivity', 'sessions', 'stats'));
    }

    /**
     * Show the form for editing the specified user
     */
    public function edit(User $user)
    {
        $departments = Department::orderBy('name')->get();
        $hubs = Hub::orderBy('name')->get();
        $roles = Role::where('is_active', true)->orderBy('name')->get();
        $userRoles = $user->roles->pluck('id')->toArray();

        return view('admin.users.edit', compact('user', 'departments', 'hubs', 'roles', 'userRoles'));
    }

    /**
     * Update the specified user
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'confirmed', Password::defaults()],
            'employee_id' => ['nullable', 'string', 'max:50', Rule::unique('users')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:20'],
            'job_title' => ['nullable', 'string', 'max:100'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'hub_id' => ['nullable', 'exists:hubs,id'],
            'roles' => ['array'],
            'roles.*' => ['exists:roles,id'],
            'is_active' => ['boolean'],
        ]);

        $oldData = $user->only(['name', 'email', 'employee_id', 'department_id', 'hub_id', 'is_active']);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'employee_id' => $validated['employee_id'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'job_title' => $validated['job_title'] ?? null,
            'department_id' => $validated['department_id'] ?? null,
            'hub_id' => $validated['hub_id'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        // Update password if provided
        if (!empty($validated['password'])) {
            $user->update(['password' => Hash::make($validated['password'])]);
            
            ActivityLog::log(
                ActivityLog::TYPE_PASSWORD_CHANGE,
                "Password was changed for {$user->name}",
                ['user_id' => $user->id, 'changed_by' => Auth::user()->name]
            );
        }

        // Sync roles
        $oldRoles = $user->roles->pluck('id')->toArray();
        $user->roles()->sync($validated['roles'] ?? []);

        // Log role changes
        $newRoles = $validated['roles'] ?? [];
        $addedRoles = array_diff($newRoles, $oldRoles);
        $removedRoles = array_diff($oldRoles, $newRoles);

        foreach ($addedRoles as $roleId) {
            $role = Role::find($roleId);
            if ($role) {
                ActivityLog::logRoleAssigned($user, $role);
            }
        }

        foreach ($removedRoles as $roleId) {
            $role = Role::find($roleId);
            if ($role) {
                ActivityLog::logRoleRemoved($user, $role);
            }
        }

        // Log user update
        ActivityLog::log(
            ActivityLog::TYPE_USER_UPDATED,
            "User {$user->name} was updated",
            ['user_id' => $user->id, 'old_data' => $oldData]
        );

        return redirect()
            ->route('admin.users.show', $user)
            ->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified user
     */
    public function destroy(User $user)
    {
        // Prevent self-deletion
        if ($user->id === Auth::id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $userName = $user->name;
        $userId = $user->id;

        // Soft delete the user
        $user->delete();

        // Log activity
        ActivityLog::log(
            ActivityLog::TYPE_USER_DELETED,
            "User {$userName} was deleted",
            ['user_id' => $userId, 'deleted_by' => Auth::user()->name]
        );

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User deleted successfully.');
    }

    /**
     * Toggle user active status
     */
    public function toggleStatus(User $user)
    {
        // Prevent self-deactivation
        if ($user->id === Auth::id()) {
            return back()->with('error', 'You cannot deactivate your own account.');
        }

        $user->update(['is_active' => !$user->is_active]);

        $type = $user->is_active ? ActivityLog::TYPE_USER_ACTIVATED : ActivityLog::TYPE_USER_DEACTIVATED;
        $action = $user->is_active ? 'activated' : 'deactivated';

        ActivityLog::log(
            $type,
            "User {$user->name} was {$action}",
            ['user_id' => $user->id]
        );

        return back()->with('success', "User {$action} successfully.");
    }

    /**
     * Reset user password
     */
    public function resetPassword(Request $request, User $user)
    {
        $validated = $request->validate([
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        ActivityLog::log(
            ActivityLog::TYPE_PASSWORD_RESET,
            "Password was reset for {$user->name}",
            ['user_id' => $user->id, 'reset_by' => Auth::user()->name]
        );

        return back()->with('success', 'Password reset successfully.');
    }

    /**
     * Invalidate all user sessions
     */
    public function invalidateSessions(User $user)
    {
        $count = UserSession::where('user_id', $user->id)->delete();

        ActivityLog::log(
            ActivityLog::TYPE_LOGOUT,
            "All sessions invalidated for {$user->name}",
            ['user_id' => $user->id, 'sessions_count' => $count]
        );

        return back()->with('success', "Invalidated {$count} session(s) for {$user->name}.");
    }

    /**
     * Bulk action on users
     */
    public function bulkAction(Request $request)
    {
        $validated = $request->validate([
            'action' => ['required', 'in:activate,deactivate,delete,assign_role,remove_role'],
            'user_ids' => ['required', 'array'],
            'user_ids.*' => ['exists:users,id'],
            'role_id' => ['required_if:action,assign_role,remove_role', 'exists:roles,id'],
        ]);

        $users = User::whereIn('id', $validated['user_ids'])
            ->where('id', '!=', Auth::id()) // Exclude current user
            ->get();

        $affectedCount = 0;

        foreach ($users as $user) {
            switch ($validated['action']) {
                case 'activate':
                    $user->update(['is_active' => true]);
                    $affectedCount++;
                    break;

                case 'deactivate':
                    $user->update(['is_active' => false]);
                    $affectedCount++;
                    break;

                case 'delete':
                    $user->delete();
                    $affectedCount++;
                    break;

                case 'assign_role':
                    $user->roles()->syncWithoutDetaching([$validated['role_id']]);
                    $affectedCount++;
                    break;

                case 'remove_role':
                    $user->roles()->detach($validated['role_id']);
                    $affectedCount++;
                    break;
            }
        }

        ActivityLog::log(
            ActivityLog::TYPE_BULK_ACTION,
            "Bulk {$validated['action']} performed on {$affectedCount} users",
            [
                'action' => $validated['action'],
                'affected_count' => $affectedCount,
                'user_ids' => $validated['user_ids'],
            ]
        );

        return back()->with('success', "Action performed on {$affectedCount} user(s).");
    }

    /**
     * Export users to CSV
     */
    public function export(Request $request)
    {
        $users = User::with(['department', 'hub', 'roles'])
            ->when($request->get('is_active'), function ($q, $isActive) {
                return $q->where('is_active', $isActive === 'true');
            })
            ->orderBy('name')
            ->get();

        $filename = 'users_export_' . now()->format('Y-m-d_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($users) {
            $file = fopen('php://output', 'w');
            
            // Headers
            fputcsv($file, [
                'ID', 'Name', 'Email', 'Employee ID', 'Phone', 
                'Job Title', 'Department', 'Hub', 'Roles', 
                'Status', 'Created At', 'Last Login'
            ]);

            foreach ($users as $user) {
                $lastLogin = ActivityLog::where('user_id', $user->id)
                    ->where('type', ActivityLog::TYPE_LOGIN)
                    ->latest('created_at')
                    ->first();

                fputcsv($file, [
                    $user->id,
                    $user->name,
                    $user->email,
                    $user->employee_id,
                    $user->phone,
                    $user->job_title,
                    $user->department?->name,
                    $user->hub?->name,
                    $user->roles->pluck('name')->implode(', '),
                    $user->is_active ? 'Active' : 'Inactive',
                    $user->created_at->format('Y-m-d H:i'),
                    $lastLogin?->created_at?->format('Y-m-d H:i'),
                ]);
            }

            fclose($file);
        };

        ActivityLog::log(
            ActivityLog::TYPE_EXPORT,
            "Users exported to CSV ({$users->count()} records)"
        );

        return response()->stream($callback, 200, $headers);
    }
}
