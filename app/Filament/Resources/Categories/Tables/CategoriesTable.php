<?php

namespace App\Filament\Resources\Categories\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ColorColumn::make('color')->label(''),
 
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->weight(\Filament\Support\Enums\FontWeight::SemiBold),
 
                BadgeColumn::make('type')
                    ->label('Tipe')
                    ->formatStateUsing(fn ($state) => $state === 'income' ? 'Pemasukan' : 'Pengeluaran')
                    ->colors(['success' => 'income', 'danger' => 'expense']),
 
                TextColumn::make('parent.name')
                    ->label('Parent')
                    ->placeholder('Kategori Utama'),
 
                TextColumn::make('transaction_items_count')
                    ->label('Jumlah Transaksi')
                    ->counts('transactionItems'),
 
                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->options(['income' => 'Pemasukan', 'expense' => 'Pengeluaran']),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
