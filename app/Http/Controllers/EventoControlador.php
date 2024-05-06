<?php

namespace App\Http\Controllers;

use App\Models\Agenda;
use App\Models\Evento;
use Carbon\Carbon;
use DateTime;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class EventoControlador extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $evento = Evento::orderBy('id', 'DESC')->get(); // Todos los los eventos habilitados
        $data = array(
            'code' => 200,
            'status' => 'success',
            'evento' => $evento
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

        // 1.-Recoger los usuarios por post
        $params = (object) $request->all(); // Devulve un obejto
        $paramsArray = $request->all(); // Devulve un Array


        // 2.-Validar datos
        $validate = Validator::make($request->all(), [
            'evento' => 'required|unique:eventos',
            'lugar_evento' => 'required',
            'fecha_hora_evento' => 'required',
            'etiqueta' => 'required',
            'estado' => 'required',
            'alcance' => 'required',
            'users_id' => 'required',
        ]);

        // Comprobar si los datos son validos
        if ($validate->fails()) { // en caso si los datos fallan la validacion
            // La validacion ha fallado
            $data = array(
                'status' => 'Error',
                'code' => 400,
                'message' => 'Los datos enviados no son correctos',
                'socio' => $request->all(),
                'errors' => $validate->errors()
            );
        } else {

            // Si la validacion pasa correctamente
            // Crear el objeto evento para guardar en la base de datos
            $evento = new Evento();

            $evento->evento = $params->evento;
            $evento->lugar_evento = $params->lugar_evento;
            $evento->fecha_hora_evento = $params->fecha_hora_evento;
            $evento->etiqueta = $params->etiqueta;
            $evento->estado = $params->estado;
            $evento->alcance = $params->alcance;
            $evento->users_id = $params->users_id;

            // Transacciones en laravel
            DB::beginTransaction();

            try {
                // Guardar en la base de datos
                $evento->save();

                // Creando agenda
                $agenda = new Agenda();
                $agenda->eventos_id = $evento->id;
                $agenda->users_id = $params->users_id;

                // Guardar en la base de datos
                $agenda->save();

                // ShowEvento del ultimo id ingresado
                $showEvento = Evento::with('user')->find($evento->id);


                // Confirmar la transacción si todo va bien
                DB::commit();

                // Respuesta al Frontend
                $data = array(
                    'status' => 'success',
                    'code' => 200,
                    'message' => 'La agenda se ha creado correctamente',
                    'evento' => $showEvento
                );
            } catch (Exception $e) {

                // Revertir la transacción en caso de error
                DB::rollback();

                $data = array(
                    'status' => 'error',
                    'code' => 400,
                    'message' => 'No se pudo crear la agenda, intente nuevamente',
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
        $evento = Evento::with('user')->find($id);

        // Comprobamos si es un objeto eso quiere decir si exist en la base de datos.
        if (is_object($evento)) {
            $data = array(
                'code' => 200,
                'status' => 'success',
                'evento' => $evento
            );
        } else {
            $data = array(
                'code' => 404,
                'status' => 'error',
                'message' => 'La agenda no existe'
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
        // Validar que el servidor exista;
        $evento = Evento::find($id);

        $eventoFechaHora = $evento->fecha_hora_evento;

        if (!empty($evento)) {

            // Controla duplicado de evento
            $evento = $evento->evento;

            // 1.- Validar datos recogidos por POST. pasando al getIdentity true
            $validate = Validator::make($request->all(), [
                'evento' => 'required',
                'lugar_evento' => 'required',
                'fecha_hora_evento' => 'required',
                'estado' => 'required',
                'alcance' => 'required',
                'etiqueta' => 'required',
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

                if ($evento == $paramsArray['evento']) {
                    unset($paramsArray['evento']);
                }

                // 4.- Quitar los campos que no quiero actualizar de la peticion.
                unset($paramsArray['created_at']);

                try {

                    // Convierte las fechas en objetos DateTime para una comparación adecuada
                    $eventoFecha = new DateTime($eventoFechaHora);
                    $paramsFecha = new DateTime($params->fecha_hora_evento);

                    $fechaActual = new DateTime(); // Fecha y hora actual

                    // Compara las fechas
                    if ($paramsFecha >= $fechaActual || $paramsFecha == $eventoFecha) {
                        // Transaccion con eloquent
                        DB::transaction(function () use ($id, $paramsArray) {
                            Evento::where('id', $id)->update($paramsArray);
                        }, 2); // Cantidad de intentos 

                        // Fin transacción
                        // 6.- Devolver el array con el resultado.
                        $data = array(
                            'status' => 'success',
                            'code' => 200,
                            'message' => 'Los datos se  modificaron correctamente',
                            'evento' => Evento::find($id),
                            'changes' => $paramsArray
                        );
                    } else {
                        $data = array(
                            'status' => 'error',
                            'code' => 200,
                            'message' => '¡Error! La fecha y hora es anterior a la actual.'
                        );
                    }
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
                'message' => 'Esta agenda no existe.',
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
            Evento::where('id', $id)->delete();

            // 6.- Devolver el array con el resultado.
            $data = array(
                'status' => 'success',
                'code' => 200,
                'message' => 'La agenda ha eliminada correctamente',
            );
        } catch (\Exception $e) {
            $data = array(
                'status' => 'error',
                'code' => 400,
                'message' => 'La agenda no ha sido eliminada',
                'error' => $e->getMessage()
            );
        }
        return response()->json($data, $data['code']);
    }

    // Para cambiar de estado a concluido
    function cambiarEstadoConcluido()
    {

        // Obtener todos los eventos habilitados
        $eventos = Evento::all();

        // Obtener la fecha y hora local actual del servidor
        $fechaHoraLocal = Carbon::now();

        if ($eventos->isNotEmpty()) {
            foreach ($eventos as $evento) {

                $fechaLocal = Carbon::parse($fechaHoraLocal);
                $fechaBaseDatos = Carbon::parse($evento->fecha_hora_evento);

                $fechaSoloLocal = $fechaLocal->toDateString();
                $fechaSoloBD = $fechaBaseDatos->toDateString();

                $local = Carbon::parse($fechaSoloLocal);
                $bd = Carbon::parse($fechaSoloBD);

                // Comparar las fechas
                if ($local->greaterThan($bd)) {
                    $evento->estado = 'Concluido';
                    $evento->save();
                }
            }

            $data = array(
                'code' => 200,
                'status' => 'success',
                'message' => 'Estados de todos los eventos actualizados',
            );
        } else {
            $data = array(
                'code' => 404,
                'status' => 'error',
                'message' => 'No se encontraron eventos',
            );
        }

        return response()->json($data, $data['code']);
    }
}
