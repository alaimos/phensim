<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ImportAll extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:all';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import all data';

    /**
     * Create a new command instance.
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
        $ret = $this->call('migrate:reset');
        if (!$ret) {
            $ret = $this->call('migrate');
            if (!$ret) {
                $ret = $this->call('db:seed');
                if (!$ret) {
                    $ret = $this->call('import:pathways');
                    if (!$ret) {
                        $ret = $this->call('index:pathways');
                    }
                }
            }
        }
        return $ret;
    }
}
