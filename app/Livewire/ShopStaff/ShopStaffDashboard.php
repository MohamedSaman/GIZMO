<?php

namespace App\Livewire\ShopStaff;

use App\Models\Sale;
use App\Models\Payment;
use App\Models\ProductStock;
use App\Models\ProductDetail;
use App\Models\ProductPrice;
use App\Models\CategoryList;
use App\Models\Expense;
use App\Models\StaffExpense;
use App\Models\POSSession;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Title('Shop Staff Dashboard')]
#[Layout('components.layouts.shop-staff')]
class ShopStaffDashboard extends Component
{
    // Metric Properties
    public $totalStaffSales = 0;
    public $todaySales = 0;
    public $cashAmount = 0;
    public $totalStock = 0;
    public $availableStock = 0;
    
    // Percentages and secondary data
    public $salePercentage = 0;
    public $availablePercentage = 0;
    public $todayCashAmount = 0;
    
    // Lists for widgets
    public $recentSales = [];
    public $productInventory = [];
    public $dailySales = [];

    // Today Summary Modal
    public $showTodaySummary = false;
    public $summaryData = [];

    public function mount()
    {
        $userId = Auth::id();
        $today = Carbon::today();

        // 1. Staff Sale Amount (Total historical sales by this staff)
        $this->totalStaffSales = Sale::where('user_id', $userId)->sum('total_amount');

        // 2. Today Sale (Sales made today by this staff)
        $this->todaySales = Sale::where('user_id', $userId)
            ->whereDate('created_at', $today)
            ->sum('total_amount');

        // 3. Cash Amount (Total cash collected by this staff across all time or today?)
        // Usually dashboards show "Total" for historical and a smaller sub-metric for "Today".
        // I'll calculate total cash payments and today's cash payments.
        $staffSalesIds = Sale::where('user_id', $userId)->pluck('id');
        $this->cashAmount = Payment::whereIn('sale_id', $staffSalesIds)
            ->where('payment_method', 'cash')
            ->sum('amount');
        
        $this->todayCashAmount = Payment::whereIn('sale_id', $staffSalesIds)
            ->where('payment_method', 'cash')
            ->whereDate('payment_date', $today)
            ->sum('amount');

        // 4. Stock Total (Overall available stock in the system)
        $stockStats = ProductStock::select(
            DB::raw('SUM(total_stock) as total'),
            DB::raw('SUM(available_stock) as available')
        )->first();
        
        $this->totalStock = $stockStats->total ?? 0;
        $this->availableStock = $stockStats->available ?? 0;

        // Calculate percentages
        if ($this->totalStaffSales > 0) {
            $this->salePercentage = round(($this->todaySales / $this->totalStaffSales) * 100, 1);
        }
        
        if ($this->totalStock > 0) {
            $this->availablePercentage = round(($this->availableStock / $this->totalStock) * 100, 1);
        }

        // Load lists/widgets
        $this->loadRecentSales($userId);
        $this->loadProductInventory();
        $this->loadDailySales($userId);
    }

    public function openTodaySummary()
    {
        $userId = Auth::id();
        $today = Carbon::today();

        // Get today's POS session for opening balance
        $session = POSSession::where('user_id', $userId)
            ->where('session_date', $today)
            ->first();

        $openingBalance = $session ? floatval($session->opening_cash) : 0;

        // Get today's sales IDs
        $todaySalesQuery = Sale::where('user_id', $userId)
            ->whereDate('created_at', $today);

        $totalSale = $todaySalesQuery->sum('total_amount');
        $totalSaleCount = $todaySalesQuery->count();
        $todaySaleIds = $todaySalesQuery->pluck('id');

        // Get payments breakdown by method
        $todayPayments = Payment::whereIn('sale_id', $todaySaleIds)
            ->whereDate('payment_date', $today)
            ->whereNull('due_payment_method') // Exclude due payments to prevent double counting
            ->get();

        $cashSale = $todayPayments->where('payment_method', 'cash')->sum('amount');
        $bankSale = $todayPayments->where('payment_method', 'bank_transfer')->sum('amount');
        $onlineSale = $todayPayments->where('payment_method', 'online')->sum('amount');

        // Today's expenses
        $todayExpenses = StaffExpense::where('staff_id', $userId)
            ->whereDate('expense_date', $today)
            ->where('status', 'approved')
            ->sum('amount');

        // Today's revenue (total sale - expenses)
        $todayRevenue = $totalSale - $todayExpenses;

        // Calculate product cost and profit
        // Get all sale items for today's sales with their product prices
        $totalCost = 0;
        $todaySaleItems = DB::table('sale_items')
            ->whereIn('sale_id', $todaySaleIds)
            ->get();

        foreach ($todaySaleItems as $item) {
            // Get supplier price (cost) for this product
            $productPrice = ProductPrice::where('product_id', $item->product_id)->first();
            if ($productPrice) {
                $totalCost += floatval($productPrice->supplier_price) * $item->quantity;
            }
        }

        $grossProfit = $totalSale - $totalCost;
        $netProfit = $grossProfit - $todayExpenses;

        // Due amount for today
        $todayDue = $todaySalesQuery->sum('due_amount');

        // Due amounts received today for any sales (including sales made today)
        $receivedDuePayments = Payment::query()
            ->join('sales', 'payments.sale_id', '=', 'sales.id')
            ->where('sales.user_id', $userId)
            ->whereDate('payments.payment_date', $today)
            ->whereIn('payments.payment_method', ['cash', 'online', 'bank_transfer'])
            ->whereIn('payments.status', ['paid', 'approved'])
            ->where(function ($query) {
                // due_payment_method is set for due collections; fallback keeps compatibility for older records.
                $query->whereNotNull('payments.due_payment_method')
                    ->orWhereColumn('payments.payment_date', '>', 'sales.created_at');
            })
            ->select('payments.payment_method', 'payments.amount')
            ->get();

        $receivedDueCash = (float) $receivedDuePayments->where('payment_method', 'cash')->sum('amount');
        $receivedDueOnline = (float) $receivedDuePayments->where('payment_method', 'online')->sum('amount');
        $receivedDueBank = (float) $receivedDuePayments->where('payment_method', 'bank_transfer')->sum('amount');
        $receivedDueTotal = round($receivedDueCash + $receivedDueOnline + $receivedDueBank, 2);

        $receivedDueCashPercent = $receivedDueTotal > 0 ? round(($receivedDueCash / $receivedDueTotal) * 100, 1) : 0;
        $receivedDueOnlinePercent = $receivedDueTotal > 0 ? round(($receivedDueOnline / $receivedDueTotal) * 100, 1) : 0;
        $receivedDueBankPercent = $receivedDueTotal > 0 ? round(($receivedDueBank / $receivedDueTotal) * 100, 1) : 0;

        // Show cash net of today's due amount in summary output.
        $netCashSale = round($cashSale, 2);

        // Cash-in-hand style helper metrics for summary display.
        $openingWithReceipts = round($openingBalance + $netCashSale + $receivedDueCash, 2);
        $netCashAfterExpenses = round(($netCashSale + $receivedDueTotal) - $todayExpenses, 2);

        $this->summaryData = [
            'openingBalance' => $openingBalance,
            'totalSale' => $totalSale,
            'totalSaleCount' => $totalSaleCount,
            'cashSale' => $netCashSale,
            'cashSaleBeforeDue' => $cashSale,
            'bankSale' => $bankSale,
            'onlineSale' => $onlineSale,
            'todayExpenses' => $todayExpenses,
            'todayRevenue' => $todayRevenue,
            'totalCost' => $totalCost,
            'grossProfit' => $grossProfit,
            'netProfit' => $netProfit,
            'todayDue' => $todayDue,
            'receivedDueTotal' => $receivedDueTotal,
            'receivedDueCash' => $receivedDueCash,
            'receivedDueOnline' => $receivedDueOnline,
            'receivedDueBank' => $receivedDueBank,
            'receivedDueCashPercent' => $receivedDueCashPercent,
            'receivedDueOnlinePercent' => $receivedDueOnlinePercent,
            'receivedDueBankPercent' => $receivedDueBankPercent,
            'openingWithReceipts' => $openingWithReceipts,
            'netCashAfterExpenses' => $netCashAfterExpenses,
        ];

        $this->showTodaySummary = true;
    }

    public function closeTodaySummary()
    {
        $this->showTodaySummary = false;
        $this->summaryData = [];
    }

    public function loadRecentSales($userId)
    {
        $this->recentSales = DB::table('sales')
            ->leftJoin('customers', 'sales.customer_id', '=', 'customers.id')
            ->where('sales.user_id', $userId)
            ->select(
                'sales.id',
                'sales.invoice_number',
                'sales.total_amount',
                'sales.payment_status',
                'sales.created_at',
                DB::raw('COALESCE(customers.name, sales.walking_customer_name, "Walk-in Customer") as customer_name'),
                'sales.due_amount'
            )
            ->orderBy('sales.created_at', 'desc')
            ->limit(5)
            ->get();
    }

    public function loadProductInventory()
    {
        $this->productInventory = DB::table('product_details')
            ->join('product_stocks', 'product_details.id', '=', 'product_stocks.product_id')
            ->leftJoin('brand_lists', 'product_details.brand_id', '=', 'brand_lists.id')
            ->select(
                'product_details.id',
                'product_details.code',
                'product_details.name',
                'brand_lists.brand_name as brand',
                'product_stocks.available_stock',
                'product_stocks.total_stock'
            )
            ->where('product_stocks.available_stock', '<=', 10)
            ->orderBy('product_stocks.available_stock', 'asc')
            ->limit(5)
            ->get();
    }

    public function loadDailySales($userId)
    {
        $dailyData = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $sales = Sale::where('user_id', $userId)
                ->whereDate('created_at', $date)
                ->sum('total_amount');

            $dailyData[] = [
                'date' => $date->format('M d'),
                'total_sales' => (float) $sales
            ];
        }
        $this->dailySales = $dailyData;
    }

    public function render()
    {
        return view('livewire.shop-staff.shop-staff-dashboard');
    }
}
