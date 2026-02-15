<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quote extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'quote_number',
        'supplier_reference',
        'rfq_id',
        'supplier_id',
        'status',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'currency',
        'quote_date',
        'valid_until',
        'delivery_days',
        'delivery_date',
        'delivery_terms',
        'payment_terms',
        'notes',
        'terms_and_conditions',
        'evaluation_score',
        'evaluation_notes',
        'evaluated_by',
        'evaluated_at',
        'attachment_path',
        'created_by',
        'submitted_at',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'quote_date' => 'date',
        'valid_until' => 'date',
        'delivery_date' => 'date',
        'evaluation_score' => 'decimal:2',
        'evaluated_at' => 'datetime',
        'submitted_at' => 'datetime',
    ];

    /**
     * Status constants
     */
    const STATUS_DRAFT = 'draft';
    const STATUS_SUBMITTED = 'submitted';
    const STATUS_UNDER_REVIEW = 'under_review';
    const STATUS_SELECTED = 'selected';
    const STATUS_NOT_SELECTED = 'not_selected';
    const STATUS_WITHDRAWN = 'withdrawn';

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($quote) {
            if (empty($quote->quote_number)) {
                $quote->quote_number = self::generateNumber();
            }
            if (empty($quote->created_by) && auth()->check()) {
                $quote->created_by = auth()->id();
            }
        });
    }

    /**
     * Generate unique quote number
     */
    public static function generateNumber(): string
    {
        $year = date('Y');
        $lastQuote = self::whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();

        if ($lastQuote && preg_match('/QUO-' . $year . '-(\d+)/', $lastQuote->quote_number, $matches)) {
            $nextNumber = intval($matches[1]) + 1;
        } else {
            $nextNumber = 1;
        }

        return 'QUO-' . $year . '-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Relationships
     */
    public function rfq(): BelongsTo
    {
        return $this->belongsTo(Rfq::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(QuoteItem::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function evaluator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'evaluated_by');
    }

    /**
     * Status checks
     */
    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isSubmitted(): bool
    {
        return $this->status === self::STATUS_SUBMITTED;
    }

    public function isSelected(): bool
    {
        return $this->status === self::STATUS_SELECTED;
    }

    public function isNotSelected(): bool
    {
        return $this->status === self::STATUS_NOT_SELECTED;
    }

    public function isValid(): bool
    {
        return $this->valid_until === null || $this->valid_until->isFuture();
    }

    public function canEdit(): bool
    {
        return $this->isDraft();
    }

    public function canSubmit(): bool
    {
        return $this->isDraft() && $this->items()->count() > 0;
    }

    /**
     * Submit the quote
     */
    public function submit(): bool
    {
        if (!$this->canSubmit()) {
            return false;
        }

        $this->update([
            'status' => self::STATUS_SUBMITTED,
            'submitted_at' => now(),
        ]);

        // Update RFQ status if this is the first quote
        if ($this->rfq->status === Rfq::STATUS_SENT) {
            $this->rfq->update(['status' => Rfq::STATUS_QUOTES_RECEIVED]);
        }

        // Update pivot table
        $this->rfq->suppliers()->updateExistingPivot($this->supplier_id, [
            'status' => RfqSupplier::STATUS_QUOTED,
        ]);

        return true;
    }

    /**
     * Mark as under review
     */
    public function startReview(): bool
    {
        if (!$this->isSubmitted()) {
            return false;
        }

        $this->update(['status' => self::STATUS_UNDER_REVIEW]);
        return true;
    }

    /**
     * Evaluate the quote
     */
    public function evaluate(float $score, ?string $notes = null, ?User $evaluator = null): bool
    {
        $this->update([
            'evaluation_score' => $score,
            'evaluation_notes' => $notes,
            'evaluated_by' => $evaluator?->id ?? auth()->id(),
            'evaluated_at' => now(),
        ]);

        return true;
    }

    /**
     * Withdraw the quote
     */
    public function withdraw(): bool
    {
        if ($this->isSelected()) {
            return false;
        }

        $this->update(['status' => self::STATUS_WITHDRAWN]);
        return true;
    }

    /**
     * Recalculate totals from items
     */
    public function recalculateTotals(): void
    {
        $subtotal = $this->items()->sum('total_price');
        
        $this->update([
            'subtotal' => $subtotal,
            'total_amount' => $subtotal + $this->tax_amount - $this->discount_amount,
        ]);
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeClass(): string
    {
        return match($this->status) {
            self::STATUS_DRAFT => 'badge-secondary',
            self::STATUS_SUBMITTED => 'badge-info',
            self::STATUS_UNDER_REVIEW => 'badge-warning',
            self::STATUS_SELECTED => 'badge-success',
            self::STATUS_NOT_SELECTED => 'badge-danger',
            self::STATUS_WITHDRAWN => 'badge-secondary',
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
            self::STATUS_SUBMITTED => 'Submitted',
            self::STATUS_UNDER_REVIEW => 'Under Review',
            self::STATUS_SELECTED => 'Selected',
            self::STATUS_NOT_SELECTED => 'Not Selected',
            self::STATUS_WITHDRAWN => 'Withdrawn',
            default => ucfirst($this->status),
        };
    }

    /**
     * Scopes
     */
    public function scopeSubmitted($query)
    {
        return $query->where('status', self::STATUS_SUBMITTED);
    }

    public function scopeSelected($query)
    {
        return $query->where('status', self::STATUS_SELECTED);
    }

    public function scopeForRfq($query, $rfqId)
    {
        return $query->where('rfq_id', $rfqId);
    }
}
