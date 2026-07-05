<?php

namespace App\Filament\Widgets;

use App\Models\PageView;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\DB;

class AnalyticsOverviewWidget extends Widget
{
    public static function shouldRegister(): bool
    {
        $user = auth()->user();
        return $user && $user->role === 'admin';
    }
    protected static string $view = 'filament.widgets.analytics-overview-widget';

    protected static ?int $sort = 1;

    protected int | string | array $columnSpan = 'full';

    protected function getViewData(): array
    {
        // Visitas totales (sesiones únicas) por período
        $todayViews      = PageView::today()->count();
        $todayUnique     = PageView::today()->distinct('session_id')->count('session_id');
        $weekViews       = PageView::thisWeek()->count();
        $weekUnique      = PageView::thisWeek()->distinct('session_id')->count('session_id');
        $monthViews      = PageView::thisMonth()->count();
        $monthUnique     = PageView::thisMonth()->distinct('session_id')->count('session_id');

        // Top 5 páginas más vistas este mes
        $topPages = PageView::thisMonth()
            ->select('path', DB::raw('count(*) as total'))
            ->groupBy('path')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        // Dispositivos este mes
        $devices = PageView::thisMonth()
            ->select('device', DB::raw('count(*) as total'))
            ->groupBy('device')
            ->orderByDesc('total')
            ->get();

        // Fuentes de tráfico este mes (utm_source o "directo")
        $sources = PageView::thisMonth()
            ->select(
                DB::raw("COALESCE(utm_source, 'directo') as source"),
                DB::raw('count(*) as total')
            )
            ->groupBy('source')
            ->orderByDesc('total')
            ->limit(6)
            ->get();

        // Visitas por día últimos 14 días (para mini gráfico)
        $dailyViews = PageView::where('created_at', '>=', now()->subDays(13)->startOfDay())
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('count(*) as total'),
                DB::raw('count(distinct session_id) as unique_total')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        // Rellenar días sin visitas con 0
        $days = [];
        for ($i = 13; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $days[] = [
                'date'   => now()->subDays($i)->format('d/m'),
                'total'  => $dailyViews->get($date)?->total ?? 0,
                'unique' => $dailyViews->get($date)?->unique_total ?? 0,
            ];
        }

        return compact(
            'todayViews', 'todayUnique',
            'weekViews',  'weekUnique',
            'monthViews', 'monthUnique',
            'topPages', 'devices', 'sources', 'days'
        );
    }
}
