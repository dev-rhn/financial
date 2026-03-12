<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'account_id',
        'type',
        'amount',
        'date',
        'description',
        'notes',
        'reference_number',
        'is_split',
        'destination_account_id',
        'admin_fee',
        'destination_amount',
        'attachments',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'admin_fee' => 'decimal:2',
        'destination_amount' => 'decimal:2',
        'date' => 'date',
        'is_split' => 'boolean',
        'attachments' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function destinationAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'destination_account_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(TransactionItem::class);
    }

    public function getTypeLabel(): string
    {
        return match($this->type) {
            'income' => 'Pemasukan',
            'expense' => 'Pengeluaran',
            'transfer' => 'Transfer',
            'adjustment' => 'Penyesuaian',
            default => $this->type,
        };
    }

    public function getTypeColor(): string
    {
        return match($this->type) {
            'income' => 'success',
            'expense' => 'danger',
            'transfer' => 'info',
            'adjustment' => 'warning',
            default => 'gray',
        };
    }

    /**
     * Get display amount with sign based on type
     */
    public function getSignedAmount(): float
    {
        return match($this->type) {
            'income' => (float) $this->amount,
            'expense' => -(float) $this->amount,
            'transfer' => -(float) ($this->amount + $this->admin_fee),
            'adjustment' => (float) $this->amount,
            default => (float) $this->amount,
        };
    }

    /**
     * After creating/updating transaction, recalculate account balances
     */
    protected static function booted(): void
    {
        static::created(function (Transaction $transaction) {
            $transaction->account->recalculateBalance();
            if ($transaction->destinationAccount) {
                $transaction->destinationAccount->recalculateBalance();
            }
        });

        static::updated(function (Transaction $transaction) {
            $transaction->account->recalculateBalance();
            if ($transaction->destinationAccount) {
                $transaction->destinationAccount->recalculateBalance();
            }
            // Handle if account changed
            if ($transaction->wasChanged('account_id')) {
                Account::find($transaction->getOriginal('account_id'))?->recalculateBalance();
            }
            if ($transaction->wasChanged('destination_account_id')) {
                Account::find($transaction->getOriginal('destination_account_id'))?->recalculateBalance();
            }
        });

        static::deleted(function (Transaction $transaction) {
            $transaction->account->recalculateBalance();
            if ($transaction->destinationAccount) {
                $transaction->destinationAccount->recalculateBalance();
            }
        });
    }
}