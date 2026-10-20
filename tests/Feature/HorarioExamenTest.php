<?php

namespace Tests\Feature;

use App\Models\Alumno;
use App\Models\Docente;
use App\Models\Examen;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HorarioExamenTest extends TestCase
{
    use RefreshDatabase;

    public function test_solo_se_aceptan_bloques_de_hora_completa(): void
    {
        $alumno = $this->crearAlumno();

        $this->actingAs(User::factory()->create())
            ->post(route('examenes.store'), [
                'alumnos' => [$alumno->id],
                'fecha' => Carbon::now()->next(Carbon::MONDAY)->toDateString(),
                'hora_inicio' => '10:30',
            ])
            ->assertSessionHasErrors('hora_inicio');

        $this->assertSame(0, Examen::count());
    }

    public function test_al_editar_el_examen_dura_una_hora(): void
    {
        $examen = Examen::create(['fecha' => now()->toDateString(), 'hora_inicio' => '10:00', 'hora_fin' => '11:00']);
        $examen->alumnos()->attach($this->crearAlumno());

        $this->actingAs(User::factory()->create())
            ->put(route('examenes.update', $examen->id), [
                'fecha' => now()->toDateString(),
                'hora_inicio' => '13:00',
                'estado' => 'programado',
            ])
            ->assertSessionHasNoErrors();

        $examen->refresh();
        $this->assertSame('13:00', substr($examen->hora_inicio, 0, 5));
        $this->assertSame('14:00', substr($examen->hora_fin, 0, 5));
    }

    public function test_al_editar_se_conserva_un_horario_previo_fuera_de_bloque(): void
    {
        $examen = Examen::create(['fecha' => now()->toDateString(), 'hora_inicio' => '10:30', 'hora_fin' => '11:30']);
        $examen->alumnos()->attach($this->crearAlumno());

        $this->actingAs(User::factory()->create())
            ->put(route('examenes.update', $examen->id), [
                'fecha' => now()->toDateString(),
                'hora_inicio' => '10:30',
                'salon' => 'A-101',
                'estado' => 'programado',
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame('A-101', $examen->fresh()->salon);
    }

    private function crearAlumno(): Alumno
    {
        $presidente = Docente::create(['nombre' => 'Elena', 'apellido_paterno' => 'Mora', 'tipo' => 'ptc']);
        $presidente->horarios()->create(['dia' => 'lunes', 'hora_entrada' => '08:00', 'hora_salida' => '20:00']);

        return Alumno::create([
            'nombre' => 'María',
            'apellido_paterno' => 'López',
            'matricula' => '22000001',
            'modalidad' => 'tesis',
            'presidente_id' => $presidente->id,
        ]);
    }
}
