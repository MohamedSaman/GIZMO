<?php

namespace App\Livewire\ShopStaff;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\ReturnsProduct;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Auth;

#[Title('My Sales')]
#[Layout('components.layouts.shop-staff')]
class ShopStaffSalesList extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $statusFilter = '';
    public $dateFilter = '';

    public function mount()
    {
        $this->dateFilter = now()->format('Y-m-d');
    }

    // View details
    public $selectedSale = null;
    public $showDetailsModal = false;
    public $saleReturns = [];

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    public function updatedDateFilter()
    {
        $this->resetPage();
    }

    public function viewDetails($saleId)
    {
        $this->selectedSale = Sale::with(['customer', 'items.product', 'payments', 'returns.product'])
            ->find($saleId);

        $this->saleReturns = ReturnsProduct::where('sale_id', $saleId)
            ->with('product')
            ->get();

        $this->showDetailsModal = true;
    }

    public function closeDetailsModal()
    {
        $this->showDetailsModal = false;
        $this->selectedSale = null;
        $this->saleReturns = [];
    }

    /**
     * Redirect to store-billing page with the sale ID for editing
     */
    public function editSale($saleId)
    {
        $sale = Sale::find($saleId);

        if (!$sale) {
            $this->showToast('error', 'Sale not found.');
            return;
        }

        if ($sale->user_id !== Auth::id()) {
            $this->showToast('error', 'You can only edit your own sales.');
            return;
        }

        return redirect()->route('shop-staff.store-billing', ['edit' => $saleId]);
    }

    /**
     * Print invoice
     */
    public function printInvoice($saleId)
    {
        $sale = Sale::find($saleId);
        if (!$sale) {
            $this->showToast('error', 'Sale not found.');
            return;
        }

        session(['print_sale_id' => $sale->id]);

        $printUrl = route('print.sale', $sale->id);
        $this->js("window.open('$printUrl', '_blank', 'width=800,height=600,scrollbars=yes')");
    }

    public function render()
    {
        $baseQuery = Sale::where('user_id', Auth::id())
            ->when($this->search, function ($q) {
                $q->where(function ($sq) {
                    $sq->where('invoice_number', 'like', '%' . $this->search . '%')
                        ->orWhere('walking_customer_name', 'like', '%' . $this->search . '%')
                        ->orWhere('walking_customer_phone', 'like', '%' . $this->search . '%')
                        ->orWhereHas('customer', function ($cq) {
                            $cq->where('name', 'like', '%' . $this->search . '%')
                                ->orWhere('phone', 'like', '%' . $this->search . '%');
                        });
                });
            })
            ->when($this->statusFilter, function ($q) {
                $q->where('payment_status', $this->statusFilter);
            })
            ->when($this->dateFilter, function ($q) {
                $q->whereDate('created_at', $this->dateFilter);
            });

        $salesListQuery = (clone $baseQuery)->with(['customer', 'payments'])->orderBy('created_at', 'desc');

        $statsQuery = clone $baseQuery;

        $summarySales = $statsQuery->sum('total_amount');
        $summaryCount = $statsQuery->count();

        return view('livewire.shop-staff.shop-staff-sales-list', [
            'sales' => $salesListQuery->get(),
            'summarySales' => $summarySales,
            'summaryCount' => $summaryCount,
        ]);
    }

    private function showToast($type, $message)
    {
        $bgColors = [
            'success' => '#10b981',
            'error' => '#ef4444',
            'warning' => '#f59e0b',
            'info' => '#3b82f6',
        ];

        $icons = [
            'success' => '✓',
            'error' => '✕',
            'warning' => '⚠',
            'info' => 'ℹ',
        ];

        $bg = $bgColors[$type] ?? $bgColors['info'];
        $icon = $icons[$type] ?? $icons['info'];
        $escapedMessage = addslashes($message);

        $this->js("
            const toast = document.createElement('div');
            toast.style.cssText = 'position:fixed;top:20px;right:20px;background:{$bg};color:white;padding:16px 24px;border-radius:8px;box-shadow:0 4px 12px rgba(0,0,0,0.15);z-index:9999;font-size:14px;font-weight:600;display:flex;align-items:center;gap:12px;animation:slideIn 0.3s ease;min-width:300px;max-width:500px;';
            toast.innerHTML = '<span style=\"font-size:20px;font-weight:bold;\">{$icon}</span><span>{$escapedMessage}</span>';
            document.body.appendChild(toast);
            const style = document.createElement('style');
            style.textContent = '@keyframes slideIn { from { transform: translateX(400px); opacity: 0; } to { transform: translateX(0); opacity: 1; } } @keyframes slideOut { from { transform: translateX(0); opacity: 1; } to { transform: translateX(400px); opacity: 0; } }';
            document.head.appendChild(style);
            setTimeout(() => {
                toast.style.animation = 'slideOut 0.3s ease';
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        ");
    }
}
