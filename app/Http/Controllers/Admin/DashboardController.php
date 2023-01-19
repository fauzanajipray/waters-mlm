<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function getProductBelowMinimumStock()
    {
        $products = Product::
            leftJoin('stocks', 'stocks.product_id', '=', 'products.id')
            ->select('products.*', DB::raw('IFNULL(stocks.quantity, 0) as quantity'))
            ->whereRaw('stocks.quantity < products.min_stock_pusat')
            ->orderBy(DB::raw('(products.min_stock_pusat - stocks.quantity)'), 'desc')
            ->limit(10)
            ->get();
        $productsTotal = Product::
            leftJoin('stocks', 'stocks.product_id', '=', 'products.id')
            ->select('products.*', DB::raw('IFNULL(stocks.quantity, 0) as quantity'))
            ->whereRaw('stocks.quantity < products.min_stock_pusat')
            ->count();

        $products->map(function ($p) {
            if($p->model) $p->name .= ' - ' . $p->model;
            if($p->capacity) $p->name .= ' - ' . $p->capacity;
            $p->id = $p->product_id;
            return $p;
        });
        return [
            'products' => $products,
            'total' => $productsTotal
        ];
    }
}
