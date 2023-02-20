<?php

namespace App\Console\Commands;

use App\Http\Traits\LevelUpTrait;
use App\Http\Traits\TransactionPaymentTrait;
use App\Models\Level;
use App\Models\LevelSnapshot;
use App\Models\Transaction;
use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SnapshotLevel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'snapshot-levels';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check if levels update, then create new snapshot level';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        DB::beginTransaction();
        try {
            $this->info('Snapshot level started, Date : '. date('Y-m-d H:i:s'));

            $levels = Level::all();
            foreach($levels as $level) {
                $dateNow = Carbon::now()->startOfMonth()->startOfDay();
                $snapshot = LevelSnapshot::where('level_id', $level->id)->where('date_start', '<=', $dateNow)
                    ->orderBy('date_start', 'desc')->first();
                if(!$snapshot) {
                    $this->info('   Create new snapshot for level : '. $level->name);
                    $newSnapshot = new LevelSnapshot();
                    $newSnapshot->level_id = $level->id;
                    $newSnapshot->minimum_downline = $level->minimum_downline;
                    $newSnapshot->minimum_sold_by_downline = $level->minimum_sold_by_downline;
                    $newSnapshot->minimum_sold = $level->minimum_sold;
                    $newSnapshot->ordering_level = $level->ordering_level;
                    $newSnapshot->bp_percentage = $level->bp_percentage;
                    $newSnapshot->gm_percentage = $level->gm_percentage;
                    $newSnapshot->or_percentage = $level->or_percentage;
                    $newSnapshot->or2_percentage = $level->or2_percentage;
                    $newSnapshot->date_start = $dateNow;
                    $newSnapshot->save();
                } else {
                    if($snapshot->minimum_downline != $level->minimum_downline ||
                        $snapshot->minimum_sold_by_downline != $level->minimum_sold_by_downline ||
                        $snapshot->minimum_sold != $level->minimum_sold ||
                        $snapshot->ordering_level != $level->ordering_level ||
                        $snapshot->bp_percentage != $level->bp_percentage ||
                        $snapshot->gm_percentage != $level->gm_percentage ||
                        $snapshot->or_percentage != $level->or_percentage ||
                        $snapshot->or2_percentage != $level->or2_percentage) {

                        $this->info('   Create new snapshot for level : '. $level->name);
                        $newSnapshot = new LevelSnapshot();
                        $newSnapshot->level_id = $level->id;
                        $newSnapshot->minimum_downline = $level->minimum_downline;
                        $newSnapshot->minimum_sold_by_downline = $level->minimum_sold_by_downline;
                        $newSnapshot->minimum_sold = $level->minimum_sold;
                        $newSnapshot->ordering_level = $level->ordering_level;
                        $newSnapshot->bp_percentage = $level->bp_percentage;
                        $newSnapshot->gm_percentage = $level->gm_percentage;
                        $newSnapshot->or_percentage = $level->or_percentage;
                        $newSnapshot->or2_percentage = $level->or2_percentage;
                        $newSnapshot->date_start = $dateNow;
                        $newSnapshot->save();
                    } else {
                        $this->info('   No changes for level : '. $level->name);
                    }
                }
            }
            $this->info('Snapshot level finished, Date : '. date('Y-m-d H:i:s'));
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
