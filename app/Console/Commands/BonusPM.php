<?php

namespace App\Console\Commands;

use App\Http\Traits\BonusTrait;
use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BonusPM extends Command
{
    use BonusTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bonus-pm';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Bonus PM';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        DB::beginTransaction();
        try {
            $this->info('--- Komisi PM calculate bonus started, Date : '. date('Y-m-d H:i:s') . ' ---');
            $this->newLine();
            $log = $this->calculateBonusPM(Carbon::now());
            if($log){
                foreach($log as $l){ $this->info($l); }
            }
            $this->newLine();
            $this->info('--- Komisi PM calculate bonus end ---');
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

