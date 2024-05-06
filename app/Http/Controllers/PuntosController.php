<?php

namespace App\Http\Controllers;

use App\Models\Agenda;
use App\Models\Punto;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PuntosController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
    }


    // Para retornar todos los puntos de dicho evento
    function indexPuntosEventos($id)
    {

        //  Encuentra por id del evento
        $puntos = Punto::with('evento', 'user')->where('eventos_id', $id)->orderBy('id', 'asc')->get();

        // Comprobamos si es un objeto eso quiere decir si exist en la base de datos.
        if (is_object($puntos)) {
            $data = array(
                'code' => 200,
                'status' => 'success',
                'puntos' => $puntos
            );
        } else {
            $data = array(
                'code' => 404,
                'status' => 'error',
                'message' => 'Los puntos del evento no existen'
            );
        }
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

        // 1.-Recoger los usuarios por post
        $params = (object) $request->all(); // Devulve un obejto
        $paramsArray = $request->all(); // Devulve un Array

        // 2.-Validar datos
        $validate = Validator::make($request->all(), [
            'puntos' => 'required',
            'eventos_id' => 'required',
            'users_id' => 'required',
            'original' => 'required'
        ]);

        // Comprobar si los datos son validos
        if ($validate->fails()) { // en caso si los datos fallan la validacion
            // La validacion ha fallado
            $data = array(
                'status' => 'Error',
                'code' => 400,
                'message' => 'Los datos enviados no son correctos',
                'punto' => $request->all(),
                'errors' => $validate->errors()
            );
        } else {
            // Si la validacion pasa correctamente
            // Crear el objeto usuario para guardar en la base de datos
            $puntos = new Punto();
            $puntos->puntos = $params->puntos;
            $puntos->eventos_id = $params->eventos_id;
            $puntos->users_id = $params->users_id;
            $puntos->original = $params->original;

            // Transacciones en laravel
            DB::beginTransaction();


            try {
                // Guardar en la base de datos
                $puntos->save();

                // Realizar la consulta para verificar si existe un registro que coincida con los campos especificados
                $existeUsuario = Agenda::where('eventos_id', $params->eventos_id)
                    ->where('users_id', $params->users_id)
                    ->exists();

                if ($existeUsuario) {
                    $data = array(
                        'status' => 'success',
                        'code' => 200,
                        'message' => 'El punto se ha creado correctamente',
                    );
                } else {
                    $agenda = new Agenda();
                    $agenda->eventos_id = $params->eventos_id;
                    $agenda->users_id = $params->users_id;

                    // Guardar en la base de datos
                    $agenda->save();

                    $data = array(
                        'status' => 'success',
                        'code' => 200,
                        'message' => 'El punto se ha creado correctamente',
                        'punto' => $puntos
                    );
                }

                // Confirmar la transacción si todo va bien
                DB::commit();
            } catch (Exception $e) {

                // Revertir la transacción en caso de error
                DB::rollback();

                $data = array(
                    'status' => 'error',
                    'code' => 400,
                    'message' => 'No se pudo crear el punto, intente nuevamente',
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
        $punto = Punto::where('id', $id)->first();


        // Comprobamos si es un objeto eso quiere decir si exist en la base de datos.
        if (is_object($punto)) {
            $data = array(
                'code' => 200,
                'status' => 'success',
                'punto' => $punto
            );
        } else {
            $data = array(
                'code' => 404,
                'status' => 'error',
                'message' => 'El punto de lla agenda no existe'
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
        $punto = Punto::find($id);

        if (!empty($punto)) {

            // 1.- Validar datos recogidos por POST. pasando al getIdentity true
            $validate = Validator::make($request->all(), [
                'puntos' => 'required',
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

                // 4.- Quitar los campos que no quiero actualizar de la peticion.
                unset($paramsArray['eventos_id']);
                unset($paramsArray['created_at']);

                try {

                    // Transaccion con eloquent
                    DB::transaction(function () use ($id, $paramsArray) {
                        Punto::where('id', $id)->update($paramsArray);
                    }, 2); // Cantidad de intentos 

                    // Fin transacción
                    // 6.- Devolver el array con el resultado.
                    $data = array(
                        'status' => 'success',
                        'code' => 200,
                        'message' => 'Los datos se  modificaron correctamente',
                        'punto' => Punto::where('id', $id)->first(),
                        'changes' => $paramsArray
                    );
                } catch (\Exception $e) {
                    $data = array(
                        'status' => 'error',
                        'code' => 400,
                        'message' => 'No se hizo la modificación. Este registro ya existe',
                        'error' => $e->getMessage()
                    );
                }
            }
            return response()->json($data, $data['code']);
        } else {
            $data = array(
                'status' => 'error',
                'code' => 400,
                'message' => 'Esta punto no existe.',
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

        try {
            // 5.- Actualizar los datos en la base de datos.
            Punto::where('id', $id)->delete();
            // 6.- Devolver el array con el resultado.
            $data = array(
                'status' => 'success',
                'code' => 200,
                'message' => 'El punto se elimino correctamente'
            );
        } catch (\Exception $e) {
            $data = array(
                'status' => 'error',
                'code' => 400,
                'message' => 'El punto no se ha eliminado',
                'error' => $e->getMessage()
            );
        }
        return response()->json($data, $data['code']);
    }
}
