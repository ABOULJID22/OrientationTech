<?php

namespace App\Filament\Widgets;

use App\Models\Purchase;
use App\Models\TradeOperation;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon; // Ensure Carbon is imported

class ClientKpis extends BaseWidget
{
    protected static ?int $sort = 2; // Optional: Adjust sorting if you have other widgets
    protected ?string $pollingInterval = '60s';

    protected function getStats(): array
    {
        $user = auth()->user();
        // Assuming isClient() is defined on your User model and returns a boolean
        $isClient = $user && method_exists($user, 'isClient') && $user->isClient();

        $purchasesQuery = Purchase::query();
        $tradesQuery = TradeOperation::query();

        if ($isClient) {
            $purchasesQuery->where('user_id', $user->id);
            $tradesQuery->where('user_id', $user->id);
        }

        $ordersCount = $purchasesQuery->count();
        
        // Find the next order date that is in the future
        $nextOrder = (clone $purchasesQuery)
            ->whereNotNull('next_order_date')
            ->where('next_order_date', '>=', Carbon::today()) // Only future orders
            ->orderBy('next_order_date')
            ->value('next_order_date');

        // Consider 'challenge_end' as the trade end date
        $activeTrades = (clone $tradesQuery)
            ->where(function ($q) {
                // A trade is active if challenge_end is NULL (ongoing indefinitely)
                // or if challenge_end is in the future or today.
                $q->whereNull('challenge_end')->orWhere('challenge_end', '>=', Carbon::today());
            })
            ->count();

    // Determine color for Next Order based on proximity
    $nextOrderColor = 'gray';
    $nextOrderDescription = __('widgets.client.next_order_no_schedule');
        if ($nextOrder) {
            $nextOrderCarbon = Carbon::parse($nextOrder);
            if ($nextOrderCarbon->isToday()) {
                $nextOrderColor = 'warning'; // Order due today
                $nextOrderDescription = __('widgets.client.next_order_today');
            } elseif ($nextOrderCarbon->isFuture() && $nextOrderCarbon->diffInDays(Carbon::today()) <= 7) {
                $nextOrderColor = 'info'; // Order in the next 7 days
                $nextOrderDescription = __('widgets.client.next_order_week');
            } elseif ($nextOrderCarbon->isPast()) { // This case should ideally be caught by the where clause above
                $nextOrderColor = 'danger'; // Should not happen with the where clause, but good for robustness
                $nextOrderDescription = __('widgets.client.next_order_past');
            } else {
                $nextOrderColor = 'success'; // Order in the distant future
            }
        } else {
             $nextOrderDescription = __('widgets.client.next_order_no_schedule');
        }


        return [
            Stat::make(__('widgets.client.total_orders'), number_format($ordersCount))
                ->description(__('widgets.client.total_orders_description'))
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->icon('heroicon-o-shopping-cart')
                ->color('primary')
                ->url(route('filament.admin.resources.purchases.index'))
                ->chart([3, 5, 4, 6, 7, 8, 10, 9, 12, 11, 14, 13])
                ->extraAttributes([
                    'class' => 'stat-card stat-card-primary',
                ]),

            Stat::make(__('widgets.client.next_order'), $nextOrder ? Carbon::parse($nextOrder)->format('d/m/Y') : __('widgets.client.next_order_none'))
                ->description($nextOrderDescription)
                ->descriptionIcon($nextOrder ? 'heroicon-m-calendar-days' : 'heroicon-m-sparkles')
                ->icon($nextOrder ? 'heroicon-o-calendar-days' : 'heroicon-o-sparkles')
                ->color($nextOrderColor)
                ->chart([3, 4, 5, 2, 6, 4, 9])
                ->extraAttributes([
                    'class' => 'stat-card stat-card-neutral',
                ]),

            Stat::make(__('widgets.client.active_trades'), number_format($activeTrades))
                ->description(__('widgets.client.active_trades_description'))
                ->descriptionIcon('heroicon-m-megaphone')
                ->icon('heroicon-o-megaphone')
                ->color($activeTrades > 0 ? 'success' : 'gray')
                ->chart([3, 4, 5, 2, 6, 4, 8])
                ->extraAttributes([
                    'class' => $activeTrades > 0 ? 'stat-card stat-card-success' : 'stat-card stat-card-neutral',
                ]),
        ];
    }

    protected function getColumns(): array
    {
        return [
            'default' => 1,
            'sm' => 2,
            'lg' => 3,
        ];
    }

    public static function canView(): bool
    {
        $user = auth()->user();
        return $user && ($user->isClient() || $user->isSuperAdmin());
    }
}