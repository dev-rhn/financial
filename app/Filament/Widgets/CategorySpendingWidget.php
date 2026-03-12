<?php

namespace App\Filament\Widgets;

use App\Models\TransactionItem;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

class CategorySpendingWidget extends ChartWidget
{
    protected ?string $heading = 'Pengeluaran per Kategori (Bulan Ini)';
    protected static ?int $sort = 4;
    protected int | string | array $columnSpan = 1;
 
    protected function getData(): array
    {
        $userId = Auth::user()->id;
        $start = Carbon::now()->startOfMonth();
        $end = Carbon::now()->endOfMonth();
 
        $spending = TransactionItem::whereHas('transaction', fn ($q) =>
            $q->where('user_id', $userId)
              ->where('type', 'expense')
              ->whereBetween('date', [$start, $end])
        )
        ->with('category')
        ->get()
        ->groupBy('category_id')
        ->map(fn ($items) => [
            'name' => $items->first()->category?->name ?? 'Tanpa Kategori',
            'total' => $items->sum('amount'),
            'color' => $items->first()->category?->color ?? '#6b7280',
        ])
        ->sortByDesc('total')
        ->take(8)
        ->values();
 
        // Also include non-split expenses
        $directExpenses = \App\Models\Transaction::where('user_id', $userId)
            ->where('type', 'expense')
            ->where('is_split', false)
            ->whereBetween('date', [$start, $end])
            ->get();
 
        return [
            'datasets' => [
                [
                    'data' => $spending->pluck('total')->toArray(),
                    'backgroundColor' => $spending->pluck('color')->toArray(),
                    'borderWidth' => 2,
                    'borderColor' => '#1f2937',
                ],
            ],
            'labels' => $spending->pluck('name')->toArray(),
        ];
    }
 
    protected function getType(): string
    {
        return 'bar';
    }
 
    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                ],
            ],
        ];
    }
}
