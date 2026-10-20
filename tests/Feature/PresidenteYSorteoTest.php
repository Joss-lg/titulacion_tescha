<?php

namespace Tests\Feature;

use App\Models\Alumno;
use App\Models\Docente;
use App\Models\Examen;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PresidenteYSorteoTest extends TestCase
{
    use RefreshDatabase;

    private Carbon $lunes;

    protected function setUp(): void
    {
        parent::setUp();

        $this->lunes = Carbon::now()->next(Carbon::MONDAY);
    }

    public function test_el_alumno_se_registra_con_su_presidente(): void
    {
        $presidente = $this->crearDocente('Elena');

        $this->actingAs(User::factory()->create())
            ->post(route('alumnos.store'), $this->datosAlumno(['presidente_id' => $presidente->id]))
            ->assertSessionHasNoErrors();

        $this->assertSame($presidente->id, Alumno::sole()->presidente_id);
    }

    public function test_el_presidente_es_obligatorio_al_registrar_al_alumno(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('alumnos.store'), $this->datosAlumno())
            ->assertSessionHasErrors('presidente_id');

        $this->assertSame(0, Alumno::count());
    }

    public function test_al_programar_el_examen_se_asigna_el_presidente_del_alumno(): void
    {
        $presidente = $this->crearDocente('Elena');
        $alumno = $this->crearAlumno('22000001', $presidente);

        $this->actingAs(User::factory()->create())
            ->post(route('examenes.store'), $this->datosExamen([$alumno->id]))
            ->assertSessionHasNoErrors();

        $sinodales = Examen::sole()->sinodales;
        $this->assertCount(1, $sinodales);
        $this->assertSame('presidente', $sinodales->first()->rol);
        $this->assertSame($presidente->id, $sinodales->first()->docente_id);
    }

    public function test_no_se_programa_si_el_alumno_no_tiene_presidente(): void
    {
        $alumno = $this->crearAlumno('22000001', null);

        $this->actingAs(User::factory()->create())
            ->post(route('examenes.store'), $this->datosExamen([$alumno->id]))
            ->assertSessionHasErrors('alumnos');

        $this->assertSame(0, Examen::count());
    }

    public function test_no_se_programa_un_equipo_con_presidentes_distintos(): void
    {
        $alumnoA = $this->crearAlumno('22000001', $this->crearDocente('Elena'));
        $alumnoB = $this->crearAlumno('22000002', $this->crearDocente('Mario'));

        $this->actingAs(User::factory()->create())
            ->post(route('examenes.store'), $this->datosExamen([$alumnoA->id, $alumnoB->id]))
            ->assertSessionHasErrors('alumnos');

        $this->assertSame(0, Examen::count());
    }

    public function test_no_se_programa_si_el_presidente_no_esta_disponible(): void
    {
        $presidente = $this->crearDocente('Elena', entrada: '14:00');
        $alumno = $this->crearAlumno('22000001', $presidente);

        $this->actingAs(User::factory()->create())
            ->post(route('examenes.store'), $this->datosExamen([$alumno->id]))
            ->assertSessionHasErrors('hora_inicio');

        $this->assertSame(0, Examen::count());
    }

    public function test_el_sorteo_elige_disponibles_distintos_al_presidente_y_prefiere_menor_carga(): void
    {
        $presidente = $this->crearDocente('Elena');
        $libreA = $this->crearDocente('Ana');
        $libreB = $this->crearDocente('Beto');
        $cargado = $this->crearDocente('Carla');
        $this->crearDocente('Dora', entrada: '14:00');

        // Carla ya fue sinodal en un examen anterior
        $anterior = Examen::create(['fecha' => $this->lunes->copy()->subWeeks(2)->toDateString(), 'hora_inicio' => '10:00', 'hora_fin' => '11:00', 'estado' => 'realizado']);
        $anterior->sinodales()->create(['docente_id' => $cargado->id, 'rol' => 'vocal']);

        $examen = $this->crearExamenConPresidente($presidente);

        $response = $this->actingAs(User::factory()->create())
            ->get(route('examenes.sinodales', ['id' => $examen->id, 'sortear' => 1]));

        $response->assertOk();
        $sugeridos = $response->viewData('sugeridos');
        $this->assertSame($presidente->id, $sugeridos['presidente']);
        $this->assertEqualsCanonicalizing([$libreA->id, $libreB->id], [$sugeridos['secretario'], $sugeridos['vocal']]);
    }

    public function test_al_guardar_se_usa_el_presidente_del_alumno_aunque_se_envie_otro(): void
    {
        $presidente = $this->crearDocente('Elena');
        $otro = $this->crearDocente('Mario');
        $examen = $this->crearExamenConPresidente($presidente);

        $this->actingAs(User::factory()->create())
            ->post(route('examenes.sinodales.store', $examen->id), [
                'presidente' => $otro->id,
                'secretario' => $this->crearDocente('Ana')->id,
                'vocal' => $this->crearDocente('Beto')->id,
            ])
            ->assertDownload();

        unlink(storage_path('app/oficios/oficio_sinodales_22000001_'.$examen->id.'.docx'));
        $this->assertSame($presidente->id, $examen->sinodales()->where('rol', 'presidente')->value('docente_id'));
    }

    private function crearDocente(string $nombre, string $entrada = '08:00'): Docente
    {
        $docente = Docente::create(['nombre' => $nombre, 'apellido_paterno' => 'Ruiz', 'tipo' => 'asignatura']);
        $docente->horarios()->create(['dia' => 'lunes', 'hora_entrada' => $entrada, 'hora_salida' => '20:00']);

        return $docente;
    }

    private function crearAlumno(string $matricula, ?Docente $presidente): Alumno
    {
        return Alumno::create($this->datosAlumno([
            'matricula' => $matricula,
            'presidente_id' => $presidente?->id,
        ]));
    }

    private function crearExamenConPresidente(Docente $presidente): Examen
    {
        $examen = Examen::create(['fecha' => $this->lunes->toDateString(), 'hora_inicio' => '10:00', 'hora_fin' => '11:00']);
        $examen->alumnos()->attach($this->crearAlumno('22000001', $presidente));
        $examen->sinodales()->create(['docente_id' => $presidente->id, 'rol' => 'presidente']);

        return $examen;
    }

    /**
     * @param  array<string, mixed>  $cambios
     * @return array<string, mixed>
     */
    private function datosAlumno(array $cambios = []): array
    {
        return array_merge([
            'nombre' => 'María',
            'apellido_paterno' => 'López',
            'matricula' => '22000001',
            'modalidad' => 'tesis',
            'estado' => 'en_proceso',
        ], $cambios);
    }

    /**
     * @param  list<int>  $alumnos
     * @return array<string, mixed>
     */
    private function datosExamen(array $alumnos): array
    {
        return [
            'alumnos' => $alumnos,
            'fecha' => $this->lunes->toDateString(),
            'hora_inicio' => '10:00',
        ];
    }
}
