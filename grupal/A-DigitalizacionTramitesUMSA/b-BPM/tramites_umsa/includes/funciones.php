<?php
/**
 * funciones.php — Helpers globales del sistema
 * Sistema de Trámites UMSA
 */
date_default_timezone_set('America/La_Paz');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('DATA_DIR', __DIR__ . '/../data/');

// ── JSON helpers ──────────────────────────────────────────────────
function leerJSON(string $archivo): array {
    $ruta = DATA_DIR . $archivo;
    if (!file_exists($ruta)) return [];
    return json_decode(file_get_contents($ruta), true) ?? [];
}

function guardarJSON(string $archivo, array $datos): bool {
    $ruta = DATA_DIR . $archivo;
    return file_put_contents($ruta, json_encode($datos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) !== false;
}

function generarID(string $prefijo = 'SOL'): string {
    return strtoupper($prefijo . '-' . date('Ymd') . '-' . substr(uniqid(), -4));
}

function limpiar(string $valor): string {
    return htmlspecialchars(trim($valor), ENT_QUOTES, 'UTF-8');
}

function fechaHora(): string {
    return date('Y-m-d H:i:s');
}

function fechaLegible(string $fecha): string {
    $ts = strtotime($fecha);
    return $ts ? date('d/m/Y H:i', $ts) : $fecha;
}

// ── Badge de estado (título) ──────────────────────────────────────
function badgeEstadoTitulo(string $estado): string {
    $map = [
        'enviado'       => ['warning', 'Enviado'],
        'en_revision'   => ['info',    'En Revisión'],
        'observado'     => ['danger',  'Con Observaciones'],
        'verificado'    => ['primary', 'Verificado'],
        'en_decanatura' => ['primary', 'En Decanatura'],
        'aprobado'      => ['success', 'Aprobado'],
        'rechazado'     => ['danger',  'Rechazado'],
        'en_registros'  => ['info',    'En Registros'],
        'completado'    => ['success', 'Completado'],
    ];
    [$c, $l] = $map[$estado] ?? ['secondary', ucfirst($estado)];
    return "<span class=\"badge bg-{$c}\">{$l}</span>";
}

// ── Badge de estado (certificado) ─────────────────────────────────
function badgeEstadoCert(string $estado): string {
    $map = [
        'enviado'         => ['warning', 'Enviado'],
        'en_verificacion' => ['info',    'En Verificación'],
        'sin_proceso'     => ['primary', 'Sin Proceso'],
        'tiene_proceso'   => ['danger',  'Tiene Proceso'],
        'en_legal'        => ['info',    'En Legal'],
        'completado'      => ['success', 'Certificado Emitido'],
        'no_aplica'       => ['danger',  'No Aplica'],
    ];
    [$c, $l] = $map[$estado] ?? ['secondary', ucfirst($estado)];
    return "<span class=\"badge bg-{$c}\">{$l}</span>";
}

// ── Stepper BPMN visual ───────────────────────────────────────────
function renderStepper(array $pasos, string $estadoActual): string {
    $estados   = array_column($pasos, 'estado');
    $idxActual = array_search($estadoActual, $estados);
    if ($idxActual === false) $idxActual = -1;

    $html = '<div class="bpmn-stepper d-flex align-items-center gap-0 mb-4 overflow-auto">';
    foreach ($pasos as $i => $paso) {
        $done    = $i < $idxActual;
        $active  = $i === $idxActual;
        $upcom   = $i > $idxActual;

        $cls   = $done ? 'done' : ($active ? 'active' : 'upcoming');
        $bgC   = $done ? '#198754' : ($active ? '#003082' : '#dee2e6');
        $txtC  = ($done || $active) ? '#fff' : '#6c757d';
        $lblC  = $done ? '#198754' : ($active ? '#003082' : '#adb5bd');

        $html .= "<div class=\"step-item text-center flex-shrink-0\" style=\"min-width:90px;\">";
        $html .= "<div class=\"step-circle mx-auto d-flex align-items-center justify-content-center rounded-circle\"
                       style=\"width:44px;height:44px;background:{$bgC};color:{$txtC};font-size:1.1rem;\">";
        if ($done) {
            $html .= "<i class='bi bi-check-lg'></i>";
        } else {
            $html .= "<i class='bi bi-{$paso['icono']}'></i>";
        }
        $html .= "</div>";
        $html .= "<div class=\"step-label mt-1\" style=\"font-size:.68rem;color:{$lblC};font-weight:" . ($active ? '700' : '400') . ";line-height:1.2;\">" . limpiar($paso['label']) . "</div>";
        $html .= "</div>";

        // Conector
        if ($i < count($pasos) - 1) {
            $lineC = ($i < $idxActual) ? '#198754' : '#dee2e6';
            $html .= "<div style=\"flex:1;height:2px;background:{$lineC};min-width:16px;margin-top:-20px;\"></div>";
        }
    }
    $html .= '</div>';
    return $html;
}

// ── Historial de estado ───────────────────────────────────────────
function agregarHistorial(array &$solicitud, string $nuevoEstado, string $usuario, string $comentario = ''): void {
    $solicitud['estado'] = $nuevoEstado;
    $solicitud['historial'][] = [
        'estado'    => $nuevoEstado,
        'fecha'     => fechaHora(),
        'usuario'   => $usuario,
        'comentario'=> $comentario,
    ];
    $solicitud['fecha_actualizacion'] = fechaHora();
}

// ── Renderizar timeline del historial ─────────────────────────────
function renderTimeline(array $historial): string {
    if (empty($historial)) return '<p class="text-muted">Sin historial.</p>';
    $html = '<ul class="timeline list-unstyled">';
    foreach (array_reverse($historial) as $h) {
        $html .= "<li class=\"timeline-item mb-3 d-flex gap-3\">
            <div class=\"timeline-dot bg-primary rounded-circle\" style=\"width:12px;height:12px;margin-top:5px;flex-shrink:0;\"></div>
            <div>
              <div class=\"fw-semibold small\">" . limpiar($h['estado']) . "</div>
              <div class=\"text-muted\" style=\"font-size:.78rem;\">" . fechaLegible($h['fecha']) . " — " . limpiar($h['usuario']) . "</div>
              " . (!empty($h['comentario']) ? "<div class=\"fst-italic small mt-1\">" . limpiar($h['comentario']) . "</div>" : '') . "
            </div>
          </li>";
    }
    $html .= '</ul>';
    return $html;
}

// badgeEstado alias (compatibilidad con código previo)
function badgeEstado(string $estado): string {
    return badgeEstadoTitulo($estado);
}
