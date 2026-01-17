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
    protected $signature = 'app:create-account {--admin : Create an admin user}';

    protected $description = 'Create a new user account';

    public function handle(): void
    {
        $name = $this->ask('Enter your name');
        $email = $this->ask('Enter your email');
        $password = $this->secret('Enter your password');
        $isAdmin = $this->option('admin') || $this->confirm('Should this user be an admin?', false);

        if (User::where('email', $email)->exists()) {
            $this->error('Email already exists');

            return;
        }

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'is_admin' => $isAdmin,
        ]);

        $this->info('User created successfully!');
        $this->table(['Name', 'Email', 'Admin'], [[$user->name, $user->email, $isAdmin ? 'Yes' : 'No']]);
    }
}
