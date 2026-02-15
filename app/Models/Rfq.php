<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Rfq extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'rfq_number',
        'title',
        'description',
        'requisition_id',
        'rfqable_type',
        'rfqable_id',
        'status',
        'issue_date',
        'closing_date',
        'delivery_date',
        'terms_and_conditions',
        'submission_instructions',
        'evaluation_criteria',
        'min_quotes',
        'awarded_supplier_id',
        'awarded_quote_id',
        'awarded_at',
        'award_justification',
        'created_by',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'closing_date' => 'date',
        'delivery_date' => 'date',
        'awarded_at' => 'datetime',
    ];

    /**
     * Status constants
     */
    const STATUS_DRAFT = 'draft';
    const STATUS_SENT = 'sent';
    const STATUS_QUOTES_RECEIVED = 'quotes_received';
    const STATUS_UNDER_EVALUATION = 'under_evaluation';
    const STATUS_AWARDED = 'awarded';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($rfq) {
            if (empty($rfq->rfq_number)) {
                $rfq->rfq_number = self::generateNumber();
            }
            if (empty($rfq->created_by) && auth()->check()) {
                $rfq->created_by = auth()->id();
            }
        });
    }

    /**
     * Generate unique RFQ number
     */
    public static function generateNumber(): string
    {
        $year = date('Y');
        $lastRfq = self::whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();

        if ($lastRfq && preg_match('/RFQ-' . $year . '-(\d+)/', $lastRfq->rfq_number, $matches)) {
            $nextNumber = intval($matches[1]) + 1;
        } else {
            $nextNumber = 1;
        }

        return 'RFQ-' . $year . '-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Relationships
     */
    public function rfqable(): MorphTo
    {
        return $this->morphTo();
    }

    public function requisition(): BelongsTo
    {
        return $this->belongsTo(Requisition::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function awardedSupplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'awarded_supplier_id');
    }

    public function awardedQuote(): BelongsTo
    {
        return $this->belongsTo(Quote::class, 'awarded_quote_id');
    }

    public function suppliers(): BelongsToMany
    {
        return $this->belongsToMany(Supplier::class, 'rfq_supplier')
            ->withPivot(['invited_at', 'viewed_at', 'status', 'decline_reason'])
            ->withTimestamps();
    }

    public function quotes(): HasMany
    {
        return $this->hasMany(Quote::class);
    }

    /**
     * Status checks
     */
    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isSent(): bool
    {
        return $this->status === self::STATUS_SENT;
    }

    public function isAwarded(): bool
    {
        return $this->status === self::STATUS_AWARDED;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function isOpen(): bool
    {
        return in_array($this->status, [
            self::STATUS_SENT,
            self::STATUS_QUOTES_RECEIVED,
        ]) && ($this->closing_date === null || $this->closing_date->isFuture());
    }

    public function isClosed(): bool
    {
        return $this->closing_date && $this->closing_date->isPast();
    }

    public function canEdit(): bool
    {
        return $this->isDraft();
    }

    public function canSend(): bool
    {
        return $this->isDraft() && $this->suppliers()->count() >= 1;
    }

    public function canReceiveQuotes(): bool
    {
        return in_array($this->status, [
            self::STATUS_SENT,
            self::STATUS_QUOTES_RECEIVED,
        ]);
    }

    public function canEvaluate(): bool
    {
        return in_array($this->status, [
            self::STATUS_QUOTES_RECEIVED,
            self::STATUS_UNDER_EVALUATION,
        ]) && $this->quotes()->count() > 0;
    }

    public function canAward(): bool
    {
        return $this->status === self::STATUS_UNDER_EVALUATION 
            && $this->quotes()->count() >= 1;
    }

    /**
     * Send RFQ to suppliers
     */
    public function send(): bool
    {
        if (!$this->canSend()) {
            return false;
        }

        $this->update([
            'status' => self::STATUS_SENT,
            'issue_date' => now(),
        ]);

        // Mark all suppliers as invited
        $this->suppliers()->update(['rfq_supplier.invited_at' => now()]);

        return true;
    }

    /**
     * Move to evaluation
     */
    public function startEvaluation(): bool
    {
        if (!$this->canEvaluate()) {
            return false;
        }

        $this->update(['status' => self::STATUS_UNDER_EVALUATION]);
        return true;
    }

    /**
     * Award RFQ to a supplier
     */
    public function award(Quote $quote, ?string $justification = null): bool
    {
        if (!$this->canAward()) {
            return false;
        }

        $this->update([
            'status' => self::STATUS_AWARDED,
            'awarded_supplier_id' => $quote->supplier_id,
            'awarded_quote_id' => $quote->id,
            'awarded_at' => now(),
            'award_justification' => $justification,
        ]);

        // Mark quote as selected
        $quote->update(['status' => Quote::STATUS_SELECTED]);

        // Mark other quotes as not selected
        $this->quotes()
            ->where('id', '!=', $quote->id)
            ->update(['status' => Quote::STATUS_NOT_SELECTED]);

        // Update requisition status
        $this->requisition->update(['status' => Requisition::STATUS_IN_PROGRESS]);

        return true;
    }

    /**
     * Cancel the RFQ
     */
    public function cancel(?string $reason = null): bool
    {
        if ($this->isAwarded()) {
            return false;
        }

        $this->update([
            'status' => self::STATUS_CANCELLED,
            'award_justification' => $reason ? "Cancelled: {$reason}" : null,
        ]);

        return true;
    }

    /**
     * Check if minimum quotes met
     */
    public function hasMinimumQuotes(): bool
    {
        return $this->quotes()->count() >= $this->min_quotes;
    }

    /**
     * Get quotes count
     */
    public function getQuotesCountAttribute(): int
    {
        return $this->quotes()->count();
    }

    /**
     * Get lowest quote
     */
    public function getLowestQuoteAttribute(): ?Quote
    {
        return $this->quotes()->orderBy('total_amount', 'asc')->first();
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeClass(): string
    {
        return match($this->status) {
            self::STATUS_DRAFT => 'badge-secondary',
            self::STATUS_SENT => 'badge-info',
            self::STATUS_QUOTES_RECEIVED => 'badge-primary',
            self::STATUS_UNDER_EVALUATION => 'badge-warning',
            self::STATUS_AWARDED => 'badge-success',
            self::STATUS_CANCELLED => 'badge-danger',
            default => 'badge-secondary',
        };
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_SENT => 'Sent to Suppliers',
            self::STATUS_QUOTES_RECEIVED => 'Quotes Received',
            self::STATUS_UNDER_EVALUATION => 'Under Evaluation',
            self::STATUS_AWARDED => 'Awarded',
            self::STATUS_CANCELLED => 'Cancelled',
            default => ucfirst($this->status),
        };
    }

    /**
     * Scopes
     */
    public function scopeForProject($query, $projectId)
    {
        return $query->where('rfqable_type', Project::class)
            ->where('rfqable_id', $projectId);
    }

    public function scopeForDepartmentBudget($query, $budgetId)
    {
        return $query->where('rfqable_type', DepartmentBudget::class)
            ->where('rfqable_id', $budgetId);
    }

    public function scopeOpen($query)
    {
        return $query->whereIn('status', [
            self::STATUS_SENT,
            self::STATUS_QUOTES_RECEIVED,
        ]);
    }

    public function scopeAwarded($query)
    {
        return $query->where('status', self::STATUS_AWARDED);
    }
}
