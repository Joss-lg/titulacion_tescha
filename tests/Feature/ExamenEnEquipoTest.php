<?php

namespace Tests\Feature;

use App\Models\Alumno;
use App\Models\Docente;
use App\Models\Examen;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use ZipArchive;

class ExamenEnEquipoTest extends TestCase
{
    use RefreshDatabase;

    private Carbon $lunes;

    private Docente $presidente;

    protected function setUp(): void
    {
        parent::setUp();

        $this->lunes = Carbon::now()->next(Carbon::MONDAY);
        $this->presidente = Docente::create(['nombre' => 'Elena', 'apellido_paterno' => 'Mora', 'tipo' => 'ptc']);
        $this->presidente->horarios()->create(['dia' => 'lunes', 'hora_entrada' => '08:00', 'hora_salida' => '20:00']);
    }

    public function test_se_programa_un_examen_para_un_equipo_de_tres(): void
    {
        $equipo = [$this->crearAlumno('Ana', '22000001'), $this->crearAlumno('Beto', '22000002'), $this->crearAlumno('Carla', '22000003')];

        $this->actingAs(User::factory()->create())
            ->post(route('examenes.store'), [
                'alumnos' => array_map(fn (Alumno $alumno) => $alumno->id, $equipo),
                'fecha' => $this->lunes->toDateString(),
                'hora_inicio' => '10:00',
            ])
            ->assertSessionHasNoErrors();

        $examen = Examen::sole();
        $this->assertEqualsCanonicalizing(
            array_map(fn (Alumno $alumno) => $alumno->id, $equipo),
            $examen->alumnos->pluck('id')->all()
        );
        $this->assertSame('11:00', substr($examen->hora_fin, 0, 5));
        foreach ($equipo as $alumno) {
            $this->assertSame('asignado', $alumno->fresh()->estado);
        }
    }

    public function test_se_programa_un_examen_individual_ignorando_lugares_vacios(): void
    {
        $alumno = $this->crearAlumno('Ana', '22000001');

        $this->actingAs(User::factory()->create())
            ->post(route('examenes.store'), [
                'alumnos' => [$alumno->id, '', ''],
                'fecha' => $this->lunes->toDateString(),
                'hora_inicio' => '10:00',
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame([$alumno->id], Examen::sole()->alumnos->pluck('id')->all());
    }

    public function test_no_se_permiten_mas_de_tres_alumnos(): void
    {
        $ids = collect(range(1, 4))->map(fn (int $n) => $this->crearAlumno("Alumno{$n}", "2200000{$n}")->id)->all();

        $this->actingAs(User::factory()->create())
            ->post(route('examenes.store'), [
                'alumnos' => $ids,
                'fecha' => $this->lunes->toDateString(),
                'hora_inicio' => '10:00',
            ])
            ->assertSessionHasErrors('alumnos');

        $this->assertSame(0, Examen::count());
    }

    public function test_no_se_incluye_un_alumno_que_ya_tiene_examen_programado(): void
    {
        $ocupado = $this->crearAlumno('Ana', '22000001');
        $examenPrevio = Examen::create(['fecha' => $this->lunes->toDateString(), 'hora_inicio' => '09:00', 'hora_fin' => '10:00']);
        $examenPrevio->alumnos()->attach($ocupado);

        $this->actingAs(User::factory()->create())
            ->post(route('examenes.store'), [
                'alumnos' => [$this->crearAlumno('Beto', '22000002')->id, $ocupado->id],
                'fecha' => $this->lunes->copy()->addWeek()->toDateString(),
                'hora_inicio' => '10:00',
            ])
            ->assertSessionHasErrors('alumnos.1');

        $this->assertSame(1, Examen::count());
    }

    public function test_oficio_de_equipo_menciona_a_todos_los_sustentantes(): void
    {
        $examen = Examen::create(['fecha' => $this->lunes->toDateString(), 'hora_inicio' => '10:00', 'hora_fin' => '11:00']);
        $examen->alumnos()->attach([
            $this->crearAlumno('Ana', '22000001')->id,
            $this->crearAlumno('Beto', '22000002')->id,
        ]);
        foreach (['presidente', 'secretario', 'vocal'] as $rol) {
            $docente = Docente::create(['nombre' => ucfirst($rol), 'apellido_paterno' => 'Ruiz', 'tipo' => 'ptc']);
            $examen->sinodales()->create(['docente_id' => $docente->id, 'rol' => $rol]);
        }

        $response = $this->actingAs(User::factory()->create())
            ->get(route('examenes.oficio', $examen->id));

        $response->assertDownload('Oficio_Sinodales_22000001_22000002.docx');
        $ruta = $response->baseResponse->getFile()->getPathname();
        $zip = new ZipArchive;
        $zip->open($ruta);
        $documento = $zip->getFromName('word/document.xml');
        $membrete = $zip->locateName('word/media/image1.png');
        $zip->close();
        unlink($ruta);

        $this->assertStringContainsString('de los CC.', $documento);
        $this->assertStringContainsString('ANA LÓPEZ', $documento);
        $this->assertStringContainsString('BETO LÓPEZ', $documento);
        $this->assertStringContainsString('22000002', $documento);
        $this->assertStringContainsString('quienes sustentarán', $documento);
        $this->assertNotFalse($membrete, 'El oficio debe incluir el membrete.');
    }

    private function crearAlumno(string $nombre, string $matricula): Alumno
    {
        return Alumno::create([
            'nombre' => $nombre,
            'apellido_paterno' => 'López',
            'matricula' => $matricula,
            'modalidad' => 'tesis',
            'presidente_id' => $this->presidente->id,
        ]);
    }
}
