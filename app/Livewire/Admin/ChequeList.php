<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Cheque;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\PaymentAllocation;
use App\Models\Sale;
use App\Models\HistoricalCheque;
use App\Models\ProductSupplier;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Log;
use App\Livewire\Concerns\WithDynamicLayout;

#[Title('Cheque List')]
class ChequeList extends Component
{
    use WithDynamicLayout;

    use WithPagination;
    public $perPage = 10;
    public $search = '';
    public $statusFilter = 'all';

    public $activeTab = 'system'; // 'system' or 'historical'
    public $showHistoricalModal = false;
    public $historicalChequeId = null;
    public $h_type = 'received';
    public $h_party_name = '';
    public $h_cheque_number = '';
    public $h_bank_name = '';
    public $h_cheque_date = '';
    public $h_cheque_amount = '';
    public $h_status = 'pending';
    public $h_note = '';

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    public function getChequesProperty()
    {
        // Show pending cheques first, then others by cheque_date desc
        $query = Cheque::with('customer')
            ->orderByRaw("CASE WHEN status = 'pending' THEN 0 ELSE 1 END ASC")
            ->orderByDesc('cheque_date');

        if (!empty($this->search)) {
            $term = '%' . $this->search . '%';
            $query->where(function ($q) use ($term) {
                $q->where('cheque_number', 'like', $term)
                    ->orWhere('bank_name', 'like', $term)
                    ->orWhereHas('customer', function ($cq) use ($term) {
                        $cq->where('name', 'like', $term)
                            ->orWhere('phone', 'like', $term);
                    });
            });
        }
        // Apply status filter if set
        if (!empty($this->statusFilter) && $this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        return $query->paginate($this->perPage);
    }

    public function getHistoricalChequesProperty()
    {
        $query = HistoricalCheque::orderByDesc('cheque_date');

        if (!empty($this->search)) {
            $term = '%' . $this->search . '%';
            $query->where(function ($q) use ($term) {
                $q->where('cheque_number', 'like', $term)
                    ->orWhere('bank_name', 'like', $term)
                    ->orWhere('party_name', 'like', $term)
                    ->orWhere('note', 'like', $term);
            });
        }

        if (!empty($this->statusFilter) && $this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        return $query->paginate($this->perPage);
    }

    public function getPartySuggestionsProperty()
    {
        if ($this->h_type === 'received') {
            return Customer::select('name')->distinct()->pluck('name')->toArray();
        } else {
            return ProductSupplier::select('name')->distinct()->pluck('name')->toArray();
        }
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    public function getPendingCountProperty()
    {
        return Cheque::where('status', 'pending')->count();
    }

    public function getCompleteCountProperty()
    {
        return Cheque::where('status', 'complete')->count();
    }

    public function getOverdueCountProperty()
    {
        return Cheque::where('status', 'overdue')->count();
    }

    public function confirmComplete($id)
    {
        $this->js("
            Swal.fire({
                title: 'Mark as Complete?',
                text: 'Are you sure you want to mark this cheque as complete?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#198754',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, mark as complete!'
            }).then((result) => {
                if (result.isConfirmed) {
                    \$wire.completeCheque({$id});
                }
            });
        ");
    }

    public function confirmReturn($id)
    {
        $this->js("
            Swal.fire({
                title: 'Return Cheque?',
                text: 'Are you sure you want to return this cheque?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, return cheque!'
            }).then((result) => {
                if (result.isConfirmed) {
                    \$wire.returnCheque({$id});
                }
            });
        ");
    }

    public function confirmHistoricalComplete($id)
    {
        $this->js("
            Swal.fire({
                title: 'Mark as Complete?',
                text: 'Are you sure you want to mark this historical cheque as complete?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#198754',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, mark as complete!'
            }).then((result) => {
                if (result.isConfirmed) {
                    \$wire.completeHistoricalCheque({$id});
                }
            });
        ");
    }

    public function confirmHistoricalReturn($id)
    {
        $this->js("
            Swal.fire({
                title: 'Return Cheque?',
                text: 'Are you sure you want to return this historical cheque?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, return cheque!'
            }).then((result) => {
                if (result.isConfirmed) {
                    \$wire.returnHistoricalCheque({$id});
                }
            });
        ");
    }

    public function completeCheque($id)
    {
        try {
            $cheque = Cheque::find($id);

            if (!$cheque) {
                $this->js("Swal.fire('Error', 'Cheque not found!', 'error');");
                return;
            }

            $cheque->status = 'complete';
            $cheque->save();

            // Refresh the data


            $this->js("
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: 'Cheque marked as complete successfully!',
                    timer: 2000,
                    showConfirmButton: false
                });
            ");
        } catch (\Exception $e) {
            Log::error("Error completing cheque: " . $e->getMessage());
            $this->js("Swal.fire('Error', 'Failed to mark cheque as complete!', 'error');");
        }
    }

    public function returnCheque($id)
    {
        try {
            \Illuminate\Support\Facades\DB::beginTransaction();

            $cheque = Cheque::find($id);

            if (!$cheque) {
                $this->js("Swal.fire('Error', 'Cheque not found!', 'error');");
                return;
            }

            $cheque->status = 'return';
            $cheque->save();

            // Revert payments and allocations if linked
            if ($cheque->payment_id) {
                $payment = Payment::find($cheque->payment_id);
                if ($payment) {
                    $allocations = PaymentAllocation::where('payment_id', $payment->id)->get();
                    $totalAllocated = 0;

                    foreach ($allocations as $allocation) {
                        $sale = Sale::find($allocation->sale_id);
                        if ($sale) {
                            $sale->due_amount = floatval($sale->due_amount) + floatval($allocation->allocated_amount);
                            // Adjust payment status
                            if ($sale->due_amount >= $sale->total_amount) {
                                $sale->payment_status = 'pending';
                            } else {
                                $sale->payment_status = 'partial';
                            }
                            $sale->save();
                        }
                        $totalAllocated += floatval($allocation->allocated_amount);
                    }

                    $paymentAmount = floatval($payment->amount);
                    $remainder = max(0, $paymentAmount - $totalAllocated);

                    $customer = Customer::find($cheque->customer_id);
                    if ($customer) {
                        $customer->due_amount = floatval($customer->due_amount) + $totalAllocated;
                        if ($remainder > 0) {
                            $customer->opening_balance = floatval($customer->opening_balance) + $remainder;
                        }
                        $customer->total_due = floatval($customer->opening_balance) + floatval($customer->due_amount);
                        $customer->save();
                    }

                    $payment->status = 'returned';
                    $payment->save();
                }
            }

            \Illuminate\Support\Facades\DB::commit();

            $this->js("
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: 'Cheque returned successfully and customer balances updated!',
                    timer: 2000,
                    showConfirmButton: false
                });
            ");
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            Log::error("Error returning cheque: " . $e->getMessage());
            $this->js("Swal.fire('Error', 'Failed to return cheque: " . addslashes($e->getMessage()) . "', 'error');");
        }
    }

    public function completeHistoricalCheque($id)
    {
        try {
            $cheque = HistoricalCheque::find($id);
            if ($cheque) {
                $cheque->status = 'complete';
                $cheque->save();
                $this->js("Swal.fire({icon: 'success', title: 'Success!', text: 'Historical cheque marked as complete!', timer: 2000, showConfirmButton: false});");
            }
        } catch (\Exception $e) {
            $this->js("Swal.fire('Error', 'Failed to mark cheque as complete.', 'error');");
        }
    }

    public function returnHistoricalCheque($id)
    {
        try {
            $cheque = HistoricalCheque::find($id);
            if ($cheque) {
                $cheque->status = 'return';
                $cheque->save();
                $this->js("Swal.fire({icon: 'success', title: 'Success!', text: 'Historical cheque marked as returned!', timer: 2000, showConfirmButton: false});");
            }
        } catch (\Exception $e) {
            $this->js("Swal.fire('Error', 'Failed to return cheque.', 'error');");
        }
    }

    public function editHistoricalCheque($id)
    {
        $cheque = HistoricalCheque::find($id);
        if ($cheque) {
            $this->historicalChequeId = $cheque->id;
            $this->h_type = $cheque->type;
            $this->h_party_name = $cheque->party_name;
            $this->h_cheque_number = $cheque->cheque_number;
            $this->h_bank_name = $cheque->bank_name;
            $this->h_cheque_date = $cheque->cheque_date;
            $this->h_cheque_amount = $cheque->cheque_amount;
            $this->h_status = $cheque->status;
            $this->h_note = $cheque->note;
            $this->showHistoricalModal = true;
        }
    }

    public function openHistoricalModal()
    {
        $this->resetHistoricalForm();
        $this->showHistoricalModal = true;
    }

    public function closeHistoricalModal()
    {
        $this->showHistoricalModal = false;
        $this->resetHistoricalForm();
    }

    public function resetHistoricalForm()
    {
        $this->historicalChequeId = null;
        $this->h_type = 'received';
        $this->h_party_name = '';
        $this->h_cheque_number = '';
        $this->h_bank_name = '';
        $this->h_cheque_date = '';
        $this->h_cheque_amount = '';
        $this->h_status = 'pending';
        $this->h_note = '';
    }

    public function saveHistoricalCheque()
    {
        $this->validate([
            'h_type' => 'required|in:received,issued',
            'h_party_name' => 'required|string|max:255',
            'h_cheque_number' => 'required|string|max:255',
            'h_bank_name' => 'required|string|max:255',
            'h_cheque_date' => 'required|date',
            'h_cheque_amount' => 'required|numeric|min:0',
            'h_status' => 'required|in:pending,complete,return,cancelled',
        ]);

        try {
            if ($this->historicalChequeId) {
                $cheque = HistoricalCheque::find($this->historicalChequeId);
                $cheque->update([
                    'type' => $this->h_type,
                    'party_name' => $this->h_party_name,
                    'cheque_number' => $this->h_cheque_number,
                    'bank_name' => $this->h_bank_name,
                    'cheque_date' => $this->h_cheque_date,
                    'cheque_amount' => $this->h_cheque_amount,
                    'status' => $this->h_status,
                    'note' => $this->h_note,
                ]);
            } else {
                HistoricalCheque::create([
                    'type' => $this->h_type,
                    'party_name' => $this->h_party_name,
                    'cheque_number' => $this->h_cheque_number,
                    'bank_name' => $this->h_bank_name,
                    'cheque_date' => $this->h_cheque_date,
                    'cheque_amount' => $this->h_cheque_amount,
                    'status' => $this->h_status,
                    'note' => $this->h_note,
                ]);
            }

            $this->closeHistoricalModal();
            $this->js("Swal.fire('Success!', 'Historical cheque saved successfully!', 'success');");
        } catch (\Exception $e) {
            Log::error('Error saving historical cheque: ' . $e->getMessage());
            $this->js("Swal.fire('Error', 'Failed to save historical cheque.', 'error');");
        }
    }

    public function render()
    {
        return view('livewire.admin.cheque-list', [
            'cheques' => $this->cheques,
            'historicalCheques' => $this->historicalCheques,
            'pendingCount' => $this->pendingCount,
            'completeCount' => $this->completeCount,
            'overdueCount' => $this->overdueCount,
        ])->layout($this->layout);
    }
}
