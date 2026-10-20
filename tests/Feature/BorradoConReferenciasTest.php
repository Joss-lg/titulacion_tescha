<?php

namespace Tests\Feature;

use App\Models\Alumno;
use App\Models\Docente;
use App\Models\Examen;
use App\Models\Titulacion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BorradoConReferenciasTest extends TestCase
{
    use RefreshDatabase;

    public function test_no_se_elimina_un_docente_que_ya_fue_sinodal(): void
    {
        $docente = $this->crearDocente();
        $this->crearExamen()->sinodales()->create(['docente_id' => $docente->id, 'rol' => 'presidente']);

        $this->actingAs(User::factory()->create())
            ->delete(route('docentes.destroy', $docente))
            ->assertSessionHas('error');

        $this->assertModelExists($docente);
    }

    public function test_se_elimina_un_docente_sin_asignaciones(): void
    {
        $docente = $this->crearDocente();

        $this->actingAs(User::factory()->create())
            ->delete(route('docentes.destroy', $docente))
            ->assertRedirect(route('docentes.index'));

        $this->assertModelMissing($docente);
    }

    public function test_no_se_elimina_un_alumno_con_historial(): void
    {
        $examen = $this->crearExamen();
        $this->crearTitulacion($examen);

        $this->actingAs(User::factory()->create())
            ->delete(route('alumnos.destroy', $examen->alumnos->first()))
            ->assertSessionHas('error');

        $this->assertModelExists($examen->alumnos->first());
    }

    public function test_no_se_elimina_un_examen_con_historial(): void
    {
        $examen = $this->crearExamen();
        $this->crearTitulacion($examen);

        $this->actingAs(User::factory()->create())
            ->delete(route('examenes.destroy', $examen->id))
            ->assertSessionHas('error');

        $this->assertModelExists($examen);
    }

    public function test_hoja_del_examen_se_muestra(): void
    {
        $examen = $this->crearExamen();

        $this->actingAs(User::factory()->create())
            ->get(route('examenes.hoja', $examen->id))
            ->assertOk()
            ->assertSee($examen->alumnos->first()->nombre_completo);
    }

    private function crearDocente(): Docente
    {
        return Docente::create([
            'nombre' => 'Ana',
            'apellido_paterno' => 'Ruiz',
            'tipo' => 'ptc',
        ]);
    }

    private function crearExamen(): Examen
    {
        $alumno = Alumno::create([
            'nombre' => 'María',
            'apellido_paterno' => 'López',
            'matricula' => '22000001',
            'modalidad' => 'tesis',
        ]);

        $examen = Examen::create([
            'fecha' => now()->toDateString(),
            'hora_inicio' => '10:00:00',
            'hora_fin' => '11:00:00',
        ]);
        $examen->alumnos()->attach($alumno);

        return $examen;
    }

    private function crearTitulacion(Examen $examen): Titulacion
    {
        return Titulacion::create([
            'alumno_id' => $examen->alumnos->first()->id,
            'examen_id' => $examen->id,
            'nombre_completo' => $examen->alumnos->first()->nombre_completo,
            'matricula' => $examen->alumnos->first()->matricula,
            'modalidad' => 'tesis',
            'resultado' => 'aprobado',
        ]);
    }
}
