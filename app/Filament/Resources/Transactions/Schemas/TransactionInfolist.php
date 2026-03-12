<?php

namespace App\Filament\Resources\Transactions\Schemas;

use App\Models\Transaction;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TransactionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Detail Transaksi')
                    ->schema([
                        TextEntry::make('type')
                            ->label('Tipe')
                            ->badge()
                            ->formatStateUsing(fn ($state) => match($state) {
                                'income' => 'Pemasukan', 'expense' => 'Pengeluaran',
                                'transfer' => 'Transfer', 'adjustment' => 'Penyesuaian', default => $state,
                            })
                            ->color(fn ($state) => match($state) {
                                'income' => 'success', 'expense' => 'danger',
                                'transfer' => 'info', 'adjustment' => 'warning', default => 'gray',
                            }),
 
                        TextEntry::make('date')->label('Tanggal')->date('d MMMM Y'),
                        TextEntry::make('description')->label('Deskripsi'),
                        TextEntry::make('account.name')->label('Akun'),
                        TextEntry::make('amount')->label('Jumlah')->money('IDR'),
                        TextEntry::make('admin_fee')->label('Biaya Admin')->money('IDR'),
                        TextEntry::make('destinationAccount.name')->label('Akun Tujuan')->placeholder('—'),
                        TextEntry::make('destination_amount')->label('Jumlah Diterima')->money('IDR')->placeholder('—'),
                        TextEntry::make('reference_number')->label('No. Referensi')->placeholder('—'),
                        TextEntry::make('notes')->label('Catatan')->placeholder('—')->columnSpanFull(),
                    ])
                    ->columns(2),
 
                Section::make('Item Transaksi (Split)')
                    ->schema([
                        RepeatableEntry::make('items')
                            ->label('')
                            ->schema([
                                TextEntry::make('category.name')->label('Kategori'),
                                TextEntry::make('amount')->label('Jumlah')->money('IDR'),
                                TextEntry::make('description')->label('Keterangan')->placeholder('—'),
                            ])
                            ->columns(3),
                    ])
                    ->visible(fn (Transaction $record) => $record->is_split),
            ]);
    }
}
