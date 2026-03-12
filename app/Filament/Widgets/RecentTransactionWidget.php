<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Filament\Tables\Table;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Facades\Auth;
use Filament\Widgets\TableWidget;

class RecentTransactionWidget extends TableWidget
{
    protected static ?int $sort = 5;
    protected int | string | array $columnSpan = 'full';
 
    public function table(Table $table): Table
    {
        return $table
            ->query(
                Transaction::where('user_id', Auth::user()->id)
                    ->with(['account', 'destinationAccount'])
                    ->latest('date')
                    ->limit(10)
            )
            ->columns([
                TextColumn::make('date')
                    ->label('Tanggal')
                    ->date('d M Y'),
 
                BadgeColumn::make('type')
                    ->label('Tipe')
                    ->formatStateUsing(fn ($state) => match($state) {
                        'income' => '↓ Masuk', 'expense' => '↑ Keluar',
                        'transfer' => '⇄ Transfer', 'adjustment' => '⚖ Sesuai',
                        default => $state,
                    })
                    ->colors([
                        'success' => 'income', 'danger' => 'expense',
                        'info' => 'transfer', 'warning' => 'adjustment',
                    ]),
 
                TextColumn::make('description')
                    ->label('Deskripsi')
                    ->limit(35),
 
                TextColumn::make('account.name')
                    ->label('Akun'),
 
                TextColumn::make('amount')
                    ->label('Jumlah')
                    ->formatStateUsing(fn ($state, Transaction $record) =>
                        ($record->type === 'income' ? '+' : ($record->type === 'expense' ? '-' : '')) .
                        'Rp ' . number_format($state, 0, ',', '.')
                    )
                    ->color(fn (Transaction $record) => match($record->type) {
                        'income' => 'success', 'expense' => 'danger', default => 'gray',
                    })
                    ->weight(\Filament\Support\Enums\FontWeight::SemiBold),
            ])
            ->paginated(false);
    }
}
