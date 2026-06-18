<?php

namespace App\Livewire\ShopStaff;

use App\Models\StaffExpense;
use App\Models\Expense;
use App\Models\POSSession;
use App\Models\CashAdvance;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\WithPagination;
use Carbon\Carbon;

#[Layout('components.layouts.shop-staff')]
#[Title('Daily Expenses')]
class ShopStaffExpenses extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $expense_type = '';
    // Pre-defined expense types for dropdown
    public $expenseTypes = [];
    public $customExpenseType = '';
    public $amount = '';
    public $description = '';
    public $expense_date = '';
    public $filter_date = '';
    public $search = '';

    // Salary Deduction
    public $is_salary_deduction = false;
    public $deduction_staff_id = null;
    public $allStaff = [];

    // Modal states
    public $showAddModal = false;
    public $showDeleteModal = false;
    public $expenseToDelete = null;

    protected $rules = [
        'expense_type' => 'required|string|max:255',
        'amount' => 'required|numeric|min:0',
        'description' => 'nullable|string',
        'expense_date' => 'required|date',
    ];

    public function mount()
    {
        $this->expense_date = date('Y-m-d');
        $this->filter_date = date('Y-m-d');
        $this->loadStaffList();
        $this->loadCategories();
    }

    public function loadStaffList()
    {
        $this->allStaff = \App\Models\Employee::orderBy('name')->get()->map(function($emp) {
            return [
                'id' => 'e_' . $emp->id,
                'name' => $emp->name,
                'type' => 'employee',
                'real_id' => $emp->id
            ];
        })->toArray();
    }

    public function loadCategories()
    {
        $categories = \App\Models\ExpenseCategory::where('expense_category', 'Daily Expenses')
            ->pluck('type')
            ->toArray();

        if (empty($categories)) {
            $defaultCategories = [
                'Sadhaka', 'Refreshment', 'Packaging', 'Stationery', 'Cleaning', 
                'Telephone', 'Water', 'Electricity', 'Internet', 'Daily Wages', 
                'Staff Meals', 'Overtime'
            ];
            foreach ($defaultCategories as $type) {
                \App\Models\ExpenseCategory::firstOrCreate([
                    'expense_category' => 'Daily Expenses',
                    'type' => $type
                ]);
            }
            $categories = $defaultCategories;
        }

        $this->expenseTypes = $categories;
    }

    public function openAddModal()
    {
        $this->resetForm();
        $this->showAddModal = true;
    }

    public function closeAddModal()
    {
        $this->showAddModal = false;
        $this->resetForm();
    }

    private function isEmployeePaidOffForCurrentMonth($employeeId, $month, $year)
    {
        // Check if there's any payment record for this employee in the current month
        $payment = \App\Models\EmployeePayment::where('employee_id', $employeeId)
            ->where('month', $month)
            ->where('year', $year)
            ->first();

        // If payment exists and amount_paid > 0, employee is paid off
        return $payment && $payment->amount_paid > 0;
    }

    public function addExpense()
    {
    // If user selected 'Other', ensure a custom type is provided and use it
    if ($this->expense_type === 'Other') {
        if (trim($this->customExpenseType) === '') {
            $this->addError('customExpenseType', 'Please enter expense type');
            return;
        }
        $this->expense_type = trim($this->customExpenseType);
        
        // Save new category to database if it doesn't exist
        \App\Models\ExpenseCategory::firstOrCreate([
            'expense_category' => 'Daily Expenses',
            'type' => $this->expense_type
        ]);
    }

    // If Salary Deduction is enabled, set the type to 'Daily Pay' for display purposes
    if ($this->is_salary_deduction) {
        $this->expense_type = 'Daily Wage';
    }

    $this->validate();

    // NEW VALIDATION: If amount is 0, check if employee already has a draw on the same day
    $isZeroAmountRecord = ($this->is_salary_deduction && floatval($this->amount) == 0);
    
    if ($isZeroAmountRecord && $this->deduction_staff_id) {
        $target = collect($this->allStaff)->firstWhere('id', $this->deduction_staff_id);
        if ($target) {
            $parsedDate = \Carbon\Carbon::parse($this->expense_date);
            $intMonth = (int)$parsedDate->format('m');
            $intYear = (int)$parsedDate->format('Y');
            
            // Check if there's already a draw (positive amount) for this employee on the same day
            $existingDrawOnSameDay = \App\Models\CashAdvance::where('employee_id', $target['real_id'])
                ->whereDate('date', $this->expense_date)
                ->where('amount', '>', 0)
                ->exists();
            
            if ($existingDrawOnSameDay) {
                $this->addError('amount', 'Cannot add zero amount: This employee already received a draw (positive amount) on ' . $parsedDate->format('d M Y') . '.');
                return;
            }
            
            // Also check if there's already a zero attendance record for same day (prevent duplicates)
            $existingZeroAttendance = \App\Models\CashAdvance::where('employee_id', $target['real_id'])
                ->whereDate('date', $this->expense_date)
                ->where('amount', '=', 0)
                ->exists();
            
            if ($existingZeroAttendance) {
                $this->addError('amount', 'Attendance already recorded for this employee on ' . $parsedDate->format('d M Y') . '.');
                return;
            }
        }
    }
    
    // Only check cash balance if amount > 0
    if (!$isZeroAmountRecord) {
        $cashInHandRecord = DB::table('cash_in_hands')->where('key', 'cash_amount')->first();
        $availableBalance = $cashInHandRecord ? floatval($cashInHandRecord->value) : 0;

        if ($availableBalance < floatval($this->amount)) {
            $this->addError('amount', 'Insufficient balance (Rs. ' . number_format($availableBalance, 2) . ') to add expense.');
            return;
        }
    }

    try {
        DB::beginTransaction();

        // Prepare description: For salary deduction, automatically add employee name
        $finalDescription = $this->description;
        if ($this->is_salary_deduction && $this->deduction_staff_id) {
            $target = collect($this->allStaff)->firstWhere('id', $this->deduction_staff_id);
            if ($target) {
                $employeeName = $target['name'];
                $finalDescription = $this->description 
                    ? "{$employeeName} - {$this->description}" 
                    : "Attendance record for {$employeeName}";
                    
                // Add zero amount indicator
                if ($isZeroAmountRecord) {
                    $finalDescription .= " (No draw - attendance recorded)";
                }
            }
        }

        // 1. Create staff expense with auto-approval (for staff portal tracking)
        // Skip creating StaffExpense if amount is 0 (just for attendance record)
        if (!$isZeroAmountRecord) {
            $staffExpense = StaffExpense::create([
                'staff_id' => Auth::id(),
                'expense_type' => $this->expense_type,
                'amount' => $this->amount,
                'description' => $finalDescription,
                'expense_date' => $this->expense_date,
                'status' => 'approved',
                'admin_notes' => 'Auto-approved (Shop Staff)',
            ]);

            // 2. Create the primary expense record (skip for zero amount)
            Expense::create([
                'category' => 'Staff Expense - ' . $this->expense_type,
                'amount' => $this->amount,
                'description' => 'Staff: ' . Auth::user()->name . ' - ' . ($finalDescription ?? $this->expense_type),
                'date' => $this->expense_date,
                'expense_type' => 'daily',
            ]);
        }

        // 3. Handle Salary Deduction (Daily Pay) if enabled
        if ($this->is_salary_deduction && $this->deduction_staff_id) {
            $target = collect($this->allStaff)->firstWhere('id', $this->deduction_staff_id);
            if ($target) {
                $parsedDate = \Carbon\Carbon::parse($this->expense_date);
                $salaryMonthDate = $parsedDate->startOfMonth()->format('Y-m-d');

                // Explicitly cast down values as integers to match Employee Model querying parameters exactly
                $intMonth = (int)$parsedDate->format('m');
                $intYear  = (int)$parsedDate->format('Y');

                $salaryData = [
                    'salary_month' => $salaryMonthDate,
                    'salary_type' => 'monthly',
                    'payment_status' => 'pending',
                    'deductions' => 0,
                    'net_salary' => 0,
                    'total_hours' => 0,
                    'overtime_hours' => 0,
                    'overtime_images' => 0,
                ];

                if ($target['type'] === 'user') {
                    $staffUser = \App\Models\User::with('userDetail')->find($target['real_id']);
                    $salaryData['user_id'] = $target['real_id'];
                    $salaryData['employee_id'] = null;
                    $salaryData['basic_salary'] = $staffUser->userDetail->basic_salary ?? 0;

                    $salary = \App\Models\Salary::firstOrCreate(
                        ['user_id' => $target['real_id'], 'salary_month' => $salaryMonthDate],
                        $salaryData
                    );

                    // REINFORCED LOOKUP ENGINE: Matching against user parameters to find the correct Employee row profile
                    $linkedEmployee = \App\Models\Employee::where(function($query) use ($staffUser) {
                        $query->whereRaw('LOWER(TRIM(name)) = ?', [strtolower(trim($staffUser->name))]);

                        if (!empty($staffUser->userDetail->phone)) {
                            $query->orWhere('contact', trim($staffUser->userDetail->phone));
                        }
                    })->first();

                    if ($linkedEmployee) {
                        // CHECK: Is this employee already paid off this month?
                        if ($this->isEmployeePaidOffForCurrentMonth($linkedEmployee->id, $intMonth, $intYear)) {
                            DB::rollBack();
                            $this->addError('amount', 'Cannot add daily pay: This employee has already been paid off for ' . $parsedDate->format('F Y') . '. Daily pay can only be added before payroll is settled.');
                            return;
                        }

                        // Create cash advance record for the employee (ALWAYS create, even for zero amount)
                        CashAdvance::create([
                            'employee_id' => $linkedEmployee->id,
                            'amount'      => (float)$this->amount,
                            'date'        => $this->expense_date,
                            'note'        => $isZeroAmountRecord ? 'Daily Pay: Attendance only (No draw)' : 'Daily Pay:' . ($finalDescription ?? $this->expense_type),
                            'month'       => $intMonth,
                            'year'        => $intYear,
                        ]);
                        
                        // Dispatch event to refresh employee data
                        $this->dispatch('employee-data-updated');
                    } else {
                        Log::warning("Daily Pay created for User ID {$staffUser->id} but matching profile in Employees table could not be found.");
                    }
                } else {
                    $employee = \App\Models\Employee::find($target['real_id']);

                    // CHECK: Is this employee already paid off this month?
                    if ($this->isEmployeePaidOffForCurrentMonth($target['real_id'], $intMonth, $intYear)) {
                        DB::rollBack();
                        $this->addError('amount', 'Cannot add daily pay: This employee has already been paid off for ' . $parsedDate->format('F Y') . '. Daily pay can only be added before payroll is settled.');
                        return;
                    }

                    $salaryData['user_id'] = null;
                    $salaryData['employee_id'] = $target['real_id'];
                    $salaryData['basic_salary'] = $employee->basic_salary ?? 0;

                    // DRAW RESTRICTION: Only check if amount > 0
                    if (!$isZeroAmountRecord) {
                        $existingDraws = (float)CashAdvance::where('employee_id', $target['real_id'])
                            ->where('month', $intMonth)
                            ->where('year', $intYear)
                            ->sum('amount');
                        $employeeNetSalary = (float)($employee->basic_salary ?? 0);
                        if (($existingDraws + (float)$this->amount) > $employeeNetSalary) {
                            DB::rollBack();
                            $nextMonthStart = $parsedDate->copy()->addMonth()->startOfMonth()->format('d M Y');
                            $remaining = max(0, $employeeNetSalary - $existingDraws);
                            $this->addError('amount', 'Daily pay limit exceeded for ' . $employee->name . '. Already paid: Rs. ' . number_format($existingDraws, 2) . ' of Rs. ' . number_format($employeeNetSalary, 2) . ' salary. Remaining: Rs. ' . number_format($remaining, 2) . '. Next month starts ' . $nextMonthStart . '.');
                            return;
                        }
                    }

                    $salary = \App\Models\Salary::firstOrCreate(
                        ['employee_id' => $target['real_id'], 'salary_month' => $salaryMonthDate],
                        $salaryData
                    );

                    // SEAMLESS INTEGRATION: Automatically generate a CashAdvance record (ALWAYS, even for zero)
                    CashAdvance::create([
                        'employee_id' => $target['real_id'],
                        'amount'      => (float)$this->amount,
                        'date'        => $this->expense_date,
                        'note'        => $isZeroAmountRecord ? 'Daily Pay: Attendance only (No draw)' : 'Daily Pay: ' . ($finalDescription ?? $this->expense_type),
                        'month'       => $intMonth,
                        'year'        => $intYear,
                    ]);
                    
                    // Dispatch event to refresh employee data
                    $this->dispatch('employee-data-updated');
                }

                // Only increment deductions if amount > 0
                if (!$isZeroAmountRecord) {
                    $salary->increment('deductions', $this->amount);
                }

                // Recalculate net salary
                $salary->net_salary = $salary->basic_salary
                    + ($salary->bonus ?? 0)
                    + ($salary->allowance ?? 0)
                    + ($salary->overtime ?? 0)
                    - $salary->deductions
                    - ($salary->additional_salary ?? 0);
                $salary->save();
            }
        }

        // 4. Update cash in hands — subtract expense amount only if amount > 0
        if (!$isZeroAmountRecord) {
            $cashInHandRecord = DB::table('cash_in_hands')->where('key', 'cash_amount')->first();
            if ($cashInHandRecord) {
                DB::table('cash_in_hands')
                    ->where('key', 'cash_amount')
                    ->update([
                        'value' => $cashInHandRecord->value - $this->amount,
                        'updated_at' => now()
                    ]);
            }

            // 5. Update today's POS session if expense is for today
            try {
                if (Carbon::parse($this->expense_date)->isToday()) {
                    $session = POSSession::getTodaySession(Auth::id());
                    if ($session) {
                        $session->expenses = ($session->expenses ?? 0) + $this->amount;
                        $session->save();
                        $session->calculateDifference();
                    }
                }
            } catch (\Exception $e) {
                Log::warning('Failed to update POS session after staff expense: ' . $e->getMessage());
            }
        }

        DB::commit();

        $message = $isZeroAmountRecord ? 'Attendance recorded successfully! (No monetary transaction)' : 'Expense added successfully!';
        $this->showToast('success', $message);
        $this->closeAddModal();
        $this->resetPage();
    } catch (\Exception $e) {
        DB::rollBack();
        $this->showToast('error', 'Error adding expense: ' . $e->getMessage());
    }
    }

    public function resetForm()
    {
        $this->expense_type = '';
        $this->amount = '';
        $this->description = '';
        $this->expense_date = date('Y-m-d');
        $this->customExpenseType = '';
        $this->is_salary_deduction = false;
        $this->deduction_staff_id = null;
        $this->resetValidation();
    }

    public function updatedFilterDate()
    {
        $this->resetPage();
    }

    public function confirmDelete($expenseId)
    {
        $this->expenseToDelete = $expenseId;
        $this->showDeleteModal = true;
    }

    public function cancelDelete()
    {
        $this->expenseToDelete = null;
        $this->showDeleteModal = false;
    }

    public function deleteExpense()
    {
        try {
            $expense = StaffExpense::where('staff_id', Auth::id())
                ->where('id', $this->expenseToDelete)
                ->first();

            if ($expense) {
                // If approved, add back the amount to cash in hand
                if ($expense->status === 'approved') {
                    $cashInHandRecord = DB::table('cash_in_hands')->where('key', 'cash_amount')->first();
                    if ($cashInHandRecord) {
                        DB::table('cash_in_hands')
                            ->where('key', 'cash_amount')
                            ->update([
                                'value' => $cashInHandRecord->value + $expense->amount,
                                'updated_at' => now()
                            ]);
                    }
                }

                $expense->delete();
                $this->showToast('success', 'Expense deleted successfully.');
            } else {
                $this->showToast('error', 'Expense not found.');
            }
        } catch (\Exception $e) {
            $this->showToast('error', 'Error deleting expense: ' . $e->getMessage());
        }

        $this->cancelDelete();
    }

    public function render()
    {
        $selectedDate = $this->filter_date ?: now()->toDateString();
        $selectedDate = Carbon::parse($selectedDate)->toDateString();

        $query = StaffExpense::where('staff_id', Auth::id())
            ->whereDate('expense_date', $selectedDate);

        // Apply search filter
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('expense_type', 'like', '%' . $this->search . '%')
                    ->orWhere('description', 'like', '%' . $this->search . '%');
            });
        }

        $expenses = $query->orderBy('created_at', 'desc')
            ->get();

        // Calculate totals
        $todayExpenses = StaffExpense::where('staff_id', Auth::id())
            ->whereDate('expense_date', $selectedDate)
            ->sum('amount');

        $month = Carbon::parse($selectedDate)->month;
        $year = Carbon::parse($selectedDate)->year;

        $monthExpenses = StaffExpense::where('staff_id', Auth::id())
            ->whereMonth('expense_date', $month)
            ->whereYear('expense_date', $year)
            ->sum('amount');

        return view('livewire.shop-staff.shop-staff-expenses', [
            'expenses' => $expenses,
            'todayExpenses' => $todayExpenses,
            'monthExpenses' => $monthExpenses,
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