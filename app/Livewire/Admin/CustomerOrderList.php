<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Quotation;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Customer;
use App\Models\ProductDetail;
use App\Services\FIFOStockService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Livewire\Concerns\WithDynamicLayout;

#[Layout('components.layouts.admin')]
#[\Livewire\Attributes\Title('Customer Orders Management')]
class CustomerOrderList extends Component
{
    use WithDynamicLayout;
    use WithPagination;

    public $search = '';
    public $perPage = 10;

    // View Modal
    public $selectedOrder = null;
    public $showViewModal = false;

    // Convert Modal
    public $createSaleModal = false;
    public $editableItems = [];
    public $saleData = [
        'notes' => '',
        'additional_discount' => 0,
        'additional_discount_type' => 'fixed',
        'additional_discount_amount' => 0
    ];

    public $totalAmount = 0;
    public $totalDiscount = 0;
    public $subtotal = 0;
    public $grandTotal = 0;
    public $additionalDiscountAmount = 0;

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    public function viewOrder($id)
    {
        $this->selectedOrder = Quotation::find($id);

        if ($this->selectedOrder && is_string($this->selectedOrder->items)) {
            $this->selectedOrder->items = json_decode($this->selectedOrder->items, true);
        }

        $this->showViewModal = true;
    }

    public function closeViewModal()
    {
        $this->showViewModal = false;
        $this->selectedOrder = null;
    }

    public function openCreateSaleModal($orderId)
    {
        $this->selectedOrder = Quotation::find($orderId);

        if ($this->selectedOrder) {
            $items = $this->selectedOrder->items;
            if (is_string($items)) {
                $items = json_decode($items, true);
            }

            $this->editableItems = collect($items)->map(function ($item) {
                $product = ProductDetail::find($item['product_id']);
                $variantId = $item['variant_id'] ?? null;
                $variantValue = $item['variant_value'] ?? null;

                if ($variantId && $product) {
                    $stockRecord = $product->stocks()->where('variant_id', $variantId)->first();
                    $currentStock = $stockRecord->available_stock ?? 0;
                    $priceRecord = $product->prices()->where('variant_id', $variantId)->first();
                    $discountPrice = $priceRecord->discount_price ?? 0;
                } else {
                    $currentStock = $product && $product->stock ? $product->stock->available_stock : 0;
                    $discountPrice = $product && $product->price ? $product->price->discount_price : 0;
                }

                return [
                    'product_id' => $item['product_id'] ?? null,
                    'variant_id' => $variantId,
                    'variant_value' => $variantValue,
                    'product_name' => $item['product_name'] ?? 'N/A',
                    'product_code' => $item['product_code'] ?? '',
                    'product_model' => $item['product_model'] ?? '',
                    'quantity' => $item['quantity'] ?? 1,
                    'unit_price' => $item['unit_price'] ?? 0,
                    'discount_per_unit' => $item['discount_per_unit'] ?? $discountPrice,
                    'total_discount' => ($item['discount_per_unit'] ?? $discountPrice) * ($item['quantity'] ?? 1),
                    'total' => (($item['unit_price'] ?? 0) - ($item['discount_per_unit'] ?? $discountPrice)) * ($item['quantity'] ?? 1),
                    'current_stock' => $currentStock,
                    'original_quantity' => $item['quantity'] ?? 1
                ];
            })->toArray();

            $this->calculateInitialTotals();

            $additionalDiscountValue = $this->selectedOrder->additional_discount_value ?? 0;
            $additionalDiscountType = $this->selectedOrder->additional_discount_type ?? 'fixed';
            $additionalDiscountAmount = $this->selectedOrder->additional_discount ?? 0;

            $this->saleData['additional_discount'] = $additionalDiscountValue;
            $this->saleData['additional_discount_type'] = $additionalDiscountType;
            $this->saleData['additional_discount_amount'] = $additionalDiscountAmount;

            $this->calculateTotals();
            $this->saleData['notes'] = "Created from Customer Order #" . $this->selectedOrder->quotation_number;

            $this->createSaleModal = true;
        }
    }

    private function calculateInitialTotals()
    {
        foreach ($this->editableItems as $index => $item) {
            $quantity = $item['quantity'] ?? 1;
            $unitPrice = $item['unit_price'] ?? 0;
            $discountPerUnit = $item['discount_per_unit'] ?? 0;

            $totalDiscount = $discountPerUnit * $quantity;
            $total = ($unitPrice * $quantity) - $totalDiscount;

            $this->editableItems[$index]['total_discount'] = $totalDiscount;
            $this->editableItems[$index]['total'] = max(0, $total);
        }

        $this->totalDiscount = collect($this->editableItems)->sum('total_discount');
        $this->totalAmount = collect($this->editableItems)->sum('total');
        $this->subtotal = $this->totalAmount + $this->totalDiscount;
    }

    public function closeCreateSaleModal()
    {
        $this->createSaleModal = false;
        $this->editableItems = [];
        $this->saleData = [
            'notes' => '',
            'additional_discount' => 0,
            'additional_discount_type' => 'fixed',
            'additional_discount_amount' => 0
        ];
        $this->selectedOrder = null;
        $this->totalAmount = 0;
        $this->totalDiscount = 0;
        $this->subtotal = 0;
        $this->grandTotal = 0;
        $this->additionalDiscountAmount = 0;
    }

    public function calculateTotals()
    {
        foreach ($this->editableItems as $index => $item) {
            $quantity = $item['quantity'] ?? 1;
            $unitPrice = $item['unit_price'] ?? 0;
            $discountPerUnit = $item['discount_per_unit'] ?? 0;

            $totalDiscount = $discountPerUnit * $quantity;
            $total = ($unitPrice * $quantity) - $totalDiscount;

            $this->editableItems[$index]['total_discount'] = $totalDiscount;
            $this->editableItems[$index]['total'] = max(0, $total);
        }

        $this->totalDiscount = collect($this->editableItems)->sum('total_discount');
        $this->totalAmount = collect($this->editableItems)->sum('total');
        $this->subtotal = $this->totalAmount + $this->totalDiscount;

        $this->calculateAdditionalDiscount();

        $totalCombinedDiscount = $this->totalDiscount + $this->additionalDiscountAmount;
        $this->grandTotal = $this->subtotal - $totalCombinedDiscount;
    }

    private function calculateAdditionalDiscount()
    {
        if (isset($this->saleData['additional_discount_amount']) && $this->saleData['additional_discount_amount'] > 0) {
            $this->additionalDiscountAmount = min($this->saleData['additional_discount_amount'], $this->subtotal);
            return;
        }

        $additionalDiscount = floatval($this->saleData['additional_discount'] ?? 0);
        if ($additionalDiscount <= 0) {
            $this->additionalDiscountAmount = 0;
            return;
        }

        if ($this->saleData['additional_discount_type'] === 'percentage') {
            $this->additionalDiscountAmount = ($this->subtotal * $additionalDiscount) / 100;
        } else {
            $this->additionalDiscountAmount = min($additionalDiscount, $this->subtotal);
        }
    }

    public function updatedSaleDataAdditionalDiscountType()
    {
        $this->saleData['additional_discount'] = 0;
        $this->saleData['additional_discount_amount'] = 0;
        $this->calculateTotals();
    }

    public function updatedSaleDataAdditionalDiscount($value)
    {
        if ($value === '') {
            $this->saleData['additional_discount'] = 0;
        } else {
            $value = floatval($value);
            if ($value < 0) {
                $this->saleData['additional_discount'] = 0;
            } elseif ($this->saleData['additional_discount_type'] === 'percentage' && $value > 100) {
                $this->saleData['additional_discount'] = 100;
            } else {
                $this->saleData['additional_discount'] = $value;
            }
        }

        if ($this->saleData['additional_discount_type'] === 'percentage') {
            $this->saleData['additional_discount_amount'] = ($this->subtotal * $this->saleData['additional_discount']) / 100;
        } else {
            $this->saleData['additional_discount_amount'] = $this->saleData['additional_discount'];
        }

        $this->calculateTotals();
    }

    public function updateItemQuantity($index, $quantity)
    {
        if (isset($this->editableItems[$index])) {
            $quantity = max(1, intval($quantity));
            $maxStock = $this->editableItems[$index]['current_stock'];

            if ($quantity > $maxStock) {
                $this->dispatch('show-toast', ['type' => 'error', 'message' => "Not enough stock! Maximum available: {$maxStock}"]);
                $quantity = $maxStock;
            }

            $this->editableItems[$index]['quantity'] = $quantity;
            $this->calculateTotals();
        }
    }

    public function updateItemDiscount($index, $discount)
    {
        if (isset($this->editableItems[$index])) {
            $this->editableItems[$index]['discount_per_unit'] = max(0, floatval($discount));
            $this->calculateTotals();
        }
    }

    public function removeItem($index)
    {
        if (isset($this->editableItems[$index]) && count($this->editableItems) > 1) {
            unset($this->editableItems[$index]);
            $this->editableItems = array_values($this->editableItems);
            $this->calculateTotals();
        } else {
            $this->dispatch('show-toast', ['type' => 'error', 'message' => 'Order must have at least one product.']);
        }
    }

    public function createSale()
    {
        if (empty($this->editableItems)) {
            $this->dispatch('show-toast', ['type' => 'error', 'message' => 'Please add at least one product to the sale.']);
            return;
        }

        foreach ($this->editableItems as $index => $item) {
            if (!$item['product_id']) {
                $this->dispatch('show-toast', ['type' => 'error', 'message' => "Please select a product for item #" . ($index + 1)]);
                return;
            }

            if ($item['quantity'] > $item['current_stock']) {
                $this->dispatch('show-toast', ['type' => 'error', 'message' => "Not enough stock for {$item['product_name']}. Available: {$item['current_stock']}"]);
                return;
            }
        }

        try {
            DB::transaction(function () {
                $customer = Customer::find($this->selectedOrder->customer_id);
                if (!$customer) {
                    throw new \Exception("Customer associated with this order was not found.");
                }

                $totalCombinedDiscount = $this->totalDiscount + $this->additionalDiscountAmount;
                $saleCustomerType = match ($customer->type) {
                    'distributor', 'wholesale' => 'wholesale',
                    'retail' => 'retail',
                    default => 'retail'
                };

                // Create sale with payment_status = pending, due_amount = grandTotal (Credit Sale)
                $sale = Sale::create([
                    'sale_id' => Sale::generateSaleId(),
                    'invoice_number' => Sale::generateInvoiceNumber(),
                    'customer_id' => $customer->id,
                    'customer_type' => $saleCustomerType,
                    'subtotal' => $this->subtotal,
                    'discount_amount' => $totalCombinedDiscount,
                    'total_amount' => $this->grandTotal,
                    'payment_type' => 'full',
                    'payment_status' => 'pending',
                    'due_amount' => $this->grandTotal,
                    'notes' => $this->saleData['notes'],
                    'user_id' => Auth::id(),
                    'status' => 'confirm',
                    'sale_type' => 'admin'
                ]);

                // Create sale items and deduct stock using FIFOStockService
                foreach ($this->editableItems as $item) {
                    $variantId = $item['variant_id'] ?? null;
                    $variantValue = $item['variant_value'] ?? null;
                    $quantity = $item['quantity'];

                    // FIFO Deduction
                    $fifoResult = FIFOStockService::deductStock(
                        $item['product_id'],
                        $quantity,
                        $variantId,
                        $variantValue
                    );

                    foreach ($fifoResult['deductions'] as $deduction) {
                        $itemTotal = ($item['unit_price'] - $item['discount_per_unit']) * $deduction['quantity'];
                        $warrantyThreshold = \App\Models\Setting::where('key', 'warranty_min_amount')->value('value') ?? 1000;
                        $hasWarranty = ($itemTotal / $deduction['quantity']) >= $warrantyThreshold;

                        SaleItem::create([
                            'sale_id' => $sale->id,
                            'product_id' => $item['product_id'],
                            'product_code' => $item['product_code'],
                            'product_name' => $item['product_name'],
                            'product_model' => $item['product_model'],
                            'quantity' => $deduction['quantity'],
                            'unit_price' => $item['unit_price'],
                            'discount_per_unit' => $item['discount_per_unit'],
                            'total_discount' => $item['discount_per_unit'] * $deduction['quantity'],
                            'total' => $itemTotal,
                            'variant_id' => $variantId,
                            'variant_value' => $variantValue,
                            'has_warranty' => $hasWarranty,
                            'warranty_duration' => $hasWarranty ? '6 Months' : null,
                        ]);
                    }
                }

                // Increment customer outstanding balance
                $customer->due_amount = floatval($customer->due_amount ?? 0) + floatval($this->grandTotal);
                $customer->total_due = floatval($customer->opening_balance ?? 0) + floatval($customer->due_amount);
                $customer->save();

                // Mark order quotation as converted
                $this->selectedOrder->update([
                    'status' => 'converted',
                    'converted_at' => now()
                ]);
            });

            $this->dispatch('show-toast', ['type' => 'success', 'message' => 'Customer Order successfully converted to Sale!']);
            $this->closeCreateSaleModal();

        } catch (\Exception $e) {
            Log::error('Quotation conversion to sale failed: ' . $e->getMessage());
            $this->dispatch('show-toast', ['type' => 'error', 'message' => 'Failed to convert order: ' . $e->getMessage()]);
        }
    }

    public function render()
    {
        $query = Quotation::with('customer')
            ->where('status', 'sent');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('quotation_number', 'like', '%' . $this->search . '%')
                    ->orWhere('customer_name', 'like', '%' . $this->search . '%')
                    ->orWhere('customer_phone', 'like', '%' . $this->search . '%');
            });
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate($this->perPage);

        return view('livewire.admin.customer-order-list', [
            'orders' => $orders,
        ])->layout($this->layout);
    }
}
