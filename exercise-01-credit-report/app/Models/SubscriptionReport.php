<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class SubscriptionReport extends Model
{
    use HasFactory;
     protected $fillable = [
        'subscription_id',
        'period'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function loans(): HasMany
    {
        return $this->hasMany(ReportLoan::class);
    }

    public function creditCards(): HasMany
    {
        return $this->hasMany(ReportCreditCard::class);
    }

    public function otherDebts(): HasMany
    {
        return $this->hasMany(ReportOtherDebt::class);
    }

    /**
     * Get all debts combined for this report
     */
    public function getAllDebts()
    {
        $debts = collect();

        foreach ($this->loans as $loan) {
            $debts->push([
                'type' => 'Préstamo',
                'company' => $loan->bank,
                'status' => $loan->status,
                'expiration_days' => $loan->expiration_days,
                'entity' => $loan->bank,
                'amount' => $loan->amount,
                'currency' => $loan->currency,
                'total_line' => null,
                'timeline' => null,
            ]);
        }

        foreach ($this->creditCards as $card) {
            $debts->push([
                'type' => 'Tarjeta de crédito',
                'company' => $card->bank,
                'status' => null,
                'expiration_days' => null,
                'entity' => $card->bank,
                'amount' => null,
                'currency' => $card->currency,
                'total_line' => $card->line,
                'timeline' => $card->used,
            ]);
        }

        foreach ($this->otherDebts as $debt) {
            $debts->push([
                'type' => 'Otra deuda',
                'company' => $debt->entity,
                'status' => null,
                'expiration_days' => $debt->expiration_days,
                'entity' => $debt->entity,
                'amount' => $debt->amount,
                'currency' => $debt->currency,
                'total_line' => null,
                'timeline' => null,
            ]);
        }

        return $debts;
    }
}
