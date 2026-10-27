<?php

namespace App\Http\Controllers;

use App\Models\AkunKelola;
use App\Models\Driver;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AkunKelolaController extends Controller
{
    private function generateUsernameFromName($name)
    {
        $parts = explode(' ', trim($name));
        $firstName = strtolower($parts[0]);
        $username = $firstName.'SKB';

        $counter = 1;
        $original = $username;
        while (User::where('username', $username)->exists() || Driver::where('username', $username)->exists()) {
            $username = $original.$counter;
            $counter++;
        }

        return $username;
    }

    private function generateUsername($prefix = 'user')
    {
        do {
            $username = $prefix.'_'.strtolower(Str::random(8));
        } while (User::where('username', $username)->exists() || Driver::where('username', $username)->exists());

        return $username;
    }

    private function generatePassword()
    {
        return strtoupper(Str::random(4)).strtolower(Str::random(4));
    }

    public function index(Request $request)
    {
        $tab = $request->get('tab', 'admin');
        $q = $request->get('q', '');

        if ($tab === 'driver') {
            $items = User::whereHas('akunKelola', fn ($query) => $query->where('status_driver', 'driver'))
                ->when($q, function ($b) use ($q) {
                    return $b->where(function ($query) use ($q) {
                        $query->where('name', 'like', '%'.$q.'%')
                            ->orWhere('username', 'like', '%'.$q.'%');
                    });
                })->paginate(15);
        } else {
            $items = User::whereDoesntHave('akunKelola', function ($query) {
                $query->where('status_driver', 'driver');
            })->when($q, function ($b) use ($q) {
                return $b->where(function ($query) use ($q) {
                    $query->where('name', 'like', '%'.$q.'%')
                        ->orWhere('username', 'like', '%'.$q.'%')
                        ->orWhere('email', 'like', '%'.$q.'%');
                });
            })->paginate(15);
        }

        return view('akun_kelola.index', compact('items', 'tab', 'q'));
    }

    public function createAdmin()
    {
        return view('akun_kelola.admin_form', ['item' => new User]);
    }

    public function storeAdmin(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:user',
        ]);

        $username = $this->generateUsername('admin');
        $password = $this->generatePassword();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'username' => $username,
            'password' => Hash::make($password),
        ]);

        AkunKelola::create([
            'id_user' => $user->id_user,
            'aktivitas_akun' => 'Akun admin baru dibuat. Username: '.$username.', Password: '.$password,
        ]);

        $message = 'Admin berhasil dibuat.';
        $message .= "\nNama: ".$validated['name'];
        $message .= "\nEmail: ".$validated['email'];
        $message .= "\nUsername: ".$username;
        $message .= "\nPassword: ".$password;

        session()->flash('success_message', $message);

        return redirect()->route('akun_kelola.index', ['tab' => 'admin']);
    }

    public function editAdmin(User $user)
    {
        return view('akun_kelola.admin_form', ['item' => $user]);
    }

    public function updateAdmin(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:user,email,'.$user->id_user.',id_user',
        ]);

        $old_data = $user->only(['name', 'email']);
        $user->update($validated);

        $changes = [];
        foreach ($validated as $key => $value) {
            if (isset($old_data[$key]) && $old_data[$key] !== $value) {
                $changes[] = $key.': '.$old_data[$key].' → '.$value;
            }
        }

        if (! empty($changes)) {
            AkunKelola::create([
                'id_user' => $user->id_user,
                'aktivitas_akun' => 'Akun admin diupdate. '.implode(', ', $changes),
            ]);
        }

        return redirect()->route('akun_kelola.index', ['tab' => 'admin'])->with('success', 'Admin berhasil diupdate');
    }

    public function resetPasswordAdmin(User $user)
    {
        $new_password = $this->generatePassword();
        $user->update(['password' => Hash::make($new_password)]);

        AkunKelola::create([
            'id_user' => $user->id_user,
            'aktivitas_akun' => 'Password admin direset. Password baru: '.$new_password,
        ]);

        $message = 'Password admin berhasil direset.';
        $message .= "\nUsername: ".$user->username;
        $message .= "\nPassword Baru: ".$new_password;

        session()->flash('success_message', $message);

        return back();
    }

    public function createDriver()
    {
        return view('akun_kelola.driver_form', ['item' => new User]);
    }

    public function storeDriver(Request $request)
    {
        $validated = $request->validate([
            'nama_driver' => 'required|string|max:255',
        ]);

        $username = $this->generateUsernameFromName($validated['nama_driver']);
        $password = $this->generatePassword();

        $user = User::create([
            'name' => $validated['nama_driver'],
            'email' => null,
            'username' => $username,
            'password' => Hash::make($password),
        ]);

        AkunKelola::create([
            'id_user' => $user->id_user,
            'status_driver' => 'driver',
            'aktivitas_akun' => 'Akun driver baru dibuat. Username: '.$username.', Password: '.$password,
        ]);

        $message = 'Driver berhasil dibuat.';
        $message .= "\nNama: ".$user->name;
        $message .= "\nUsername: ".$username;
        $message .= "\nPassword: ".$password;

        session()->flash('success_message', $message);

        return redirect()->route('akun_kelola.index', ['tab' => 'driver']);
    }

    public function editDriver(User $driver)
    {
        return view('akun_kelola.driver_form', ['item' => $driver]);
    }

    public function updateDriver(Request $request, User $driver)
    {
        $validated = $request->validate(['nama_driver' => 'required|string|max:255']);
        $driver->update(['name' => $validated['nama_driver']]);

        return redirect()->route('akun_kelola.index', ['tab' => 'driver'])->with('success', 'Akun driver berhasil diperbarui');
    }

    public function resetPasswordDriver(User $driver)
    {
        $new_password = $this->generatePassword();
        $driver->update(['password' => Hash::make($new_password)]);

        AkunKelola::create([
            'id_user' => $driver->id_user,
            'status_driver' => 'driver',
            'aktivitas_akun' => 'Password driver direset. Password baru: '.$new_password,
        ]);

        $message = 'Password driver berhasil direset.';
        $message .= "\nUsername: ".$driver->username;
        $message .= "\nPassword Baru: ".$new_password;

        session()->flash('success_message', $message);

        return back();
    }

    public function updateStatusDriver(Request $request, User $driver)
    {
        $validated = $request->validate([
            'status' => 'required|in:aktif,nonaktif',
        ]);

        $old_status = $driver->akunKelola?->status_driver === 'driver' ? 'aktif' : 'nonaktif';
        $new_status = $validated['status'];

        if ($old_status !== $new_status) {
            AkunKelola::create([
                'id_user' => $driver->id_user,
                'status_driver' => $new_status === 'aktif' ? 'driver' : 'nonaktif',
                'aktivitas_akun' => 'Status driver diubah: '.$old_status.' → '.$new_status,
            ]);

            return back()->with('success', 'Status driver berhasil diubah');
        }

        return back();
    }

    public function destroy(Request $request)
    {
        $type = $request->get('type', 'admin');
        $id = $request->get('id');

        if ($type === 'driver') {
            $driver = User::findOrFail($id);
            $nama = $driver->name;
            $driver->delete();
        } else {
            $user = User::findOrFail($id);
            $nama = $user->name;
            $user->delete();
            AkunKelola::create([
                'id_user' => $id,
                'aktivitas_akun' => "Akun admin '".$nama."' dihapus",
            ]);
        }

        return back()->with('success', 'Akun berhasil dihapus');
    }

    public function history(Request $request)
    {
        $q = $request->get('q', '');

        $history = AkunKelola::with(['user', 'driver'])
            ->when($q, function ($b) use ($q) {
                return $b->where(function ($qWhere) use ($q) {
                    $qWhere->where('aktivitas_akun', 'like', '%'.$q.'%')
                        ->orWhereHas('user', function ($ub) use ($q) {
                            $ub->where('username', 'like', '%'.$q.'%')
                                ->orWhere('name', 'like', '%'.$q.'%')
                                ->orWhere('email', 'like', '%'.$q.'%');
                        })
                        ->orWhereHas('driver', function ($db) use ($q) {
                            $db->where('username', 'like', '%'.$q.'%')
                                ->orWhere('nama_driver', 'like', '%'.$q.'%');
                        });
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20)->appends(['q' => $q]);

        return view('akun_kelola.history', compact('history', 'q'));
    }
}
