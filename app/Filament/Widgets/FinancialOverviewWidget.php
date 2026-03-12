<?php

namespace App\Filament\Widgets;

use App\Models\Account;
use App\Models\Transaction;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget;
use Illuminate\Support\Facades\Auth;

class FinancialOverviewWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 1;
 
    protected function getStats(): array
    {
        $userId = Auth::user()->id;
        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth = $now->copy()->endOfMonth();
        $lastMonthStart = $now->copy()->subMonth()->startOfMonth();
        $lastMonthEnd = $now->copy()->subMonth()->endOfMonth();
 
        // Net worth
        $netWorth = Account::where('user_id', $userId)
            ->where('is_active', true)
            ->sum('current_balance');
 
        // This month income
        $monthlyIncome = Transaction::where('user_id', $userId)
            ->where('type', 'income')
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->sum('amount');
 
        $lastMonthIncome = Transaction::where('user_id', $userId)
            ->where('type', 'income')
            ->whereBetween('date', [$lastMonthStart, $lastMonthEnd])
            ->sum('amount');
 
        // This month expense
        $monthlyExpense = Transaction::where('user_id', $userId)
            ->where('type', 'expense')
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->sum('amount');
 
        $lastMonthExpense = Transaction::where('user_id', $userId)
            ->where('type', 'expense')
            ->whereBetween('date', [$lastMonthStart, $lastMonthEnd])
            ->sum('amount');
 
        // Net cash flow
        $netFlow = $monthlyIncome - $monthlyExpense;
 
        // Income trend (last 7 days)
        $incomeTrend = collect(range(6, 0))->map(fn ($i) =>
            Transaction::where('user_id', $userId)
                ->where('type', 'income')
                ->whereDate('date', $now->copy()->subDays($i))
                ->sum('amount')
        )->toArray();
 
        $expenseTrend = collect(range(6, 0))->map(fn ($i) =>
            Transaction::where('user_id', $userId)
                ->where('type', 'expense')
                ->whereDate('date', $now->copy()->subDays($i))
                ->sum('amount')
        )->toArray();
 
        $incomeChange = $lastMonthIncome > 0
            ? round((($monthlyIncome - $lastMonthIncome) / $lastMonthIncome) * 100, 1)
            : 0;
        $expenseChange = $lastMonthExpense > 0
            ? round((($monthlyExpense - $lastMonthExpense) / $lastMonthExpense) * 100, 1)
            : 0;
 
        return [
            Stat::make('Total Kekayaan Bersih', 'Rp ' . number_format($netWorth, 0, ',', '.'))
                ->description('Seluruh saldo akun aktif')
                ->descriptionIcon('heroicon-m-wallet')
                ->color($netWorth >= 0 ? 'success' : 'danger')
                ->chart([7, 2, 10, 3, 15, 4, $netWorth > 0 ? 20 : 0]),
 
            Stat::make('Pemasukan Bulan Ini', 'Rp ' . number_format($monthlyIncome, 0, ',', '.'))
                ->description(($incomeChange >= 0 ? '+' : '') . $incomeChange . '% vs bulan lalu')
                ->descriptionIcon($incomeChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color('success')
                ->chart($incomeTrend),
 
            Stat::make('Pengeluaran Bulan Ini', 'Rp ' . number_format($monthlyExpense, 0, ',', '.'))
                ->description(($expenseChange >= 0 ? '+' : '') . $expenseChange . '% vs bulan lalu')
                ->descriptionIcon($expenseChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color('danger')
                ->chart($expenseTrend),
 
            Stat::make('Arus Kas Bersih', 'Rp ' . number_format($netFlow, 0, ',', '.'))
                ->description('Income-Expenses bulan ini')
                ->descriptionIcon($netFlow >= 0 ? 'heroicon-m-check-circle' : 'heroicon-m-exclamation-circle')
                ->color($netFlow >= 0 ? 'success' : 'danger'),
        ];
    }
}
