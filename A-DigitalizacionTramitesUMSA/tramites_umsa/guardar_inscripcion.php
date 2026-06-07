<?php
require_once 'includes/funciones.php';

// Solo aceptar POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

// ── Validaciones básicas ──────────────────────────────────────
$errores = [];

$nombre           = limpiar($_POST['nombre'] ?? '');
$carnet           = limpiar($_POST['carnet'] ?? '');
$codigo_estudiante= limpiar($_POST['codigo_estudiante'] ?? '');
$email            = limpiar($_POST['email'] ?? '');
$semestre         = (int)($_POST['semestre'] ?? 0);
$carrera          = limpiar($_POST['carrera'] ?? '');
$observaciones    = limpiar($_POST['observaciones'] ?? '');
$materias_raw     = $_POST['materias'] ?? [];

if (empty($nombre))            $errores[] = 'El nombre es obligatorio.';
if (empty($carnet))            $errores[] = 'El carnet es obligatorio.';
if (empty($codigo_estudiante)) $errores[] = 'El código de estudiante es obligatorio.';
if (empty($email) || !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL))
                               $errores[] = 'El correo electrónico no es válido.';
if ($semestre < 1)             $errores[] = 'Debe seleccionar el semestre.';
if (empty($carrera))           $errores[] = 'Debe seleccionar una carrera.';
if (empty($materias_raw))      $errores[] = 'Debe seleccionar al menos una materia.';
if (count($materias_raw) > 5)  $errores[] = 'No puede inscribir más de 5 materias.';

if (!empty($errores)) {
    $msg = urlencode(implode(' | ', $errores));
    header("Location: nueva_inscripcion.php?error={$msg}");
    exit;
}

// ── Validar materias contra JSON ──────────────────────────────
$materias_data = leerJSON('materias.json');
$materias_carrera = $materias_data[$carrera] ?? [];
$codigos_validos  = array_column($materias_carrera, 'codigo');

$materias_seleccionadas = [];
foreach ($materias_raw as $codigo) {
    $codigo = limpiar($codigo);
    if (in_array($codigo, $codigos_validos)) {
        // Buscar info completa de la materia
        foreach ($materias_carrera as $m) {
            if ($m['codigo'] === $codigo) {
                $materias_seleccionadas[] = $m;
                break;
            }
        }
    }
}

if (empty($materias_seleccionadas)) {
    header('Location: nueva_inscripcion.php?error=Las+materias+seleccionadas+no+son+válidas.');
    exit;
}

// ── Construir registro ────────────────────────────────────────
$nueva_solicitud = [
    'id'               => generarID(),
    'nombre'           => $nombre,
    'carnet'           => $carnet,
    'codigo_estudiante'=> $codigo_estudiante,
    'email'            => $email,
    'semestre'         => $semestre,
    'carrera'          => $carrera,
    'materias'         => $materias_seleccionadas,
    'observaciones'    => $observaciones,
    'estado'           => 'pendiente',
    'fecha'            => date('Y-m-d H:i:s'),
    'historial'        => [
        ['estado' => 'pendiente', 'fecha' => date('Y-m-d H:i:s'), 'nota' => 'Solicitud creada']
    ]
];

// ── Guardar en JSON ───────────────────────────────────────────
$inscripciones = leerJSON('inscripciones.json');
$inscripciones[] = $nueva_solicitud;

if (!guardarJSON('inscripciones.json', $inscripciones)) {
    header('Location: nueva_inscripcion.php?error=Error+al+guardar+la+solicitud.+Intente+nuevamente.');
    exit;
}

// ── Redirigir a confirmación ──────────────────────────────────
header('Location: confirmacion.php?id=' . urlencode($nueva_solicitud['id']));
exit;
