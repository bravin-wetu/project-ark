<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Donor extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'contact_person',
        'email',
        'phone',
        'address',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Projects funded by this donor
     */
    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    /**
     * Active projects by this donor
     */
    public function activeProjects(): HasMany
    {
        return $this->projects()->where('status', 'active');
    }

    /**
     * Total budget across all projects
     */
    public function getTotalBudgetAttribute(): float
    {
        return $this->projects()->sum('total_budget');
    }

    /**
     * Scope for active donors
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
