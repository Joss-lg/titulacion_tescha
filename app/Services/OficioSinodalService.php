<?php

namespace App\Services;

use App\Models\Examen;
use Carbon\Carbon;
use ZipArchive;

/**
 * Genera el oficio de asignación de sinodales en formato .docx
 * replicando exactamente la plantilla original (membrete, tipografía, estructura).
 *
 * Estrategia: se construye el docx desde cero copiando la estructura XML del
 * template original e inyectando los datos dinámicos.
 */
class OficioSinodalService
{
    /** Meses en español */
    private array $meses = [
        1  => 'enero',   2  => 'febrero',  3  => 'marzo',
        4  => 'abril',   5  => 'mayo',     6  => 'junio',
        7  => 'julio',   8  => 'agosto',   9  => 'septiembre',
        10 => 'octubre', 11 => 'noviembre',12 => 'diciembre',
    ];

    /** Opciones de titulación → texto legible */
    private array $modalidades = [
        'tesis'                     => 'Tesis',
        'residencia_profesional'    => 'Residencia Profesional',
        'proyecto_de_investigacion' => 'Proyecto de Investigación',
        'memoria_de_experiencia'    => 'Memoria de Experiencia Profesional',
        'titulacion_integral'       => 'Titulación Integral',
    ];

    public function generar(Examen $examen): string
    {
        $examen->load('alumno', 'sinodales.docente');

        $alumno    = $examen->alumno;
        $fecha     = Carbon::parse($examen->fecha);
        $dia       = $fecha->day;
        $mes       = $this->meses[$fecha->month];
        $anio      = $fecha->year;
        $horaInicio = substr((string) $examen->hora_inicio, 0, 5);
        $salon      = $examen->salon ?? 'AUDITORIO (CENTRO DE INFORMACIÓN)';
        $modalidad  = $this->modalidades[$alumno->modalidad] ?? strtoupper($alumno->modalidad);

        // Sinodales
        $presidente = $examen->sinodales->firstWhere('rol', 'presidente');
        $secretario = $examen->sinodales->firstWhere('rol', 'secretario');
        $vocal      = $examen->sinodales->firstWhere('rol', 'vocal');

        $nomPresidente = $presidente ? strtoupper($presidente->docente->nombre_con_grado) : '___________________';
        $nomSecretario = $secretario ? strtoupper($secretario->docente->nombre_con_grado) : '___________________';
        $nomVocal      = $vocal      ? strtoupper($vocal->docente->nombre_con_grado)      : '___________________';

        // Datos del alumno
        $nombreAlumno = strtoupper(trim("{$alumno->nombre} {$alumno->apellido_paterno} {$alumno->apellido_materno}"));
        $matricula    = $alumno->matricula;
        $carrera      = strtoupper($alumno->carrera);

        // Folio y fecha del oficio
        // El número correlativo (ej. 443) lo asigna control escolar y se guarda en folio_oficio
        $numCorrelativo = $examen->folio_oficio ?? str_pad($examen->id, 3, '0', STR_PAD_LEFT);
        $numOficio = 'Oficio no. 228C1601010003L-' . $numCorrelativo . '-' . $anio;

        if ($examen->fecha_oficio) {
            $fo = Carbon::parse($examen->fecha_oficio);
            $fechaOficioTexto = $fo->day . ' de ' . $this->meses[$fo->month] . ' de ' . $fo->year;
        } else {
            $fechaOficioTexto = "{$dia} de {$mes} de {$anio}";
        }

        // Rutas
        $membretePath = storage_path('app/plantillas/membrete.png');
        $outputDir    = storage_path('app/oficios');
        $outputFile   = $outputDir . '/oficio_sinodales_' . $alumno->matricula . '_' . $examen->id . '.docx';

        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        // Construir el docx
        $this->construirDocx(
            outputPath:       $outputFile,
            membretePath:     $membretePath,
            numOficio:        $numOficio,
            fechaOficioTexto: $fechaOficioTexto,
            nomPresidente:    $nomPresidente,
            nomSecretario:    $nomSecretario,
            nomVocal:         $nomVocal,
            nombreAlumno:     $nombreAlumno,
            matricula:        $matricula,
            carrera:          $carrera,
            modalidad:        strtoupper($modalidad),
            dia:              $dia,
            mes:              strtoupper($mes),
            anio:             $anio,
            horaInicio:       $horaInicio,
            salon:            strtoupper($salon),
        );

        return $outputFile;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // CONSTRUCCIÓN DEL DOCX
    // ─────────────────────────────────────────────────────────────────────────

    private function construirDocx(
        string $outputPath,
        string $membretePath,
        string $numOficio,
        string $fechaOficioTexto,
        string $nomPresidente,
        string $nomSecretario,
        string $nomVocal,
        string $nombreAlumno,
        string $matricula,
        string $carrera,
        string $modalidad,
        int    $dia,
        string $mes,
        int    $anio,
        string $horaInicio,
        string $salon,
    ): void {
        $zip = new ZipArchive();
        if ($zip->open($outputPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException("No se pudo crear el archivo: {$outputPath}");
        }

        // [Content_Types].xml
        $zip->addFromString('[Content_Types].xml', $this->contentTypes());

        // _rels/.rels
        $zip->addFromString('_rels/.rels', $this->rootRels());

        // word/_rels/document.xml.rels
        $zip->addFromString('word/_rels/document.xml.rels', $this->documentRels());

        // word/_rels/header1.xml.rels
        $zip->addFromString('word/_rels/header1.xml.rels', $this->headerRels());

        // Imagen del membrete
        if (file_exists($membretePath)) {
            $zip->addFile($membretePath, 'word/media/image1.png');
        }

        // word/header1.xml  (imagen full-page como anchor)
        $zip->addFromString('word/header1.xml', $this->headerXml());

        // word/document.xml  (cuerpo del oficio)
        $zip->addFromString('word/document.xml', $this->documentXml(
            numOficio:        $numOficio,
            fechaOficioTexto: $fechaOficioTexto,
            nomPresidente:    $nomPresidente,
            nomSecretario:    $nomSecretario,
            nomVocal:         $nomVocal,
            nombreAlumno:     $nombreAlumno,
            matricula:        $matricula,
            carrera:          $carrera,
            modalidad:        $modalidad,
            dia:              $dia,
            mes:              $mes,
            anio:             $anio,
            horaInicio:       $horaInicio,
            salon:            $salon,
        ));

        $zip->close();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PARTES DEL ZIP
    // ─────────────────────────────────────────────────────────────────────────

    private function contentTypes(): string
    {
        return <<<XML
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
  <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
  <Default Extension="xml"  ContentType="application/xml"/>
  <Default Extension="png"  ContentType="image/png"/>
  <Override PartName="/word/document.xml"
    ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/>
  <Override PartName="/word/header1.xml"
    ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.header+xml"/>
</Types>
XML;
    }

    private function rootRels(): string
    {
        return <<<XML
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1"
    Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument"
    Target="word/document.xml"/>
</Relationships>
XML;
    }

    private function documentRels(): string
    {
        return <<<XML
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1"
    Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/header"
    Target="header1.xml"/>
</Relationships>
XML;
    }

    private function headerRels(): string
    {
        return <<<XML
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1"
    Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/image"
    Target="media/image1.png"/>
</Relationships>
XML;
    }

    /**
     * Header XML: imagen full-page anclada (igual que el original).
     * Dimensiones: 7729870 × 9998999 EMU (~21.5cm × 27.8cm).
     * Posición: -1058544, -786602 (cubre todo incluyendo márgenes).
     */
    private function headerXml(): string
    {
        return <<<XML
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<w:hdr xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"
       xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"
       xmlns:wp="http://schemas.openxmlformats.org/drawingml/2006/wordprocessingDrawing"
       xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main"
       xmlns:pic="http://schemas.openxmlformats.org/drawingml/2006/picture">
  <w:p>
    <w:pPr>
      <w:spacing w:after="0" w:before="0" w:line="240" w:lineRule="auto"/>
    </w:pPr>
    <w:r>
      <w:drawing>
        <wp:anchor allowOverlap="1" behindDoc="0" distB="0" distT="0"
                   distL="114300" distR="114300" hidden="0" layoutInCell="1"
                   locked="0" relativeHeight="0" simplePos="0">
          <wp:simplePos x="0" y="0"/>
          <wp:positionH relativeFrom="column">
            <wp:posOffset>-1058544</wp:posOffset>
          </wp:positionH>
          <wp:positionV relativeFrom="paragraph">
            <wp:posOffset>-786602</wp:posOffset>
          </wp:positionV>
          <wp:extent cx="7729870" cy="9998999"/>
          <wp:effectExtent b="0" l="0" r="0" t="0"/>
          <wp:wrapNone/>
          <wp:docPr id="2" name="image1.png"/>
          <a:graphic>
            <a:graphicData uri="http://schemas.openxmlformats.org/drawingml/2006/picture">
              <pic:pic>
                <pic:nvPicPr>
                  <pic:cNvPr id="0" name="image1.png"/>
                  <pic:cNvPicPr preferRelativeResize="0"/>
                </pic:nvPicPr>
                <pic:blipFill>
                  <a:blip r:embed="rId1"/>
                  <a:srcRect b="0" l="0" r="0" t="0"/>
                  <a:stretch><a:fillRect/></a:stretch>
                </pic:blipFill>
                <pic:spPr>
                  <a:xfrm>
                    <a:off x="0" y="0"/>
                    <a:ext cx="7729870" cy="9998999"/>
                  </a:xfrm>
                  <a:prstGeom prst="rect"/>
                  <a:ln/>
                </pic:spPr>
              </pic:pic>
            </a:graphicData>
          </a:graphic>
        </wp:anchor>
      </w:drawing>
    </w:r>
  </w:p>
</w:hdr>
XML;
    }

    /**
     * Helper: escapa caracteres especiales de XML.
     */
    private function esc(string $s): string
    {
        return htmlspecialchars($s, ENT_XML1, 'UTF-8');
    }

    /**
     * Helper: genera un párrafo con texto simple.
     * $runs = array de ['text' => ..., 'bold' => bool, 'sz' => int (half-pts)]
     */
    private function para(
        string $align,
        array  $runs,
        int    $spaceBefore = 0,
        int    $spaceAfter  = 0,
        int    $lineSpacing = 240,
        string $font        = 'Helvetica Neue',
        int    $defaultSz   = 22,
    ): string {
        $runXml = '';
        foreach ($runs as $run) {
            $text  = $this->esc($run['text'] ?? '');
            $bold  = ($run['bold'] ?? false) ? '<w:b/>' : '<w:b w:val="0"/>';
            $sz    = $run['sz'] ?? $defaultSz;
            $space = str_contains($text, ' ') || str_starts_with($text, ' ') || str_ends_with($text, ' ')
                ? ' xml:space="preserve"'
                : '';
            $runXml .= <<<RUNXML
      <w:r>
        <w:rPr>
          <w:rFonts w:ascii="{$font}" w:hAnsi="{$font}" w:cs="{$font}" w:eastAsia="{$font}"/>
          {$bold}
          <w:sz w:val="{$sz}"/>
          <w:szCs w:val="{$sz}"/>
        </w:rPr>
        <w:t{$space}>{$text}</w:t>
      </w:r>
RUNXML;
        }

        return <<<PARAXML
    <w:p>
      <w:pPr>
        <w:jc w:val="{$align}"/>
        <w:spacing w:before="{$spaceBefore}" w:after="{$spaceAfter}" w:line="{$lineSpacing}" w:lineRule="auto"/>
      </w:pPr>
{$runXml}
    </w:p>
PARAXML;
    }

    /**
     * Párrafo vacío.
     */
    private function emptyPara(int $spaceAfter = 0): string
    {
        return "<w:p><w:pPr><w:spacing w:before=\"0\" w:after=\"{$spaceAfter}\"/></w:pPr></w:p>\n";
    }

    /**
     * Ccp line: "Ccp." TAB "Rol."
     */
    private function ccpLine(string $rol): string
    {
        $rolEsc = $this->esc($rol);
        return <<<CCPXML
    <w:p>
      <w:pPr>
        <w:spacing w:after="0" w:line="240" w:lineRule="auto"/>
        <w:rPr>
          <w:rFonts w:ascii="Helvetica Neue" w:hAnsi="Helvetica Neue" w:cs="Helvetica Neue" w:eastAsia="Helvetica Neue"/>
          <w:sz w:val="16"/>
          <w:szCs w:val="16"/>
        </w:rPr>
      </w:pPr>
      <w:r>
        <w:rPr>
          <w:rFonts w:ascii="Helvetica Neue" w:hAnsi="Helvetica Neue" w:cs="Helvetica Neue" w:eastAsia="Helvetica Neue"/>
          <w:sz w:val="16"/>
          <w:szCs w:val="16"/>
        </w:rPr>
        <w:t xml:space="preserve">Ccp.</w:t>
        <w:tab/>
        <w:t xml:space="preserve">{$rolEsc}.</w:t>
      </w:r>
    </w:p>
CCPXML;
    }

    /**
     * Tabla de firma (igual a la original: 1 columna, 2 filas).
     * Fila 1: ATENTAMENTE + saltos + nombre
     * Fila 2: cargo
     */
    private function tablaFirma(): string
    {
        return <<<TBLXML
    <w:tbl>
      <w:tblPr>
        <w:tblStyle w:val="TableNormal"/>
        <w:tblW w:w="5665" w:type="dxa"/>
        <w:tblBorders>
          <w:top w:val="none" w:sz="0" w:space="0" w:color="auto"/>
          <w:left w:val="none" w:sz="0" w:space="0" w:color="auto"/>
          <w:bottom w:val="none" w:sz="0" w:space="0" w:color="auto"/>
          <w:right w:val="none" w:sz="0" w:space="0" w:color="auto"/>
          <w:insideH w:val="none" w:sz="0" w:space="0" w:color="auto"/>
          <w:insideV w:val="none" w:sz="0" w:space="0" w:color="auto"/>
        </w:tblBorders>
        <w:tblLayout w:type="fixed"/>
      </w:tblPr>
      <w:tblGrid>
        <w:gridCol w:w="5665"/>
      </w:tblGrid>
      <w:tr>
        <w:tc>
          <w:tcPr><w:tcW w:w="5665" w:type="dxa"/></w:tcPr>
          <w:p>
            <w:pPr><w:jc w:val="left"/><w:spacing w:after="0" w:line="240" w:lineRule="auto"/></w:pPr>
            <w:r>
              <w:rPr>
                <w:rFonts w:ascii="Helvetica Neue" w:hAnsi="Helvetica Neue" w:cs="Helvetica Neue" w:eastAsia="Helvetica Neue"/>
                <w:b/>
                <w:sz w:val="22"/>
                <w:szCs w:val="22"/>
              </w:rPr>
              <w:t>ATENTAMENTE</w:t>
            </w:r>
          </w:p>
          <w:p><w:pPr><w:spacing w:after="0" w:line="240" w:lineRule="auto"/><w:jc w:val="center"/></w:pPr></w:p>
          <w:p><w:pPr><w:spacing w:after="0" w:line="240" w:lineRule="auto"/><w:jc w:val="center"/></w:pPr></w:p>
          <w:p><w:pPr><w:spacing w:after="0" w:line="240" w:lineRule="auto"/><w:jc w:val="center"/></w:pPr></w:p>
          <w:p>
            <w:pPr><w:jc w:val="left"/><w:spacing w:after="0" w:line="240" w:lineRule="auto"/></w:pPr>
            <w:r>
              <w:rPr>
                <w:rFonts w:ascii="Helvetica Neue" w:hAnsi="Helvetica Neue" w:cs="Helvetica Neue" w:eastAsia="Helvetica Neue"/>
                <w:b/>
                <w:sz w:val="22"/>
                <w:szCs w:val="22"/>
              </w:rPr>
              <w:t>DRA. FABIOLA ORQUÍDEA SÁNCHEZ HERNÁNDEZ</w:t>
            </w:r>
          </w:p>
        </w:tc>
      </w:tr>
      <w:tr>
        <w:tc>
          <w:tcPr><w:tcW w:w="5665" w:type="dxa"/></w:tcPr>
          <w:p>
            <w:pPr><w:jc w:val="both"/><w:spacing w:after="0" w:line="240" w:lineRule="auto"/></w:pPr>
            <w:r>
              <w:rPr>
                <w:rFonts w:ascii="Helvetica Neue" w:hAnsi="Helvetica Neue" w:cs="Helvetica Neue" w:eastAsia="Helvetica Neue"/>
                <w:sz w:val="22"/>
                <w:szCs w:val="22"/>
              </w:rPr>
              <w:t>JEFA DE DIVISIÓN DE INGENIERÍA EN SISTEMAS COMPUTACIONALES</w:t>
            </w:r>
          </w:p>
        </w:tc>
      </w:tr>
    </w:tbl>
TBLXML;
    }

    /**
     * Cuerpo principal del documento.
     */
    private function documentXml(
        string $numOficio,
        string $fechaOficioTexto,
        string $nomPresidente,
        string $nomSecretario,
        string $nomVocal,
        string $nombreAlumno,
        string $matricula,
        string $carrera,
        string $modalidad,
        int    $dia,
        string $mes,
        int    $anio,
        string $horaInicio,
        string $salon,
    ): string {
        $HN = 'Helvetica Neue'; // fuente principal
        $AR = 'Arial';          // fuente del título

        // ── Párrafo del cuerpo (runs con negrita dinámica) ──────────────────
        // "Por este medio les informo que el acto protocolario de la Titulación
        //  Integral del NOMBRE con número de Matrícula MAT egresado del programa
        //  de Estudios de CARRERA quien sustentara el protocolo de titulación
        //  por la opción de MODALIDAD, el cual se llevará a cabo el día DÍA a
        //  las HORA en el SALON de esta Institución por lo que se le pide su
        //  puntual asistencia."
        $cuerpoRuns = [
            ['text' => 'Por este medio les informo que el acto protocolario de la Titulación Integral del C. ', 'bold' => false],
            ['text' => $nombreAlumno . ' ', 'bold' => true],
            ['text' => 'con número de Matrícula ', 'bold' => false],
            ['text' => $matricula, 'bold' => true],
            ['text' => ' egresado del programa de Estudios de ', 'bold' => false],
            ['text' => $carrera . ' ', 'bold' => true],
            ['text' => 'quien sustentara el protocolo de titulación por la opción de ', 'bold' => false],
            ['text' => $modalidad, 'bold' => true],
            ['text' => ', el cual se llevará a cabo el día ', 'bold' => false],
            ['text' => "{$dia} DE {$mes} DE {$anio}", 'bold' => true],
            ['text' => ' a las ', 'bold' => false],
            ['text' => $horaInicio . ' HORAS ', 'bold' => true],
            ['text' => ' en el ', 'bold' => false],
            ['text' => $salon, 'bold' => true],
            ['text' => ' de esta Institución por lo que se le pide su puntual asistencia.', 'bold' => false],
        ];

        $cuerpoXml = '';
        foreach ($cuerpoRuns as $run) {
            $text  = $this->esc($run['text']);
            $bold  = $run['bold'] ? '<w:b/>' : '<w:b w:val="0"/>';
            $cuerpoXml .= <<<RUNXML
          <w:r>
            <w:rPr>
              <w:rFonts w:ascii="{$HN}" w:hAnsi="{$HN}" w:cs="{$HN}" w:eastAsia="{$HN}"/>
              {$bold}
              <w:sz w:val="22"/>
              <w:szCs w:val="22"/>
            </w:rPr>
            <w:t xml:space="preserve">{$text}</w:t>
          </w:r>
RUNXML;
        }

        $numOficioEsc     = $this->esc($numOficio);
        $fechaOficioEsc   = $this->esc($fechaOficioTexto);
        $nomPresidenteEsc = $this->esc($nomPresidente);
        $nomSecretarioEsc = $this->esc($nomSecretario);
        $nomVocalEsc      = $this->esc($nomVocal);

        $tablaFirma = $this->tablaFirma();
        $ccpPresidente = $this->ccpLine('Presidente');
        $ccpSecretario = $this->ccpLine('Secretario');
        $ccpVocal      = $this->ccpLine('Vocal');
        $ccpEgresado   = $this->ccpLine('Egresado');
        $ccpArchivo    = $this->ccpLine('Archivo');

        return <<<DOCXML
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<w:document
  xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"
  xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"
  xmlns:wp="http://schemas.openxmlformats.org/drawingml/2006/wordprocessingDrawing"
  xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main"
  xmlns:pic="http://schemas.openxmlformats.org/drawingml/2006/picture">
  <w:body>

    <!-- Espacio inicial para dejar área al membrete del header -->
    <w:p><w:pPr><w:spacing w:before="0" w:after="0"/></w:pPr></w:p>

    <!-- Oficio number: alineado a la derecha -->
    <w:p>
      <w:pPr>
        <w:jc w:val="right"/>
        <w:spacing w:before="0" w:after="20" w:line="240" w:lineRule="auto"/>
      </w:pPr>
      <w:r>
        <w:rPr>
          <w:rFonts w:ascii="{$HN}" w:hAnsi="{$HN}" w:cs="{$HN}" w:eastAsia="{$HN}"/>
          <w:b/>
          <w:sz w:val="22"/>
          <w:szCs w:val="22"/>
        </w:rPr>
        <w:t>{$numOficioEsc}</w:t>
      </w:r>
    </w:p>

    <!-- Fecha del oficio: alineada a la derecha -->
    <w:p>
      <w:pPr>
        <w:jc w:val="right"/>
        <w:spacing w:before="0" w:after="200" w:line="240" w:lineRule="auto"/>
      </w:pPr>
      <w:r>
        <w:rPr>
          <w:rFonts w:ascii="{$HN}" w:hAnsi="{$HN}" w:cs="{$HN}" w:eastAsia="{$HN}"/>
          <w:sz w:val="22"/>
          <w:szCs w:val="22"/>
        </w:rPr>
        <w:t>Chalco, Estado de México a {$fechaOficioEsc}</w:t>
      </w:r>
    </w:p>

    <!-- Sección central: ASIGNACIÓN DE SINODALES -->
    <w:p><w:pPr><w:jc w:val="center"/><w:spacing w:before="0" w:after="0"/></w:pPr></w:p>
    <w:p><w:pPr><w:jc w:val="center"/><w:spacing w:before="0" w:after="0"/></w:pPr></w:p>
    <w:p>
      <w:pPr>
        <w:jc w:val="center"/>
        <w:spacing w:before="0" w:after="60" w:line="240" w:lineRule="auto"/>
      </w:pPr>
      <w:r>
        <w:rPr>
          <w:rFonts w:ascii="{$HN}" w:hAnsi="{$HN}" w:cs="{$HN}" w:eastAsia="{$HN}"/>
          <w:b/>
          <w:sz w:val="22"/>
          <w:szCs w:val="22"/>
        </w:rPr>
        <w:t>ASIGNACIÓN DE SINODALES  DE TITULACIÓN INTEGRAL</w:t>
      </w:r>
    </w:p>

    <!-- Líneas vacías -->
    <w:p><w:pPr><w:spacing w:before="0" w:after="0"/></w:pPr></w:p>
    <w:p><w:pPr><w:spacing w:before="0" w:after="0"/></w:pPr></w:p>

    <!-- Sinodales: Rol (negrita) + nombre -->
    <w:p>
      <w:pPr><w:jc w:val="left"/><w:spacing w:before="0" w:after="20" w:line="240" w:lineRule="auto"/></w:pPr>
      <w:r>
        <w:rPr>
          <w:rFonts w:ascii="{$HN}" w:hAnsi="{$HN}" w:cs="{$HN}" w:eastAsia="{$HN}"/>
          <w:b/>
          <w:sz w:val="22"/>
          <w:szCs w:val="22"/>
        </w:rPr>
        <w:t xml:space="preserve">PRESIDENTE    </w:t>
      </w:r>
      <w:r>
        <w:rPr>
          <w:rFonts w:ascii="{$HN}" w:hAnsi="{$HN}" w:cs="{$HN}" w:eastAsia="{$HN}"/>
          <w:sz w:val="22"/>
          <w:szCs w:val="22"/>
        </w:rPr>
        <w:t xml:space="preserve">{$nomPresidenteEsc}</w:t>
      </w:r>
    </w:p>
    <w:p>
      <w:pPr><w:jc w:val="left"/><w:spacing w:before="0" w:after="20" w:line="240" w:lineRule="auto"/></w:pPr>
      <w:r>
        <w:rPr>
          <w:rFonts w:ascii="{$HN}" w:hAnsi="{$HN}" w:cs="{$HN}" w:eastAsia="{$HN}"/>
          <w:b/>
          <w:sz w:val="22"/>
          <w:szCs w:val="22"/>
        </w:rPr>
        <w:t xml:space="preserve">SECRETARIO    </w:t>
      </w:r>
      <w:r>
        <w:rPr>
          <w:rFonts w:ascii="{$HN}" w:hAnsi="{$HN}" w:cs="{$HN}" w:eastAsia="{$HN}"/>
          <w:sz w:val="22"/>
          <w:szCs w:val="22"/>
        </w:rPr>
        <w:t xml:space="preserve">{$nomSecretarioEsc}</w:t>
      </w:r>
    </w:p>
    <w:p>
      <w:pPr><w:jc w:val="left"/><w:spacing w:before="0" w:after="20" w:line="240" w:lineRule="auto"/></w:pPr>
      <w:r>
        <w:rPr>
          <w:rFonts w:ascii="{$HN}" w:hAnsi="{$HN}" w:cs="{$HN}" w:eastAsia="{$HN}"/>
          <w:b/>
          <w:sz w:val="22"/>
          <w:szCs w:val="22"/>
        </w:rPr>
        <w:t xml:space="preserve">VOCAL             </w:t>
      </w:r>
      <w:r>
        <w:rPr>
          <w:rFonts w:ascii="{$HN}" w:hAnsi="{$HN}" w:cs="{$HN}" w:eastAsia="{$HN}"/>
          <w:sz w:val="22"/>
          <w:szCs w:val="22"/>
        </w:rPr>
        <w:t xml:space="preserve">{$nomVocalEsc}</w:t>
      </w:r>
    </w:p>

    <!-- INTEGRANTES DE SINODALES -->
    <w:p>
      <w:pPr><w:jc w:val="left"/><w:spacing w:before="0" w:after="40" w:line="240" w:lineRule="auto"/></w:pPr>
      <w:r>
        <w:rPr>
          <w:rFonts w:ascii="{$HN}" w:hAnsi="{$HN}" w:cs="{$HN}" w:eastAsia="{$HN}"/>
          <w:b/>
          <w:sz w:val="22"/>
          <w:szCs w:val="22"/>
        </w:rPr>
        <w:t>INTEGRANTES DE SINODALES DE TITULACIÓN INTEGRAL</w:t>
      </w:r>
    </w:p>

    <w:p><w:pPr><w:spacing w:before="0" w:after="0"/></w:pPr></w:p>

    <!-- P R E S E N T E S -->
    <w:p>
      <w:pPr><w:jc w:val="left"/><w:spacing w:before="0" w:after="80" w:line="240" w:lineRule="auto"/></w:pPr>
      <w:r>
        <w:rPr>
          <w:rFonts w:ascii="{$HN}" w:hAnsi="{$HN}" w:cs="{$HN}" w:eastAsia="{$HN}"/>
          <w:b/>
          <w:sz w:val="22"/>
          <w:szCs w:val="22"/>
        </w:rPr>
        <w:t>P R E S E N T E S</w:t>
      </w:r>
    </w:p>

    <w:p><w:pPr><w:spacing w:before="0" w:after="0"/></w:pPr></w:p>

    <!-- CUERPO DEL OFICIO (1.5x line spacing = 360) -->
    <w:p>
      <w:pPr>
        <w:jc w:val="both"/>
        <w:spacing w:before="0" w:after="0" w:line="360" w:lineRule="auto"/>
      </w:pPr>
{$cuerpoXml}
    </w:p>

    <w:p><w:pPr><w:spacing w:before="0" w:after="0"/></w:pPr></w:p>

    <!-- Sin más por el momento -->
    <w:p>
      <w:pPr><w:jc w:val="left"/><w:spacing w:before="0" w:after="200" w:line="240" w:lineRule="auto"/></w:pPr>
      <w:r>
        <w:rPr>
          <w:rFonts w:ascii="{$HN}" w:hAnsi="{$HN}" w:cs="{$HN}" w:eastAsia="{$HN}"/>
          <w:sz w:val="22"/>
          <w:szCs w:val="22"/>
        </w:rPr>
        <w:t>Sin más por el momento, me despido de ustedes enviándoles un cordial saludo.</w:t>
      </w:r>
    </w:p>

    <w:p><w:pPr><w:spacing w:before="0" w:after="0"/></w:pPr></w:p>

    <!-- TABLA DE FIRMA -->
{$tablaFirma}

    <!-- Espacio después de firma (moderado, para dejar area de ccps) -->
    <w:p><w:pPr><w:jc w:val="center"/><w:spacing w:before="0" w:after="0"/></w:pPr></w:p>
    <w:p><w:pPr><w:spacing w:before="0" w:after="0"/></w:pPr></w:p>
    <w:p><w:pPr><w:spacing w:before="0" w:after="0"/></w:pPr></w:p>

    <!-- CCP lines (8pt = sz 16) -->
{$ccpPresidente}
{$ccpSecretario}
{$ccpVocal}
{$ccpEgresado}
{$ccpArchivo}

    <w:p><w:pPr><w:spacing w:before="0" w:after="0"/></w:pPr></w:p>

    <!-- Propiedades de página: carta, márgenes como el original -->
    <w:sectPr>
      <w:headerReference r:id="rId1" w:type="default"/>
      <w:pgSz w:h="15840" w:w="12240" w:orient="portrait"/>
      <w:pgMar w:bottom="1417" w:top="1417" w:left="1701" w:right="1701"
               w:header="708" w:footer="708"/>
    </w:sectPr>

  </w:body>
</w:document>
DOCXML;
    }
}