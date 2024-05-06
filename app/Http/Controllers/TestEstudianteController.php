<?php

namespace App\Http\Controllers;

use App\Models\TestEstudiante;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TestEstudianteController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $test = TestEstudiante::orderBy('id', 'ASC')->get();
        $data = array(
            'code' => 200,
            'status' => 'success',
            'tests' => $test
        );
        return response()->json($data, $data['code']);
    }

    // Para retornar todos los puntos de dicho evento
    function indexTestPrueba(Request $request)
    {
        // 1.-Recoger los usuarios por post
        $params = (object) $request->all(); // Devulve un obejto
        $paramsArray = $request->all(); // Devulve un Array

        // $test = TestEstudiante::where('pruebas_id', $params->idPrueba)->orderBy('id', 'DESC')->get();

        try {
            $test = DB::table('test_estudiantes')
                ->where('pruebas_id', $params->idPrueba)
                ->orderBy('id', 'DESC')
                ->get();

            $data = array(
                'code' => 200,
                'status' => 'success',
                'numero' => $params->idPrueba,
                'tests' => $test
            );
        } catch (Exception $e) {

            $data = array(
                'status' => 'error',
                'code' => 400,
                'message' => 'No se puede mustrar la pruebas',
                'error' => $e->getMessage()
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
            'nombres' => 'required',
            'sexo' => 'required',
            'edad' => 'required',
            'celular' => 'required',
            'email' => 'required',
            'nombre_madre' => 'required',
            'celular_madre' => 'required',

            'carrera_bd' => 'required',
            'intereses_bd' => 'required',
            'aptitudes_bd' => 'required',
            'carreras_aptas_bd' => 'required',
            'datos_tests' => 'required',
            'pruebas_id' => 'required'
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
            $test = new TestEstudiante();
            $test->nombres = $params->nombres;
            $test->sexo = $params->sexo;
            $test->edad = $params->edad;
            $test->celular = $params->celular;
            $test->nombre_madre = $params->nombre_madre;
            $test->celular_madre = $params->celular_madre;
            $test->nombre_padre = $params->nombre_padre;
            $test->celular_padre = $params->celular_padre;
            $test->carrera_bd = $params->carrera_bd;
            $test->intereses_bd = $params->intereses_bd;
            $test->aptitudes_bd = $params->aptitudes_bd;
            $test->carreras_aptas_bd = $params->carreras_aptas_bd;
            $test->datos_tests = json_encode($params->datos_tests);
            $test->pruebas_id = $params->pruebas_id;

            try {
                // Guardar en la base de datos
                $test->save();
                $data = array(
                    'status' => 'success',
                    'code' => 200,
                    'message' => 'La prueba se creo correctamente',
                    'tests' => $test
                );
            } catch (Exception $e) {

                $data = array(
                    'status' => 'error',
                    'code' => 400,
                    'message' => 'La prueba no se ha creado, intente nuevamente',
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
        $test = TestEstudiante::find($id);

        // Comprobamos si es un objeto eso quiere decir si exist en la base de datos.
        if (is_object($test)) {
            $data = array(
                'code' => 200,
                'status' => 'success',
                'tests' => $test
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

    // Buscar Usuario
    public function buscarTest(Request $request)
    {
        $params = (object) $request->all(); // Devuelve un objeto
        $texto = trim($params->prueba);

        try {
            $test = TestEstudiante::where(function ($query) use ($texto) {
                $query->where('nombres', 'LIKE', "%{$texto}%");
            })
                ->orderBy('id', 'DESC')
                ->get();

            $data = [
                'status' => 'success',
                'code' => 200,
                'tests' => $test,
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
