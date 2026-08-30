<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;

class ResetPasswords extends Command
{
    protected $signature = 'passwords:reset';
    protected $description = 'Reset null encrypted passwords';

    public function handle()
    {
        $users = User::whereNull('encrypted_password')->get();
        foreach ($users as $user) {
            $user->password = Hash::make('Welcome123');
            $user->encrypted_password = Crypt::encryptString('Welcome123');
            $user->save();
        }
        $this->info("Updated " . $users->count() . " users.");
    }
}
