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
        $segment = $request->input('segment', 'all');

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

        // Load clients count
        $totalClientsCount = Client::count();

        if ($segment === 'all') {
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
                $isSqlite = DB::connection()->getDriverName() === 'sqlite';
                $monthExpr = $isSqlite ? "strftime('%Y-%m', order_date)" : 'DATE_FORMAT(order_date, "%Y-%m")';
                $salesTrend = (clone $trendQuery)
                    ->select(DB::raw("{$monthExpr} as month"), DB::raw('SUM(total_amount) as total'))
                    ->groupBy('month')
                    ->orderBy('month', 'asc')
                    ->get();
                
                foreach ($salesTrend as $trend) {
                    $trendLabels[] = Carbon::parse($trend->month . '-01')->format('M Y');
                    $trendData[] = floatval($trend->total);
                }
            }

            // 5. Calculations per user (when 'all' is selected) - optimized to avoid N+1 queries
            $usersStats = [];
            if ($userId === 'all') {
                $orderSums = Order::whereBetween('order_date', [$start, $end])
                    ->select('user_id', DB::raw('SUM(total_amount) as ca'), DB::raw('SUM(paid_amount) as collected'), DB::raw('COUNT(id) as tickets'))
                    ->groupBy('user_id')
                    ->get()
                    ->keyBy('user_id');

                $expenseSums = Expense::whereBetween('expense_date', [$start, $end])
                    ->select('user_id', DB::raw('SUM(amount) as expenses'))
                    ->groupBy('user_id')
                    ->get()
                    ->keyBy('user_id');

                foreach ($users as $u) {
                    $uOrder = $orderSums->get($u->id);
                    $uExpense = $expenseSums->get($u->id);
                    
                    $ca = $uOrder ? floatval($uOrder->ca) : 0;
                    $collected = $uOrder ? floatval($uOrder->collected) : 0;
                    $expenses = $uExpense ? floatval($uExpense->expenses) : 0;
                    $tickets = $uOrder ? intval($uOrder->tickets) : 0;
                    
                    $usersStats[] = [
                        'user' => $u,
                        'ca' => $ca,
                        'collected' => $collected,
                        'expenses' => $expenses,
                        'profit' => floatval($collected - $expenses),
                        'tickets' => $tickets
                    ];
                }
            }
        } else {
            // Segment-specific calculations (Blanchisserie, Teinture, or Others)
            $orders = Order::whereBetween('order_date', [$start, $end])
                ->when($userId !== 'all', function ($query) use ($userId) {
                    $query->where('user_id', $userId);
                })
                ->orderBy('order_date', 'asc')
                ->with(['orderItems.service', 'client', 'user'])
                ->get();

            $totalNetCA = 0;
            $totalCollected = 0;
            $totalBalance = 0;
            $totalExpenses = null; // Charges/dépenses globales
            $netProfit = null;     // Bénéfice non applicable
            $totalOrdersCount = 0;

            $servicesBreakdownTemp = [];
            $topClientsTemp = [];
            $trendTemp = [];
            $usersStatsTemp = [];

            foreach ($orders as $order) {
                // Compute subtotal for the whole order first
                $orderSubtotal = 0;
                foreach ($order->orderItems as $item) {
                    $orderSubtotal += floatval($item->total_price);
                }

                if ($orderSubtotal <= 0) {
                    continue;
                }

                $netRatio = floatval($order->total_amount) / $orderSubtotal;
                $paidRatio = floatval($order->paid_amount) / $orderSubtotal;
                $balanceRatio = floatval($order->balance_amount) / $orderSubtotal;

                $orderHasSegmentItem = false;

                foreach ($order->orderItems as $item) {
                    $serviceCode = $item->service->code;

                    // Match item to segment
                    $matchesSegment = false;
                    if ($segment === 'blanchisserie' && $serviceCode === 'blanchisserie') {
                        $matchesSegment = true;
                    } elseif ($segment === 'teinture' && $serviceCode === 'teinture') {
                        $matchesSegment = true;
                    } elseif ($segment === 'others' && $serviceCode !== 'blanchisserie' && $serviceCode !== 'teinture') {
                        $matchesSegment = true;
                    }

                    if ($matchesSegment) {
                        $orderHasSegmentItem = true;

                        // Calculate item proportional values
                        $itemNetCA = floatval($item->total_price) * $netRatio;
                        $itemCollected = floatval($item->total_price) * $paidRatio;
                        $itemBalance = floatval($item->total_price) * $balanceRatio;

                        $totalNetCA += $itemNetCA;
                        $totalCollected += $itemCollected;
                        $totalBalance += $itemBalance;

                        // Services breakdown
                        if (!isset($servicesBreakdownTemp[$item->service_id])) {
                            $servicesBreakdownTemp[$item->service_id] = [
                                'service_id' => $item->service_id,
                                'qty' => 0,
                                'revenue' => 0,
                                'service' => $item->service
                            ];
                        }
                        $servicesBreakdownTemp[$item->service_id]['qty'] += floatval($item->quantity);
                        $servicesBreakdownTemp[$item->service_id]['revenue'] += $itemNetCA;

                        // Top clients
                        if (!isset($topClientsTemp[$order->client_id])) {
                            $topClientsTemp[$order->client_id] = [
                                'client_id' => $order->client_id,
                                'total_spent' => 0,
                                'tickets_count' => 0,
                                'client' => $order->client
                            ];
                        }
                        $topClientsTemp[$order->client_id]['total_spent'] += $itemNetCA;
                    }
                }

                if ($orderHasSegmentItem) {
                    $totalOrdersCount++;

                    if (isset($topClientsTemp[$order->client_id])) {
                        $topClientsTemp[$order->client_id]['tickets_count']++;
                    }

                    // Trend grouping
                    if ($range === 'today' || $range === 'week' || $range === 'month') {
                        $trendKey = $order->order_date->format('d/m');
                    } else {
                        $trendKey = $order->order_date->format('M Y');
                    }

                    if (!isset($trendTemp[$trendKey])) {
                        $trendTemp[$trendKey] = 0;
                    }
                    
                    // Add net contribution of the segment for this order
                    foreach ($order->orderItems as $item) {
                        $serviceCode = $item->service->code;
                        $matchesSegment = false;
                        if ($segment === 'blanchisserie' && $serviceCode === 'blanchisserie') {
                            $matchesSegment = true;
                        } elseif ($segment === 'teinture' && $serviceCode === 'teinture') {
                            $matchesSegment = true;
                        } elseif ($segment === 'others' && $serviceCode !== 'blanchisserie' && $serviceCode !== 'teinture') {
                            $matchesSegment = true;
                        }
                        if ($matchesSegment) {
                            $trendTemp[$trendKey] += floatval($item->total_price) * $netRatio;
                        }
                    }

                    // Users performance
                    if ($userId === 'all') {
                        if (!isset($usersStatsTemp[$order->user_id])) {
                            $usersStatsTemp[$order->user_id] = [
                                'user' => $order->user,
                                'ca' => 0,
                                'collected' => 0,
                                'expenses' => null,
                                'profit' => null,
                                'tickets' => 0
                            ];
                        }
                        $usersStatsTemp[$order->user_id]['tickets']++;
                        foreach ($order->orderItems as $item) {
                            $serviceCode = $item->service->code;
                            $matchesSegment = false;
                            if ($segment === 'blanchisserie' && $serviceCode === 'blanchisserie') {
                                $matchesSegment = true;
                            } elseif ($segment === 'teinture' && $serviceCode === 'teinture') {
                                $matchesSegment = true;
                            } elseif ($segment === 'others' && $serviceCode !== 'blanchisserie' && $serviceCode !== 'teinture') {
                                $matchesSegment = true;
                            }
                            if ($matchesSegment) {
                                $usersStatsTemp[$order->user_id]['ca'] += floatval($item->total_price) * $netRatio;
                                $usersStatsTemp[$order->user_id]['collected'] += floatval($item->total_price) * $paidRatio;
                            }
                        }
                    }
                }
            }

            // Convert temps to final collections
            $servicesBreakdown = collect(array_values($servicesBreakdownTemp))->map(function($item) {
                return (object)$item;
            });

            // Prepare chart labels and revenues
            $serviceLabels = [];
            $serviceRevenues = [];
            $serviceColors = ['#4f46e5', '#10b981', '#3b82f6', '#f59e0b', '#ec4899', '#8b5cf6'];
            
            foreach ($servicesBreakdown as $sb) {
                $serviceLabels[] = $sb->service->name;
                $serviceRevenues[] = floatval($sb->revenue);
            }

            $topClients = collect(array_values($topClientsTemp))->sortByDesc('total_spent')->take(5)->map(function($item) {
                return (object)$item;
            });

            // Trend sorting & formatting
            $trendLabels = array_keys($trendTemp);
            $trendData = array_values($trendTemp);

            // Users stats
            $usersStats = [];
            if ($userId === 'all') {
                foreach ($users as $u) {
                    if (isset($usersStatsTemp[$u->id])) {
                        $usersStats[] = $usersStatsTemp[$u->id];
                    } else {
                        $usersStats[] = [
                            'user' => $u,
                            'ca' => 0,
                            'collected' => 0,
                            'expenses' => null,
                            'profit' => null,
                            'tickets' => 0
                        ];
                    }
                }
            }
        }

        // --- Discount statistics calculations ---
        $discountStatsQuery = Order::whereBetween('order_date', [$start, $end]);
        if ($segment !== 'all') {
            $discountStatsQuery->whereHas('orderItems.service', function ($q) use ($segment) {
                if ($segment === 'blanchisserie') {
                    $q->where('code', 'blanchisserie');
                } elseif ($segment === 'teinture') {
                    $q->where('code', 'teinture');
                } else {
                    $q->where('code', '!=', 'blanchisserie')->where('code', '!=', 'teinture');
                }
            });
        }
        if ($userId !== 'all') {
            $discountStatsQuery->where('user_id', $userId);
        }

        $totalDiscountedTickets = (clone $discountStatsQuery)->where('discount_amount', '>', 0)->count();
        $totalDiscountAmount = floatval((clone $discountStatsQuery)->sum('discount_amount'));
        
        $totalGrossCA = $totalNetCA + $totalDiscountAmount;
        $averageDiscountPercent = $totalGrossCA > 0 ? ($totalDiscountAmount / $totalGrossCA) * 100 : 0;

        // Daily breakdown of discounts
        $dailyDiscountsRaw = (clone $discountStatsQuery)
            ->select(
                DB::raw('DATE(order_date) as date_only'),
                DB::raw('COUNT(CASE WHEN discount_amount > 0 THEN 1 END) as discount_count'),
                DB::raw('SUM(discount_amount) as discount_sum'),
                DB::raw('SUM(total_amount + discount_amount) as gross_sum')
            )
            ->groupBy('date_only')
            ->orderBy('date_only', 'desc')
            ->get();

        $dailyDiscounts = [];
        foreach ($dailyDiscountsRaw as $dd) {
            $gross = floatval($dd->gross_sum);
            $sum = floatval($dd->discount_sum);
            $dailyDiscounts[] = (object)[
                'date' => Carbon::parse($dd->date_only)->format('d/m/Y'),
                'count' => intval($dd->discount_count),
                'amount' => $sum,
                'percentage' => $gross > 0 ? ($sum / $gross) * 100 : 0
            ];
        }

        // --- Credit statistics calculations (Commandes livrées non soldées) ---
        $creditStatsQuery = Order::whereIn('status', ['delivered', 'partially_delivered'])
            ->where('balance_amount', '>', 0)
            ->whereBetween('order_date', [$start, $end]);

        if ($segment !== 'all') {
            $creditStatsQuery->whereHas('orderItems.service', function ($q) use ($segment) {
                if ($segment === 'blanchisserie') {
                    $q->where('code', 'blanchisserie');
                } elseif ($segment === 'teinture') {
                    $q->where('code', 'teinture');
                } else {
                    $q->where('code', '!=', 'blanchisserie')->where('code', '!=', 'teinture');
                }
            });
        }
        if ($userId !== 'all') {
            $creditStatsQuery->where('user_id', $userId);
        }

        $totalCreditTickets = (clone $creditStatsQuery)->count();
        $totalCreditAmount = floatval((clone $creditStatsQuery)->sum('balance_amount'));
        $totalCreditOrderTotal = floatval((clone $creditStatsQuery)->sum('total_amount'));
        $totalCreditPaid = floatval((clone $creditStatsQuery)->sum('paid_amount'));

        $creditOrders = (clone $creditStatsQuery)
            ->with(['client', 'user'])
            ->orderBy('order_date', 'desc')
            ->limit(50)
            ->get();

        // Global credit stats (all time, not bound to date filter)
        $globalCreditQuery = Order::whereIn('status', ['delivered', 'partially_delivered'])
            ->where('balance_amount', '>', 0);
        if ($userId !== 'all') {
            $globalCreditQuery->where('user_id', $userId);
        }
        $globalOutstandingCredit = floatval((clone $globalCreditQuery)->sum('balance_amount'));
        $globalOutstandingTickets = (clone $globalCreditQuery)->count();

        return view('admin.dashboard', compact(
            'range',
            'userId',
            'segment',
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
            'usersStats',
            'totalDiscountedTickets',
            'totalDiscountAmount',
            'averageDiscountPercent',
            'dailyDiscounts',
            'totalCreditTickets',
            'totalCreditAmount',
            'totalCreditOrderTotal',
            'totalCreditPaid',
            'creditOrders',
            'globalOutstandingCredit',
            'globalOutstandingTickets'
        ));
    }
}
