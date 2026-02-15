<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'permissions',
        'is_active',
    ];

    protected $casts = [
        'permissions' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Users with this role
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    /**
     * Approval matrices using this role
     */
    public function approvalMatrices(): HasMany
    {
        return $this->hasMany(ApprovalMatrix::class, 'approver_role_id');
    }

    /**
     * Check if role has a specific permission
     */
    public function hasPermission(string $permission): bool
    {
        if (!$this->permissions) {
            return false;
        }

        return in_array($permission, $this->permissions);
    }

    /**
     * Scope for active roles
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Predefined role slugs
     */
    const ADMIN = 'admin';
    const PROJECT_MANAGER = 'project-manager';
    const DEPARTMENT_HEAD = 'department-head';
    const FINANCE_OFFICER = 'finance-officer';
    const PROCUREMENT_OFFICER = 'procurement-officer';
    const HUB_MANAGER = 'hub-manager';
    const EXECUTIVE_APPROVER = 'executive-approver';
    const STAFF = 'staff';
}
