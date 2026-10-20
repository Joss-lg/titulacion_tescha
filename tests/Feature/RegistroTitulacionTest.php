<?php

namespace Tests\Feature;

use App\Models\Alumno;
use App\Models\Examen;
use App\Models\Titulacion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class RegistroTitulacionTest extends TestCase
{
    use RefreshDatabase;

    public function test_resultado_aprobado_titula_al_alumno(): void
    {
        [$alumno, $examen] = $this->crearAlumnoConExamen();

        $this->actingAs(User::factory()->create())
            ->post(route('alumnos.titular', $alumno), ['resultado' => 'aprobado'])
            ->assertRedirect(route('historial.index'));

        $this->assertSame('titulado', $alumno->fresh()->estado);
        $this->assertSame('realizado', $examen->fresh()->estado);
        $this->assertSame('aprobado', Titulacion::where('alumno_id', $alumno->id)->value('resultado'));
    }

    #[TestWith(['no_aprobado'])]
    #[TestWith(['aplazado'])]
    public function test_resultado_no_aprobatorio_no_titula_al_alumno(string $resultado): void
    {
        [$alumno, $examen] = $this->crearAlumnoConExamen();

        $this->actingAs(User::factory()->create())
            ->post(route('alumnos.titular', $alumno), ['resultado' => $resultado])
            ->assertRedirect(route('historial.index'));

        $this->assertSame('documentacion', $alumno->fresh()->estado);
        $this->assertSame('realizado', $examen->fresh()->estado);
        $this->assertSame($resultado, Titulacion::where('alumno_id', $alumno->id)->value('resultado'));
    }

    /**
     * @return array{0: Alumno, 1: Examen}
     */
    private function crearAlumnoConExamen(): array
    {
        $alumno = Alumno::create([
            'nombre' => 'María',
            'apellido_paterno' => 'López',
            'matricula' => '22000001',
            'modalidad' => 'tesis',
            'estado' => 'asignado',
        ]);

        $examen = Examen::create([
            'fecha' => now()->toDateString(),
            'hora_inicio' => '10:00:00',
            'hora_fin' => '11:00:00',
        ]);
        $examen->alumnos()->attach($alumno);

        return [$alumno, $examen];
    }
}
