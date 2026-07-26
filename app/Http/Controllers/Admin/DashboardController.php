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
        $userId = $request->input('user_id', 'all');

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

        // Load users for filtering and comparisons
        $users = \App\Models\User::all();

        // 1. Core Financial metrics
        $orderQuery = Order::whereBetween('order_date', [$start, $end]);
        $expenseQuery = Expense::whereBetween('expense_date', [$start, $end]);

        if ($userId !== 'all') {
            $orderQuery->where('user_id', $userId);
            $expenseQuery->where('user_id', $userId);
        }

        $totalNetCA = (clone $orderQuery)->sum('total_amount');
        $totalCollected = (clone $orderQuery)->sum('paid_amount');
        $totalBalance = (clone $orderQuery)->sum('balance_amount');
        $totalExpenses = (clone $expenseQuery)->sum('amount');
        
        // Net profit is the actual cash collected minus the expenses incurred
        $netProfit = $totalCollected - $totalExpenses;

        $totalOrdersCount = (clone $orderQuery)->count();
        $totalClientsCount = Client::count();

        // 2. Services popularity (Quantity of clothes and revenue generated per service)
        $servicesBreakdown = OrderItem::whereHas('order', function ($query) use ($start, $end, $userId) {
                $query->whereBetween('order_date', [$start, $end]);
                if ($userId !== 'all') {
                    $query->where('user_id', $userId);
                }
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
        $topClientsQuery = Order::whereBetween('order_date', [$start, $end]);
        if ($userId !== 'all') {
            $topClientsQuery->where('user_id', $userId);
        }
        $topClients = $topClientsQuery
            ->select('client_id', DB::raw('SUM(total_amount) as total_spent'), DB::raw('COUNT(id) as tickets_count'))
            ->groupBy('client_id')
            ->orderBy('total_spent', 'desc')
            ->limit(5)
            ->with('client')
            ->get();

        // 4. Monthly/Daily revenue trend for Chart (shows sales line chart)
        $trendData = [];
        $trendLabels = [];

        $trendQuery = Order::whereBetween('order_date', [$start, $end]);
        if ($userId !== 'all') {
            $trendQuery->where('user_id', $userId);
        }

        if ($range === 'today' || $range === 'week' || $range === 'month') {
            $salesTrend = (clone $trendQuery)
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
            $salesTrend = (clone $trendQuery)
                ->select(DB::raw('DATE_FORMAT(order_date, "%Y-%m") as month'), DB::raw('SUM(total_amount) as total'))
                ->groupBy('month')
                ->orderBy('month', 'asc')
                ->get();
            
            foreach ($salesTrend as $trend) {
                $trendLabels[] = Carbon::parse($trend->month . '-01')->format('M Y');
                $trendData[] = floatval($trend->total);
            }
        }

        // 5. Calculations per user (when 'all' is selected)
        $usersStats = [];
        if ($userId === 'all') {
            foreach ($users as $u) {
                $userCA = Order::where('user_id', $u->id)->whereBetween('order_date', [$start, $end])->sum('total_amount');
                $userCollected = Order::where('user_id', $u->id)->whereBetween('order_date', [$start, $end])->sum('paid_amount');
                $userExpenses = Expense::where('user_id', $u->id)->whereBetween('expense_date', [$start, $end])->sum('amount');
                $userTickets = Order::where('user_id', $u->id)->whereBetween('order_date', [$start, $end])->count();
                $usersStats[] = [
                    'user' => $u,
                    'ca' => floatval($userCA),
                    'collected' => floatval($userCollected),
                    'expenses' => floatval($userExpenses),
                    'profit' => floatval($userCollected - $userExpenses),
                    'tickets' => $userTickets
                ];
            }
        }

        return view('admin.dashboard', compact(
            'range',
            'userId',
            'users',
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
            'trendData',
            'usersStats'
        ));
    }
}
