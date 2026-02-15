<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\DB;

class PurchaseOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'po_number',
        'rfq_id',
        'quote_id',
        'requisition_id',
        'purchaseable_type',
        'purchaseable_id',
        'supplier_id',
        'budget_line_id',
        'delivery_hub_id',
        'delivery_address',
        'expected_delivery_date',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'shipping_amount',
        'total_amount',
        'currency',
        'status',
        'payment_terms',
        'delivery_terms',
        'terms_and_conditions',
        'notes',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
        'rejection_reason',
        'sent_at',
        'acknowledged_at',
        'created_by',
    ];

    protected $casts = [
        'expected_delivery_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'shipping_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'sent_at' => 'datetime',
        'acknowledged_at' => 'datetime',
    ];

    /**
     * Status constants
     */
    const STATUS_DRAFT = 'draft';
    const STATUS_PENDING_APPROVAL = 'pending_approval';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_SENT = 'sent';
    const STATUS_ACKNOWLEDGED = 'acknowledged';
    const STATUS_PARTIALLY_RECEIVED = 'partially_received';
    const STATUS_RECEIVED = 'received';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_CLOSED = 'closed';

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($po) {
            if (empty($po->po_number)) {
                $po->po_number = self::generateNumber();
            }
            if (empty($po->created_by) && auth()->check()) {
                $po->created_by = auth()->id();
            }
        });
    }

    /**
     * Generate unique PO number
     */
    public static function generateNumber(): string
    {
        $year = date('Y');
        $lastPo = self::whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();

        if ($lastPo && preg_match('/PO-' . $year . '-(\d+)/', $lastPo->po_number, $matches)) {
            $nextNumber = intval($matches[1]) + 1;
        } else {
            $nextNumber = 1;
        }

        return 'PO-' . $year . '-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Relationships
     */
    public function purchaseable(): MorphTo
    {
        return $this->morphTo();
    }

    public function rfq(): BelongsTo
    {
        return $this->belongsTo(Rfq::class);
    }

    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class);
    }

    public function requisition(): BelongsTo
    {
        return $this->belongsTo(Requisition::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function budgetLine(): BelongsTo
    {
        return $this->belongsTo(BudgetLine::class);
    }

    public function deliveryHub(): BelongsTo
    {
        return $this->belongsTo(Hub::class, 'delivery_hub_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function receipts(): HasMany
    {
        return $this->hasMany(GoodsReceipt::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejecter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    /**
     * Status checks
     */
    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isPendingApproval(): bool
    {
        return $this->status === self::STATUS_PENDING_APPROVAL;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    public function isSent(): bool
    {
        return in_array($this->status, [
            self::STATUS_SENT,
            self::STATUS_ACKNOWLEDGED,
            self::STATUS_PARTIALLY_RECEIVED,
            self::STATUS_RECEIVED,
        ]);
    }

    public function isReceived(): bool
    {
        return $this->status === self::STATUS_RECEIVED;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function isClosed(): bool
    {
        return $this->status === self::STATUS_CLOSED;
    }

    public function canEdit(): bool
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_REJECTED]);
    }

    public function canSubmit(): bool
    {
        return $this->isDraft() && $this->items()->count() > 0;
    }

    public function canApprove(): bool
    {
        return $this->isPendingApproval();
    }

    public function canSend(): bool
    {
        return $this->isApproved();
    }

    public function canReceive(): bool
    {
        return in_array($this->status, [
            self::STATUS_SENT,
            self::STATUS_ACKNOWLEDGED,
            self::STATUS_PARTIALLY_RECEIVED,
        ]);
    }

    public function canCancel(): bool
    {
        return in_array($this->status, [
            self::STATUS_DRAFT,
            self::STATUS_PENDING_APPROVAL,
            self::STATUS_APPROVED,
        ]);
    }

    /**
     * Submit for approval
     */
    public function submitForApproval(): bool
    {
        if (!$this->canSubmit()) {
            return false;
        }

        $this->update(['status' => self::STATUS_PENDING_APPROVAL]);
        return true;
    }

    /**
     * Approve the PO
     */
    public function approve(?User $approver = null): bool
    {
        if (!$this->canApprove()) {
            return false;
        }

        DB::transaction(function () use ($approver) {
            $this->update([
                'status' => self::STATUS_APPROVED,
                'approved_by' => $approver?->id ?? auth()->id(),
                'approved_at' => now(),
            ]);

            // Commit budget
            $this->budgetLine->addCommitment($this->total_amount);
        });

        return true;
    }

    /**
     * Reject the PO
     */
    public function reject(?string $reason = null, ?User $rejecter = null): bool
    {
        if (!$this->canApprove()) {
            return false;
        }

        $this->update([
            'status' => self::STATUS_REJECTED,
            'rejected_by' => $rejecter?->id ?? auth()->id(),
            'rejected_at' => now(),
            'rejection_reason' => $reason,
        ]);

        return true;
    }

    /**
     * Send to supplier
     */
    public function send(): bool
    {
        if (!$this->canSend()) {
            return false;
        }

        $this->update([
            'status' => self::STATUS_SENT,
            'sent_at' => now(),
        ]);

        return true;
    }

    /**
     * Mark as acknowledged by supplier
     */
    public function acknowledge(): bool
    {
        if ($this->status !== self::STATUS_SENT) {
            return false;
        }

        $this->update([
            'status' => self::STATUS_ACKNOWLEDGED,
            'acknowledged_at' => now(),
        ]);

        return true;
    }

    /**
     * Update receipt status based on items
     */
    public function updateReceiptStatus(): void
    {
        $items = $this->items;
        $totalItems = $items->count();
        $receivedItems = $items->where('status', 'received')->count();
        $partialItems = $items->where('status', 'partially_received')->count();

        if ($receivedItems === $totalItems) {
            $this->update(['status' => self::STATUS_RECEIVED]);
            
            // Convert commitment to spent
            $this->budgetLine->releaseCommitment($this->total_amount);
            $this->budgetLine->addExpenditure($this->total_amount);
            
            // Update requisition status
            if ($this->requisition) {
                $this->requisition->update(['status' => Requisition::STATUS_COMPLETED]);
            }
        } elseif ($receivedItems > 0 || $partialItems > 0) {
            $this->update(['status' => self::STATUS_PARTIALLY_RECEIVED]);
        }
    }

    /**
     * Cancel the PO
     */
    public function cancel(?string $reason = null): bool
    {
        if (!$this->canCancel()) {
            return false;
        }

        DB::transaction(function () use ($reason) {
            // Release commitment if approved
            if ($this->isApproved()) {
                $this->budgetLine->releaseCommitment($this->total_amount);
            }

            $this->update([
                'status' => self::STATUS_CANCELLED,
                'notes' => $reason ? $this->notes . "\n\nCancelled: {$reason}" : $this->notes,
            ]);
        });

        return true;
    }

    /**
     * Close the PO
     */
    public function close(): bool
    {
        if (!$this->isReceived()) {
            return false;
        }

        $this->update(['status' => self::STATUS_CLOSED]);
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
            'total_amount' => $subtotal + $this->tax_amount + $this->shipping_amount - $this->discount_amount,
        ]);
    }

    /**
     * Create PO from awarded quote
     */
    public static function createFromQuote(Quote $quote): self
    {
        $rfq = $quote->rfq;
        $requisition = $rfq->requisition;

        $po = self::create([
            'rfq_id' => $rfq->id,
            'quote_id' => $quote->id,
            'requisition_id' => $requisition->id,
            'purchaseable_type' => $rfq->rfqable_type,
            'purchaseable_id' => $rfq->rfqable_id,
            'supplier_id' => $quote->supplier_id,
            'budget_line_id' => $requisition->budget_line_id,
            'delivery_hub_id' => $requisition->delivery_hub_id,
            'expected_delivery_date' => $quote->delivery_date ?? $requisition->required_date,
            'payment_terms' => $quote->payment_terms,
            'delivery_terms' => $quote->delivery_terms,
        ]);

        // Copy items from quote
        foreach ($quote->items as $quoteItem) {
            $po->items()->create([
                'quote_item_id' => $quoteItem->id,
                'requisition_item_id' => $quoteItem->requisition_item_id,
                'name' => $quoteItem->name,
                'description' => $quoteItem->description,
                'specifications' => $quoteItem->specifications,
                'quantity' => $quoteItem->quantity,
                'unit' => $quoteItem->unit,
                'unit_price' => $quoteItem->unit_price,
                'total_price' => $quoteItem->total_price,
            ]);
        }

        $po->recalculateTotals();

        return $po;
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeClass(): string
    {
        return match($this->status) {
            self::STATUS_DRAFT => 'badge-secondary',
            self::STATUS_PENDING_APPROVAL => 'badge-warning',
            self::STATUS_APPROVED => 'badge-info',
            self::STATUS_REJECTED => 'badge-danger',
            self::STATUS_SENT => 'badge-primary',
            self::STATUS_ACKNOWLEDGED => 'badge-primary',
            self::STATUS_PARTIALLY_RECEIVED => 'badge-warning',
            self::STATUS_RECEIVED => 'badge-success',
            self::STATUS_CANCELLED => 'badge-danger',
            self::STATUS_CLOSED => 'badge-secondary',
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
            self::STATUS_PENDING_APPROVAL => 'Pending Approval',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_REJECTED => 'Rejected',
            self::STATUS_SENT => 'Sent to Supplier',
            self::STATUS_ACKNOWLEDGED => 'Acknowledged',
            self::STATUS_PARTIALLY_RECEIVED => 'Partially Received',
            self::STATUS_RECEIVED => 'Received',
            self::STATUS_CANCELLED => 'Cancelled',
            self::STATUS_CLOSED => 'Closed',
            default => ucfirst(str_replace('_', ' ', $this->status)),
        };
    }

    /**
     * Get received percentage
     */
    public function getReceivedPercentageAttribute(): float
    {
        $totalQty = $this->items()->sum('quantity');
        if ($totalQty == 0) return 0;
        
        $receivedQty = $this->items()->sum('received_quantity');
        return round(($receivedQty / $totalQty) * 100, 1);
    }

    /**
     * Scopes
     */
    public function scopeForProject($query, $projectId)
    {
        return $query->where('purchaseable_type', Project::class)
            ->where('purchaseable_id', $projectId);
    }

    public function scopeForDepartmentBudget($query, $budgetId)
    {
        return $query->where('purchaseable_type', DepartmentBudget::class)
            ->where('purchaseable_id', $budgetId);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING_APPROVAL);
    }

    public function scopeActive($query)
    {
        return $query->whereNotIn('status', [
            self::STATUS_CANCELLED,
            self::STATUS_CLOSED,
        ]);
    }
}
