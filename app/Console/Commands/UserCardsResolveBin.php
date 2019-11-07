<?php

namespace App\Console\Commands;

use App\Paystack\BankNameAliases;
use App\Paystack\BankNameMapper;
use App\Paystack\PaystackApi;
use App\UserCard;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class UserCardsResolveBin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user-cards:resolve-bin';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Resolve Cards BIN';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(PaystackApi $paystackApi)
    {

        /** @var Collection $bin */
        $bins = UserCard::select('bin')
            ->withTrashed()
            ->groupBy('bin')
            ->get()
        ;

        $updated = [];
        $names = [];

        foreach ($bins as $bin) {

            if (! $bin->bin) {
                continue;
            }

            try {
                $response = $paystackApi->resolveCardBin($bin->bin);
            }
            catch (\Exception $exception) {
                $badBins[] = $bin->bin;
                continue;
            }

            $bankNameInSystem = BankNameMapper::mapName($response->data->bank, $response->data->bank);

            if (! $bankNameInSystem) {
                $names[] = $response->data->bank;
            }

            $updated[$bin->bin] = UserCard::withTrashed()->where('bin', $bin->bin)->update(['bank_name' => $bankNameInSystem]);
        }

        dd($names, $updated);

    }
}
