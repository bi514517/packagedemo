<?php

namespace App\Console\Commands;

use App\Http\Controllers\StartLeech;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class LeechCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'leech:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
    public function handle()
    {
        (new StartLeech)->start();
    }
}
