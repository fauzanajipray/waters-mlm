<?php

namespace App\Console\Commands;

use App\Http\Traits\TransactionPaymentTrait;
use App\Models\Transaction;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class LevelUpMember extends Command
{
    use TransactionPaymentTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'level-up-member';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Level up member';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        DB::beginTransaction();
        try {
            $this->info('Level up member started, Date : '. date('Y-m-d H:i:s'));
            $transactions = Transaction::with(['member', 'transactionPayments'])
                ->thisMonthAndYear()
                ->where('type', 'Normal')
                ->where('status_paid', true)->get();
            // $this->table(['ID', 'Member', 'Total Price', 'Status Paid', 'Create At'], $transactions->toArray());
            foreach($transactions as $transaction){
                if($transaction->type != "Sparepart") {
                    $this->levelUpMember($transaction->member->id);
                }
            }
            $this->info('Level up member ended');
            $this->newLine();
            DB::commit();
            return Command::SUCCESS;
        } catch (\Throwable $th) {
            DB::rollBack();
            $this->error($th->getMessage());
            return Command::FAILURE;
        } catch (Exception $e) {
            DB::rollBack();
            $this->error($e->getMessage());
            return Command::FAILURE;
        }
    }
}
