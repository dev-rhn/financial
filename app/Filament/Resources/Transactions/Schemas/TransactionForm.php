<?php

namespace App\Filament\Resources\Transactions\Schemas;

use App\Models\Account;
use App\Models\Category;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class TransactionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Jenis Transaksi')
                    ->schema([
                        ToggleButtons::make('type')
                            ->label('Tipe')
                            ->options([
                                'income' => 'Pemasukan',
                                'expense' => 'Pengeluaran',
                                'transfer' => 'Transfer / Tarik Tunai',
                            ])
                            ->icons([
                                'income' => 'heroicon-o-arrow-down-circle',
                                'expense' => 'heroicon-o-arrow-up-circle',
                                'transfer' => 'heroicon-o-arrows-right-left',
                            ])
                            ->colors([
                                'income' => 'success',
                                'expense' => 'danger',
                                'transfer' => 'info',
                            ])
                            ->required()
                            ->live()
                            ->inline()
                            ->columnSpanFull(),
                    ]),
 
                Section::make('Detail Transaksi')
                    ->schema([
                        DatePicker::make('date')
                            ->label('Tanggal')
                            ->required()
                            ->default(now()),
 
                        Select::make('account_id')
                            ->label(fn (Get $get) => $get('type') === 'transfer' ? 'Akun Asal' : 'Akun')
                            ->required()
                            ->options(fn () => Account::where('user_id', Auth::user()->id)
                                ->where('is_active', true)
                                ->get()
                                ->mapWithKeys(fn ($a) => [$a->id => "{$a->name} (Rp " . number_format($a->current_balance, 0, ',', '.') . ")"])
                            )
                            ->searchable()
                            ->live(),
 
                        Select::make('destination_account_id')
                            ->label('Akun Tujuan')
                            ->required()
                            ->options(fn (Get $get) => Account::where('user_id', Auth::user()->id)
                                ->where('is_active', true)
                                ->where('id', '!=', $get('account_id'))
                                ->get()
                                ->mapWithKeys(fn ($a) => [$a->id => "{$a->name} (Rp " . number_format($a->current_balance, 0, ',', '.') . ")"])
                            )
                            ->searchable()
                            ->visible(fn (Get $get) => $get('type') === 'transfer'),
 
                        TextInput::make('amount')
                            ->label(fn (Get $get) => $get('type') === 'transfer' ? 'Jumlah Dikirim' : 'Jumlah')
                            ->required()
                            ->numeric()
                            ->prefix('Rp')
                            ->live(debounce: 500)
                            ->afterStateUpdated(function (Get $get, Set $set, $state) {
                                if ($get('type') === 'transfer') {
                                    $adminFee = (float)($get('admin_fee') ?? 0);
                                    $set('destination_amount', max(0, (float)$state - $adminFee));
                                }
                            }),
 
                        TextInput::make('admin_fee')
                            ->label('Biaya Admin')
                            ->numeric()
                            ->prefix('Rp')
                            ->default(0)
                            ->live(debounce: 500)
                            ->afterStateUpdated(function (Get $get, Set $set, $state) {
                                $amount = (float)($get('amount') ?? 0);
                                $set('destination_amount', max(0, $amount - (float)$state));
                            })
                            ->visible(fn (Get $get) => $get('type') === 'transfer')
                            ->helperText('Biaya admin akan mengurangi saldo akun asal, tapi tidak ditambahkan ke tujuan.'),
 
                        TextInput::make('destination_amount')
                            ->label('Jumlah Diterima')
                            ->numeric()
                            ->prefix('Rp')
                            ->visible(fn (Get $get) => $get('type') === 'transfer')
                            ->helperText('Otomatis dihitung: Jumlah Dikirim - Biaya Admin'),
 
                        TextInput::make('description')
                            ->label('Deskripsi')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Contoh: Gaji Bulan Ini, Makan Siang, dll.'),
 
                        TextInput::make('reference_number')
                            ->label('No. Referensi')
                            ->placeholder('Opsional')
                            ->maxLength(100),
                    ])
                    ->columns(2),
 
                // Split Transaction Section
                Section::make('Kategori Transaksi')
                    ->schema([
                        ToggleButtons::make('is_split')
                            ->label('Split Transaksi (Beberapa Kategori)')
                            ->live()
                            ->visible(fn (Get $get) => in_array($get('type'), ['income', 'expense'])),
 
                        // Single category (non-split)
                        Select::make('_single_category_id')
                            ->label('Kategori')
                            ->options(fn (Get $get) => Category::where('user_id', Auth::user()->id)
                                ->where('type', $get('type') === 'income' ? 'income' : 'expense')
                                ->where('is_active', true)
                                ->get()
                                ->mapWithKeys(fn ($c) => [$c->id => $c->name])
                            )
                            ->searchable()
                            ->visible(fn (Get $get) =>
                                in_array($get('type'), ['income', 'expense']) && !$get('is_split')
                            ),
 
                        // Split items
                        Repeater::make('items')
                            ->label('Item Transaksi')
                            ->relationship()
                            ->schema([
                                Select::make('category_id')
                                    ->label('Kategori')
                                    ->options(fn (Get $get) => Category::where('user_id', Auth::user()->id)
                                        ->where('is_active', true)
                                        ->get()
                                        ->mapWithKeys(fn ($c) => [$c->id => "{$c->name} ({$c->getTypeLabel()})"])
                                    )
                                    ->required()
                                    ->searchable(),
 
                                TextInput::make('amount')
                                    ->label('Jumlah')
                                    ->required()
                                    ->numeric()
                                    ->prefix('Rp'),
 
                                TextInput::make('description')
                                    ->label('Deskripsi Item')
                                    ->placeholder('Opsional'),
                            ])
                            ->columns(3)
                            ->minItems(1)
                            ->addActionLabel('+ Tambah Item')
                            ->visible(fn (Get $get) =>
                                in_array($get('type'), ['income', 'expense']) && $get('is_split')
                            )
                            ->columnSpanFull(),
                    ])
                    ->visible(fn (Get $get) => in_array($get('type'), ['income', 'expense'])),
 
                Section::make('Catatan')
                    ->schema([
                        Textarea::make('notes')
                            ->label('Catatan Tambahan')
                            ->rows(2)
                            ->columnSpanFull(),
                    ])
                    ->collapsed(),
            ]);
    }
}
