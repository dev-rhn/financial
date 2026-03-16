<?php

namespace App\Filament\Resources\Providers\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProviderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Provider')
                            ->required()
                            ->maxLength(100),

                        ToggleButtons::make('type')
                            ->label('Tipe')
                            ->options([
                                'bank' => 'Bank', 
                                'ewallet' => 'E-Wallet',
                                'cash' => 'Tunai (Cash)',
                            ])
                            ->colors([
                                'bank' => 'warning', 
                                'ewallet' => 'info',
                                'cash' => 'success',
                            ])
                            ->required()
                            ->inline(),

                        TextInput::make('sort_order')
                            ->label('Urutan Tampil')
                            ->numeric()
                            ->default(0),

                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true),
                        ])->columnSpanFull(),
                    ]);
    }
}
