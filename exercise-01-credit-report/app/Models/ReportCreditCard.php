<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportCreditCard extends Model
{
    use HasFactory;
     protected $fillable = [
        'subscription_report_id',
        'bank',
        'currency',
        'line',
        'used'
    ];

    protected $casts = [
        'line' => 'decimal:2',
        'used' => 'decimal:2'
    ];

    public function report(): BelongsTo
    {
        return $this->belongsTo(SubscriptionReport::class, 'subscription_report_id');
    }
}
