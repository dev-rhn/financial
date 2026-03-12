<?php

namespace App\Filament\Resources\Categories\Schemas;

use App\Models\Category;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Toggle;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Kategori')
                            ->required()
                            ->maxLength(255),
 
                        ToggleButtons::make('type')
                            ->label('Tipe')
                            ->options([
                                'income' => 'Pemasukan',
                                'expense' => 'Pengeluaran',
                            ])
                            ->colors([
                                'income' => 'success',
                                'expense' => 'danger',
                            ])
                            ->required()
                            ->inline(),
 
                        Select::make('parent_id')
                            ->label('Sub-kategori dari')
                            ->options(fn () => Category::where('user_id', auth()->id())
                                ->whereNull('parent_id')
                                ->pluck('name', 'id')
                            )
                            ->placeholder('— Tidak ada (kategori utama) —')
                            ->searchable(),
 
                        ColorPicker::make('color')
                            ->label('Warna')
                            ->default('#6366f1'),
 
                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true),
                    ])
                    ->columns(2),
            ]);
    }
}
