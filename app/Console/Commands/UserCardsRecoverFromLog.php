<?php

namespace App\Console\Commands;

use App\NodLog;
use App\UserCard;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UserCardsRecoverFromLog extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user-cards:recover-from-log';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recover Cards';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $logs = NodLog::where('subject', 'Card deleted')->get();

        foreach ($logs as $log) {
            $data = json_decode($log->message);

            $dataArray = (array) $data;
            $dataArray['deleted_at'] = $log->created_at;

            DB::table('user_cards')->insert($dataArray);
        }
    }
}
