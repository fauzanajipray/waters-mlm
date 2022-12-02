<?php
namespace App\Http\Traits;

use App\Models\BonusHistory;
use App\Models\Customer;
use App\Models\Level;
use App\Models\LevelUpHistories;
use App\Models\Member;
use App\Models\Transaction;
use App\Models\TransactionProduct;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Prologue\Alerts\Facades\Alert;
use Nasution\Terbilang;

trait TransactionPaymentTrait {

    private function calculateBonus($transaction, $member, $lastPaymentDate ,$log = [])
    {
        $levelNow = Level::where('id', $member->level_id)->first();
        if ($this->isMemberTypePusat($member)){
            return;
        } 
        if ($transaction->type != 'Normal'){
            return;
        } else {
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
                // dd($uplineProductSold, $upline->toArray(), $member->toArray(), $uplineLevel->toArray());
                if ($uplineProductSold >= $upline->level->minimum_sold && $uplineLevel->gm_percentage > 0 && $this->isActiveMember($upline)) {  // Cek apakah pernah melakukan transaksi
                    // TODO : Tanya Minimal transaksi atau jual produk
                    $bonus = BonusHistory::create([
                        'member_id' => $upline->id,
                        'member_numb' => $upline->member_numb,
                        'transaction_id' => $transaction['id'],
                        'level_id' => $upline->level_id,
                        'bonus_type' => "GM",
                        'bonus_percent' => $uplineLevel->gm_percentage,
                        'bonus' => $transaction['total_price'] * $uplineLevel->gm_percentage / 100,
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
                        ]);
    
                        $log[] = $upline2->name . " mendapatkan Bonus Overriding sebesar Rp. " . number_format($bonus->bonus, 0, ',', '.');
                    } 
                }
            }
        }

        if($log) {
            foreach($log as $l) {
                Alert::info($l)->flash();
            }
        }
    }

    protected function levelUpMember($id, $isCheckAgain = false, $historyLevelUp = []) 
    {
        
        $this->info('Check Level Up Member ID : ' . $id);
        /* Logic kenaikan level member */
        $member = Member::with(['upline' => function($query) {
            $query->with([
                'upline' => function($query) { $query->with('level'); }, 
                'downlines' => function($query) { 
                    $query->with(['level']); 
                },
                'level'
            ]);
        }, 'level'])->find($id);
        $uplineMember = $member->upline;
        if ($isCheckAgain){ $uplineMember = $member; }
        if (!$uplineMember) return ;
        $uplineLevel = Level::where('id', $uplineMember->level_id)->first();
        $levelNext =  Level::where('ordering_level', $uplineLevel->ordering_level + 1)->first();
        if(!$levelNext) return ;
        $levels = Level::orderBy('ordering_level', 'asc')->get();
        $minimumDownlineNext = $levels[$uplineLevel->ordering_level]->minimum_downline;
        $minimumSoldByDownlineNext = $levels[$uplineLevel->ordering_level]->minimum_sold_by_downline;
        $downline = $this->getDownline($uplineMember->id, $uplineLevel);
        if(!$downline){ return ; }
        
        $removeDownline = $this->removeDownlineWhereTransaction($downline, $minimumSoldByDownlineNext, $uplineLevel);
        $downline = $removeDownline['downline'];
        $downlineCountLevelNow = $removeDownline['downlineCountLevelNow'];

        if ($downlineCountLevelNow >= $minimumDownlineNext) {
            if ($this->isActiveMember($uplineMember)){
                $uplineMember = Member::find($uplineMember->id);
                $uplineMember->level_id = $levelNext->id; // Naik Level
                $uplineMember->update();
                $levelHistory = LevelUpHistories::with('level')->create([
                    'member_id' => $uplineMember->id,
                    'old_level_id' => $uplineLevel->id,
                    'new_level_id' => $uplineMember->level_id,
                    'old_level_code' => $uplineLevel->code,
                    'new_level_code' => $uplineMember->level->code,
                ]);
                Alert::info('Member '.$uplineMember->name.' level up to '.$uplineMember->level->name)->flash();
                $historyLevelUp[] = 'Member '.$uplineMember->name.' level up to '.$uplineMember->level->name; 
    
                /* check apakah bisa level up lagi */
                $levelNow = Level::where('id', $levelHistory->new_level_id)->first();
                $levelNext = Level::where('ordering_level', $levelNow->ordering_level + 1)->first();
                if(!$levelNext) { return ; }

                $minimumDownlineNext = $levelNext->minimum_downline;
                $minimumSoldByDownlineNext = $levelNext->minimum_sold_by_downline;
                $removedDownline = $this->removeDownlineWhereTransaction($downline, $minimumSoldByDownlineNext, $levelNow);
                $downline = $removedDownline['downline'];
                $downlineCountLevelNow = $removedDownline['downlineCountLevelNow'];
                if ($downlineCountLevelNow >= $minimumDownlineNext) {
                    $this->levelUpMember($uplineMember->id, true, $historyLevelUp);
                }
            } 
            else {
                // For Testing purpose
                // Alert::error('Member '.$uplineMember->name.' is not active')->flash();
                // $historyLevelUp[] = 'Member '.$uplineMember->name.' tidak bisa level up karena tidak aktif';
            }
            $this->levelUpMember($uplineMember->id, false, $historyLevelUp);
        }
        if($historyLevelUp) {
            foreach($historyLevelUp as $l) {
                $this->info($l);
            }
        }
    }

    private function getDownline($uplineID) 
    {
        $downline = Member::with(['transactions' => function($query) {
            $query
                ->with('transactionProducts')
                ->WhereMonth('transaction_date', date('m'))
                ->whereYear('transaction_date', date('Y'));
        }])->where('upline_id', $uplineID)
        ->get();
        return $downline;
    }

    private function removeDownlineWhereTransaction($downline, $minimumSoldByDownlineNext, $uplineLevel){
        foreach ($downline as $key => $value) {
            $downlineSold = 0;
            foreach ($value->transactions as $key => $transaction) {
                if($transaction->type != 'Normal') continue;
                foreach ($transaction->transactionProducts as $key => $transactionProduct) {
                    $downlineSold += $transactionProduct->quantity;
                }
            }
            if ($downlineSold < $minimumSoldByDownlineNext) { 
                $downline->forget($key); // remove downline yang belum melakukan transaksi
            }
        }
        $downlineCountLevelNow = 0;
        foreach ($downline as $key => $value) {
            if ($value->level->ordering_level >= $uplineLevel->ordering_level) {
                $downlineCountLevelNow++; // hitung downline yang sudah melakukan transaksi dan levelnya sama atau lebih tinggi
            }
        }
        return [ 
            "downline" => $downline, 
            "downlineCountLevelNow" => $downlineCountLevelNow,
        ];
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
