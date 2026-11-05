<?php

namespace App\Console\Commands;

use App\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class CreateAdminUser extends Command
{
    protected $signature = 'admin:create
                            {username? : Nombre de usuario (ej. osemidei)}
                            {--name= : Nombre completo}
                            {--email= : Correo electrónico}
                            {--password= : Contraseña local para /admin/login}';

    protected $description = 'Crea un usuario administrador o convierte en admin a un usuario existente asignándole contraseña local';

    public function handle()
    {
        $username = $this->argument('username') ?: $this->ask('Ingrese el username (ej. osemidei)');
        if (!$username) {
            $this->error('El username es obligatorio.');
            return 1;
        }

        $user = User::where('username', $username)->first();

        if ($user) {
            $this->info("El usuario '{$username}' ya existe (ID: {$user->id}). Se actualizará su contraseña local y se le asignará el rol 'admin'.");
            $name = $this->option('name') ?: $user->name;
            $email = $this->option('email') ?: $user->email;
        } else {
            $name = $this->option('name') ?: $this->ask('Ingrese el nombre completo', $username);
            $email = $this->option('email') ?: $this->ask('Ingrese el correo electrónico', $username . '@muvh.gov.py');
        }

        $password = $this->option('password') ?: $this->secret('Ingrese la contraseña local para /admin/login');
        if (!$password) {
            $this->error('La contraseña no puede estar vacía.');
            return 1;
        }

        $adminRole = Role::firstOrCreate([
            'name'       => 'admin',
            'guard_name' => 'web',
        ]);

        if (!$user) {
            $user = User::create([
                'username' => $username,
                'name'     => $name,
                'email'    => $email,
                'password' => Hash::make($password),
            ]);
        } else {
            $user->name = $name;
            $user->email = $email;
            $user->password = Hash::make($password);
            $user->save();
        }

        if (!$user->hasRole($adminRole)) {
            $user->assignRole($adminRole);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->info("✓ Usuario '{$user->username}' configurado como 'admin' con contraseña local exitosamente.");
        return 0;
    }
}

