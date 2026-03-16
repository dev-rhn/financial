<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Account extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'type',
        'provider_id',
        'account_number',
        'initial_balance',
        'current_balance',
        'color',
        'icon',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'initial_balance' => 'decimal:2',
        'current_balance' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class, 'provider_id');
    }

    public function incomingTransfers(): HasMany
    {
        return $this->hasMany(Transaction::class, 'destination_account_id');
    }

    public function getTypeLabel(): string
    {
        return match($this->type) {
            'bank' => 'Bank',
            'ewallet' => 'E-Wallet',
            'cash' => 'Tunai',
            default => $this->type,
        };
    }

    /**
     * Recalculate balance from initial balance + all transactions
     */
    public function recalculateBalance(): void
    {
        $income = $this->transactions()
            ->where('type', 'income')
            ->sum('amount');

        $expense = $this->transactions()
            ->whereIn('type', ['expense'])
            ->sum('amount');

        // Outgoing transfers (including admin fees)
        $outgoingTransfers = $this->transactions()
            ->where('type', 'transfer')
            ->selectRaw('SUM(amount + admin_fee) as total')
            ->value('total') ?? 0;

        // Incoming transfers
        $incomingTransfers = Transaction::where('destination_account_id', $this->id)
            ->where('type', 'transfer')
            ->sum('destination_amount');

        // Adjustments
        $adjustments = $this->transactions()
            ->where('type', 'adjustment')
            ->sum('amount');

        $this->current_balance = $this->initial_balance
            + $income
            - $expense
            - $outgoingTransfers
            + $incomingTransfers
            + $adjustments;

        $this->saveQuietly();
    }
}