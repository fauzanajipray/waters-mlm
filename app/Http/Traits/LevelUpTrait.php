<?php

namespace App\Http\Traits;

use App\Models\Level;
use App\Models\LevelUpHistories;
use App\Models\Member;
use Carbon\Carbon;

trait LevelUpTrait {
  protected function levelUpMember($id, $transactionDate, $isCheckAgain = false, $historyLevelUp = [])
  {
    $this->info(' - Check Level Up Member ID : ' . $id);
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
    $downline = $this->getDownline($uplineMember->id, $transactionDate);
    if(!$downline){ return ; }

    $removeDownline = $this->removeDownlineWhereTransaction($downline, $minimumSoldByDownlineNext, $uplineLevel);
    $downline = $removeDownline['downline'];
    $downlineCountLevelNow = $removeDownline['downlineCountLevelNow'];

    if ($downlineCountLevelNow >= $minimumDownlineNext) {
        if ($this->isActiveMember($uplineMember)){
          if ($this->isAlreadyLevelUpThisMonth($uplineMember->id, $transactionDate)){
              $historyLevelUp[] = 'Member '.$uplineMember->name.' sudah level up bulan ini';
          } else {
            $uplineMember = Member::find($uplineMember->id);
            $uplineMember->level_id = $levelNext->id; // Naik Level
            $uplineMember->update();
            $levelHistory = LevelUpHistories::with('level')->create([
                'member_id' => $uplineMember->id,
                'old_level_id' => $uplineLevel->id,
                'new_level_id' => $uplineMember->level_id,
                'old_level_code' => $uplineLevel->code,
                'new_level_code' => $uplineMember->level->code,
                'created_at' => Carbon::parse($transactionDate)->format('Y-m-d H:i:s'),
                'updated_at' => Carbon::parse($transactionDate)->format('Y-m-d H:i:s'),
            ]);
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
                $this->levelUpMember($uplineMember->id, $transactionDate, true, $historyLevelUp);
            }
          }
        }
        else {
            $historyLevelUp[] = 'Member '.$uplineMember->name.' tidak bisa level up karena tidak aktif';
        }
        $this->levelUpMember($uplineMember->id, $transactionDate, false, $historyLevelUp);
    }
    if($historyLevelUp) {
        foreach($historyLevelUp as $l) {
            $this->info('   '.$l);
        }
    }
  }

  private function isAlreadyLevelUpThisMonth($member_id, $transactionDate) { 
      $date = Carbon::parse($transactionDate)->startOfMonth()->format('Y-m-d H:i:s');
      $dateNext = Carbon::parse($transactionDate)->addMonth()->startOfMonth()->format('Y-m-d H:i:s');
      $levelUpHistory = LevelUpHistories::where('member_id', $member_id)
        ->where('created_at', '>=', $date)
        ->where('created_at', '<', $dateNext)
        ->first();
      return $levelUpHistory ? true : false;
  }

  private function getDownline($uplineID, $transactionDate)
  {
    $date = Carbon::parse($transactionDate)->startOfMonth()->format('Y-m-d H:i:s');
    $dateNext = Carbon::parse($transactionDate)->addMonth()->startOfMonth()->format('Y-m-d H:i:s');
    $member = Member::where('id', $uplineID)->first();
    $level = Level::where('id', $member->level_id)->first();
    
    if (!$level->month_unlimited) {
      $downline = Member::with([
        'transactions' => function($query) use ($date, $dateNext) {
          $query->with('transactionProducts')
            ->join('transaction_payments', 'transactions.id', '=', 'transaction_payments.transaction_id')
            ->where('transaction_payments.payment_date', '>=', $date)
            ->where('transaction_payments.payment_date', '<', $dateNext)
            ->select('transactions.*', 'transaction_payments.payment_date');
        }
      ])->where('upline_id', $uplineID)->get();
    } else {
      $downline = Member::with(['transactions' => function($query) use ($date, $dateNext) {
        $query->with('transactionProducts')
          ->join('transaction_payments', 'transactions.id', '=', 'transaction_payments.transaction_id')
          ->where('transaction_payments.payment_date', '<', $dateNext)
          ->select('transactions.*', 'transaction_payments.payment_date');
      }])->where('upline_id', $uplineID)->get();
    }
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
}
