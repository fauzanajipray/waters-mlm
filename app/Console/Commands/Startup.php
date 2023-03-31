<?php

namespace App\Console\Commands;

use App\Http\Traits\BonusTrait;
use App\Http\Traits\LevelUpTrait;
use App\Http\Traits\TransactionPaymentTrait;
use App\Models\Configuration;
use App\Models\Member;
use App\Models\Transaction;
use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class Startup extends Command
{
    use LevelUpTrait, BonusTrait, TransactionPaymentTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'start-up';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        DB::beginTransaction();
        try {
            $transactionsBefore = Transaction::where("transaction_date", "<", Carbon::now()->startOfMonth()->toDateString())
                ->where('status_paid', 1)
                ->get();
            $transactions = Transaction::where('type', 'Normal')
                ->where('status_paid', 1)
                ->get();
            $this->calculateTotalAndLevelUp($this->getMonthYear($transactions));
            $monthYears = $this->getMonthYear($transactionsBefore);
            $this->bonusNSI($monthYears);
            $this->bonusLSI($monthYears);
            $this->bonusPM($monthYears);
            dd('done');
            DB::commit();
            return Command::SUCCESS;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function getMonthYear($transactions){
        $monthYears = []; // array of month and year, example: 2021-01
        foreach($transactions as $transaction){
            $ym = Carbon::parse($transaction->transaction_date)->format('Y-m');
            $monthYears[] = $ym;
        }
        $monthYears = array_unique($monthYears);
        return $monthYears;
    }

    public function calculateTotalAndLevelUp($monthYears)
    {
        $this->info('* Startup Command : Level up member and calculated Bonus started');
        $this->newLine();

        $types = ['Normal', 'Sparepart', 'Stock'];
        $date = Carbon::now()->format('Y-m');

        foreach($monthYears as $key => $monthYear){
            $this->newLine();
            $this->info('() Date : '. $monthYear . ' ()');

            $dateStart = Carbon::parse($monthYear)->startOfMonth();
            $dateEnd = Carbon::parse($monthYear)->endOfMonth();

            $transactions = Transaction::
                join('transaction_payments', 'transactions.id', '=', 'transaction_payments.transaction_id')
                ->where("transaction_date", ">=", $dateStart->toDateString())
                ->where("transaction_date", "<=", $dateEnd->toDateString())
                ->whereIn('transactions.type', $types)
                ->where('status_paid', 1)
                ->select('transactions.*', 'transaction_payments.payment_date')
                ->get();
            foreach ($transactions as $transaction) {
                $lastPaymentDate = $transaction->transactionPayments->sortByDesc('payment_date')->first()->payment_date;
                $log = $this->calculateBonus($transaction, $transaction->member, $lastPaymentDate);  
                if($log){
                    foreach($log as $l){ $this->info('   -> '.$l); }
                } else {
                    $this->info('   -> No bonus for transaction ' . $transaction->code);
                }
            }

            $this->newLine();
            if($monthYear == $date){
                $this->info('   -> Skip level up member, date : ' . $date);
                continue;
            }
            $transactionsGroupByMember = $transactions->groupBy('member_id');
            foreach ($transactionsGroupByMember as $member_id => $transactions) {
                foreach ($transactions as $transaction) {
                    if($transaction->type == "Normal") {
                        $this->levelUpMember($member_id, $transaction->transaction_date);
                    }
                }
            }
        }
        $this->newLine();
        $this->info('* Startup Command : Level up member end');
        $this->newLine();
        
    }
    public function bonusNSI($monthYears){

        $this->info('* Startup Command : Calculate Bonus NSI started');

        $nsiMembers = Member::where('member_type', 'NSI')->get();
        if($nsiMembers->count() > 0) {
            $this->newLine();
            foreach($monthYears as $monthYear){
                $date = Carbon::parse($monthYear)->endOfMonth();
                $this->info(' - Komisi NSI calculate bonus started, Date : '. $date . ' -');
                foreach($nsiMembers as $member){
                    $log = $this->calculateBonusNsi($member, $date);
                    if($log){
                        foreach($log as $l){ $this->info($l); }
                    } else {
                        $this->info('   No bonus for member ' . $member->name);
                    }
                }
                $this->info(' - Komisi NSI calculate bonus end -');
                $this->newLine();
            }
        } else {
            $this->info('   No NSI member');
        }
        $this->info('* Startup Command : Calculate Bonus NSI end');
        $this->newLine();
    }

    public function bonusLSI($monthYears){
        $this->info('* Startup Command : Calculate Bonus LSI started');
        $this->newLine();

        $dateConfig = Configuration::where('key', 'date_start_komisi_lsi')->first();
        $dateConfig = Carbon::parse($dateConfig->value)->startOfMonth();
        foreach($monthYears as $monthYear){
            $date = Carbon::parse($monthYear)->endOfMonth();
            if($dateConfig->gt($date)){
                $this->info('   Skip calculate bonus PM, date config : ' . $dateConfig . ' date : ' . $date);
                continue;
            }
            $this->info(' - Komisi LSI calculate bonus started, Date : '. $date . ' -');
            $log = $this->calculateBonusLsi($date);
            if($log){
                foreach($log as $l){ $this->info($l); }
            }
            $this->info(' - Komisi LSI calculate bonus end -');
            $this->newLine();
        }
        $this->info('* Startup Command : Calculate Bonus LSI end');
        $this->newLine();
    }

    public function bonusPM($monthYears){
        $this->info('* Startup Command : Calculate Bonus PM started');
        $this->newLine();

        $dateConfig = Configuration::where('key', 'date_start_komisi_pm')->first();
        $dateConfig = Carbon::parse($dateConfig->value)->startOfMonth();
        foreach($monthYears as $monthYear){
            $date = Carbon::parse($monthYear)->endOfMonth();
            if($dateConfig->gt($date)){
                $this->info('   Skip calculate bonus PM, date config : ' . $dateConfig . ' date : ' . $date);
                continue;
            } 
            $this->info(' - Komisi PM calculate bonus started, Date : '. Carbon::now() . ' -');
            $log = $this->calculateBonusPm($date);
            if($log){
                foreach($log as $l){ $this->info($l); }
            }
            $this->info(' - Komisi PM calculate bonus end -');
            $this->newLine();
        }
        $this->info('* Startup Command : Calculate Bonus PM end');
        $this->newLine(); 
    }
}
