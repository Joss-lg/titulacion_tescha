<?php

namespace Tests\Feature;

use App\Models\Alumno;
use App\Models\Docente;
use App\Models\Examen;
use App\Models\Sinodal;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use ZipArchive;

class AsignacionSinodalesTest extends TestCase
{
    use RefreshDatabase;

    private Carbon $lunes;

    protected function setUp(): void
    {
        parent::setUp();

        $this->lunes = Carbon::now()->next(Carbon::MONDAY);
    }

    public function test_docente_ocupado_en_examen_empalmado_no_aparece_como_disponible(): void
    {
        $ocupado = $this->crearDocente('Ana');
        $this->crearExamen('22000001', '10:30', '11:30', presidente: $ocupado);
        $examen = $this->crearExamen('22000002', '10:00', '11:00');

        $response = $this->actingAs(User::factory()->create())
            ->get(route('examenes.sinodales', $examen->id));

        $response->assertOk();
        $candidatos = $response->viewData('candidatos');
        $this->assertNotContains($ocupado->id, collect($candidatos['disponibles'])->pluck('docente.id'));
        $noDisponible = collect($candidatos['no_disponibles'])->firstWhere('docente.id', $ocupado->id);
        $this->assertStringContainsString('Ya es sinodal de otro examen', $noDisponible['razon']);
    }

    public function test_no_se_puede_asignar_un_docente_con_examen_empalmado(): void
    {
        $ocupado = $this->crearDocente('Ana');
        $this->crearExamen('22000001', '10:30', '11:30', presidente: $ocupado);
        $examen = $this->crearExamen('22000002', '10:00', '11:00');

        $response = $this->actingAs(User::factory()->create())
            ->from(route('examenes.sinodales', $examen->id))
            ->post(route('examenes.sinodales.store', $examen->id), [
                'presidente' => $ocupado->id,
                'secretario' => $this->crearDocente('Beto')->id,
                'vocal' => $this->crearDocente('Carla')->id,
            ]);

        $response->assertRedirect(route('examenes.sinodales', $examen->id));
        $response->assertSessionHasErrors('presidente');
        $this->assertSame(0, Sinodal::where('examen_id', $examen->id)->count());
    }

    public function test_examen_contiguo_no_bloquea_al_docente_y_el_oficio_conserva_acentos(): void
    {
        $docente = $this->crearDocente('José');
        $this->crearExamen('22000001', '11:00', '12:00', presidente: $docente);
        $examen = $this->crearExamen('22000002', '10:00', '11:00');

        $response = $this->actingAs(User::factory()->create())
            ->post(route('examenes.sinodales.store', $examen->id), [
                'presidente' => $docente->id,
                'secretario' => $this->crearDocente('Beto')->id,
                'vocal' => $this->crearDocente('Carla')->id,
            ]);

        $response->assertDownload();
        $this->assertSame(3, Sinodal::where('examen_id', $examen->id)->count());

        $ruta = $response->baseResponse->getFile()->getPathname();
        $zip = new ZipArchive;
        $zip->open($ruta);
        $documento = $zip->getFromName('word/document.xml');
        $zip->close();
        unlink($ruta);

        $this->assertStringContainsString('ING. JOSÉ PÉREZ', $documento);
        $this->assertStringContainsString('INGENIERÍA EN SISTEMAS COMPUTACIONALES', $documento);
    }

    private function crearDocente(string $nombre): Docente
    {
        $docente = Docente::create([
            'nombre' => $nombre,
            'apellido_paterno' => 'Pérez',
            'grado' => 'Ing.',
            'tipo' => 'asignatura',
        ]);

        $docente->horarios()->create([
            'dia' => 'lunes',
            'hora_entrada' => '08:00',
            'hora_salida' => '20:00',
        ]);

        return $docente;
    }

    private function crearExamen(string $matricula, string $inicio, string $fin, ?Docente $presidente = null): Examen
    {
        $alumno = Alumno::create([
            'nombre' => 'Alumno',
            'apellido_paterno' => $matricula,
            'matricula' => $matricula,
            'modalidad' => 'tesis',
        ]);

        $examen = Examen::create([
            'fecha' => $this->lunes->toDateString(),
            'hora_inicio' => $inicio.':00',
            'hora_fin' => $fin.':00',
        ]);
        $examen->alumnos()->attach($alumno);

        if ($presidente) {
            $examen->sinodales()->create(['docente_id' => $presidente->id, 'rol' => 'presidente']);
        }

        return $examen;
    }
}
