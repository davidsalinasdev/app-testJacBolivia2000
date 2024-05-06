<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UsuariosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        // Administrador
        $user = new User();
        $user->nombres = "Christian";
        $user->cargo = "Admin";
        $user->user = "Christian";
        $user->password = "dd27564ac5d8b5065d5986d0f9e92fb91e71a23f9f5c13e599985c646c078a16"; //1231230
        $user->rol = 'Administrador';
        $user->estado = 'Habilitado';
        $user->save();

        // Administrador 2
        // $user = new User();
        // $user->nombres = "Demetrio Pinto Vargas";
        // $user->cargo = "Jefe de unidad - UGE";
        // $user->user = "dpintov";
        // $user->password = "dd27564ac5d8b5065d5986d0f9e92fb91e71a23f9f5c13e599985c646c078a16"; //1231230
        // $user->rol = 'Administrador';
        // $user->estado = 'Habilitado';
        // $user->save();

        // Funcionario_1
        // $user = new User();
        // $user->nombres = "Evert Rojas";
        // $user->cargo = "Profesional II - UGE";
        // $user->user = "erojas";
        // $user->password = "dd27564ac5d8b5065d5986d0f9e92fb91e71a23f9f5c13e599985c646c078a16"; //1231230
        // $user->rol = 'Funcionario';
        // $user->estado = 'Habilitado';
        // $user->save();

        // Funcionario_2
        // $user = new User();
        // $user->nombres = "Rodrigo Pinto Crispin";
        // $user->cargo = "Profesional II -UGE";
        // $user->user = "rpintoc";
        // $user->password = "dd27564ac5d8b5065d5986d0f9e92fb91e71a23f9f5c13e599985c646c078a16"; //1231230
        // $user->rol = 'Funcionario';
        // $user->estado = 'Habilitado';
        // $user->save();

        // Funcionario_3
        // $user = new User();
        // $user->nombres = "Amalia Vila";
        // $user->cargo = "Profesional I -UGE";
        // $user->user = "amaliav";
        // $user->password = "dd27564ac5d8b5065d5986d0f9e92fb91e71a23f9f5c13e599985c646c078a16"; //1231230
        // $user->rol = 'Funcionario';
        // $user->estado = 'Habilitado';
        // $user->save();

        // Invitado
        // $user = new User();
        // $user->nombres = "Naomi Orellana";
        // $user->cargo = "Administrativo I - SDPLA";
        // $user->user = "norellana";
        // $user->password = "dd27564ac5d8b5065d5986d0f9e92fb91e71a23f9f5c13e599985c646c078a16"; //1231230
        // $user->rol = 'Invitado';
        // $user->estado = 'Habilitado';
        // $user->save();
    }
}
