<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Provider extends Model
{
    protected $fillable = [
        'name', 
        'type', 
        'logo', 
        'is_active', 
        'sort_order'
        ];

    protected $casts = [
        'is_active' => 'boolean'
        ];

    public function getTypeLabel(): string
    {
        return match($this->type) {
            'bank' => 'Bank',
            'ewallet' => 'E-Wallet',
            'cash' => 'Cash',
            default => $this->type,
        };
    }
}
