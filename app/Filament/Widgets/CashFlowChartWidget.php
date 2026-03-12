<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

class CashFlowChartWidget extends ChartWidget
{
    protected ?string $heading = 'Cash Flow Chart Widget';
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 1;
    public ?string $filter = 'monthly';
 
    protected function getFilters(): ?array
    {
        return [
            'monthly' => 'Per Bulan',
            'weekly' => 'Per Minggu',
        ];
    }
 
    protected function getData(): array
    {
        $userId = Auth::user()->id;
 
        if ($this->filter === 'weekly') {
            $labels = [];
            $incomeData = [];
            $expenseData = [];
 
            for ($i = 11; $i >= 0; $i--) {
                $start = Carbon::now()->subWeeks($i)->startOfWeek();
                $end = Carbon::now()->subWeeks($i)->endOfWeek();
                $labels[] = $start->format('d M');
 
                $incomeData[] = Transaction::where('user_id', $userId)
                    ->where('type', 'income')
                    ->whereBetween('date', [$start, $end])
                    ->sum('amount');
 
                $expenseData[] = Transaction::where('user_id', $userId)
                    ->where('type', 'expense')
                    ->whereBetween('date', [$start, $end])
                    ->sum('amount');
            }
        } else {
            $labels = [];
            $incomeData = [];
            $expenseData = [];
 
            for ($i = 5; $i >= 0; $i--) {
                $start = Carbon::now()->subMonths($i)->startOfMonth();
                $end = Carbon::now()->subMonths($i)->endOfMonth();
                $labels[] = $start->locale('id')->translatedFormat('M Y');
 
                $incomeData[] = Transaction::where('user_id', $userId)
                    ->where('type', 'income')
                    ->whereBetween('date', [$start, $end])
                    ->sum('amount');
 
                $expenseData[] = Transaction::where('user_id', $userId)
                    ->where('type', 'expense')
                    ->whereBetween('date', [$start, $end])
                    ->sum('amount');
            }
        }
 
        return [
            'datasets' => [
                [
                    'label' => 'Pemasukan',
                    'data' => $incomeData,
                    'borderColor' => '#10b981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'fill' => true,
                    'tension' => 0.4,
                ],
                [
                    'label' => 'Pengeluaran',
                    'data' => $expenseData,
                    'borderColor' => '#ef4444',
                    'backgroundColor' => 'rgba(239, 68, 68, 0.1)',
                    'fill' => true,
                    'tension' => 0.4,
                ],
            ],
            'labels' => $labels,
        ];
    }
 
    protected function getType(): string
    {
        return 'line';
    }
 
    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'ticks' => [
                        'callback' => "function(value) { return 'Rp ' + new Intl.NumberFormat('id-ID').format(value); }",
                    ],
                ],
            ],
        ];
    }

}
