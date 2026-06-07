<?php
/**
 * titulo/mis_solicitudes.php — Ver solicitudes del estudiante
 */
require_once '../includes/auth.php';
require_once '../includes/funciones.php';
requiereLogin('estudiante','admin');

$titulo = 'Mis Solicitudes de Título — UMSA';
$prefix = '../';
$u      = usuarioActual();

$todas = leerJSON('titulos.json');
$mias  = array_values(array_filter($todas, fn($s) => $s['estudiante_usuario'] === $u['usuario']));
usort($mias, fn($a,$b) => strcmp($b['fecha_creacion'], $a['fecha_creacion']));

$pasos = [
    ['estado'=>'enviado',       'label'=>'Solicitud\nEnviada',   'icono'=>'send-fill'],
    ['estado'=>'en_revision',   'label'=>'Revisión\nSecretaría', 'icono'=>'search'],
    ['estado'=>'verificado',    'label'=>'Docs\nVerificados',    'icono'=>'file-check'],
    ['estado'=>'en_decanatura', 'label'=>'Decanatura',           'icono'=>'award'],
    ['estado'=>'en_registros',  'label'=>'Registros',            'icono'=>'archive'],
    ['estado'=>'completado',    'label'=>'Completado',           'icono'=>'check-circle-fill'],
];
?>
<?php include '../includes/header.php'; ?>

<div class="container py-4">

  <div class="d-flex align-items-center justify-content-between mb-4">
    <div>
      <h1 class="h4 fw-bold mb-0"><i class="bi bi-list-ul me-2 text-primary"></i>Mis Solicitudes de Título</h1>
      <p class="text-muted small mb-0"><?= count($mias) ?> solicitud(es) registradas para <?= limpiar($u['nombre']) ?></p>
    </div>
    <a href="nueva_solicitud.php" class="btn btn-primary fw-bold" id="btn-nueva-desde-lista">
      <i class="bi bi-plus-circle me-1"></i>Nueva
    </a>
  </div>

  <?php if (empty($mias)): ?>
  <div class="card">
    <div class="card-body text-center py-5">
      <i class="bi bi-inbox text-muted" style="font-size:3rem;"></i>
      <h3 class="h5 mt-3 text-muted">No tienes solicitudes</h3>
      <p class="text-muted">Aún no has iniciado ningún trámite de título profesional.</p>
      <a href="nueva_solicitud.php" class="btn btn-primary" id="btn-crear-primera">
        <i class="bi bi-plus-circle me-1"></i>Crear primera solicitud
      </a>
    </div>
  </div>
  <?php else: ?>

  <?php foreach ($mias as $sol): ?>
  <div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
      <div>
        <code class="small"><?= limpiar($sol['id']) ?></code>
        <span class="ms-2"><?= badgeEstadoTitulo($sol['estado']) ?></span>
        <?php if ($sol['estado'] === 'observado'): ?>
        <span class="badge bg-danger ms-1"><i class="bi bi-exclamation-triangle me-1"></i>Requiere correcciones</span>
        <?php endif; ?>
      </div>
      <div class="text-muted small"><?= fechaLegible($sol['fecha_creacion']) ?></div>
    </div>
    <div class="card-body">
      <!-- Stepper con estado actual -->
      <?= renderStepper($pasos, $sol['estado'] === 'rechazado' ? 'enviado' : $sol['estado']) ?>

      <div class="row g-3">
        <div class="col-sm-4">
          <div class="text-muted small fw-medium">Carrera</div>
          <div><?= limpiar($sol['carrera']) ?></div>
        </div>
        <div class="col-sm-4">
          <div class="text-muted small fw-medium">Modalidad</div>
          <div><?= limpiar($sol['modalidad']) ?></div>
        </div>
        <div class="col-sm-4">
          <div class="text-muted small fw-medium">Promedio</div>
          <div><?= limpiar($sol['promedio']) ?></div>
        </div>
        <?php if ($sol['titulo_proyecto']): ?>
        <div class="col-12">
          <div class="text-muted small fw-medium">Título del Proyecto</div>
          <div><?= limpiar($sol['titulo_proyecto']) ?></div>
        </div>
        <?php endif; ?>
      </div>

      <?php if ($sol['estado'] === 'observado' && $sol['observaciones']): ?>
      <div class="alert alert-warning mt-3 mb-0">
        <div class="fw-semibold"><i class="bi bi-exclamation-triangle me-1"></i>Observaciones de Secretaría:</div>
        <p class="mb-0 mt-1"><?= limpiar($sol['observaciones']) ?></p>
      </div>
      <?php endif; ?>

      <?php if ($sol['estado'] === 'rechazado'): ?>
      <div class="alert alert-danger mt-3 mb-0">
        <div class="fw-semibold"><i class="bi bi-x-circle me-1"></i>Solicitud Rechazada</div>
        <p class="mb-0 mt-1"><?= limpiar($sol['observaciones'] ?? 'Sin comentarios adicionales.') ?></p>
      </div>
      <?php endif; ?>

      <?php if ($sol['estado'] === 'completado'): ?>
      <div class="alert alert-success mt-3 mb-0">
        <i class="bi bi-check-circle-fill me-1"></i>
        <strong>¡Trámite completado!</strong> <?= limpiar($sol['resolucion'] ?? 'Tu título fue procesado exitosamente.') ?>
      </div>
      <?php endif; ?>
    </div>
    <div class="card-footer d-flex gap-2 justify-content-end">
      <a href="seguimiento.php?id=<?= urlencode($sol['id']) ?>" class="btn btn-sm btn-outline-primary" id="btn-seguimiento-<?= limpiar($sol['id']) ?>">
        <i class="bi bi-clock-history me-1"></i>Ver Historial
      </a>
      <a href="detalle.php?id=<?= urlencode($sol['id']) ?>" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-eye me-1"></i>Detalle
      </a>
    </div>
  </div>
  <?php endforeach; ?>
  <?php endif; ?>

  <div class="mt-3">
    <a href="index.php" class="text-decoration-none text-muted">
      <i class="bi bi-arrow-left me-1"></i>Volver al inicio del trámite
    </a>
  </div>

</div>

<?php include '../includes/footer.php'; ?>
