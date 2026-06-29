<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    /**
     * Show the public invoice view via a signed URL.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function showPublic(Request $request, $id)
    {
        // Load sale with all necessary relationships including returns and deliverySale
        $sale = Sale::with(['customer', 'items.product', 'payments', 'deliverySale', 'returns' => function ($q) {
            $q->with('product');
        }])->findOrFail($id);

        // Return the existing print view used for receipts
        return view('components.sale-receipt-print', compact('sale'));
    }
}
