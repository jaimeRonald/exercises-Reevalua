<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportLoan extends Model
{
    use HasFactory;
    protected $fillable = [
        'subscription_report_id',
        'bank',
        'status',
        'currency',
        'amount',
        'expiration_days'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'expiration_days' => 'integer'
    ];

    public function report(): BelongsTo
    {
        return $this->belongsTo(SubscriptionReport::class, 'subscription_report_id');
    }
}
