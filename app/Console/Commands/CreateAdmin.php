<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class CreateAdmin extends Command
{
    protected $signature = 'blomfree:create-admin';

    protected $description = 'Create a new BLOMFREE admin user with full access to the admin panel';

    public function handle(): int
    {
        $name = trim((string) $this->ask('Full name'));
        $email = strtolower(trim((string) $this->ask('Email address')));
        $password = (string) $this->secret('Password');
        $passwordConfirmation = (string) $this->secret('Confirm password');

        $validator = Validator::make([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'password_confirmation' => $passwordConfirmation,
        ], [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        if ($validator->fails()) {
            $this->error('Could not create the admin user:');

            foreach ($validator->errors()->all() as $error) {
                $this->line('  - '.$error);
            }

            return self::FAILURE;
        }

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'is_admin' => true,
            'email_verified_at' => now(),
        ]);

        $this->newLine();
        $this->info('Admin user created successfully.');
        $this->line('  Name:  '.$user->name);
        $this->line('  Email: '.$user->email);
        $this->line('  Login: '.url('/admin/login'));

        return self::SUCCESS;
    }
}
