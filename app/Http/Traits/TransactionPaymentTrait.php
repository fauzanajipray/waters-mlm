<?php
namespace App\Http\Traits;

use App\Models\BonusHistory;
use App\Models\Branch;
use App\Models\Configuration;
use App\Models\LevelSnapshot;
use App\Models\Member;
use App\Models\TransactionProduct;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Prologue\Alerts\Facades\Alert;

trait TransactionPaymentTrait {

    private function levelNow($lastPaymentDate, $level_id)
    {
        return LevelSnapshot::
            join('levels', 'level_snapshots.level_id', '=', 'levels.id')
            ->select('level_snapshots.*', 'levels.code', 'levels.name')
            ->where('level_snapshots.level_id', $level_id)
            ->where('level_snapshots.date_start', '<=', $lastPaymentDate)
            ->orderBy('level_snapshots.date_start', 'desc')
            ->first();
    }
    private function calculateBonus($transaction, $member, $lastPaymentDate ,$log = [])
    {
        $levelNow = $this->levelNow($lastPaymentDate, $member->level_id);
        
        if ($this->isMemberTypePusat($member)) return;

        if ($transaction->type == 'Normal') {
            /* Bonus Penjualan Cabang */
            $memberCabang = Member::where('id', $transaction->member_id)->first();
            $products = TransactionProduct::
                leftJoin('products', 'products.id', '=', 'transaction_products.product_id')
                ->leftJoin(DB::raw('(SELECT * FROM branch_products WHERE branch_id = ' . $transaction->branch_id . ') as branch_products'),
                    'branch_products.product_id', '=', 'products.id')
                ->where('transaction_id', $transaction->id)->get();
            $branch = Branch::where('id', $transaction->branch_id)->first();
            $branchesWithOwner = Branch::with('member')->get()->where('member', $memberCabang)->first();
            if($branchesWithOwner) {
                $config = Configuration::where('key', 'bonus_stokist_percentage_for_product')->first();
                $bonusPercent = (Double) $config->value; // 2.0975%
                $config = Configuration::where('key', 'bonus_cabang_percentage_for_product')->first();
                $bonusPercentCabang = (Double) $config->value; // 4.195%

                foreach ($products as $p) {
                    if($memberCabang->id == 1) continue;
                    for($i=0; $i<$p->quantity; $i++){
                        if($branchesWithOwner->type == 'CABANG'){
                            if($transaction->branch_id == $branchesWithOwner->id) continue; // Jika owner cabang membeli produk di cabang?
                            // ? Jika owner cabang membeli produk di pusat
                            $bonus = BonusHistory::create([
                                'member_id' => $memberCabang->id,
                                'member_numb' => $memberCabang->member_numb,
                                'transaction_id' => $transaction['id'],
                                'level_id' => $memberCabang->level_id,
                                'bonus_type' => "KC",
                                'bonus_percent' => $bonusPercentCabang,
                                'bonus' => ceil(($p->price + $p->additional_price) * $bonusPercentCabang / 100 /1000) * 1000,
                                'bonus_from' => $transaction->branch_id,
                                'created_at' => $lastPaymentDate,
                                'updated_at' => $lastPaymentDate,
                                'product_id' => $p->product_id,
                                'kc_type' => 'LANGSUNG',
                            ]);
                            $log[] = $memberCabang->name . " mendapatkan Bonus Cabang sebesar Rp. " . number_format($bonus->bonus, 0, ',', '.');
                        }
                        else if ($branchesWithOwner->type == 'STOKIST') {
                            if($branch->type == 'CABANG'){
                                // ? Jika owner stokist membeli produk di cabang
                                $bonus = BonusHistory::create([
                                    'member_id' => $memberCabang->id,
                                    'member_numb' => $memberCabang->member_numb,
                                    'transaction_id' => $transaction['id'],
                                    'level_id' => $memberCabang->level_id,
                                    'bonus_type' => "KS",
                                    'bonus_percent' => $bonusPercent,
                                    'bonus' => ceil(($p->price + $p->additional_price) * $bonusPercent / 100 /1000) * 1000,
                                    'bonus_from' => $transaction->branch_id,
                                    'created_at' => $lastPaymentDate,
                                    'updated_at' => $lastPaymentDate,
                                    'product_id' => $p->product_id,
                                ]);
                                $log[] = $memberCabang->name . " mendapatkan Bonus Stokist sebesar Rp. " . number_format($bonus->bonus, 0, ',', '.');
                            }
                        }
                    }
                }
            }
            /* End Bonus Penjualan Cabang */

            /* Bonus Penjualan Pribadi */
            if ($this->isActiveMember2($member)) {
                $bonus = BonusHistory::create([
                    'member_id' => $member->id,
                    'member_numb' => $member->member_numb,
                    'transaction_id' => $transaction['id'],
                    'level_id' => $member->level_id,
                    'bonus_type' => "BP",
                    'bonus_percent' => $levelNow->bp_percentage,
                    'bonus' => $transaction['total_price'] * $levelNow->bp_percentage / 100,
                    'bonus_from' => $transaction->branch_id,
                    'created_at' => $lastPaymentDate,
                    'updated_at' => $lastPaymentDate,
                ]);
                $log[] = $member->name . " mendapatkan Bonus Penjualan sebesar Rp. " . number_format($bonus->bonus, 0, ',', '.');
            }
            /* End Bonus Penjualan Pribadi */

            $upline = $member->upline;
            if ($upline) {
                /* Bonus Goldmine */
                $uplineLevel = $this->levelNow($lastPaymentDate, $upline->level_id);
                if ($uplineLevel->gm_percentage > 0 && $this->isActiveMember2($upline) && !$this->isMemberTypePusat($upline)) {
                    if ($upline->free_pass_or_gm) { // Mempunyai Free Pass OR/GM
                        $bonus = BonusHistory::create([
                            'member_id' => $upline->id,
                            'member_numb' => $upline->member_numb,
                            'transaction_id' => $transaction['id'],
                            'level_id' => $upline->level_id,
                            'bonus_type' => "GM",
                            'bonus_percent' => $uplineLevel->gm_percentage,
                            'bonus' => $transaction['total_price'] * $uplineLevel->gm_percentage / 100,
                            'bonus_from' => $transaction->branch_id,
                            'created_at' => $lastPaymentDate,
                            'updated_at' => $lastPaymentDate,
                        ]);
                        $log[] = $upline->name . " mendapatkan Bonus Goldmine sebesar Rp. " . number_format($bonus->bonus, 0, ',', '.');
                    } else {
                        $productSold = TransactionProduct::with('transaction')->whereHas('transaction', function($query) use ($upline) {
                            $query->where('member_id', $upline->id);
                        })->sum('quantity');
                        if ($productSold >= $levelNow->minimum_sold) { // Cek apakah pernah melakukan transaksi
                            $bonus = BonusHistory::create([
                                'member_id' => $upline->id,
                                'member_numb' => $upline->member_numb,
                                'transaction_id' => $transaction['id'],
                                'level_id' => $upline->level_id,
                                'bonus_type' => "GM",
                                'bonus_percent' => $uplineLevel->gm_percentage,
                                'bonus' => $transaction['total_price'] * $uplineLevel->gm_percentage / 100,
                                'bonus_from' => $transaction->branch_id,
                                'created_at' => $lastPaymentDate,
                                'updated_at' => $lastPaymentDate,
                            ]);
                            $log[] = $upline->name . " mendapatkan Bonus Goldmine sebesar Rp. " . number_format($bonus->bonus, 0, ',', '.');
                        }
                    }
                }
                /* End Bonus Goldmine */

                /* Bonus Overriding */
                $upline2 = $upline->upline ?? null;
                if ($upline2) {
                    $upline2Level = $this->levelNow($lastPaymentDate, $upline2->level_id);
                    if($upline2Level->or_percentage > 0 && $this->isActiveMember2($upline2) && !$this->isMemberTypePusat($upline2)) {
                        if ($upline2->free_pass_or_gm) {
                            // Mempunyai Free Pass OR/GM
                            $bonus = BonusHistory::create([
                                'member_id' => $upline2->id,
                                'member_numb' => $upline2->member_numb,
                                'transaction_id' => $transaction['id'],
                                'level_id' => $upline2->level_id,
                                'bonus_type' => "OR",
                                'bonus_percent' => $upline2Level->or_percentage,
                                'bonus' => $transaction['total_price'] * $upline2Level->or_percentage / 100,
                                'bonus_from' => $transaction->branch_id,
                                'created_at' => $lastPaymentDate,
                                'updated_at' => $lastPaymentDate,
                            ]);
                            $log[] = $upline2->name . " mendapatkan Bonus Overriding sebesar Rp. " . number_format($bonus->bonus, 0, ',', '.');
                        } else {
                            $uplineProductSold = TransactionProduct::with('transaction')->whereHas('transaction', function($query) use ($upline2) {
                                $query->where('member_id', $upline2->id);
                            })->sum('quantity');
                            if ($uplineProductSold >= $upline2Level->minimum_sold) { // Cek apakah pernah melakukan transaksi
                                $bonus = BonusHistory::create([
                                    'member_id' => $upline2->id,
                                    'member_numb' => $upline2->member_numb,
                                    'transaction_id' => $transaction['id'],
                                    'level_id' => $upline2->level_id,
                                    'bonus_type' => "OR",
                                    'bonus_percent' => $upline2Level->or_percentage,
                                    'bonus' => $transaction['total_price'] * $upline2Level->or_percentage / 100,
                                    'bonus_from' => $transaction->branch_id,
                                    'created_at' => $lastPaymentDate,
                                    'updated_at' => $lastPaymentDate,
                                ]);
                                $log[] = $upline2->name . " mendapatkan Bonus Overriding sebesar Rp. " . number_format($bonus->bonus, 0, ',', '.');
                            }
                        }
                    }
                    /* Bonus Bonus Overriding2 */
                    $dateStartFeb = "2023-02-01";
                    $dateStartFeb = Carbon::parse($dateStartFeb);
                    $paymentDate = Carbon::parse($lastPaymentDate);

                    if($dateStartFeb->lte($paymentDate)) { // gte (>=)
                        $upline = $upline2->upline;
                        if($upline){
                            $log = $this->bonusOverriding2($transaction, $upline, $lastPaymentDate, 1, $log);
                        }
                        if($log) {
                            foreach($log as $l) { Alert::info($l)->flash();  }
                            return $log;
                        }
                    }
                    /* End Bonus Bonus Overriding2 */
                }
                /* End Bonus Overriding */
            }
        }
        /* Bonus Penjualan Sparepart */
        else if ($transaction->type == 'Sparepart') {
            $member = Member::where('id', $transaction->member_id)->first();
            $products = TransactionProduct::
                leftJoin('products', 'products.id', '=', 'transaction_products.product_id')
                ->leftJoin(DB::raw('(SELECT * FROM branch_products WHERE branch_id = ' . $transaction->branch_id . ') as branch_products'),
                    'branch_products.product_id', '=', 'products.id')
                ->where('transaction_id', $transaction->id)->get();
            foreach ($products as $p) {
                $branchMember = Branch::with('member')->where('id', $member->branch_id)->first();
                $isMemberOwner = $branchMember->member->id == $member->id;
                $branch = Branch::where('id', $transaction->branch_id)->first();
                if($member->id == 1) return;
                if ($transaction->branch_id == 1) {
                    // ? Jika member membeli sparepart di pusat
                    if(!$isMemberOwner) {
                        $bonus = BonusHistory::create([
                            'member_id' => $member->id,
                            'member_numb' => $member->member_numb,
                            'transaction_id' => $transaction['id'],
                            'level_id' => $member->level_id,
                            'bonus_type' => "SS",
                            'bonus_percent' => 10, // TODO : Tanyain percentnya
                            'bonus' => ($p->price + $p->additional_price) * $p->quantity * 10 / 100,
                            'ss_type' => 'MEMBER',
                            'product_id' => $p->product_id,
                            'bonus_from' => $transaction->branch_id,
                            'created_at' => $lastPaymentDate,
                            'updated_at' => $lastPaymentDate,
                        ]);
                    }
                    // ? Jika owner cabang,stokist membeli sparepart di pusat
                    else {
                        $bonus = BonusHistory::create([
                            'member_id' => $member->id,
                            'member_numb' => $member->member_numb,
                            'transaction_id' => $transaction['id'],
                            'level_id' => $member->level_id,
                            'bonus_type' => "SS",
                            'bonus_percent' => 20, // TODO : Tanyain percentnya
                            'bonus' => ($p->price + $p->additional_price) * $p->quantity * 20 / 100,
                            'ss_type' => 'MEMBER',
                            'product_id' => $p->product_id,
                            'bonus_from' => $transaction->branch_id,
                            'created_at' => $lastPaymentDate,
                            'updated_at' => $lastPaymentDate,
                        ]);
                    }
                    $log[] = $member->name . " mendapatkan Bonus Penjualan Sparepart sebesar Rp. " . number_format($bonus->bonus, 0, ',', '.');
                } else {
                    if (!$isMemberOwner) {
                        // ? Jika member membeli dari cabang
                        if ($branch->type == 'CABANG') {
                            $member = Member::with('branch')->where('id', $transaction->member_id)->first();
                            $bonus = BonusHistory::create([
                                'member_id' => $member->id,
                                'member_numb' => $member->member_numb,
                                'transaction_id' => $transaction['id'],
                                'level_id' => $member->level_id,
                                'bonus_type' => "SS",
                                'bonus_percent' => 10,
                                'bonus' => ($p->price + $p->additional_price) * 10 / 100 * $p->quantity,
                                'ss_type' => 'MEMBER',
                                'bonus_from' => $transaction->branch_id,
                                'created_at' => $lastPaymentDate,
                                'updated_at' => $lastPaymentDate,
                            ]);
                            $log[] = $member->name . " mendapatkan Bonus Penjualan Sparepart sebesar Rp. " . number_format($bonus->bonus, 0, ',', '.');
                        }
                        // ? Jika member membeli dari stokist
                        else if ($branch->type == 'STOKIST') {
                            $member = Member::with('branch')->where('id', $transaction->member_id)->first();
                            $bonus = BonusHistory::create([
                                'member_id' => $member->id,
                                'member_numb' => $member->member_numb,
                                'transaction_id' => $transaction['id'],
                                'level_id' => $member->level_id,
                                'bonus_type' => "SS",
                                'bonus_percent' => 10,
                                'bonus' => ($p->price + $p->additional_price) * 10 / 100 * $p->quantity,
                                'ss_type' => 'MEMBER',
                                'bonus_from' => $transaction->branch_id,
                                'created_at' => $lastPaymentDate,
                                'updated_at' => $lastPaymentDate,
                            ]);
                            $log[] = $member->name . " mendapatkan Bonus Penjualan Sparepart sebesar Rp. " . number_format($bonus->bonus, 0, ',', '.');
                        }
                    } else {
                        // ? Jika owner stokist membeli sparepart di cabang
                        if ($branchMember->type == 'STOKIST') {
                            $bonus = BonusHistory::create([
                                'member_id' => $member->id,
                                'member_numb' => $member->member_numb,
                                'transaction_id' => $transaction['id'],
                                'level_id' => $member->level_id,
                                'bonus_type' => "SS",
                                'bonus_percent' => 15,
                                'bonus' => ($p->price + $p->additional_price) * 15 / 100 * $p->quantity,
                                'ss_type' => 'MEMBER',
                                'bonus_from' => $transaction->member->branch->id,
                                'product_id' => $p->product_id,
                                'created_at' => $lastPaymentDate,
                                'updated_at' => $lastPaymentDate,
                            ]);
                            $log[] = $member->name . " mendapatkan Bonus Penjualan Sparepart sebesar Rp. " . number_format($bonus->bonus, 0, ',', '.');
                        }
                    }
                }
            }
        }
        /* Bonus Penjualan Stock */
        else if ($transaction->type = 'Stock') {
            $product = TransactionProduct::
                leftJoin('products', 'products.id', '=', 'transaction_products.product_id')
                ->leftJoin(DB::raw('(SELECT * FROM branch_products WHERE branch_id = ' . $transaction->branch_id . ') as branch_products'),
                    'branch_products.product_id', '=', 'products.id')
                ->where('transaction_id', $transaction->id)->get();
            foreach ($product as $p) {
                for($i=0; $i<$p->quantity; $i++){
                    if($p->type == 'sparepart') {
                        $member = Member::with('branch')->where('id', $transaction->member_id)->first();
                        // ? Jika menambah stok sparepart di cabang
                        if($member->branch->type == 'CABANG'){
                            $bonus = BonusHistory::create([
                                'member_id' => $member->id,
                                'member_numb' => $member->member_numb,
                                'transaction_id' => $transaction['id'],
                                'level_id' => $member->level_id,
                                'bonus_type' => "SS",
                                'bonus_percent' => 20,
                                'bonus' => ($p->price + $p->additional_price) * 20 / 100,
                                'ss_type' => 'CABANG',
                                'product_id' => $p->product_id,
                                'created_at' => $lastPaymentDate,
                                'updated_at' => $lastPaymentDate,
                            ]);
                            $log[] = 'Bonus Komisi Sparepart Cabang member ' . $member->member_numb . " ditambahkan sebesar Rp. " . number_format($bonus->bonus, 0, ',', '.');
                        }
                        // ? Jika menambah stok sparepart di stokist
                        else if ($member->branch->type == 'STOKIST') {
                            $branch = Branch::with('member')->where('id', $transaction->branch_id)->first();
                            $bonus = BonusHistory::create([ // Harusnya didalam if tapi karena ada fitur adjustment stock dikeluarkan dari if
                                    'member_id' => $member->id,
                                    'member_numb' => $member->member_numb,
                                    'transaction_id' => $transaction['id'],
                                    'level_id' => $member->level_id,
                                    'bonus_type' => "SS",
                                    'bonus_percent' => 15,
                                    'bonus' => ($p->price + $p->additional_price) * 15 / 100,
                                    'created_at' => $lastPaymentDate,
                                    'updated_at' => $lastPaymentDate,
                                    'ss_type' => 'STOKIST',
                                    'bonus_from' => $branch->id,
                                    'product_id' => $p->product_id,
                                ]);
                            $log[] = 'Bonus Komisi Sparepart Stokist member ' . $member->member_numb . " ditambahkan sebesar Rp. " .
                                number_format($bonus->bonus, 0, ',', '.');
                        }
                    } else {
                        $member = Member::with('branch')->where('id', $transaction->member_id)->first();
                        // ? Komisi jika menambah produk di cabang
                        if($member->branch->type == 'CABANG'){
                            $configBonusCabang = Configuration::where('key', 'bonus_cabang_percentage_for_product')->first();
                            $bonusPercent = (Double) $configBonusCabang->value; // 4.195%
                            $bonus = BonusHistory::create([
                                'member_id' => $member->id,
                                'member_numb' => $member->member_numb,
                                'transaction_id' => $transaction['id'],
                                'level_id' => $member->level_id,
                                'bonus_type' => "KC",
                                'bonus_percent' => $bonusPercent,
                                'bonus' => ceil(($p->price + $p->additional_price) * $bonusPercent / 100 /1000)* 1000,
                                'created_at' => $lastPaymentDate,
                                'updated_at' => $lastPaymentDate,
                                'product_id' => $p->product_id,
                                'kc_type' => 'STOCK',
                                'bonus_from' => $transaction->branch_id,
                            ]);
                            $log[] = 'Bonus Komisi Cabang member ' . $member->member_numb . " ditambahkan sebesar Rp. " . number_format($bonus->bonus, 0, ',', '.');
                        }
                        // ? Komisi jika menambahkan produk di stokist
                        else if ($member->branch->type == 'STOKIST') {
                            $config = Configuration::where('key', 'bonus_stokist_percentage_for_product')->first();
                            $bonusPercent = (Double) $config->value; // 2.0975%
                            $branch = Branch::with('member')->where('id', $transaction->branch_id)->first();
                            $bonus = BonusHistory::create([
                                'member_id' => $member->id,
                                'member_numb' => $member->member_numb,
                                'transaction_id' => $transaction['id'],
                                'level_id' => $member->level_id,
                                'bonus_type' => "KS",
                                'bonus_percent' => $bonusPercent,
                                'bonus' => ceil(($p->price + $p->additional_price) * $bonusPercent / 100 /1000) * 1000,
                                'bonus_from' => $branch->id,
                                'created_at' => $lastPaymentDate,
                                'updated_at' => $lastPaymentDate,
                            ]);
                            $log[] = 'Bonus Komisi Stokist member ' . $member->member_numb . " ditambahkan sebesar Rp. " . number_format($bonus->bonus, 0, ',', '.');
                        } else {
                            $log[] = 'Bonus Komisi Stokist member ' . $member->member_numb . " tidak ditambahkan karena tidak ada cabang";
                        }
                    }
                }
            }
        }
        if($log) {
            foreach($log as $l) {
                Alert::info($l)->flash();
            }
            return $log;
        }
    }

    private function isActiveMember2($member)
    {
        if ($member->expired_at < Carbon::now()) {
            return false;
        }
        return true;
    }

    private function isMemberTypePusat($member)
    {
        if ($member->type == 'PUSAT') {
            return true;
        }
        return false;
    }

    private function bonusOverriding2($transaction, $member, $lastPaymentDate, $totalOR, $log)
    {
        $levelNow = $this->levelNow($lastPaymentDate, $member->level_id);
        if ($this->isMemberTypePusat($member)){
            return $log;
        }
        if ($totalOR > 5) {
            return $log;
        }
        if ($this->isActiveMember2($member) && $levelNow->or2_percentage > 0 && !$this->isMemberTypePusat($member)) {
            if ($member->free_pass_or_gm) {
                $bonus = BonusHistory::create([
                    'member_id' => $member->id,
                    'member_numb' => $member->member_numb,
                    'transaction_id' => $transaction['id'],
                    'level_id' => $member->level_id,
                    'bonus_type' => "OR2",
                    'bonus_percent' => $levelNow->or2_percentage,
                    'bonus' => $transaction['total_price'] * $levelNow->or2_percentage / 100,
                    'bonus_from' => $transaction['branch_id'],
                    'created_at' => $lastPaymentDate,
                    'updated_at' => $lastPaymentDate,
                ]);
                $log[] = $member->name . " mendapatkan Bonus OR 2 sebesar Rp. " . number_format($bonus->bonus, 0, ',', '.');
            } else {
                $productSold = TransactionProduct::with('transaction')->whereHas('transaction', function($query) use ($member) {
                    $query->where('member_id', $member->id);
                })->sum('quantity');
                if ($productSold >= $levelNow->minimum_sold) {
                    $bonus = BonusHistory::create([
                        'member_id' => $member->id,
                        'member_numb' => $member->member_numb,
                        'transaction_id' => $transaction['id'],
                        'level_id' => $member->level_id,
                        'bonus_type' => "OR2",
                        'bonus_percent' => $levelNow->or2_percentage,
                        'bonus' => $transaction['total_price'] * $levelNow->or2_percentage / 100,
                        'bonus_from' => $transaction['branch_id'],
                        'created_at' => $lastPaymentDate,
                        'updated_at' => $lastPaymentDate,
                    ]);
                    $log[] = $member->name . " mendapatkan Bonus OR 2 sebesar Rp. " . number_format($bonus->bonus, 0, ',', '.');
                }
            }
        }
        $member = $member->upline;
        if ($member) {
            $totalOR++;
            $log = $this->bonusOverriding2($transaction, $member, $lastPaymentDate, $totalOR, $log);
        }
        return $log;
    }

}
