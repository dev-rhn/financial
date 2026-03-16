<?php

namespace App\Filament\Resources\Accounts\Schemas;

use App\Models\Provider;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class AccountForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Akun')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Akun')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Contoh: BCA Utama, GoPay, Dompet Harian'),
 
                        Select::make('type')
                            ->label('Tipe Akun')
                            ->required()
                            ->options([
                                'bank' => '🏦 Bank',
                                'ewallet' => '📱 E-Wallet',
                                'cash' => '💵 Tunai (Cash)',
                            ])
                            ->live()
                            ->afterStateUpdated(fn ($state, Set $set) =>
                                $state === 'cash' ? $set('provider', null) : null
                            ),

                        Select::make('provider_id') 
                            ->label('Provider / Bank')
                            ->options(fn (Get $get) => Provider::where('type', $get('type'))
                                ->where('is_active', true)
                                ->orderBy('sort_order')
                                ->pluck('name', 'id')
                            )
                            ->searchable()
                            ->visible(fn (Get $get) => $get('type') !== 'cash'),
 
                        TextInput::make('account_number')
                            ->label('Nomor Rekening / Akun')
                            ->placeholder('Contoh: 1234567890')
                            ->visible(fn (Get $get) => $get('type') === 'bank')
                            ->maxLength(50)
                            ->columnSpanFull(),
                    ])
                    ->columns(3)
                    ->columnSpanFull(),
 
                Section::make('Saldo & Tampilan')
                    ->schema([
                        TextInput::make('initial_balance')
                            ->label('Saldo Awal')
                            ->numeric()
                            ->prefix('Rp')
                            ->default(0)
                            ->helperText('Masukkan saldo saat ini sebagai saldo awal untuk memulai pencatatan.'),
 
                        ColorPicker::make('color')
                            ->label('Warna Akun')
                            ->default('#6366f1'),
 
                        Toggle::make('is_active')
                            ->label('Akun Aktif')
                            ->default(true),
 
                        Textarea::make('notes')
                            ->label('Catatan')
                            ->rows(2)
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }
}
