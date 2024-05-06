<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTestEstudiantesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('test_estudiantes', function (Blueprint $table) {
            $table->id();
            $table->string('nombres');
            $table->string('sexo');
            $table->text('edad');
            $table->text('celular');
            $table->string('nombre_madre');
            $table->text('celular_madre');
            $table->string('nombre_padre')->nullable();
            $table->text('celular_padre')->nullable();
            $table->text('carrera_bd');
            $table->text('intereses_bd');
            $table->text('aptitudes_bd');
            $table->text('carreras_aptas_bd');
            $table->text('datos_tests');
            $table->foreignId('pruebas_id')->constrained('pruebas')->onUpdate('cascade')->onDelete('restrict');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('test_estudiantes');
    }
}
