<?php

namespace App\Http\Traits;

use App\Models\BonusHistory;
use App\Models\LevelNsi;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

trait BonusNsiTrait {
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
            $log[] = "(".$member->member_numb . ") - " . $member->name . " mendapatkan Bonus Komisi NSI sebesar Rp. " . number_format($bonus->bonus, 0, ',', '.');
        }

        return $log;
    }
}
