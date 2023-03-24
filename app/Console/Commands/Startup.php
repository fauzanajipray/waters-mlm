<?php

namespace App\Console\Commands;

use App\Http\Traits\LevelUpTrait;
use App\Models\Member;
use App\Models\Transaction;
use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class Startup extends Command
{
    use LevelUpTrait;

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
            DB::commit();
            return Command::SUCCESS;
        } catch (Exception $e) {
            DB::rollBack();
            return $this->error($e->getMessage());
            return Command::FAILURE;
        }
    }
}
