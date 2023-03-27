<?php

namespace App\Console\Commands;

use App\Http\Traits\BonusTrait;
use App\Http\Traits\LevelUpTrait;
use App\Models\Configuration;
use App\Models\Member;
use App\Models\Transaction;
use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class Startup extends Command
{
    use LevelUpTrait, BonusTrait;

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
            $this->info('* Startup Command : Level up member started');
            $this->newLine();
            $transactions = Transaction::where("transaction_date", "<", Carbon::now()->startOfMonth()->toDateString())
                ->where('type', 'Normal')
                ->where('status_paid', 1)
                ->get();
            $monthYears = []; // array of month and year, example: 2021-01
            foreach($transactions as $transaction){
                $ym = Carbon::parse($transaction->transaction_date)->format('Y-m');
                $monthYears[$ym] = $ym;
                if($transaction->type != "Sparepart") {
                    $this->levelUpMember($transaction->member->id, $transaction->transaction_date);
                }
            }
            $this->newLine();
            $this->info('* Startup Command : Level up member end');
            $this->newLine();
            $this->bonusNSI($monthYears);
            $this->bonusLSI($monthYears);
            $this->bonusPM($monthYears);
            
            DB::commit();
            return Command::SUCCESS;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
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
