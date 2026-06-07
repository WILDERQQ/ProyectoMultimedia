<?php
/**
 * certificado/mis_solicitudes.php — Ver solicitudes del solicitante
 */
require_once '../includes/auth.php';
require_once '../includes/funciones.php';
requiereLogin('estudiante','admin');

$titulo = 'Mis Solicitudes de Certificado — UMSA';
$prefix = '../';
$u      = usuarioActual();
$todas  = leerJSON('certificados.json');
$mias   = array_values(array_filter($todas, fn($s) => $s['solicitante_usuario'] === $u['usuario']));
usort($mias, fn($a,$b) => strcmp($b['fecha_creacion'],$a['fecha_creacion']));

$pasos = [
    ['estado'=>'enviado',         'label'=>'Enviado',        'icono'=>'send-fill'],
    ['estado'=>'en_verificacion', 'label'=>'Verificación',   'icono'=>'search'],
    ['estado'=>'sin_proceso',     'label'=>'Sin Proceso',    'icono'=>'shield-check'],
    ['estado'=>'en_legal',        'label'=>'Legal',          'icono'=>'shield-fill-check'],
    ['estado'=>'completado',      'label'=>'Completado',     'icono'=>'file-earmark-check-fill'],
];
?>
<?php include '../includes/header.php'; ?>

<div class="container py-4">

  <div class="d-flex align-items-center justify-content-between mb-4">
    <div>
      <h1 class="h4 fw-bold mb-0"><i class="bi bi-list-ul me-2" style="color:#00897b;"></i>Mis Solicitudes de Certificado</h1>
      <p class="text-muted small mb-0"><?= count($mias) ?> solicitud(es) para <?= limpiar($u['nombre']) ?></p>
    </div>
    <a href="nueva_solicitud.php" class="btn fw-bold text-white" id="btn-nueva-cert-lista"
       style="background:linear-gradient(135deg,#00695c,#00897b);">
      <i class="bi bi-plus-circle me-1"></i>Nueva
    </a>
  </div>

  <?php if (empty($mias)): ?>
  <div class="card">
    <div class="card-body text-center py-5">
      <i class="bi bi-file-earmark-x" style="font-size:3rem;color:#00897b;opacity:.4;"></i>
      <h3 class="h5 mt-3 text-muted">No tienes solicitudes</h3>
      <a href="nueva_solicitud.php" class="btn text-white mt-2" id="btn-crear-primera-cert"
         style="background:linear-gradient(135deg,#00695c,#00897b);">
        <i class="bi bi-plus-circle me-1"></i>Crear primera solicitud
      </a>
    </div>
  </div>
  <?php else: ?>

  <?php foreach ($mias as $sol): ?>
  <div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
      <div>
        <code class="small"><?= limpiar($sol['id']) ?></code>
        <span class="ms-2"><?= badgeEstadoCert($sol['estado']) ?></span>
        <?php if ($sol['estado'] === 'no_aplica'): ?>
        <span class="badge bg-danger ms-1">Tiene proceso activo</span>
        <?php endif; ?>
        <?php if ($sol['estado'] === 'completado'): ?>
        <span class="badge bg-success ms-1"><i class="bi bi-check-circle me-1"></i>Certificado listo</span>
        <?php endif; ?>
      </div>
      <div class="text-muted small"><?= fechaLegible($sol['fecha_creacion']) ?></div>
    </div>
    <div class="card-body">
      <?= renderStepper($pasos, in_array($sol['estado'],['no_aplica','tiene_proceso']) ? 'enviado' : $sol['estado']) ?>
      <div class="row g-2">
        <div class="col-sm-4">
          <div class="text-muted small">Nombre</div>
          <div><?= limpiar($sol['nombre_completo']) ?></div>
        </div>
        <div class="col-sm-3">
          <div class="text-muted small">Carnet</div>
          <div><?= limpiar($sol['carnet_identidad']) ?> (<?= limpiar($sol['expedido_en']) ?>)</div>
        </div>
        <div class="col-sm-5">
          <div class="text-muted small">Motivo</div>
          <div><?= limpiar($sol['motivo']) ?></div>
        </div>
      </div>

      <?php if ($sol['estado'] === 'no_aplica'): ?>
      <div class="alert alert-danger mt-3 mb-0">
        <i class="bi bi-x-circle-fill me-1"></i>
        <strong>No aplica:</strong> Se encontró un proceso universitario activo. No es posible emitir el certificado.
      </div>
      <?php elseif ($sol['estado'] === 'completado'): ?>
      <div class="alert alert-success mt-3 mb-0">
        <i class="bi bi-file-earmark-check-fill me-1"></i>
        <strong>Certificado Emitido.</strong>
        Tu certificado de No Proceso Universitario fue generado y firmado digitalmente.
        <a href="seguimiento.php?id=<?= urlencode($sol['id']) ?>" class="btn btn-sm btn-success ms-2">
          <i class="bi bi-download me-1"></i>Ver Certificado
        </a>
      </div>
      <?php endif; ?>
    </div>
    <div class="card-footer d-flex gap-2 justify-content-end">
      <a href="seguimiento.php?id=<?= urlencode($sol['id']) ?>" class="btn btn-sm btn-outline-primary" id="btn-seg-<?= limpiar($sol['id']) ?>">
        <i class="bi bi-clock-history me-1"></i>Seguimiento
      </a>
    </div>
  </div>
  <?php endforeach; ?>
  <?php endif; ?>

</div>

<?php include '../includes/footer.php'; ?>
