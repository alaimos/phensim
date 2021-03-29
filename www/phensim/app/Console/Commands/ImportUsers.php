<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class ImportUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:users {usersArchive}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import users from the old phensim GUI';

    /**
     * Execute the console command.
     *
     * @return int
     * @throws \JsonException
     */
    public function handle(): int
    {
        $usersArchive = $this->argument('usersArchive');
        if (!file_exists($usersArchive) || !is_file($usersArchive) || !is_readable($usersArchive)) {
            $this->error('Invalid input archive.');

            return 101;
        }

        $users = json_decode(file_get_contents($usersArchive), true, 512, JSON_THROW_ON_ERROR);

        foreach ($users as $user) {
            $email = $user['email'] ?? null;
            if (!empty($email) && User::where('email', $email)->count() === 0) {
                User::create(
                    [
                        'name'              => $user['name'],
                        'email'             => $email,
                        'password'          => $user['password'],
                        'affiliation'       => $user['affiliation'],
                        'email_verified_at' => now(),
                        'is_admin'          => $user['is_admin'] ?? false,
                    ]
                );
            }
        }

        $this->info('Users have been imported!');

        return 0;
    }
}
