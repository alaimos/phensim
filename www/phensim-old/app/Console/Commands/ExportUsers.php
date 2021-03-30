<?php

namespace App\Console\Commands;

use App\Models\Job;
use App\Models\User;
use Illuminate\Console\Command;

class ExportUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'export:users {outputFile}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export all users';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $outputFile = $this->argument('outputFile');
        if (!is_writable(dirname($outputFile))) {
            $this->error('Output file is not writable');

            return 102;
        }

        $result = [];
        foreach (User::all() as $user) {
            $tmp = $user->toArray();
            $tmp['password'] = $user->password;
            $tmp['is_admin'] = $user->roles->first()->name === "administrator";
            $result[] = $tmp;
        }
        @file_put_contents($outputFile, json_encode($result));

        if (!file_exists($outputFile)) {
            $this->error('Unable to write output file!');

            return 104;
        }
        $this->info("\nUsers Exported!");

        return 0;
    }
}
