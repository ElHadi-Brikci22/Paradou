<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Expense;
use App\Models\Client;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Render the admin analytics dashboard.
     */
    public function index(Request $request)
    {
        $range = $request->input('range', 'month');
        $start = now()->startOfMonth();
        $end = now()->endOfDay();

        switch ($range) {
            case 'today':
                $start = now()->startOfDay();
                break;
            case 'week':
                $start = now()->startOfWeek();
                break;
            case 'month':
                $start = now()->startOfMonth();
                break;
            case 'year':
                $start = now()->startOfYear();
                break;
            case 'all':
                // Far past to far future
                $start = Carbon::parse('2000-01-01 00:00:00');
                $end = Carbon::parse('2050-12-31 23:59:59');
                break;
        }

        // 1. Core Financial metrics
        $totalNetCA = Order::whereBetween('order_date', [$start, $end])->sum('total_amount');
        $totalCollected = Order::whereBetween('order_date', [$start, $end])->sum('paid_amount');
        $totalBalance = Order::whereBetween('order_date', [$start, $end])->sum('balance_amount');
        $totalExpenses = Expense::whereBetween('expense_date', [$start, $end])->sum('amount');
        
        // Net profit is the actual cash collected minus the expenses incurred
        $netProfit = $totalCollected - $totalExpenses;

        $totalOrdersCount = Order::whereBetween('order_date', [$start, $end])->count();
        $totalClientsCount = Client::count();

        // 2. Services popularity (Quantity of clothes and revenue generated per service)
        $servicesBreakdown = OrderItem::whereHas('order', function ($query) use ($start, $end) {
                $query->whereBetween('order_date', [$start, $end]);
            })
            ->select('service_id', DB::raw('SUM(quantity) as qty'), DB::raw('SUM(total_price) as revenue'))
            ->groupBy('service_id')
            ->with('service')
            ->get();

        // Prepare charts variables
        $serviceLabels = [];
        $serviceRevenues = [];
        $serviceColors = ['#4f46e5', '#10b981', '#3b82f6', '#f59e0b', '#ec4899', '#8b5cf6'];
        
        foreach ($servicesBreakdown as $sb) {
            $serviceLabels[] = $sb->service->name;
            $serviceRevenues[] = floatval($sb->revenue);
        }

        // 3. Top 5 active clients by billing amount in range
        $topClients = Order::whereBetween('order_date', [$start, $end])
            ->select('client_id', DB::raw('SUM(total_amount) as total_spent'), DB::raw('COUNT(id) as tickets_count'))
            ->groupBy('client_id')
            ->orderBy('total_spent', 'desc')
            ->limit(5)
            ->with('client')
            ->get();

        // 4. Monthly/Daily revenue trend for Chart (shows sales line chart)
        // Group by day for month range, group by month for year/all range
        $trendData = [];
        $trendLabels = [];

        if ($range === 'today' || $range === 'week' || $range === 'month') {
            $salesTrend = Order::whereBetween('order_date', [$start, $end])
                ->select(DB::raw('DATE(order_date) as date'), DB::raw('SUM(total_amount) as total'))
                ->groupBy('date')
                ->orderBy('date', 'asc')
                ->get();
            
            foreach ($salesTrend as $trend) {
                $trendLabels[] = Carbon::parse($trend->date)->format('d/m');
                $trendData[] = floatval($trend->total);
            }
        } else {
            // Group by year-month for longer periods
            $salesTrend = Order::whereBetween('order_date', [$start, $end])
                ->select(DB::raw('DATE_FORMAT(order_date, "%Y-%m") as month'), DB::raw('SUM(total_amount) as total'))
                ->groupBy('month')
                ->orderBy('month', 'asc')
                ->get();
            
            foreach ($salesTrend as $trend) {
                $trendLabels[] = Carbon::parse($trend->month . '-01')->format('M Y');
                $trendData[] = floatval($trend->total);
            }
        }

        return view('admin.dashboard', compact(
            'range',
            'totalNetCA',
            'totalCollected',
            'totalBalance',
            'totalExpenses',
            'netProfit',
            'totalOrdersCount',
            'totalClientsCount',
            'servicesBreakdown',
            'topClients',
            'serviceLabels',
            'serviceRevenues',
            'serviceColors',
            'trendLabels',
            'trendData'
        ));
    }
}
