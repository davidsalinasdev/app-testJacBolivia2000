<?php

namespace App\Http\Controllers;

use App\Helpers\JwtAuth;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{

    // Metodo login de usuario
    public function login(Request $request)
    {
        $jwtauth = new JwtAuth();

        // Recoger los usuarios por post
        $params = (object) $request->all(); // Devulve un obejto
        $paramsArray = $request->all(); // Devulve un Array

        // Validar los datos recibidos por POST.
        $validate = Validator::make($paramsArray, [
            // Comprobar si el usuario ya existe duplicado
            'user' => 'required',
            'password' => 'required',
        ]);
        // Comprobar si los datos son validos
        if ($validate->fails()) { // en caso si los datos fallan la validacion
            // La validacion ha fallado
            $singup = array(
                'status' => 'error',
                'code' => 404,
                'message' => 'El usuario no se ha podido identificar Faltan datos',
                'errors' => $validate->errors()
            );
        } else {
            // Cifrar la PASSWORD.
            $pwd = hash('sha256', $params->password); // para verificar que las contraseña a consultar sean iguales.

            $singup = $jwtauth->singup($params->user, $pwd); // Por defecto token codificado. token

            if (!empty($params->getToken)) { // si existe y no esta vacio y no es NULL.
                $singup = $jwtauth->singup($params->user, $pwd, true); // Token decodificado en un objeto.
            }
        }
        // Respuesta si el login es valido y si es valido devuelve el token decodificado
        return response()->json($singup, 200);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $users = User::orderBy('id', 'DESC')->paginate(5);; // Todos los servidores publicos
        $data = array(
            'code' => 200,
            'status' => 'success',
            'users' => $users
        );
        return response()->json($data, $data['code']);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        // Recoger los usuarios por post
        $params = (object) $request->all(); // Devulve un obejto
        $paramsArray = $request->all(); // Devulve un Array

        // 2.-Validar datos
        $validate = Validator::make($request->all(), [
            'nombres' => 'required',
            'cargo' => 'required',
            'roluser' => 'required',
            'user' => 'required|unique:users,user',
            'password' => 'required',
        ]);

        // Comprobar si los datos son validos
        if ($validate->fails()) { // en caso si los datos fallan la validacion
            // La validacion ha fallado
            $data = array(
                'status' => 'error',
                'code' => 400,
                'message' => 'Los datos enviados no son correctos',
                'users' => $request->all(),
                'errors' => $validate->errors()
            );
        } else {
            // Si la validacion pasa correctamente

            // 3.-Cifrar la contraseña
            $pwd = hash('sha256', $params->password); // se cifra la contraseña 4 veces

            // Crear el objeto usuario para guardar en la base de datos
            $user = new User();
            $user->nombres = $params->nombres;
            $user->cargo = $params->cargo;
            $user->rol = $params->roluser;
            $user->user = $params->user;
            $user->password = $pwd;

            try {
                // Guardar en la base de datos
                $user->save();
                $data = array(
                    'status' => 'success',
                    'code' => 200,
                    'message' => 'Se registró correctamente',
                    'users' => $user
                );
            } catch (\Exception $e) {
                $data = array(
                    'status' => 'error',
                    'code' => 400,
                    'message' => 'No se pudo registrar, intente nuevamente',
                    'error' => $e->getMessage()
                );
            }
        }
        return response()->json($data, $data['code']);
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = User::find($id);

        // Comprobamos si es un objeto eso quiere decir si exist en la base de datos.
        if (is_object($user)) {
            $data = array(
                'code' => 200,
                'status' => 'success',
                'users' => $user
            );
        } else {
            $data = array(
                'code' => 404,
                'status' => 'error',
                'message' => 'El usuario no existe'
            );
        }
        return response()->json($data, $data['code']);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {

        // Validar que el servidor exista
        $usuario = User::find($id);

        if (!empty($usuario)) {

            $user = $usuario->user;
            // 1.- Validar datos recogidos por POST. pasando al getIdentity true
            $validate = Validator::make($request->all(), [
                'nombres' => 'required',
                'cargo' => 'required',
                'user' => 'required',
                'estado' => 'required',
                'rol' => 'required',
            ]);

            // 2.-Recoger los usuarios por post
            $params = (object) $request->all(); // Devuelve un obejto
            $paramsArray = $request->all(); // Es un array

            // // Comprobar si los datos son validos
            if ($validate->fails()) { // en caso si los datos fallan la validacion
                // La validacion ha fallado
                $data = array(
                    'status' => 'Error',
                    'code' => 400,
                    'message' => 'Datos incorrectos no se puede actualizar',
                    'errors' => $validate->errors()
                );
            } else {

                if ($user == $paramsArray['user']) {
                    unset($paramsArray['user']);
                }

                // 4.- Quitar los campos que no quiero actualizar de la peticion.
                unset($paramsArray['created_at']);

                try {

                    // Transaccion con eloquent
                    DB::transaction(function () use ($id, $paramsArray) {
                        User::where('id', $id)->update($paramsArray);
                    }, 2); // Cantidad de intentos 

                    // Fin transacción
                    // 6.- Devolver el array con el resultado.
                    $data = array(
                        'status' => 'success',
                        'code' => 200,
                        'message' => 'Los datos se  modificaron correctamente',
                        'users' => $usuario,
                        'changes' => $paramsArray
                    );
                } catch (\Exception $e) {
                    $data = array(
                        'status' => 'error',
                        'code' => 400,
                        'message' => 'No se hizo la modificación. Este registro con este usuario ya existe',
                        'error' => $e->getMessage()
                    );
                }
            }
            return response()->json($data, $data['code']);
        } else {
            $data = array(
                'status' => 'error',
                'code' => 400,
                'message' => 'Este usuario no existe.',
                // 'error' => $e
            );
            return response()->json($data, $data['code']);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user = User::find($id); // Trae el usuario en formato JSON

        if (!empty($user)) {
            $paramsArray = json_decode($user, true); // devuelve un array

            // Quitar los campos que no quiero actualizar de la peticion.
            unset($paramsArray['id']);
            unset($paramsArray['nombres']);
            unset($paramsArray['cargo']);
            unset($paramsArray['rol']);
            unset($paramsArray['user']);
            unset($paramsArray['password']);
            unset($paramsArray['created_at']);
            unset($paramsArray['updated_at']);

            // Campo stado a modificar
            $paramsArray['estado'] = 'No habilitado';

            try {
                // 5.- Actualizar los datos en la base de datos.
                User::where('id', $id)->update($paramsArray);

                // 6.- Devolver el array con el resultado.
                $data = array(
                    'status' => 'success',
                    'code' => 200,
                    'message' => 'El usuario ha sido dado de baja correctamente',
                    'users' => $user,
                    'changes' => $paramsArray
                );
            } catch (Exception $e) {
                $data = array(
                    'status' => 'error',
                    'code' => 400,
                    'message' => 'El usuario no ha sido dado de baja',
                );
            }
            return response()->json($data, $data['code']);
        } else {
            $data = array(
                'status' => 'error',
                'code' => 400,
                'message' => 'Este usuario no existe.',
                // 'error' => $e
            );
            return response()->json($data, $data['code']);
        }
    }


    // Buscar Usuario
    public function buscarUsuarios(Request $request)
    {
        $params = (object) $request->all(); // Devuelve un objeto
        $texto = trim($params->servidor);

        try {
            $user = User::where(function ($query) use ($texto) {
                $query->where('nombres', 'LIKE', "%{$texto}%")
                    ->orWhere('id', 'LIKE', "%{$texto}%")
                    ->orWhere('cargo', 'LIKE', "%{$texto}%");
                // ->orWhere('estado', 'ilike', "%{$texto}%");
            })
                ->orderBy('id', 'DESC')
                ->paginate(5);

            $data = [
                'status' => 'success',
                'code' => 200,
                'users' => $user,
                'texto' => $texto
            ];
        } catch (Exception $e) {
            $data = [
                'status' => 'error',
                'code' => 400,
                'message' => 'No se pudo buscar',
                'error' => $e->getMessage(),
            ];
        }

        return response()->json($data, $data['code']);
    }

    // Actualizaccion de contraseña
    public function changesPassword(Request $request)
    {
        // 2.-Recoger los usuarios por post
        $params = (object) $request->all(); // Devuelve un obejto
        $paramsArray = $request->all(); // Es un array

        // 3.- Validar datos recogidos por POST. 
        $validate = Validator::make($paramsArray, [
            'idUsuario' => 'required',
            'password' => 'required',
        ]);

        // // Comprobar si los datos son validos
        if ($validate->fails()) { // en caso si los datos fallan la validacion
            // La validacion ha fallado
            $data = array(
                'status' => 'error',
                'code' => 400,
                'message' => 'Datos incorrectos no se realizo el cambio de contraseña',
                'errors' => $validate->errors()
            );
        } else {
            // 4.- Quitar los campos que no quiero actualizar de la peticion.
            unset($paramsArray['created_at']);
            unset($paramsArray['idUsuario']);

            // Codificar el new password
            $pwd = hash('sha256', $paramsArray['password']); // se cifra la contraseña 4 veces
            $paramsArray['password'] = $pwd;

            try {
                // 5.- Actualizar los datos en la base de datos.
                User::where('id', $params->idUsuario)->update($paramsArray);

                // var_dump($user_update);
                // die();
                // 6.- Devolver el array con el resultado.
                $data = array(
                    'status' => 'success',
                    'code' => 200,
                    'message' => 'La contraseña se ha modificado correctamente.',
                    'changes' => $paramsArray
                );
            } catch (Exception $e) {
                $data = array(
                    'status' => 'error',
                    'code' => 400,
                    'message' => 'La contraseña no se ha modificado.',
                    // 'error' => $e
                );
            }
        }
        return response()->json(
            $data,
            $data['code']
        );
    }
}
