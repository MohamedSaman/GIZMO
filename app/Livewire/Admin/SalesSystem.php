<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use App\Models\Customer;
use App\Livewire\Concerns\WithDynamicLayout;

#[Title('Customer Order Link Generator')]
class SalesSystem extends Component
{
    use WithDynamicLayout;

    public $customerId = '';
    public $validUntil;
    public $generatedLink = '';
    public $customers = [];
    public $selectedCustomer = null;

    public function mount()
    {
        $this->validUntil = now()->addDays(30)->format('Y-m-d');
        $this->loadCustomers();
        $this->setDefaultCustomer();
    }

    public function setDefaultCustomer()
    {
        $walkingCustomer = Customer::where('name', 'Walking Customer')->first();
        if (!$walkingCustomer) {
            $walkingCustomer = Customer::create([
                'name' => 'Walking Customer',
                'phone' => 'xxxxx',
                'address' => 'xxxxx',
                'type' => 'retail',
            ]);
            $this->loadCustomers();
        }
        $this->customerId = $walkingCustomer->id;
        $this->selectedCustomer = $walkingCustomer;
    }

    public function loadCustomers()
    {
        // Don't show walking customer for public link sharing to prevent order confusion
        $this->customers = Customer::where('name', '!=', 'Walking Customer')->orderBy('name')->get();
    }

    public function updatedCustomerId($value)
    {
        if ($value) {
            $this->selectedCustomer = Customer::find($value);
        } else {
            $this->selectedCustomer = null;
        }
        $this->generatedLink = '';
    }

    public function updatedValidUntil()
    {
        $this->generatedLink = '';
    }

    public function generateLink()
    {
        $this->validate([
            'customerId' => 'required|exists:customers,id',
            'validUntil' => 'required|date|after_or_equal:today',
        ], [
            'customerId.required' => 'Please select a customer first.',
            'validUntil.required' => 'Please choose a target date.',
        ]);

        $this->generatedLink = url('/create-order?customer_id=' . $this->customerId . '&valid_until=' . $this->validUntil);

        $this->js("Swal.fire({
            icon: 'success',
            title: 'Link Generated!',
            text: 'You can now copy and share this ordering link with the customer.',
            timer: 2000,
            showConfirmButton: false
        })");
    }

    public function render()
    {
        return view('livewire.admin.sales-system')->layout($this->layout);
    }
}
