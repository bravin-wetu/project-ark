<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RfqSupplier extends Pivot
{
    protected $table = 'rfq_supplier';

    protected $fillable = [
        'rfq_id',
        'supplier_id',
        'invited_at',
        'viewed_at',
        'status',
        'decline_reason',
    ];

    protected $casts = [
        'invited_at' => 'datetime',
        'viewed_at' => 'datetime',
    ];

    /**
     * Status constants
     */
    const STATUS_INVITED = 'invited';
    const STATUS_DECLINED = 'declined';
    const STATUS_QUOTED = 'quoted';

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
}
