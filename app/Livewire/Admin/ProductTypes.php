<?php

namespace App\Livewire\Admin;

use Exception;
use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\ProductType;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use App\Livewire\Concerns\WithDynamicLayout;

#[Title("Product Type List")]
class ProductTypes extends Component
{
    use WithDynamicLayout;

    public $typeName;
    public $editTypeId;
    public $editTypeName;
    public $deleteId;

    public function render()
    {
        $types = ProductType::orderBy('id', 'desc')->get();
        return view('livewire.admin.product-types', [
            'types' => $types,
        ])->layout($this->layout);
    }

    public function createType()
    {
        $this->resetType();
        $this->dispatch('create-type-modal');
    }

    public function resetType()
    {
        $this->reset(['typeName']);
        $this->resetErrorBag();
        $this->resetValidation();
    }

    public function saveType()
    {
        $this->validate([
            'typeName' => 'required|unique:product_types,type_name'
        ]);

        try {
            ProductType::create([
                'type_name' => $this->typeName,
            ]);

            $this->reset(['typeName']);
            $this->dispatch('types-updated');
            $this->js("Swal.fire('Success!', 'Product Type Created Successfully', 'success')");
        } catch (Exception $e) {
            $this->js("Swal.fire('Error!', '" . $e->getMessage() . "', 'error')");
        }
    }

    public function editType($id)
    {
        $type = ProductType::find($id);
        if (!$type) {
            $this->js("Swal.fire('Error!', 'Product Type not found', 'error')");
            return;
        }

        $this->editTypeName = $type->type_name;
        $this->editTypeId = $type->id;
        $this->resetErrorBag();
        $this->resetValidation();
        $this->dispatch('edit-type');
    }

    public function updateType()
    {
        $this->validate([
            'editTypeName' => 'required|unique:product_types,type_name,' . $this->editTypeId
        ]);

        try {
            ProductType::where('id', $this->editTypeId)->update([
                'type_name' => $this->editTypeName,
            ]);

            $this->reset(['editTypeName', 'editTypeId']);
            $this->dispatch('types-updated');
            $this->js("Swal.fire('Success!', 'Product Type Updated Successfully', 'success')");
        } catch (Exception $e) {
            $this->js("Swal.fire('Error!', '" . $e->getMessage() . "', 'error')");
        }
    }

    public function confirmDelete($id)
    {
        $this->deleteId = $id;
        $this->dispatch('confirm-delete');
    }

    #[On('confirmDelete')]
    public function deleteType()
    {
        try {
            ProductType::where('id', $this->deleteId)->delete();
            $this->js("Swal.fire('Deleted!', 'Product Type has been deleted successfully', 'success')");
            $this->reset(['deleteId']);
            $this->dispatch('types-updated');
        } catch (Exception $e) {
            $this->js("Swal.fire('Error!', '" . $e->getMessage() . "', 'error')");
        }
    }
}
