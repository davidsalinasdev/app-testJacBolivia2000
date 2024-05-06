<?php

namespace App\Http\Controllers;

use App\Models\Prueba;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PruebaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $prueba = Prueba::orderBy('id', 'DESC')->paginate(5);; // Todos las pruebas publicos
        $data = array(
            'code' => 200,
            'status' => 'success',
            'pruebas' => $prueba
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
            'prueba' => 'required|unique:pruebas',
            'users_id' => 'required',
        ]);

        // Comprobar si los datos son validos
        if ($validate->fails()) { // en caso si los datos fallan la validacion
            // La validacion ha fallado
            $data = array(
                'status' => 'error',
                'code' => 400,
                'message' => 'Los datos enviados no son correctos',
                'errors' => $validate->errors()
            );
        } else {
            // Si la validacion pasa correctamente
            // Crear el objeto usuario para guardar en la base de datos
            $prueba = new Prueba();
            $prueba->prueba = $params->prueba;
            $prueba->users_id = $params->users_id;

            try {
                // Guardar en la base de datos
                $prueba->save();
                $data = array(
                    'status' => 'success',
                    'code' => 200,
                    'message' => 'La prueba se registró correctamente',
                    'pruebas' => $prueba
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
        $prueba = Prueba::find($id);

        // Comprobamos si es un objeto eso quiere decir si exist en la base de datos.
        if (is_object($prueba)) {
            $data = array(
                'code' => 200,
                'status' => 'success',
                'pruebas' => $prueba
            );
        } else {
            $data = array(
                'code' => 404,
                'status' => 'error',
                'message' => 'La prueba no existe'
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
        $prueba = Prueba::find($id);

        if (!empty($prueba)) {

            $prueba = $prueba->prueba;
            // 1.- Validar datos recogidos por POST. pasando al getIdentity true
            $validate = Validator::make($request->all(), [
                'prueba' => 'required',
                'estado' => 'required'
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

                if ($prueba == $paramsArray['prueba']) {
                    unset($paramsArray['prueba']);
                }

                // 4.- Quitar los campos que no quiero actualizar de la peticion.
                unset($paramsArray['created_at']);

                try {

                    // Transaccion con eloquent
                    DB::transaction(function () use ($id, $paramsArray) {
                        Prueba::where('id', $id)->update($paramsArray);
                    }, 2); // Cantidad de intentos 

                    // Fin transacción
                    // 6.- Devolver el array con el resultado.
                    $data = array(
                        'status' => 'success',
                        'code' => 200,
                        'message' => 'Los datos se  modificaron correctamente',
                        'pruebas' => $prueba,
                        'changes' => $paramsArray
                    );
                } catch (\Exception $e) {
                    $data = array(
                        'status' => 'error',
                        'code' => 400,
                        'message' => 'No se hizo la modificación. Este registro con esta prueba ya existe',
                        'error' => $e->getMessage()
                    );
                }
            }
            return response()->json($data, $data['code']);
        } else {
            $data = array(
                'status' => 'error',
                'code' => 400,
                'message' => 'Esta prueba no existe.',
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
        $prueba = Prueba::find($id); // Trae el usuario en formato JSON

        if (!empty($prueba)) {
            $paramsArray = json_decode($prueba, true); // devuelve un array

            // Quitar los campos que no quiero actualizar de la peticion.
            unset($paramsArray['id']);
            unset($paramsArray['prueba']);
            unset($paramsArray['created_at']);
            unset($paramsArray['updated_at']);

            // Campo stado a modificar
            $paramsArray['estado'] = 'No habilitado';

            try {
                // 5.- Actualizar los datos en la base de datos.
                Prueba::where('id', $id)->update($paramsArray);

                // 6.- Devolver el array con el resultado.
                $data = array(
                    'status' => 'success',
                    'code' => 200,
                    'message' => 'Esta prueba ha sido dado de baja correctamente',
                    'pruebas' => $prueba,
                    'changes' => $paramsArray
                );
            } catch (Exception $e) {
                $data = array(
                    'status' => 'error',
                    'code' => 400,
                    'message' => 'Esta prueba no ha sido dado de baja',
                );
            }
            return response()->json($data, $data['code']);
        } else {
            $data = array(
                'status' => 'error',
                'code' => 400,
                'message' => 'Esta prueba no existe.',
                // 'error' => $e
            );
            return response()->json($data, $data['code']);
        }
    }


    // Buscar Usuario
    public function buscarPruebas(Request $request)
    {
        $params = (object) $request->all(); // Devuelve un objeto
        $texto = trim($params->prueba);

        try {
            $prueba = Prueba::where(function ($query) use ($texto) {
                $query->where('prueba', 'LIKE', "%{$texto}%");
                // ->orWhere('estado', 'ilike', "%{$texto}%");
            })
                ->orderBy('id', 'DESC')
                ->paginate(5);

            $data = [
                'status' => 'success',
                'code' => 200,
                'pruebas' => $prueba,
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
}
