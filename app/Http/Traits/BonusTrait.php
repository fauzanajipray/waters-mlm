<?php

namespace App\Http\Traits;

use App\Models\AreaManager;
use App\Models\BonusHistory;
use App\Models\LevelLsi;
use App\Models\LevelNsi;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

trait BonusTrait {
    public function calculateBonusNsi($member, $date)
    {
        $log = [];
        $transactionPast = Transaction::
            leftJoin(DB::raw('(
                SELECT transaction_id,MAX(created_at) as last_payment_date from transaction_payments
                GROUP BY transaction_id
            ) as transaction_payments'), 'transactions.id', '=', 'transaction_payments.transaction_id')
            ->where('status_paid', 1)
            ->where('nsi', $member->id)
            ->whereMonth('transaction_payments.last_payment_date', $date->month)
            ->whereYear('transaction_payments.last_payment_date', $date->year)
            ->get();
        $transactionTotal = $transactionPast->count();
        $level = LevelNsi::where('min_sold', '<=' , $transactionTotal)->orderBy('min_sold', 'Desc')->first();
        if(!$level) return $log;

        foreach ($transactionPast as $key => $transaction) {
            $level->bonus_percentage = (Double) str_replace(',', '.', $level->bonus_percentage);
            $bonusPembulatan = ceil(($transaction['total_price'] * $level->bonus_percentage / 100) / 1000) * 1000;
            $bonus = BonusHistory::create([
                'member_id' => $member->id,
                'member_numb' => $member->member_numb,
                'transaction_id' => $transaction['id'],
                'level_id' => $member->level_id,
                'bonus_type' => "KN",
                'bonus_percent' => $level->bonus_percentage,
                'bonus' => $bonusPembulatan,
                'bonus_from' => 1,
                'created_at' => $date,
                'updated_at' => $date,
            ]);
            $log[] = "   (".$member->member_numb . ") - " . $member->name . " mendapatkan Bonus Komisi NSI sebesar Rp. " . number_format($bonus->bonus, 0, ',', '.');
        }

        return $log;
    }

    public function calculateBonusLsi($date)
    {
        $log = [];
        $transactionPast = Transaction::
            leftJoin(DB::raw('(
                SELECT transaction_id,MAX(created_at) as last_payment_date from transaction_payments
                GROUP BY transaction_id
            ) as transaction_payments'), 'transactions.id', '=', 'transaction_payments.transaction_id')
            ->join('branches', 'transactions.branch_id', '=', 'branches.id')
            ->join('areas', 'branches.area_id', '=', 'areas.id')
            ->where('status_paid', 1)
            ->whereMonth('transaction_payments.last_payment_date', $date->month)
            ->whereYear('transaction_payments.last_payment_date', $date->year)
            ->get();
        $groupTransaction = $transactionPast->groupBy('area_id');
        $countTransaction = $groupTransaction->map(function($item, $key){
            return $item->count();
        });
        foreach($countTransaction as $area_id => $total) {
            $level = LevelLsi::where('min_sold', '<=' , $total)->orderBy('min_sold', 'Desc')->first();
            $lsiMembers = AreaManager::where('type', 'LSI')->where('area_id', $area_id)->get()->map(function ($item) {
                return $item->member;
            });
            foreach ($transactionPast as $key => $transaction) {
                $date = Carbon::parse($transaction['last_payment_date'])->format('Y-m-d');
                $bonusPembulatan = ceil(($transaction['total_price'] * $level->bonus_percentage / 100) / 1000) * 1000;
                foreach ($lsiMembers as $key2 => $member) {
                    $bonus = BonusHistory::create([
                        'member_id' => $member->id,
                        'member_numb' => $member->member_numb,
                        'transaction_id' => $transaction['id'],
                        'level_id' => $member->level_id,
                        'bonus_type' => "KLSI",
                        'bonus_percent' => $level->bonus_percentage,
                        'bonus' => $bonusPembulatan,
                        'bonus_from' => 1,
                        'created_at' => $date,
                        'updated_at' => $date,
                    ]);
                    $log[] = "   (".$member->member_numb . ")(".$date.") - " . $member->name . " mendapatkan Bonus Komisi LSI area ".$area_id.", sebesar Rp. " . number_format($bonus->bonus, 0, ',', '.');
                }
            }
        }
        return $log;
    }

    public function calculateBonusPM($date)
    {
        $log = [];
        $transactionPast = Transaction::
            leftJoin(DB::raw('(
                SELECT transaction_id,MAX(created_at) as last_payment_date from transaction_payments
                GROUP BY transaction_id
            ) as transaction_payments'), 'transactions.id', '=', 'transaction_payments.transaction_id')
            ->join('branches', 'transactions.branch_id', '=', 'branches.id')
            ->join('areas', 'branches.area_id', '=', 'areas.id')
            ->where('status_paid', 1)
            ->whereMonth('transaction_payments.last_payment_date', $date->month)
            ->whereYear('transaction_payments.last_payment_date', $date->year)
            ->get();
        $groupTransaction = $transactionPast->groupBy('area_id');
        $countTransaction = $groupTransaction->map(function($item, $key){
            return $item->count();
        });
        foreach($countTransaction as $area_id => $total) {
            $level = LevelLsi::where('min_sold', '<=' , $total)->orderBy('min_sold', 'Desc')->first();
            $pmMembers = AreaManager::where('type', 'PM')->where('area_id', $area_id)->get()->map(function ($item) {
                return $item->member;
            });
            foreach ($transactionPast as $key => $transaction) {
                $date = Carbon::parse($transaction['last_payment_date'])->format('Y-m-d');
                $bonusPembulatan = ceil(($transaction['total_price'] * $level->bonus_percentage / 100) / 1000) * 1000;
                foreach ($pmMembers as $key2 => $member) {
                    $bonus = BonusHistory::create([
                        'member_id' => $member->id,
                        'member_numb' => $member->member_numb,
                        'transaction_id' => $transaction['id'],
                        'level_id' => $member->level_id,
                        'bonus_type' => "KPM",
                        'bonus_percent' => $level->bonus_percentage,
                        'bonus' => $bonusPembulatan,
                        'bonus_from' => 1,
                        'created_at' => $date,
                        'updated_at' => $date,
                    ]);
                    $log[] = "   (".$member->member_numb . ")(".$date.") - " . $member->name . " mendapatkan Bonus Komisi PM area ".$area_id.", sebesar Rp. " . number_format($bonus->bonus, 0, ',', '.');
                }
            }
        }
        return $log;
    }
}
