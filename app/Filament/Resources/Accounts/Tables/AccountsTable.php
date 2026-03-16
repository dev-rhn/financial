<?php

namespace App\Filament\Resources\Accounts\Tables;

use App\Models\Account;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class AccountsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ColorColumn::make('color')
                    ->label(''),
 
                TextColumn::make('name')
                    ->label('Nama Akun')
                    ->searchable()
                    ->weight(\Filament\Support\Enums\FontWeight::SemiBold),
 
                BadgeColumn::make('type')
                    ->label('Tipe')
                    ->formatStateUsing(fn ($state) => match($state) {
                        'bank' => 'Bank',
                        'ewallet' => 'E-Wallet',
                        'cash' => 'Tunai',
                        default => $state,
                    })
                    ->colors([
                        'primary' => 'bank',
                        'success' => 'ewallet',
                        'warning' => 'cash',
                    ]),
 
                TextColumn::make('provider.name')
                    ->label('Provider')
                    ->placeholder('—'),
 
                TextColumn::make('current_balance')
                    ->label('Saldo Saat Ini')
                    ->money('IDR')
                    ->sortable()
                    ->color(fn ($state) => $state < 0 ? 'danger' : 'success')
                    ->weight(\Filament\Support\Enums\FontWeight::Bold),
 
                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
 
                TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Tipe Akun')
                    ->options([
                        'bank' => 'Bank',
                        'ewallet' => 'E-Wallet',
                        'cash' => 'Tunai',
                    ]),
                TernaryFilter::make('is_active')
                    ->label('Status Aktif'),
            ])
            ->recordActions([
                Action::make('adjustment')
                    ->label('Sesuaikan Saldo')
                    ->icon('heroicon-o-adjustments-horizontal')
                    ->color('warning')
                    ->form([
                        TextInput::make('actual_balance')
                            ->label('Saldo Aktual (Hitung Fisik)')
                            ->numeric()
                            ->prefix('Rp')
                            ->required()
                            ->helperText('Masukkan jumlah uang yang benar-benar ada.'),
                        Textarea::make('notes')
                            ->label('Alasan Penyesuaian')
                            ->rows(2),
                    ])
                    ->action(function (Account $record, array $data) {
                        $difference = $data['actual_balance'] - $record->current_balance;
 
                        if ($difference != 0) {
                            \App\Models\Transaction::create([
                                'user_id' => Auth::user()->id,
                                'account_id' => $record->id,
                                'type' => 'adjustment',
                                'amount' => $difference,
                                'date' => now()->toDateString(),
                                'description' => 'Penyesuaian Saldo',
                                'notes' => $data['notes'] ?? null,
                            ]);
                        }
                    })
                    ->visible(fn (Account $record) => $record->type === 'cash'),
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
