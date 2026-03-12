<?php

namespace App\Filament\Widgets;

use App\Models\Account;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Support\Facades\Auth;

class AccountBalanceWidget extends TableWidget
{
    protected static ?int $sort = 2;
    protected static ?string $heading = 'Saldo Semua Akun';
    protected int | string | array $columnSpan = 'full';
 
    public function table(Table $table): Table
    {
        return $table
            ->query(
                Account::where('user_id', Auth::user()->id)
                    ->where('is_active', true)
                    ->orderBy('type')
            )
            ->columns([
                ColorColumn::make('color')->label(''),
 
                TextColumn::make('name')
                    ->label('Nama Akun')
                    ->weight(\Filament\Support\Enums\FontWeight::SemiBold),
 
                BadgeColumn::make('type')
                    ->label('Tipe')
                    ->formatStateUsing(fn ($state) => match($state) {
                        'bank' => '🏦 Bank',
                        'ewallet' => '📱 E-Wallet',
                        'cash' => '💵 Tunai',
                        default => $state,
                    })
                    ->colors([
                        'primary' => 'bank',
                        'success' => 'ewallet',
                        'warning' => 'cash',
                    ]),
 
                TextColumn::make('provider')
                    ->label('Provider')
                    ->placeholder('—'),
 
                TextColumn::make('current_balance')
                    ->label('Saldo')
                    ->money('IDR')
                    ->sortable()
                    ->color(fn ($state) => $state < 0 ? 'danger' : 'success')
                    ->weight(\Filament\Support\Enums\FontWeight::Bold),
            ])
            ->paginated(false);
    }
}
