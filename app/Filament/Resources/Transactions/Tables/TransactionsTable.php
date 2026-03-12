<?php

namespace App\Filament\Resources\Transactions\Tables;

use App\Models\Account;
use App\Models\Transaction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class TransactionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),
 
                BadgeColumn::make('type')
                    ->label('Tipe')
                    ->formatStateUsing(fn ($state) => match($state) {
                        'income' => '↓ Pemasukan',
                        'expense' => '↑ Pengeluaran',
                        'transfer' => '⇄ Transfer',
                        'adjustment' => '⚖ Penyesuaian',
                        default => $state,
                    })
                    ->colors([
                        'success' => 'income',
                        'danger' => 'expense',
                        'info' => 'transfer',
                        'warning' => 'adjustment',
                    ]),
 
                TextColumn::make('description')
                    ->label('Deskripsi')
                    ->searchable()
                    ->limit(40),
 
                TextColumn::make('account.name')
                    ->label('Akun')
                    ->searchable(),
 
                TextColumn::make('destinationAccount.name')
                    ->label('Akun Tujuan')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
 
                TextColumn::make('amount')
                    ->label('Jumlah')
                    ->formatStateUsing(function ($state, Transaction $record) {
                        $formatted = 'Rp ' . number_format($state, 0, ',', '.');
                        return match($record->type) {
                            'income' => '+' . $formatted,
                            'expense' => '-' . $formatted,
                            'transfer' => $formatted,
                            'adjustment' => ($state >= 0 ? '+' : '') . $formatted,
                            default => $formatted,
                        };
                    })
                    ->color(fn (Transaction $record) => match($record->type) {
                        'income' => 'success',
                        'expense' => 'danger',
                        'adjustment' => fn ($state) => $state >= 0 ? 'success' : 'danger',
                        default => 'gray',
                    })
                    ->weight(\Filament\Support\Enums\FontWeight::SemiBold)
                    ->sortable(),
 
                TextColumn::make('admin_fee')
                    ->label('Biaya Admin')
                    ->money('IDR')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
 
                IconColumn::make('is_split')
                    ->label('Split')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Tipe Transaksi')
                    ->options([
                        'income' => 'Pemasukan',
                        'expense' => 'Pengeluaran',
                        'transfer' => 'Transfer',
                        'adjustment' => 'Penyesuaian',
                    ]),
 
                SelectFilter::make('account_id')
                    ->label('Akun')
                    ->options(fn () => Account::where('user_id', Auth::user()->id)->pluck('name', 'id')),
 
                Filter::make('date_range')
                    ->label('Rentang Tanggal')
                    ->form([
                        DatePicker::make('date_from')->label('Dari'),
                        DatePicker::make('date_until')->label('Sampai'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['date_from'], fn ($q, $date) => $q->whereDate('date', '>=', $date))
                            ->when($data['date_until'], fn ($q, $date) => $q->whereDate('date', '<=', $date));
                    }),

            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
