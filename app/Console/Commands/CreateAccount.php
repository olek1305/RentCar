<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateAccount extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-account';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $name = $this->ask('Enter your name');
        $email = $this->ask('Enter your email');
        $password = $this->secret('Enter your password');

        if (User::where('email', $email)->exists()) {
            $this->error('Email already exists');

            return;
        }

        $user = User::Create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
        ]);

        $this->info('User created successfully, name: '.$user->name.' email: '.$user->email);
    }
}
