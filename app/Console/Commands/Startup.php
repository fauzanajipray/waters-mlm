<?php

namespace App\Console\Commands;

use App\Http\Traits\LevelUpTrait;
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
            $this->info('Level up member started');
            $transactions = Transaction::where("transaction_date", "<", Carbon::now()->startOfMonth()->toDateString())
                ->where('type', 'Normal')
                ->where('status_paid', 1)
                ->get();
            foreach($transactions as $transaction){
                if($transaction->type != "Sparepart") {
                    $this->levelUpMember($transaction->member->id, $transaction->transaction_date);
                }
            }
            $this->info('Level up member ended');
            $this->newLine();
            DB::commit();
            return Command::SUCCESS;
        } catch (Exception $e) {
            DB::rollBack();
            $this->error($e->getMessage());
            return Command::FAILURE;
        }
    }
}
