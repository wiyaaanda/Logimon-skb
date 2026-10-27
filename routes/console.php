<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

use App\Models\Driver;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

Artisan::command('hash:passwords', function () {
    $this->info('Starting password hashing...');
    $uCount = 0;
    $dCount = 0;

    $users = User::all();
    foreach ($users as $u) {
        $pw = $u->password ?? '';
        if (! preg_match('/^\$2y\$/', $pw) && ! preg_match('/^\$argon2/', $pw) && strlen($pw) > 0) {
            $u->password = Hash::make($pw);
            $u->save();
            $uCount++;
        }
    }

    $drivers = Driver::all();
    foreach ($drivers as $d) {
        $pw = $d->password ?? '';
        if (! preg_match('/^\$2y\$/', $pw) && ! preg_match('/^\$argon2/', $pw) && strlen($pw) > 0) {
            $d->password = Hash::make($pw);
            $d->save();
            $dCount++;
        }
    }

    $this->info("Users updated: {$uCount}");
    $this->info("Drivers updated: {$dCount}");
    $this->info('Done.');
})->describe('Hash plaintext passwords for User and Driver');

Artisan::command('debug:show-passwords', function () {
    $this->info('User passwords:');
    $users = User::select('id_user', 'username', 'password')->limit(20)->get();
    foreach ($users as $u) {
        $this->line("U[{$u->id_user}] {$u->username} => {$u->password}");
    }
    $this->info('Driver passwords:');
    $drivers = Driver::select('id_driver', 'username', 'password')->limit(20)->get();
    foreach ($drivers as $d) {
        $this->line("D[{$d->id_driver}] {$d->username} => {$d->password}");
    }
})->describe('Show username and password fields for debugging');

Artisan::command('sync:passwords-from-aktivitas', function () {
    $this->info('Syncing passwords from AkunKelola aktivitas...');
    $count = 0;
    $entries = AkunKelola::where('aktivitas_akun', 'like', '%Password%')->get();
    foreach ($entries as $e) {
        $text = $e->aktivitas_akun ?? '';

        // try to extract password
        if (preg_match('/Password(?: baru| Baru)?[:\s]+([\S]+)/i', $text, $pm)) {
            $plain = $pm[1];
        } elseif (preg_match('/Password[:\s]+([\S]+)/i', $text, $pm2)) {
            $plain = $pm2[1];
        } else {
            $plain = null;
        }

        // try to extract username
        $username = null;
        if (preg_match('/Username[:\s]+([^,\n]+)/i', $text, $um)) {
            $username = trim($um[1]);
        }

        if (! $plain) {
            continue;
        }

        $updated = false;
        if ($e->id_user) {
            $u = User::find($e->id_user);
            if ($u) {
                if (! Hash::check($plain, $u->password)) {
                    $u->password = Hash::make($plain);
                    $u->save();
                    $updated = true;
                }
            }
        } elseif ($e->id_driver) {
            $d = Driver::find($e->id_driver);
            if ($d) {
                if (! Hash::check($plain, $d->password)) {
                    $d->password = Hash::make($plain);
                    $d->save();
                    $updated = true;
                }
            }
        } elseif ($username) {
            $u = User::where('username', $username)->first();
            if ($u) {
                if (! Hash::check($plain, $u->password)) {
                    $u->password = Hash::make($plain);
                    $u->save();
                    $updated = true;
                }
            } else {
                $d = Driver::where('username', $username)->first();
                if ($d) {
                    if (! Hash::check($plain, $d->password)) {
                        $d->password = Hash::make($plain);
                        $d->save();
                        $updated = true;
                    }
                }
            }
        }

        if ($updated) {
            $count++;
        }
    }

    $this->info("Passwords updated: {$count}");
})->describe('Sync passwords from AkunKelola aktivitas entries');

use App\Models\AkunKelola;
use Illuminate\Support\Str;

Artisan::command('reset:all-passwords', function () {
    $this->info('Resetting all passwords for User and Driver...');
    $uCount = 0;
    $dCount = 0;

    $generate = function () {
        return strtoupper(Str::random(4)).strtolower(Str::random(4));
    };

    $users = User::all();
    foreach ($users as $u) {
        $new = $generate();
        $u->password = Hash::make($new);
        $u->save();
        AkunKelola::create([
            'id_user' => $u->id_user,
            'aktivitas_akun' => "Password admin direset. Username: {$u->username}, Password Baru: {$new}",
        ]);
        $uCount++;
    }

    $drivers = Driver::all();
    foreach ($drivers as $d) {
        $new = $generate();
        $d->password = Hash::make($new);
        $d->save();
        AkunKelola::create([
            'id_driver' => $d->id_driver,
            'aktivitas_akun' => "Password driver direset. Username: {$d->username}, Password Baru: {$new}",
        ]);
        $dCount++;
    }

    $this->info("Admins updated: {$uCount}");
    $this->info("Drivers updated: {$dCount}");
    $this->info('Done. Plaintext passwords are recorded in akun_kelola history.');
})->describe('Reset all passwords and record plaintext in akun_kelola');

Artisan::command('debug:show-aktivitas-passwords', function () {
    $this->info('Aktivitas (Password) entries:');
    $rows = AkunKelola::where('aktivitas_akun', 'like', '%Password%')
        ->orderBy('created_at', 'desc')->get();
    foreach ($rows as $r) {
        $role = $r->id_user ? 'Admin' : ($r->id_driver ? 'Driver' : 'Unknown');
        $username = '';
        if ($r->id_user && $r->user) {
            $username = $r->user->username;
        }
        if ($r->id_driver && $r->driver) {
            $username = $r->driver->username;
        }
        // extract password plaintext
        $pw = null;
        if (preg_match('/Password(?: Baru| baru)?[:\s]+([^,\n]+)/i', $r->aktivitas_akun, $m)) {
            $pw = trim($m[1]);
        }
        if (! $pw && preg_match('/Password[:\s]+([^,\n]+)/i', $r->aktivitas_akun, $m2)) {
            $pw = trim($m2[1]);
        }
        $this->line("[{$r->created_at}] {$role} {$username} => ".($pw ?? '[no-plain]'));
    }
})->describe('Show plaintext passwords recorded in akun_kelola aktivitas');

Artisan::command('debug:check-cred', function () {
    $pairs = [
        ['username' => 'nanisSKB', 'password' => 'HO9Fp7in'],
        ['username' => 'naniSKB', 'password' => 'HO9Fp7in'],
        ['username' => 'driver1', 'password' => 'TXYB64pz'],
    ];
    foreach ($pairs as $p) {
        $this->info("Checking {$p['username']} / {$p['password']}");
        $u = User::where('username', $p['username'])->first();
        if ($u) {
            $ok = Hash::check($p['password'], $u->password);
            $this->info(' User found - match: '.($ok ? 'yes' : 'no'));
        }
        $d = Driver::where('username', $p['username'])->first();
        if ($d) {
            $okd = Hash::check($p['password'], $d->password);
            $this->info(' Driver found - match: '.($okd ? 'yes' : 'no'));
        }
    }
})->describe('Debug check credentials for sample usernames');
