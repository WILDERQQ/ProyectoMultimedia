<?php
/**
 * titulo/seguimiento.php — Seguimiento de estado (timeline visual)
 */
require_once '../includes/auth.php';
require_once '../includes/funciones.php';
requiereLogin();

$titulo = 'Seguimiento de Solicitud — UMSA';
$prefix = '../';
$u      = usuarioActual();
$id     = $_GET['id'] ?? '';
$todas  = leerJSON('titulos.json');
$idx    = array_search($id, array_column($todas, 'id'));

if ($idx === false) {
    flashMsg('error', 'Solicitud no encontrada.');
    header('Location: index.php');
    exit;
}
$sol = $todas[$idx];

$pasos = [
    ['estado'=>'enviado',       'label'=>'Solicitud Enviada',           'icono'=>'send-fill',         'desc'=>'El estudiante presentó su solicitud con la documentación requerida.'],
    ['estado'=>'en_revision',   'label'=>'Revisión en Secretaría',      'icono'=>'search',             'desc'=>'La Secretaría de Carrera está revisando los documentos adjuntos.'],
    ['estado'=>'verificado',    'label'=>'Documentos Verificados',      'icono'=>'file-check-fill',    'desc'=>'Secretaría confirmó que toda la documentación está completa y correcta.'],
    ['estado'=>'en_decanatura', 'label'=>'En Revisión de Decanatura',   'icono'=>'award',              'desc'=>'El expediente está siendo revisado por el Decano para su aprobación.'],
    ['estado'=>'en_registros',  'label'=>'Generación de Resolución',    'icono'=>'file-earmark-text',  'desc'=>'Registros está procesando y generando la resolución y el título.'],
    ['estado'=>'completado',    'label'=>'Trámite Completado',          'icono'=>'check-circle-fill',  'desc'=>'El título profesional fue emitido exitosamente.'],
];
$estadosOrden = array_column($pasos, 'estado');
$idxActual    = array_search($sol['estado'], $estadosOrden) ?? -1;
if ($sol['estado'] === 'rechazado') $idxActual = -1;
if ($sol['estado'] === 'observado') $idxActual = 1; // En revisión
?>
<?php include '../includes/header.php'; ?>

<div class="container py-4" style="max-width:760px;">

  <div class="d-flex align-items-center gap-2 mb-4">
    <a href="mis_solicitudes.php" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i></a>
    <div>
      <h1 class="h4 fw-bold mb-0"><i class="bi bi-clock-history me-2 text-primary"></i>Seguimiento de Trámite</h1>
      <code class="text-muted small"><?= limpiar($sol['id']) ?></code>
      <span class="ms-2"><?= badgeEstadoTitulo($sol['estado']) ?></span>
    </div>
  </div>

  <!-- Datos resumidos -->
  <div class="card mb-4">
    <div class="card-body p-3">
      <div class="row g-2 align-items-center">
        <div class="col-sm-4">
          <div class="text-muted small">Estudiante</div>
          <div class="fw-semibold"><?= limpiar($sol['estudiante_nombre']) ?></div>
        </div>
        <div class="col-sm-4">
          <div class="text-muted small">Carrera / Modalidad</div>
          <div><?= limpiar($sol['carrera']) ?> — <?= limpiar($sol['modalidad']) ?></div>
        </div>
        <div class="col-sm-4">
          <div class="text-muted small">Fecha de inicio</div>
          <div><?= fechaLegible($sol['fecha_creacion']) ?></div>
        </div>
      </div>
    </div>
  </div>

  <?php if ($sol['estado'] === 'rechazado'): ?>
  <div class="alert alert-danger mb-4">
    <strong><i class="bi bi-x-circle-fill me-1"></i>Solicitud Rechazada</strong>
    <p class="mb-0 mt-1"><?= limpiar($sol['observaciones'] ?? 'Sin comentarios.') ?></p>
  </div>
  <?php endif; ?>

  <?php if ($sol['estado'] === 'completado'): ?>
  <div class="alert alert-success mb-4">
    <strong><i class="bi bi-check-circle-fill me-1"></i>¡Trámite Completado Exitosamente!</strong>
    <p class="mb-1 mt-1"><?= limpiar($sol['resolucion']) ?></p>
  </div>
  <?php endif; ?>

  <!-- Timeline de pasos del BPMN -->
  <div class="card mb-4">
    <div class="card-header fw-semibold"><i class="bi bi-diagram-3 me-1"></i>Estado del Proceso</div>
    <div class="card-body py-4">
      <?php foreach ($pasos as $i => $paso):
            $done   = $i < $idxActual;
            $active = $i === $idxActual;
            $upcom  = $i > $idxActual;
            $iconBg = $done ? '#198754' : ($active ? '#003082' : '#dee2e6');
            $txtC   = ($done || $active) ? '#fff' : '#adb5bd';
            $lblC   = $done ? '#198754' : ($active ? '#003082' : '#adb5bd');
            // Buscar entrada en historial
            $hEntry = null;
            foreach ($sol['historial'] as $h) {
                if ($h['estado'] === $paso['estado']) { $hEntry = $h; break; }
            }
      ?>
      <div class="d-flex gap-3 mb-<?= $i < count($pasos)-1 ? '0' : '0' ?>">
        <!-- Columna izquierda: ícono + línea -->
        <div class="d-flex flex-column align-items-center" style="min-width:44px;">
          <div class="d-flex align-items-center justify-content-center rounded-circle flex-shrink-0"
               style="width:44px;height:44px;background:<?= $iconBg ?>;color:<?= $txtC ?>;">
            <?php if ($done): ?>
            <i class="bi bi-check-lg"></i>
            <?php else: ?>
            <i class="bi bi-<?= $paso['icono'] ?>"></i>
            <?php endif; ?>
          </div>
          <?php if ($i < count($pasos)-1): ?>
          <div style="width:2px;flex:1;min-height:32px;background:<?= $done ? '#198754' : '#dee2e6' ?>;margin:4px 0;"></div>
          <?php endif; ?>
        </div>
        <!-- Columna derecha: texto -->
        <div class="pb-4">
          <div class="fw-semibold" style="color:<?= $lblC ?>;"><?= limpiar($paso['label']) ?></div>
          <div class="text-muted small"><?= $paso['desc'] ?></div>
          <?php if ($hEntry): ?>
          <div class="mt-1 small">
            <span class="badge bg-light text-dark border">
              <i class="bi bi-calendar3 me-1"></i><?= fechaLegible($hEntry['fecha']) ?>
            </span>
            <?php if ($hEntry['comentario']): ?>
            <span class="text-muted ms-1 fst-italic"><?= limpiar(substr($hEntry['comentario'],0,80)) ?></span>
            <?php endif; ?>
          </div>
          <?php elseif ($active): ?>
          <div class="mt-1">
            <span class="badge bg-primary bg-opacity-10 text-primary">
              <i class="bi bi-hourglass-split me-1"></i>En proceso...
            </span>
          </div>
          <?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Historial completo -->
  <div class="card">
    <div class="card-header fw-semibold"><i class="bi bi-journal-text me-1"></i>Historial Completo</div>
    <div class="card-body">
      <?= renderTimeline($sol['historial'] ?? []) ?>
    </div>
  </div>

</div>

<?php include '../includes/footer.php'; ?>
