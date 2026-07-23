<?php

namespace App\Livewire\Admin;

use App\Models\Salary;
use App\Models\SalaryPayment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Title;
use Livewire\Component;
use App\Livewire\Concerns\WithDynamicLayout;
use Carbon\Carbon;
use Exception;

#[Title('Staff Salary')]
class StaffSalary extends Component
{
    use WithDynamicLayout;

    public string $selectedMonth;

    // Pay Modal
    public bool   $showPayModal     = false;
    public ?int   $payingSalaryId   = null;
    public string $payAmount        = '';
    public string $payDate          = '';
    public string $payNote          = '';
    public array  $payingSalaryInfo = [];

    // View Modal
    public bool   $showViewModal      = false;
    public array  $viewingPayments    = [];
    public array  $viewingSalaryInfo  = [];

    public function mount(): void
    {
        $this->selectedMonth = now()->format('Y-m');
        $this->payDate       = now()->format('Y-m-d');
    }

    // ── Computed list ────────────────────────────────────────────────────

    public function getStaffSalaryListProperty(): array
    {
        $month = Carbon::parse($this->selectedMonth . '-01');

        $staffList = User::where('role', 'staff')
            ->with(['userDetail', 'salaries' => function ($q) use ($month) {
                $q->whereYear('salary_month', $month->year)
                  ->whereMonth('salary_month', $month->month)
                  ->with('payments');
            }])
            ->orderBy('name')
            ->get();

        $rows = [];
        foreach ($staffList as $staff) {
            $salary      = $staff->salaries->first();
            $basicSalary = (float) ($staff->userDetail->basic_salary ?? 0);
            $netSalary   = $salary ? (float) $salary->net_salary : $basicSalary;
            $paidAmount  = $salary ? (float) $salary->payments->sum('amount') : 0.0;
            $balance     = $netSalary - $paidAmount;

            if ($salary) {
                $status = $balance <= 0 ? 'paid' : ($paidAmount > 0 ? 'partial' : 'pending');
            } else {
                $status = 'no_record';
            }

            $rows[] = [
                'user_id'      => $staff->id,
                'name'         => $staff->name,
                'staff_type'   => ucfirst(str_replace('_', ' ', $staff->staff_type ?? 'staff')),
                'salary_id'    => $salary?->salary_id,
                'basic_salary' => $basicSalary,
                'net_salary'   => $netSalary,
                'paid_amount'  => $paidAmount,
                'balance'      => $balance,
                'has_salary'   => $salary !== null,
                'status'       => $status,
            ];
        }

        return $rows;
    }

    // ── Pay Modal ────────────────────────────────────────────────────────

    public function openPayModal(int $userId): void
    {
        $month = Carbon::parse($this->selectedMonth . '-01');
        $staff = User::with('userDetail')->find($userId);

        $salary = Salary::with('payments')
            ->where('user_id', $userId)
            ->whereYear('salary_month', $month->year)
            ->whereMonth('salary_month', $month->month)
            ->first();

        if (!$salary) {
            $basicSalary = (float) ($staff?->userDetail?->basic_salary ?? 0);
            $salary = Salary::create([
                'user_id'        => $userId,
                'salary_month'   => $month->format('Y-m-d'),
                'salary_type'    => 'monthly',
                'basic_salary'   => $basicSalary,
                'net_salary'     => $basicSalary,
                'payment_status' => 'pending',
            ]);
            $salary->load('payments');
        }

        $paidAmount = (float) $salary->payments->sum('amount');
        $balance    = (float) $salary->net_salary - $paidAmount;

        $this->payingSalaryId   = $salary->salary_id;
        $this->payAmount        = '';
        $this->payDate          = now()->format('Y-m-d');
        $this->payNote          = '';
        $this->payingSalaryInfo = [
            'name'        => $staff?->name,
            'month'       => $month->format('F Y'),
            'net_salary'  => (float) $salary->net_salary,
            'paid_amount' => $paidAmount,
            'balance'     => $balance,
        ];

        $this->showPayModal = true;
    }

    public function savePayment(): void
    {
        $this->validate([
            'payAmount' => 'required|numeric|min:0.01',
            'payDate'   => 'required|date',
        ]);

        try {
            DB::beginTransaction();

            $salary = Salary::with('payments')->find($this->payingSalaryId);
            if (!$salary) {
                throw new Exception('Salary record not found.');
            }

            $paidSoFar = (float) $salary->payments->sum('amount');
            $balance   = (float) $salary->net_salary - $paidSoFar;

            if ((float) $this->payAmount > $balance + 0.01) {
                throw new Exception('Amount exceeds remaining balance of Rs. ' . number_format($balance, 2));
            }

            SalaryPayment::create([
                'salary_id'    => $this->payingSalaryId,
                'amount'       => (float) $this->payAmount,
                'payment_date' => $this->payDate,
                'note'         => $this->payNote ?: null,
            ]);

            DB::commit();

            $this->showPayModal = false;
            $this->dispatch('showToast', [
                'type'    => 'success',
                'message' => 'Payment of Rs. ' . number_format((float) $this->payAmount, 2) . ' saved.',
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            $this->dispatch('showToast', ['type' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function closePayModal(): void
    {
        $this->showPayModal   = false;
        $this->payingSalaryId = null;
        $this->payingSalaryInfo = [];
    }

    // ── View Modal ───────────────────────────────────────────────────────

    public function openViewModal(int $userId): void
    {
        $month = Carbon::parse($this->selectedMonth . '-01');
        $staff = User::find($userId);

        $salary = Salary::with('payments')
            ->where('user_id', $userId)
            ->whereYear('salary_month', $month->year)
            ->whereMonth('salary_month', $month->month)
            ->first();

        if (!$salary) {
            $this->dispatch('showToast', ['type' => 'warning', 'message' => 'No salary record for this month yet. Click Pay to create one.']);
            return;
        }

        $paidAmount = (float) $salary->payments->sum('amount');
        $balance    = (float) $salary->net_salary - $paidAmount;

        $this->viewingSalaryInfo = [
            'name'        => $staff?->name,
            'month'       => $month->format('F Y'),
            'net_salary'  => (float) $salary->net_salary,
            'paid_amount' => $paidAmount,
            'balance'     => $balance,
        ];

        $this->viewingPayments = $salary->payments
            ->sortBy('payment_date')
            ->values()
            ->map(fn($p) => [
                'date'   => Carbon::parse($p->payment_date)->format('d M Y'),
                'amount' => (float) $p->amount,
                'note'   => $p->note ?? '—',
            ])
            ->toArray();

        $this->showViewModal = true;
    }

    public function closeViewModal(): void
    {
        $this->showViewModal    = false;
        $this->viewingPayments  = [];
        $this->viewingSalaryInfo = [];
    }

    // ── Render ───────────────────────────────────────────────────────────

    public function render()
    {
        return view('livewire.admin.staff-salary', [
            'staffSalaryList' => $this->staffSalaryList,
        ])->layout($this->layout);
    }
}
