<?php

namespace App\Http\Controllers;

use App\Models\Agenda;
use App\Models\Punto;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AgendaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //  Encuentra por id del evento
        $agenda = Agenda::where('users_id', $id)->with('evento', 'user')->orderBy('id', 'desc')->get();

        // Comprobamos si es un objeto eso quiere decir si exist en la base de datos.
        if (is_object($agenda)) {
            $data = array(
                'code' => 200,
                'status' => 'success',
                'agenda' => $agenda
            );
        } else {
            $data = array(
                'code' => 404,
                'status' => 'error',
                'message' => 'No tiene nada en su bandeja de agenda'
            );
        }
        return response()->json($data, $data['code']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    function destroyAgenda(Request $request)
    {

        // 1.-Recoger los usuarios por post
        $params = (object) $request->all(); // Devulve un obejto
        $paramsArray = $request->all(); // Devulve un Array

        // 2.-Validar datos
        $validate = Validator::make($request->all(), [
            'eventos_id' => 'required',
            'users_id' => 'required'
        ]);

        // Comprobar si los datos son validos
        if ($validate->fails()) { // en caso si los datos fallan la validacion
            // La validacion ha fallado
            $data = array(
                'status' => 'Error',
                'code' => 400,
                'message' => 'Los datos enviados no son correctos',
                'agenda' => $request->all(),
                'errors' => $validate->errors()
            );
        } else {

            try {

                // Realizar la consulta para verificar si existe un registro que coincida con los campos especificados
                $existeUsuario = Agenda::where('eventos_id', $params->eventos_id)
                    ->where('users_id', $params->users_id)
                    ->exists();

                if ($existeUsuario) {

                    Agenda::where('eventos_id', $params->eventos_id)
                        ->where('users_id', $params->users_id)
                        ->delete();

                    $data = array(
                        'status' => 'success',
                        'code' => 200,
                        'message' => 'La agenda se elimino correctamente',
                    );
                }

                // Confirmar la transacción si todo va bien

            } catch (Exception $e) {

                $data = array(
                    'status' => 'error',
                    'code' => 400,
                    'message' => 'No se pudo eliminar la agenda, intente nuevamente',
                    'error' => $e->getMessage()
                );
            }
        }
        return response()->json($data, $data['code']);
    }

    function destroyAllPoinst(Request $request)
    {

        // 1.-Recoger los usuarios por post
        $params = (object) $request->all(); // Devulve un obejto
        $paramsArray = $request->all(); // Devulve un Array

        // 2.-Validar datos
        $validate = Validator::make($request->all(), [
            'eventos_id' => 'required',
            'users_id' => 'required'
        ]);

        // Comprobar si los datos son validos
        if ($validate->fails()) { // en caso si los datos fallan la validacion
            // La validacion ha fallado
            $data = array(
                'status' => 'Error',
                'code' => 400,
                'message' => 'Los datos enviados no son correctos',
                'agenda' => $request->all(),
                'errors' => $validate->errors()
            );
        } else {

            try {
                // Eliminación de una todos los que coincidan
                Punto::where([
                    'eventos_id' => $params->eventos_id,
                    'users_id' => $params->users_id
                ])->delete();

                Agenda::where([
                    'eventos_id' => $params->eventos_id,
                    'users_id' => $params->users_id
                ])->delete();



                $data = array(
                    'status' => 'success',
                    'code' => 200,
                    'message' => 'Te saliste por completo de esta agenda',
                );

                // Confirmar la transacción si todo va bien

            } catch (Exception $e) {

                $data = array(
                    'status' => 'error',
                    'code' => 400,
                    'message' => 'No se pudo salir de la agenda, intente nuevamente',
                    'error' => $e->getMessage()
                );
            }
        }
        return response()->json($data, $data['code']);
    }
}
