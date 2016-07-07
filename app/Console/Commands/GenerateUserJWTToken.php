<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;

class GenerateUserJWTToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'jwt:generate-token
        {user : The name of the user you want to generate the token for. }
        {--E|expires= : How long in days should the token last. }
        {--S|super : Should this token be granted super user privilages. }';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a web token for a user.';

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
        $super = (bool) $this->option('super');
        $expires = (int) $this->option('expires');
        $user = \App\User::findByName($this->argument('user'));

        if (! $user) {
            $this->error(sprintf('Invalid user %s', $this->argument('user')));
            return;
        }

        $token = \JWTAuth::fromUser($user, [
            'super-user' => $super,
            'exp' => Carbon::now()->addDays($expires)->timestamp
        ]);

        $this->info(sprintf('JWT Tokens: %s', $token));
    }
}
