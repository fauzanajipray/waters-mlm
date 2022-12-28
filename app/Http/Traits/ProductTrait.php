<?php
namespace App\Http\Traits;

use App\Models\Product;
use App\Models\Stock;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;

trait ProductTrait {
  public function getProduct()
    {
        $id = request()->input('product_id');
        $product = Product::
            leftJoin('branch_products', 'branch_products.product_id', 'products.id')
            ->select(
                'products.*',
                'branch_products.additional_price',
                DB::raw('(products.price + branch_products.additional_price) AS netto_price')
            )
            ->where('products.id', $id)
            ->first();
        return response()->json($product);
    }
    
    public function getDemokitProducts()
    {
        $search_term = request()->input('q');
        $form = collect(request()->input('form'));
        $member_id = $form->where('name', 'member_id')->first();
        $branch_id = $form->where('name', 'branch_id')->first();
        if(!$member_id || !$branch_id){
            return response()->json([]);
        }
        if($search_term){
            $products = Stock::
                leftJoin(
                    DB::raw('(
                        SELECT `products`.*, `branch_products2`.`additional_price` AS `additional_price`
                        FROM `products`
                        LEFT JOIN (
                            SELECT * FROM branch_products WHERE `branch_id` = '.$branch_id['value'].'
                        ) AS `branch_products2` 
                        ON `products`.`id` = `branch_products2`.`product_id`
                    ) AS `products2`' ),
                    function($join) {
                        $join->on('products2.id', '=', 'stocks.product_id');
                    }
                )
                ->where('stocks.branch_id', $branch_id['value'])
                ->where('stocks.quantity', '>', 0)
                ->where('is_demokit', 1)
                ->where('name', 'like', '%'.$search_term.'%')
                ->orWhere('model', 'like', '%'.$search_term.'%')
                ->select(
                    'stocks.*',
                    'products2.name',
                    'products2.model',
                    'products2.price',
                    'products2.type',
                    'products2.additional_price',
                    DB::raw('(products2.additional_price + products2.price) AS netto_price')
                )
                ->get();
        }else{
            $products = Stock::
                leftJoin(
                    DB::raw('(
                        SELECT `products`.*, `branch_products2`.`additional_price` AS `additional_price`
                        FROM `products`
                        LEFT JOIN (
                            SELECT * FROM branch_products WHERE `branch_id` = '.$branch_id['value'].'
                        ) AS `branch_products2` 
                        ON `products`.`id` = `branch_products2`.`product_id`
                    ) AS `products2`' ),
                    function($join) {
                        $join->on('products2.id', '=', 'stocks.product_id');
                    }
                )
                ->where('stocks.branch_id', $branch_id['value'])
                ->where('stocks.quantity', '>', 0)
                ->where('is_demokit', 1)
                ->select(
                    'stocks.*',
                    'products2.name',
                    'products2.model',
                    'products2.price',
                    'products2.type',
                    'products2.additional_price',
                    DB::raw('(products2.additional_price + products2.price) AS netto_price')
                )
                ->get();   
        }
        $products->map(function ($stock) {
            $stock->name = $stock->name.' - '.$stock->model. ' - '.number_format($stock->netto_price). ' - Stock : '.$stock->quantity ;
            $stock->id = $stock->product_id;
            return $stock;
        });
        $productBought = [];
        $transactions = Transaction::with('transactionProducts')
            ->where('member_id', $member_id['value'])->where('type', 'Demokit')->get();
        foreach($transactions as $transaction){
            foreach($transaction->transactionProducts as $transactionProduct){
                $productBought[$transactionProduct->product_id] = $transactionProduct->product_id;
            }
        }
        $products = $products->filter(function($product) use ($productBought){
            return !isset($productBought[$product->id]);
        });
        return $products;
    }

    public function getDisplayProducts()
    {
        $search_term = request()->input('q');   
        $form = collect(request()->input('form'));
        $member_id = $form->where('name', 'member_id')->first();
        $branch_id = $form->where('name', 'branch_id')->first();
        if(!$member_id || !$branch_id){
            return response()->json([]);
        }

        if($search_term){
            $products = Stock::
                leftJoin(
                    DB::raw('(
                        SELECT `products`.*, `branch_products2`.`additional_price` AS `additional_price`
                        FROM `products`
                        LEFT JOIN (
                            SELECT * FROM branch_products WHERE `branch_id` = '.$branch_id['value'].'
                        ) AS `branch_products2` 
                        ON `products`.`id` = `branch_products2`.`product_id`
                    ) AS `products2`' ),
                    function($join) {
                        $join->on('products2.id', '=', 'stocks.product_id');
                    }
                )
                ->where('stocks.branch_id', $branch_id['value'])
                ->where('stocks.quantity', '>', 0)
                ->where('name', 'like', '%'.$search_term.'%')
                ->orWhere('model', 'like', '%'.$search_term.'%')
                ->select(
                    'stocks.*',
                    'products2.name',
                    'products2.model',
                    'products2.price',
                    'products2.type',
                    'products2.additional_price',
                    DB::raw('(products2.additional_price + products2.price) AS netto_price')
                )
                ->get();
        }else{
            $products = Stock::
                leftJoin(
                    DB::raw('(
                        SELECT `products`.*, `branch_products2`.`additional_price` AS `additional_price`
                        FROM `products`
                        LEFT JOIN (
                            SELECT * FROM branch_products WHERE `branch_id` = '.$branch_id['value'].'
                        ) AS `branch_products2` 
                        ON `products`.`id` = `branch_products2`.`product_id`
                    ) AS `products2`' ),
                    function($join) {
                        $join->on('products2.id', '=', 'stocks.product_id');
                    }
                )
                ->where('stocks.branch_id', $branch_id['value'])
                ->where('stocks.quantity', '>', 0)
                ->select(
                    'stocks.*',
                    'products2.name',
                    'products2.model',
                    'products2.price',
                    'products2.type',
                    'products2.additional_price',
                    DB::raw('(products2.additional_price + products2.price) AS netto_price')
                )
                ->get();
        }

        $products->map(function ($stock) {
            if ($stock->type == 'sparepart'){
                $stock->name = $stock->name.' - '.number_format($stock->netto_price). ' - Stock : '.$stock->quantity;
            } else {
                $stock->name = $stock->name.' - '.$stock->model. ' - '.number_format($stock->netto_price). ' - Stock : '.$stock->quantity;
            }
            $stock->id = $stock->product_id;
           return $stock;
        });

        // Filter Sudah Pernah Membeli
        $productBought = [];
        $transactions = Transaction::with('transactionProducts')
            ->where('member_id', $member_id['value'])->where('type', 'Normal')->where('status_paid', true)->get();
        foreach($transactions as $transaction){
            foreach($transaction->transactionProducts as $transactionProduct){
                $productBought[$transactionProduct->product_id] = $transactionProduct->product_id;
            }
        }
        $products = $products->filter(function($product) use ($productBought){
            return isset($productBought[$product->product_id]);
        });

        // Filter sudah beli display
        $productBoughtDisplay = [];
        $transactions = Transaction::with('transactionProducts')
            ->where('member_id', $member_id['value'])->where('type', 'Display')->get();
        foreach($transactions as $transaction){
            foreach($transaction->transactionProducts as $transactionProduct){
                $productBoughtDisplay[$transactionProduct->product_id] = $transactionProduct->product_id;
            }
        }
        $products = $products->filter(function($product) use ($productBoughtDisplay){
            return !isset($productBoughtDisplay[$product->product_id]);
        });
        return $products;
    }

    public function getBebasProducts()
    {
        $search_term = request()->input('q');
        $branch_id = collect(request()->form)->where('name', 'branch_id')->first();
        if(!$branch_id){
            return response()->json([]);
        }        
        if($search_term){
            $products = Stock::
                leftJoin(
                    DB::raw('(
                        SELECT `products`.*, `branch_products2`.`additional_price` AS `additional_price`
                        FROM `products`
                        LEFT JOIN (
                            SELECT * FROM branch_products WHERE `branch_id` = '.$branch_id['value'].'
                        ) AS `branch_products2` 
                        ON `products`.`id` = `branch_products2`.`product_id`
                    ) AS `products2`' ),
                    function($join) {
                        $join->on('products2.id', '=', 'stocks.product_id');
                    }
                )
                ->where('stocks.branch_id', $branch_id['value'])
                ->where('stocks.quantity', '>', 0)
                ->where('products2.type', 'product')
                ->where('name', 'like', '%'.$search_term.'%')
                ->orWhere('model', 'like', '%'.$search_term.'%')
                ->select(
                    'stocks.*',
                    'products2.name',
                    'products2.model',
                    'products2.price',
                    'products2.type',
                    'products2.additional_price',
                    DB::raw('(products2.additional_price + products2.price) AS netto_price')
                )
                ->get();
        }else{
            $products = Stock::
                leftJoin(
                    DB::raw('(
                        SELECT `products`.*, `branch_products2`.`additional_price` AS `additional_price`
                        FROM `products`
                        LEFT JOIN (
                            SELECT * FROM branch_products WHERE `branch_id` = '.$branch_id['value'].'
                        ) AS `branch_products2` 
                        ON `products`.`id` = `branch_products2`.`product_id`
                    ) AS `products2`' ),
                    function($join) {
                        $join->on('products2.id', '=', 'stocks.product_id');
                    }
                )
                ->where('stocks.branch_id', $branch_id['value'])
                ->where('stocks.quantity', '>', 0)
                ->where('products2.type', 'product')
                ->select(
                    'stocks.*',
                    'products2.name',
                    'products2.model',
                    'products2.price',
                    'products2.type',
                    'products2.additional_price',
                    DB::raw('(products2.additional_price + products2.price) AS netto_price')
                )
                ->get();
        }

        $products->map(function ($stock) {
            $stock->name = $stock->name.' - '.$stock->model. ' - '.number_format($stock->netto_price). ' - Stock : '.$stock->quantity ;
            $stock->id = $stock->product_id;
            return $stock;
        });

        return $products;
    }

    public function getProductsForFilter() 
    {
        $search_term = request()->input('q');
        if($search_term){
            $products = Product::where('name', 'like', '%'.$search_term.'%')
                ->orWhere('model', 'like', '%'.$search_term.'%')
                ->get();
        }else{
            $products = Product::get();
        }
        $products->map(function($product){
            $product->name = $product->name.' - '.$product->model. ' - '.$product->price;
            return $product;
        });

        return $products->pluck('name', 'id');
    }

    public function getProductForStock() 
    {  
        $search_term = request()->input('q');
        $branch_id = request()->form[2];
        $origin_branch_id = request()->form[3];
        if($branch_id['name'] != 'branch_id' ) { 
            return response()->json([]);
        }
        if ($branch_id['value'] != 1) {
            if($origin_branch_id['name'] == 'origin_branch_id' ) { 
                if($search_term){
                    $stocks = Stock::leftJoin('products', 'products.id', '=', 'stocks.product_id')
                        ->where('branch_id', $origin_branch_id['value'])
                        ->where('quantity', '>', 0)
                        ->where('name', 'like', '%'.$search_term.'%')
                        ->orWhere('model', 'like', '%'.$search_term.'%')
                        ->get();
                }else{
                    $stocks = Stock::leftJoin('products', 'products.id', '=', 'stocks.product_id')
                        ->where('branch_id', $origin_branch_id['value'])
                        ->where('quantity', '>', 0)
                        ->get();
                }
                $stocks->map(function ($stock) {
                    $stock->name = $stock->name.' - '.$stock->model. ' - '.number_format($stock->price) ;
                    return $stock;
                });

                return $stocks;
            } else {
                return response()->json([]);
            }
        } else {
            if($search_term){
                $products = Product::where('name', 'like', '%'.$search_term.'%')
                    ->orWhere('model', 'like', '%'.$search_term.'%')
                    ->get();
            }else{
                $products = Product::get();
            }
            $products->map(function($product){
                if ($product->type == 'sparepart'){
                    $product->name = $product->name.' - '.$product->price;
                } else {
                    $product->name = $product->name.' - '.$product->model. ' - '.$product->price;
                }
                return $product;
            });
            return $products;
        }
    }

    public function getProductStock(Request $request, $id, $branch_id)
    {
        $stocks = Stock::where('product_id', $id)
            ->where('branch_id', $branch_id)
            ->first();
        return $stocks->quantity ?? 0;
    }

    public function getProductTransaction() 
    {
        $search_term = request()->input('q');
        $branch_id = collect(request()->form)->where('name', 'branch_id')->first();
        if(!$branch_id){
            return response()->json([]);
        }        
        if($search_term){
            $stocks = Stock::
                leftJoin(
                    DB::raw('(
                        SELECT `products`.*, `branch_products2`.`additional_price` AS `additional_price`
                        FROM `products`
                        LEFT JOIN (
                            SELECT * FROM branch_products WHERE `branch_id` = '.$branch_id['value'].'
                        ) AS `branch_products2` 
                        ON `products`.`id` = `branch_products2`.`product_id`
                    ) AS `products2`' ),
                    function($join) {
                        $join->on('products2.id', '=', 'stocks.product_id');
                    }
                )
                ->where('stocks.branch_id', $branch_id['value'])
                ->where('stocks.quantity', '>', 0)
                ->where('products2.type', 'product')
                ->where('name', 'like', '%'.$search_term.'%')
                ->orWhere('model', 'like', '%'.$search_term.'%')
                ->select(
                    'stocks.*',
                    'products2.name',
                    'products2.model',
                    'products2.price',
                    'products2.type',
                    'products2.additional_price',
                    DB::raw('(products2.additional_price + products2.price) AS netto_price')
                )
                ->get();
        }else{
            $stocks = Stock::
                leftJoin(
                    DB::raw('(
                        SELECT `products`.*, `branch_products2`.`additional_price` AS `additional_price`
                        FROM `products`
                        LEFT JOIN (
                            SELECT * FROM branch_products WHERE `branch_id` = '.$branch_id['value'].'
                        ) AS `branch_products2` 
                        ON `products`.`id` = `branch_products2`.`product_id`
                    ) AS `products2`' ),
                    function($join) {
                        $join->on('products2.id', '=', 'stocks.product_id');
                    } 
                )
                ->where('stocks.branch_id', $branch_id['value'])
                ->where('stocks.quantity', '>', 0)
                ->where('products2.type', 'product')
                ->select(
                    'stocks.*',
                    'products2.name',
                    'products2.model',
                    'products2.price',
                    'products2.type',
                    'products2.additional_price',
                    DB::raw('(products2.additional_price + products2.price) AS netto_price')
                )
                ->get();
        }
        $stocks = $stocks->map(function ($stock) {
            $stock->name = $stock->name.' - '.$stock->model. ' - '.number_format($stock->netto_price). ' - Stock : '.$stock->quantity ;
            return $stock;
        });

        return $stocks;
    }

    public function getProductSparepartTransaction() 
    {
        $search_term = request()->input('q');
        $branch_id = collect(request()->form)->where('name', 'branch_id')->first();
        if(!$branch_id){
            return response()->json([]);
        }        
        if($search_term){
            $products = Stock::
                leftJoin(
                    DB::raw('(
                        SELECT `products`.*, `branch_products2`.`additional_price` AS `additional_price`
                        FROM `products`
                        LEFT JOIN (
                            SELECT * FROM branch_products WHERE `branch_id` = '.$branch_id['value'].'
                        ) AS `branch_products2` 
                        ON `products`.`id` = `branch_products2`.`product_id`
                    ) AS `products2`' ),
                    function($join) {
                        $join->on('products2.id', '=', 'stocks.product_id');
                    }
                )
                ->where('stocks.branch_id', $branch_id['value'])
                ->where('stocks.quantity', '>', 0)
                ->where('products2.type', 'sparepart')
                ->where('name', 'like', '%'.$search_term.'%')
                ->orWhere('model', 'like', '%'.$search_term.'%')
                ->select(
                    'stocks.*',
                    'products2.name',
                    'products2.model',
                    'products2.price',
                    'products2.type',
                    'products2.additional_price',
                    DB::raw('(products2.additional_price + products2.price) AS netto_price')
                )
                ->get();   
        }else{
            $products = Stock::
                leftJoin(
                    DB::raw('(
                        SELECT `products`.*, `branch_products2`.`additional_price` AS `additional_price`
                        FROM `products`
                        LEFT JOIN (
                            SELECT * FROM branch_products WHERE `branch_id` = '.$branch_id['value'].'
                        ) AS `branch_products2` 
                        ON `products`.`id` = `branch_products2`.`product_id`
                    ) AS `products2`' ),
                    function($join) {
                        $join->on('products2.id', '=', 'stocks.product_id');
                    }
                )
                ->where('stocks.branch_id', $branch_id['value'])
                ->where('stocks.quantity', '>', 0)
                ->where('products2.type', 'sparepart')
                ->select(
                    'stocks.*',
                    'products2.name',
                    'products2.model',
                    'products2.price',
                    'products2.type',
                    'products2.additional_price',
                    DB::raw('(products2.additional_price + products2.price) AS netto_price')
                )
                ->get();  
        }
        $products = $products->map(function ($stock) {
            $stock->name = $stock->name.' - '.number_format($stock->netto_price). ' - Stock : '.$stock->quantity ;
            $stock->id = $stock->product_id;
            return $stock;
        });
        return $products;
    }

    public function getProductStockTransaction()
    {
        $search_term = request()->input('q');   
        $form = collect(request()->input('form'));
        $member_id = $form->where('name', 'member_id')->first();
        $branch_id = $form->where('name', 'branch_id')->first();
        if(!$member_id || !$branch_id){
            return response()->json([]);
        }

        if($search_term){
            $products = Stock::
                leftJoin(
                    DB::raw('(
                        SELECT `products`.*, `branch_products2`.`additional_price` AS `additional_price`
                        FROM `products`
                        LEFT JOIN (
                            SELECT * FROM branch_products WHERE `branch_id` = '.$branch_id['value'].'
                        ) AS `branch_products2` 
                        ON `products`.`id` = `branch_products2`.`product_id`
                    ) AS `products2`' ),
                    function($join) {
                        $join->on('products2.id', '=', 'stocks.product_id');
                    }
                )
                ->where('stocks.branch_id', $branch_id['value'])
                ->where('stocks.quantity', '>', 0)
                ->where('name', 'like', '%'.$search_term.'%')
                ->orWhere('model', 'like', '%'.$search_term.'%')
                ->select(
                    'stocks.*',
                    'products2.name',
                    'products2.model',
                    'products2.price',
                    'products2.type',
                    'products2.additional_price',
                    DB::raw('(products2.additional_price + products2.price) AS netto_price')
                )
                ->get();
        }else{
            $products = Stock::
                leftJoin(
                    DB::raw('(
                        SELECT `products`.*, `branch_products2`.`additional_price` AS `additional_price`
                        FROM `products`
                        LEFT JOIN (
                            SELECT * FROM branch_products WHERE `branch_id` = '.$branch_id['value'].'
                        ) AS `branch_products2` 
                        ON `products`.`id` = `branch_products2`.`product_id`
                    ) AS `products2`' ),
                    function($join) {
                        $join->on('products2.id', '=', 'stocks.product_id');
                    }
                )
                ->where('stocks.branch_id', $branch_id['value'])
                ->where('stocks.quantity', '>', 0)
                ->select(
                    'stocks.*',
                    'products2.name',
                    'products2.model',
                    'products2.price',
                    'products2.type',
                    'products2.additional_price',
                    DB::raw('(products2.additional_price + products2.price) AS netto_price')
                )
                ->get();
        }

        $products->map(function ($stock) {
            if ($stock->type == 'sparepart'){
                $stock->name = $stock->name.' - '.number_format($stock->price). ' - Stock : '.$stock->quantity;
            } else {
                $stock->name = $stock->name.' - '.$stock->model. ' - '.number_format($stock->price). ' - Stock : '.$stock->quantity;
            }
            $stock->id = $stock->product_id;
            return $stock;
        });

        return $products;
    }
}