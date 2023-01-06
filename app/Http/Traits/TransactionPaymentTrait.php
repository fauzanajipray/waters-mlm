<?php
namespace App\Http\Traits;

use App\Models\BonusHistory;
use App\Models\Branch;
use App\Models\Level;
use App\Models\LevelNsi;
use App\Models\Member;
use App\Models\Transaction;
use App\Models\TransactionPayment;
use App\Models\TransactionProduct;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use PhpParser\Node\Expr\Cast\Double;
use Prologue\Alerts\Facades\Alert;;

trait TransactionPaymentTrait {

    private function calculateBonus($transaction, $member, $lastPaymentDate ,$log = [])
    {
        $levelNow = Level::where('id', $member->level_id)->first();
        if ($this->isMemberTypePusat($member)){
            return;
        }
        if ($transaction->type == 'Normal') {
            /* Komisi NSI */
            if ($transaction->nsi) {
                $memberNsi = Member::where('id', $transaction->nsi)->first();
                $transactionPastTotal = Transaction::
                    leftJoin(DB::raw('(
                        SELECT transaction_id,MAX(created_at) as last_payment_date from transaction_payments
                        GROUP BY transaction_id
                    ) as transaction_payments'), 'transactions.id', '=', 'transaction_payments.transaction_id')
                    ->where('status_paid', 1)
                    ->where('nsi', $memberNsi->id)
                    ->whereMonth('transaction_payments.last_payment_date', Carbon::parse($lastPaymentDate)->format('m'))
                    ->whereYear('transaction_payments.last_payment_date', Carbon::parse($lastPaymentDate)->format('Y'))
                    ->select(DB::raw('COUNT(*) as total'))
                    ->first();

                $levelNsiNext = LevelNsi::where('id', $memberNsi->level_nsi_id + 1)->first();

                if ($levelNsiNext) {
                    if ($transactionPastTotal->total >= $levelNsiNext->min_sold) {
                        /* Update level NSI */
                        $memberNsi->level_nsi_id = $memberNsi->level_nsi_id + 1;
                        $memberNsi->save();
                    }
                }
                $levelNsiNow = LevelNsi::where('id', $memberNsi->level_nsi_id)->first();
                $levelNsiNow->bonus_percentage = (Double) str_replace(',', '.', $levelNsiNow->bonus_percentage);
                $bonusPembulatan = ceil(($transaction['total_price'] * $levelNsiNow->bonus_percentage / 100) / 1000) * 1000;
                $bonus = BonusHistory::create([
                    'member_id' => $memberNsi->id,
                    'member_numb' => $memberNsi->member_numb,
                    'transaction_id' => $transaction['id'],
                    'level_id' => $memberNsi->level_id,
                    'bonus_type' => "KN",
                    'bonus_percent' => $levelNsiNow->bonus_percentage,
                    'bonus' => $bonusPembulatan,
                    'created_at' => $lastPaymentDate,
                    'updated_at' => $lastPaymentDate,
                ]);
                $log[] = $memberNsi->name . " mendapatkan Bonus Komisi NSI level ". $levelNsiNow->id ." sebesar Rp. " . number_format($bonus->bonus, 0, ',', '.');
            }

            /* Bonus Penjualan Pribadi */
            if ($this->isActiveMember($member)) {
                $bonus = BonusHistory::create([
                    'member_id' => $member->id,
                    'member_numb' => $member->member_numb,
                    'transaction_id' => $transaction['id'],
                    'level_id' => $member->level_id,
                    'bonus_type' => "BP",
                    'bonus_percent' => $levelNow->bp_percentage,
                    'bonus' => $transaction['total_price'] * $levelNow->bp_percentage / 100,
                    'created_at' => $lastPaymentDate,
                    'updated_at' => $lastPaymentDate,
                ]);
                $log[] = $member->name . " mendapatkan Bonus Penjualan sebesar Rp. " . number_format($bonus->bonus, 0, ',', '.');
            }
            /* Bonus Sponsor */
            $upline = $member->upline;
            if ($upline) {
                $uplineProductSold = TransactionProduct::with('transaction')->whereHas('transaction', function($query) use ($upline) {
                    $query->where('member_id', $upline->id);
                })->sum('quantity');
                $uplineLevel = Level::where('id', $upline->level_id)->first();
                if ($uplineProductSold >= $upline->level->minimum_sold && $uplineLevel->gm_percentage > 0 && $this->isActiveMember($upline)) {  // Cek apakah pernah melakukan transaksi

                    $bonus = BonusHistory::create([
                        'member_id' => $upline->id,
                        'member_numb' => $upline->member_numb,
                        'transaction_id' => $transaction['id'],
                        'level_id' => $upline->level_id,
                        'bonus_type' => "GM",
                        'bonus_percent' => $uplineLevel->gm_percentage,
                        'bonus' => $transaction['total_price'] * $uplineLevel->gm_percentage / 100,
                        'created_at' => $lastPaymentDate,
                        'updated_at' => $lastPaymentDate,
                    ]);
                    $log[] = $upline->name . " mendapatkan Bonus Sponsor sebesar Rp. " . number_format($bonus->bonus, 0, ',', '.');
                }
                /* Bonus Overriding */
                $upline2 = $upline->upline ?? null;
                if ($upline2) {
                    // Cek apakah pernah melakukan transaksi
                    $uplineProductSold = TransactionProduct::with('transaction')->whereHas('transaction', function($query) use ($upline2) {
                        $query->where('member_id', $upline2->id);
                    })->sum('quantity');
                    $upline2Level = Level::where('id', $upline2->level_id)->first();
                    if($uplineProductSold >= $upline2->level->minimum_sold && $upline2Level->or_percentage > 0 && $this->isActiveMember($upline2)) {
                        $bonus = BonusHistory::create([
                            'member_id' => $upline2->id,
                            'member_numb' => $upline2->member_numb,
                            'transaction_id' => $transaction['id'],
                            'level_id' => $upline2->level_id,
                            'bonus_type' => "OR",
                            'bonus_percent' => $upline2Level->or_percentage,
                            'bonus' => $transaction['total_price'] * $upline2Level->or_percentage / 100,
                            'created_at' => $lastPaymentDate,
                            'updated_at' => $lastPaymentDate,
                        ]);

                        $log[] = $upline2->name . " mendapatkan Bonus Overriding sebesar Rp. " . number_format($bonus->bonus, 0, ',', '.');
                    }
                }
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
                    // Jika member membeli sparepart di pusat
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
                            'ss_product_id' => $p->product_id,
                            'created_at' => $lastPaymentDate,
                            'updated_at' => $lastPaymentDate,
                        ]);
                    }
                    // Jika owner cabang,stokist membeli sparepart di pusat
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
                            'ss_product_id' => $p->product_id,
                            'created_at' => $lastPaymentDate,
                            'updated_at' => $lastPaymentDate,
                        ]);
                    }
                    $log[] = $member->name . " mendapatkan Bonus Penjualan Sparepart sebesar Rp. " . number_format($bonus->bonus, 0, ',', '.');
                } else {
                    if (!$isMemberOwner) {
                        // Jika member membeli dari cabang
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
                                'created_at' => $lastPaymentDate,
                                'updated_at' => $lastPaymentDate,
                            ]);
                            $log[] = $member->name . " mendapatkan Bonus Penjualan Sparepart sebesar Rp. " . number_format($bonus->bonus, 0, ',', '.');
                            $bonusHistoryCabang = BonusHistory::
                                where('ss_product_id', $p->product_id)
                                ->where('bonus_type', 'SS')
                                ->where('bonus_percent', 20)
                                ->where('ss_type', 'CABANG')
                                ->orderBy('created_at', 'desc')
                                ->first();
                            if($bonusHistoryCabang) {
                                $bonusHistoryCabang->bonus = $bonusHistoryCabang->bonus * 10 / $bonusHistoryCabang->bonus_percent;
                                $bonusHistoryCabang->bonus_percent = 10;
                                $bonusHistoryCabang->save();
                                $log[] = 'Bonus Penjualan Sparepart Cabang member ' . $bonusHistoryCabang->member_numb . " diperbarui menjadi Rp. " . number_format($bonusHistoryCabang->bonus, 0, ',', '.');
                            } else {
                                $log[] = "Bonus Penjualan Sparepart Cabang tidak ditemukan";
                            }
                        }
                        // Jika member membeli dari stokist
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
                                'created_at' => $lastPaymentDate,
                                'updated_at' => $lastPaymentDate,
                            ]);
                            $log[] = $member->name . " mendapatkan Bonus Penjualan Sparepart sebesar Rp. " . number_format($bonus->bonus, 0, ',', '.');
                            $bonusHistoryStokist = BonusHistory::
                                where('ss_product_id', $p->product_id)
                                ->where('bonus_type', 'SS')
                                ->where('bonus_percent', 15)
                                ->where('ss_type', 'STOKIST')
                                ->orderBy('created_at', 'desc')
                                ->first();
                            if($bonusHistoryStokist) {
                                $bonusHistoryStokist->bonus = $bonusHistoryStokist->bonus * 5 / $bonusHistoryStokist->bonus_percent;
                                $bonusHistoryStokist->bonus_percent = 5;
                                $bonusHistoryStokist->save();
                                $log[] = 'Bonus Penjualan Sparepart Sparepart member ' . $bonusHistoryStokist->member_numb . " diperbarui menjadi Rp. " . number_format($bonusHistoryStokist->bonus, 0, ',', '.');
                            } else {
                                $log[] = "Bonus Penjualan Sparepart Stokist tidak ditemukan";
                            }
                        }
                    } else {
                        // Jika owner stokist membeli sparepart di cabang
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
                                'ss_product_id' => $p->product_id,
                                'created_at' => $lastPaymentDate,
                                'updated_at' => $lastPaymentDate,
                            ]);
                            $log[] = $member->name . " mendapatkan Bonus Penjualan Sparepart sebesar Rp. " . number_format($bonus->bonus, 0, ',', '.');
                            $bonusHistoryCabang = BonusHistory::
                                where('ss_product_id', $p->product_id)
                                ->where('bonus_type', 'SS')
                                ->where('bonus_percent', 20)
                                ->where('ss_type', 'CABANG')
                                ->orderBy('created_at', 'desc')
                                ->first();
                            if($bonusHistoryCabang) {
                                $bonusHistoryCabang->bonus = $bonusHistoryCabang->bonus * 5 / $bonusHistoryCabang->bonus_percent;
                                $bonusHistoryCabang->bonus_percent = 5;
                                $bonusHistoryCabang->save();
                                $log[] = 'Bonus Penjualan Sparepart Cabang member ' . $bonusHistoryCabang->member_numb . " diperbarui menjadi Rp. " . number_format($bonusHistoryCabang->bonus, 0, ',', '.');
                            } else {
                                $log[] = "Bonus Penjualan Sparepart Cabang tidak ditemukan";
                            }
                        }

                        // TODO : Jika owner cabang membeli sparepart di cabang?
                        // TODO : Jika owner stokist membeli sparepart di stokist?

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
                if($p->type == 'sparepart') {
                    $member = Member::with('branch')->where('id', $transaction->member_id)->first();
                    // Jika menambah stok sparepart di cabang
                    if($member->branch->type == 'CABANG'){
                        $bonus = BonusHistory::create([
                            'member_id' => $member->id,
                            'member_numb' => $member->member_numb,
                            'transaction_id' => $transaction['id'],
                            'level_id' => $member->level_id,
                            'bonus_type' => "SS",
                            'bonus_percent' => 20,
                            'bonus' => ($p->price + $p->additional_price) * 20 / 100 * $p->quantity,
                            'ss_type' => 'CABANG',
                            'ss_product_id' => $p->product_id,
                            'created_at' => $lastPaymentDate,
                            'updated_at' => $lastPaymentDate,
                        ]);
                        $log[] = 'Bonus Komisi Sparepart Cabang member ' . $member->member_numb . " ditambahkan sebesar Rp. " . number_format($bonus->bonus, 0, ',', '.');
                    }
                    // Jika menambah stok sparepart di stokist
                    else if ($member->branch->type == 'STOKIST') {
                        $bonusHistoryCabang = BonusHistory::
                            where('ss_product_id', $p->product_id)
                            ->where('bonus_type', 'SS')
                            ->where('bonus_percent', 20)
                            ->where('ss_type', 'CABANG')
                            ->orderBy('created_at', 'desc')
                            ->first();
                        $bonus = BonusHistory::create([ // Harusnya didalam if tapi karena ada fitur adjustment stock dikeluarkan dari if
                                'member_id' => $member->id,
                                'member_numb' => $member->member_numb,
                                'transaction_id' => $transaction['id'],
                                'level_id' => $member->level_id,
                                'bonus_type' => "SS",
                                'bonus_percent' => 15,
                                'bonus' => ($p->price + $p->additional_price) * 15 / 100 * $p->quantity,
                                'created_at' => $lastPaymentDate,
                                'updated_at' => $lastPaymentDate,
                                'ss_type' => 'STOKIST',
                                'ss_product_id' => $p->product_id,
                            ]);
                        $log[] = 'Bonus Komisi Sparepart Stokist member ' . $member->member_numb . " ditambahkan sebesar Rp. " .
                            number_format($bonus->bonus, 0, ',', '.');
                        if($bonusHistoryCabang) {
                            $bonusHistoryCabang->bonus = $bonusHistoryCabang->bonus * 5 / $bonusHistoryCabang->bonus_percent;
                            $bonusHistoryCabang->bonus_percent = 5;
                            $bonusHistoryCabang->save();
                            $log[] = 'Bonus Komisi Sparepart Cabang member ' . $bonusHistoryCabang->member_numb . " diperbarui menjadi Rp. " . number_format($bonusHistoryCabang->bonus, 0, ',', '.');
                        } else {
                            $log[] = "Bonus Komisi Sparepart Cabang tidak ditemukan";
                        }
                    }
                }
            }
        } else {
            return;
        }
        if($log) {
            foreach($log as $l) {
                Alert::info($l)->flash();
            }
        }
    }

    private function isActiveMember($member)
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

}
