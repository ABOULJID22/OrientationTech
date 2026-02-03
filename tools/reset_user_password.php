<?php

// Usage: php tools/reset_user_password.php <user_id> <new_password>

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';

// Bootstrap the application so we can use Eloquent and the Hash facade
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

$id = $argv[1] ?? null;
$newPassword = $argv[2] ?? null;

if (!$id || !$newPassword) {
    echo "Usage: php tools/reset_user_password.php <user_id> <new_password>\n";
    exit(1);
}

$user = User::find($id);
if (!$user) {
    echo "User with id $id not found.\n";
    exit(1);
}

$user->password = Hash::make($newPassword);
$user->save();

echo "Password updated for user id={$id}.\n";
