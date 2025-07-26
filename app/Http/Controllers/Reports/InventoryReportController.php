<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class InventoryReportController extends Controller
{
    public function index()
    {
        // Get all products for dropdown
        $products = DB::table('products')
            ->select('id', 'name_en', 'name_ar')
            ->orderBy('name_en')
            ->get();

        // Get all warehouses for dropdown
        $warehouses = DB::table('warehouses')
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return view('reports.inventoryReport', compact('products', 'warehouses'));
    }

    public function generate(Request $request)
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'product_id' => 'nullable|exists:products,id',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'report_type' => 'required|in:summary,detailed,movements'
        ]);

        $startDate = $request->start_date ? Carbon::parse($request->start_date)->startOfDay() : null;
        $endDate = $request->end_date ? Carbon::parse($request->end_date)->endOfDay() : null;

        // Get products list for dropdown
        $products = DB::table('products')
            ->select('id', 'name_en', 'name_ar')
            ->orderBy('name_en')
            ->get();

        // Get warehouses list for dropdown
        $warehouses = DB::table('warehouses')
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        if ($request->report_type === 'summary') {
            $inventoryData = $this->generateSummaryReport($request->product_id, $request->warehouse_id);
            $movements = collect();
            $summary = $this->generateOverallSummary($request->product_id, $request->warehouse_id);
        } elseif ($request->report_type === 'movements') {
            $movements = $this->generateMovementsReport($startDate, $endDate, $request->product_id, $request->warehouse_id);
            $inventoryData = collect();
            $summary = [];
        } else {
            $inventoryData = $this->generateDetailedReport($request->product_id, $request->warehouse_id, $startDate, $endDate);
            $movements = collect();
            $summary = [];
        }

        return view('reports.inventoryReport', compact(
            'inventoryData',
            'movements',
            'summary',
            'products',
            'warehouses',
            'startDate',
            'endDate'
        ));
    }

    private function generateSummaryReport($productId = null, $warehouseId = null)
    {
        $query = DB::table('products')
            ->leftJoin('voucher_products', 'products.id', '=', 'voucher_products.product_id')
            ->leftJoin('note_vouchers', 'voucher_products.note_voucher_id', '=', 'note_vouchers.id')
            ->leftJoin('warehouses', 'note_vouchers.warehouse_id', '=', 'warehouses.id')
            ->select(
                'products.id',
                'products.name_en',
                'products.name_ar',
                'warehouses.name as warehouse_name',
                'warehouses.id as warehouse_id',
                DB::raw('COALESCE(SUM(CASE WHEN note_vouchers.type = 1 THEN voucher_products.quantity ELSE 0 END), 0) as total_in'),
                DB::raw('COALESCE(SUM(CASE WHEN note_vouchers.type = 2 THEN voucher_products.quantity ELSE 0 END), 0) as total_out'),
                DB::raw('COALESCE(SUM(CASE WHEN note_vouchers.type = 1 THEN voucher_products.quantity ELSE 0 END), 0) - COALESCE(SUM(CASE WHEN note_vouchers.type = 2 THEN voucher_products.quantity ELSE 0 END), 0) as current_stock')
            );

        if ($productId) {
            $query->where('products.id', $productId);
        }

        if ($warehouseId) {
            $query->where('warehouses.id', $warehouseId);
        }

        return $query
            ->groupBy('products.id', 'products.name_en', 'products.name_ar', 'warehouses.id', 'warehouses.name')
            ->orderBy('products.name_en')
            ->paginate(50)
            ->withQueryString();
    }

    private function generateDetailedReport($productId = null, $warehouseId = null, $startDate = null, $endDate = null)
    {
        $query = DB::table('voucher_products')
            ->join('note_vouchers', 'voucher_products.note_voucher_id', '=', 'note_vouchers.id')
            ->join('products', 'voucher_products.product_id', '=', 'products.id')
            ->leftJoin('warehouses', 'note_vouchers.warehouse_id', '=', 'warehouses.id')
            ->leftJoin('orders', 'note_vouchers.order_id', '=', 'orders.id')
            ->select(
                'voucher_products.*',
                'note_vouchers.number as voucher_number',
                'note_vouchers.type as voucher_type',
                'note_vouchers.date_note_voucher',
                'note_vouchers.note as voucher_note',
                'products.name_en',
                'products.name_ar',
                'warehouses.name as warehouse_name',
                'orders.number as order_number'
            );

        if ($productId) {
            $query->where('products.id', $productId);
        }

        if ($warehouseId) {
            $query->where('warehouses.id', $warehouseId);
        }

        if ($startDate && $endDate) {
            $query->whereBetween('note_vouchers.date_note_voucher', [$startDate->toDateString(), $endDate->toDateString()]);
        }

        return $query
            ->orderBy('note_vouchers.date_note_voucher', 'desc')
            ->orderBy('note_vouchers.id', 'desc')
            ->paginate(50)
            ->withQueryString();
    }

    private function generateMovementsReport($startDate = null, $endDate = null, $productId = null, $warehouseId = null)
    {
        $query = DB::table('voucher_products')
            ->join('note_vouchers', 'voucher_products.note_voucher_id', '=', 'note_vouchers.id')
            ->join('products', 'voucher_products.product_id', '=', 'products.id')
            ->leftJoin('warehouses', 'note_vouchers.warehouse_id', '=', 'warehouses.id')
            ->leftJoin('orders', 'note_vouchers.order_id', '=', 'orders.id')
            ->select(
                'note_vouchers.date_note_voucher',
                'products.name_en',
                'products.name_ar',
                'warehouses.name as warehouse_name',
                'note_vouchers.type as movement_type',
                'voucher_products.quantity',
                'note_vouchers.note as description',
                'orders.number as order_number',
                'note_vouchers.number as voucher_number'
            );

        if ($startDate && $endDate) {
            $query->whereBetween('note_vouchers.date_note_voucher', [$startDate->toDateString(), $endDate->toDateString()]);
        }

        if ($productId) {
            $query->where('products.id', $productId);
        }

        if ($warehouseId) {
            $query->where('warehouses.id', $warehouseId);
        }

        return $query
            ->orderBy('note_vouchers.date_note_voucher', 'desc')
            ->orderBy('note_vouchers.id', 'desc')
            ->paginate(50)
            ->withQueryString();
    }

    private function generateOverallSummary($productId = null, $warehouseId = null)
    {
        $query = DB::table('voucher_products')
            ->join('note_vouchers', 'voucher_products.note_voucher_id', '=', 'note_vouchers.id')
            ->join('products', 'voucher_products.product_id', '=', 'products.id');

        if ($productId) {
            $query->where('products.id', $productId);
        }

        if ($warehouseId) {
            $query->where('note_vouchers.warehouse_id', $warehouseId);
        }

        $totals = $query
            ->select(
                DB::raw('COALESCE(SUM(CASE WHEN note_vouchers.type = 1 THEN voucher_products.quantity ELSE 0 END), 0) as total_in'),
                DB::raw('COALESCE(SUM(CASE WHEN note_vouchers.type = 2 THEN voucher_products.quantity ELSE 0 END), 0) as total_out'),
                DB::raw('COUNT(DISTINCT products.id) as total_products'),
                DB::raw('COUNT(DISTINCT note_vouchers.warehouse_id) as total_warehouses')
            )
            ->first();

        // Get low stock products
        $minimumQuantity = \App\Models\Setting::getValue('minimum_to_notify_me_for_quantity_products', 2);
        
        $lowStockQuery = DB::table('products')
            ->leftJoin('voucher_products', 'products.id', '=', 'voucher_products.product_id')
            ->leftJoin('note_vouchers', 'voucher_products.note_voucher_id', '=', 'note_vouchers.id')
            ->select(
                'products.id',
                DB::raw('COALESCE(SUM(CASE WHEN note_vouchers.type = 1 THEN voucher_products.quantity ELSE 0 END), 0) - COALESCE(SUM(CASE WHEN note_vouchers.type = 2 THEN voucher_products.quantity ELSE 0 END), 0) as current_stock')
            )
            ->groupBy('products.id')
            ->having('current_stock', '<=', $minimumQuantity);

        if ($productId) {
            $lowStockQuery->where('products.id', $productId);
        }

        if ($warehouseId) {
            $lowStockQuery->where('note_vouchers.warehouse_id', $warehouseId);
        }

        $lowStockCount = $lowStockQuery->count();

        return [
            'total_in' => $totals->total_in ?? 0,
            'total_out' => $totals->total_out ?? 0,
            'current_total_stock' => ($totals->total_in ?? 0) - ($totals->total_out ?? 0),
            'total_products' => $totals->total_products ?? 0,
            'total_warehouses' => $totals->total_warehouses ?? 0,
            'low_stock_products' => $lowStockCount,
            'minimum_quantity_threshold' => $minimumQuantity
        ];
    }

    public function export(Request $request)
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'product_id' => 'nullable|exists:products,id',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'report_type' => 'required|in:summary,detailed,movements'
        ]);

        $startDate = $request->start_date ? Carbon::parse($request->start_date) : null;
        $endDate = $request->end_date ? Carbon::parse($request->end_date) : null;

        $filename = "inventory_report_" . $request->report_type . "_" . now()->format('Y-m-d_H-i-s') . ".csv";

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($request, $startDate, $endDate) {
            $file = fopen('php://output', 'w');

            if ($request->report_type === 'summary') {
                // Summary CSV Headers
                fputcsv($file, [
                    'Product Name (EN)',
                    'Product Name (AR)', 
                    'Warehouse',
                    'Total In',
                    'Total Out',
                    'Current Stock'
                ]);

                $data = $this->generateSummaryReport($request->product_id, $request->warehouse_id);
                foreach ($data as $item) {
                    fputcsv($file, [
                        $item->name_en,
                        $item->name_ar,
                        $item->warehouse_name ?? 'N/A',
                        $item->total_in,
                        $item->total_out,
                        $item->current_stock
                    ]);
                }
            } elseif ($request->report_type === 'movements') {
                // Movements CSV Headers
                fputcsv($file, [
                    'Date',
                    'Product Name (EN)',
                    'Product Name (AR)',
                    'Warehouse',
                    'Movement Type',
                    'Quantity',
                    'Description',
                    'Order Number',
                    'Voucher Number'
                ]);

                $data = $this->generateMovementsReport($startDate, $endDate, $request->product_id, $request->warehouse_id);
                foreach ($data as $item) {
                    fputcsv($file, [
                        $item->date_note_voucher,
                        $item->name_en,
                        $item->name_ar,
                        $item->warehouse_name ?? 'N/A',
                        $item->movement_type == 1 ? 'In' : 'Out',
                        $item->quantity,
                        $item->description,
                        $item->order_number ?? 'N/A',
                        $item->voucher_number
                    ]);
                }
            } else {
                // Detailed CSV Headers
                fputcsv($file, [
                    'Date',
                    'Product Name (EN)',
                    'Product Name (AR)',
                    'Warehouse',
                    'Voucher Type',
                    'Quantity',
                    'Note',
                    'Order Number',
                    'Voucher Number'
                ]);

                $data = $this->generateDetailedReport($request->product_id, $request->warehouse_id, $startDate, $endDate);
                foreach ($data as $item) {
                    fputcsv($file, [
                        $item->date_note_voucher,
                        $item->name_en,
                        $item->name_ar,
                        $item->warehouse_name ?? 'N/A',
                        $item->voucher_type == 1 ? 'In' : 'Out',
                        $item->quantity,
                        $item->note,
                        $item->order_number ?? 'N/A',
                        $item->voucher_number
                    ]);
                }
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}