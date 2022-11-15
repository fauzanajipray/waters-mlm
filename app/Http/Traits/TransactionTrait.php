<?php
namespace App\Http\Traits;

use App\Models\BonusHistory;
use App\Models\Level;
use App\Models\LevelUpHistories;
use App\Models\LogProductSold;
use App\Models\Member;
use App\Models\Transaction;
use Carbon\Carbon;
use Prologue\Alerts\Facades\Alert;

trait TransactionTrait {
    protected function generateCode() 
    {
        $lastTransaction = Transaction::withTrashed()->orderBy('id', 'desc')->first();
        $lastTransactionCode = $lastTransaction->code ?? 'TRX-000000-0000';
        $transactionCode = explode('-', $lastTransactionCode)[2] + 1;
        $transactionCode = 'TRX-' . date('ymd') . '-' . str_pad($transactionCode, 4, '0', STR_PAD_LEFT);
        return $transactionCode;
    }

    private function calculateBonus($requests, $member)
    {
        /* Bonus Penjualan Pribadi */
        $levelNow = Level::where('id', $member->level_id)->first();
        if ($this->isActiveMember($member)) {
            BonusHistory::create([
                'member_id' => $member->id,
                'member_numb' => $member->member_numb,
                'transaction_id' => $requests['transaction_id'],
                'level_id' => $member->level_id,
                'bonus_type' => "BP",
                'bonus_percent' => $levelNow->bp_percentage,
                'bonus' => $requests['total_price'] * $levelNow->bp_percentage / 100,
            ]);
        } 

        /* Bonus Sponsor */
        $upline = $member->upline;
        if ($upline) {
            $uplineProductSold = LogProductSold::where('member_id', $upline->id)->count();
            $uplineLevel = Level::where('id', $upline->level_id)->first();
            if ($uplineProductSold >= $upline->level->minimum_sold && $uplineLevel->bs_percentage > 0 && $this->isActiveMember($upline)) {  // Cek apakah pernah melakukan transaksi
                // TODO : Tanya Minimal transaksi atau jual produk
                BonusHistory::create([
                    'member_id' => $upline->id,
                    'member_numb' => $upline->member_numb,
                    'transaction_id' => $requests['transaction_id'],
                    'level_id' => $upline->level_id,
                    'bonus_type' => "BS",
                    'bonus_percent' => $uplineLevel->bs_percentage,
                    'bonus' => $requests['total_price'] * $uplineLevel->bs_percentage / 100,
                ]);
            }
            /* Bonus Overriding */
            $upline2 = $upline->upline ?? null;
            if ($upline2) {
                // Cek apakah pernah melakukan transaksi
                $uplineProductSold = LogProductSold::where('member_id', $upline2->id)->count();
                $upline2Level = Level::where('id', $upline2->level_id)->first();
                if($uplineProductSold >= $upline2->level->minimum_sold && $upline2Level->or_percentage > 0 && $this->isActiveMember($upline2)) {
                    BonusHistory::create([
                        'member_id' => $upline2->id,
                        'member_numb' => $upline2->member_numb,
                        'transaction_id' => $requests['transaction_id'],
                        'level_id' => $upline2->level_id,
                        'bonus_type' => "OR",
                        'bonus_percent' => $upline2Level->or_percentage,
                        'bonus' => $requests['total_price'] * $upline2Level->or_percentage / 100,
                    ]);
                } 
            }
        }
    }

    protected function levelUpMember($id, $isCheckAgain = false, $historyLevelUp = []) 
    {
        /* Logic kenaikan level member */
        $member = Member::with(['upline' => function($query) {
            $query->with([
                'upline' => function($query) { $query->with('level'); }, 
                'downlines' => function($query) { $query->with(['level', 'logProductSold']); },
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
            // dd($historyLevelUp);
        }
    }

    private function getDownline($uplineID) 
    {
        $downline = Member::with(['logProductSold' => function($query) {
            $query->WhereMonth('transaction_date', date('m'))
                ->whereYear('transaction_date', date('Y'));
        }])->where('upline_id', $uplineID)
        ->get();
        return $downline;
    }

    private function removeDownlineWhereTransaction($downline, $minimumSoldByDownlineNext, $uplineLevel){
        foreach ($downline as $key => $value) {
            $downlineSold = count($value->logProductSold);
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
}
