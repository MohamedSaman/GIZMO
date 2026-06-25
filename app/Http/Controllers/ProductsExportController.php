<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ProductDetail;

class ProductsExportController extends Controller
{
    public function export()
    {
        $products = ProductDetail::join('product_prices', 'product_details.id', '=', 'product_prices.product_id')
            ->join('product_stocks', 'product_details.id', '=', 'product_stocks.product_id')
            ->leftJoin('brand_lists', 'brand_lists.id', '=', 'product_details.brand_id')
            ->leftJoin('category_lists', 'category_lists.id', '=', 'product_details.category_id')
            ->leftJoin('product_suppliers', 'product_suppliers.id', '=', 'product_details.supplier_id')
            ->select(
                'product_details.id',
                'product_details.code',
                'product_details.name as product_name',
                'product_details.model',
                'product_details.brand as brand_string',
                'brand_lists.brand_name as brand_name',
                'category_lists.category_name as category_name',
                'product_details.type',
                'product_details.specifications',
                'product_details.barcode',
                'product_details.status',
                'product_prices.supplier_price',
                'product_prices.retail_price',
                'product_prices.wholesale_price',
                'product_prices.distributor_price',
                'product_stocks.available_stock',
                'product_stocks.damage_stock',
                'product_stocks.total_stock',
                'product_suppliers.name as supplier_name'
            )
            ->orderBy('product_details.created_at', 'desc')
            ->get();

        $filename = 'Products_export_' . date('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($products) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'ID',
                'Code',
                'Name',
                'Model',
                'Brand',
                'Category',
                'Type',
                'Voltage',
                'Power Rating',
                'Warranty',
                'Material',
                'Color',
                'Barcode',
                'Status',
                'Supplier Price',
                'Retail Price',
                'Wholesale Price',
                'Distributor Price',
                'Available Stock',
                'Damage Stock',
                'Total Stock',
                'Supplier'
            ]);
            foreach ($products as $product) {
                $specs = is_string($product->specifications) 
                    ? json_decode($product->specifications, true) 
                    : ($product->specifications ?? []);

                fputcsv($handle, [
                    $product->id,
                    $product->code,
                    $product->product_name,
                    $product->model,
                    $product->brand_string ?: $product->brand_name,
                    $product->category_name,
                    $product->type,
                    $specs['voltage'] ?? '',
                    $specs['power'] ?? '',
                    $specs['warranty'] ?? '',
                    $specs['material'] ?? '',
                    $specs['color'] ?? '',
                    $product->barcode,
                    $product->status,
                    $product->supplier_price,
                    $product->retail_price,
                    $product->wholesale_price,
                    $product->distributor_price,
                    $product->available_stock,
                    $product->damage_stock,
                    $product->total_stock,
                    $product->supplier_name
                ]);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
