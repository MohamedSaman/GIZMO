<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\ProductSupplier;
use App\Models\PurchasePayment;
use App\Models\PurchasePaymentAllocation;
use App\Models\PurchaseOrder;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use App\Livewire\Concerns\WithDynamicLayout;
use Barryvdh\DomPDF\Facade\Pdf;

#[Title("List Supplier Receipt")]
class ListSupplierReceipt extends Component
{
    use WithDynamicLayout;
    use WithPagination;

    // Filter properties
    public $filterSupplier = '';
    public $filterDateFrom = '';
    public $filterDateTo = '';
    public $filterPaymentMethod = '';

    // Receipt details modal
    public $showReceiptModal = false;
    public $selectedReceipt = null;

    public function updatingFilterSupplier()
    {
        $this->resetPage();
    }

    public function updatingFilterDateFrom()
    {
        $this->resetPage();
    }

    public function updatingFilterDateTo()
    {
        $this->resetPage();
    }

    public function updatingFilterPaymentMethod()
    {
        $this->resetPage();
    }

    public function getPaymentsProperty()
    {
        $query = PurchasePayment::with(['supplier', 'allocations.order']);

        // Apply supplier filter
        if ($this->filterSupplier) {
            $query->whereHas('supplier', function ($q) {
                $q->where('name', 'LIKE', '%' . $this->filterSupplier . '%');
            });
        }

        // Apply date filters
        if ($this->filterDateFrom) {
            $query->where('payment_date', '>=', $this->filterDateFrom);
        }
        if ($this->filterDateTo) {
            $query->where('payment_date', '<=', $this->filterDateTo);
        }

        // Apply payment method filter
        if ($this->filterPaymentMethod) {
            $query->where('payment_method', $this->filterPaymentMethod);
        }

        return $query->orderByDesc('payment_date')->orderByDesc('id')->paginate(20);
    }

    public function viewReceipt($paymentId)
    {
        $this->selectedReceipt = PurchasePayment::with(['supplier', 'allocations.order'])
            ->find($paymentId);
        if ($this->selectedReceipt) {
            $this->showReceiptModal = true;
        }
    }

    public function closeReceiptModal()
    {
        $this->showReceiptModal = false;
        $this->selectedReceipt = null;
    }

    public function downloadReceipt($paymentId)
    {
        $payment = PurchasePayment::with(['supplier', 'allocations.order'])->find($paymentId);
        if (!$payment) return;

        $pdf = Pdf::loadView('components.payment-receipt', ['payment' => $payment]);
        
        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->stream();
        }, 'payment-receipt-' . $payment->id . '.pdf');
    }

    public function clearFilters()
    {
        $this->filterSupplier = '';
        $this->filterDateFrom = '';
        $this->filterDateTo = '';
        $this->filterPaymentMethod = '';
        $this->resetPage();
    }

    public function render()
    {
        $totalPaid = PurchasePayment::where('is_completed', 1)->sum('amount') 
                   + PurchasePayment::where('is_completed', 1)->sum('overpayment_used');
        $cashPaid = PurchasePayment::where('is_completed', 1)->where('payment_method', 'cash')->sum('amount');
        $chequePaid = PurchasePayment::where('is_completed', 1)->where('payment_method', 'cheque')->sum('amount');
        $transferPaid = PurchasePayment::where('is_completed', 1)->where('payment_method', 'bank_transfer')->sum('amount');

        return view('livewire.admin.list-supplier-receipt', [
            'payments' => $this->payments,
            'totalPaid' => $totalPaid,
            'cashPaid' => $cashPaid,
            'chequePaid' => $chequePaid,
            'transferPaid' => $transferPaid,
        ])->layout($this->layout);
    }
}
