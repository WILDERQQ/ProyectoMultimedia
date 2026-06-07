<?php
/**
 * certificado/seguimiento.php — Timeline visual del estado del certificado
 */
require_once '../includes/auth.php';
require_once '../includes/funciones.php';
requiereLogin();

$titulo = 'Seguimiento de Certificado — UMSA';
$prefix = '../';
$u      = usuarioActual();
$id     = $_GET['id'] ?? '';
$todas  = leerJSON('certificados.json');
$idx    = array_search($id, array_column($todas, 'id'));

if ($idx === false) {
    flashMsg('error', 'Solicitud no encontrada.');
    header('Location: index.php');
    exit;
}
$sol = $todas[$idx];

$pasos = [
    ['estado'=>'enviado',         'label'=>'Solicitud Enviada',             'icono'=>'send-fill',
     'desc'=>'El solicitante presentó sus datos personales y el motivo de la solicitud.'],
    ['estado'=>'en_verificacion', 'label'=>'Verificación en Secretaría',    'icono'=>'search',
     'desc'=>'Secretaría buscó en los registros universitarios.'],
    ['estado'=>'sin_proceso',     'label'=>'Sin Proceso Confirmado',        'icono'=>'shield-check',
     'desc'=>'Se confirmó que el solicitante NO tiene proceso universitario activo.'],
    ['estado'=>'en_legal',        'label'=>'Validación Legal',              'icono'=>'shield-fill-check',
     'desc'=>'El Departamento Legal está validando y preparando la firma digital.'],
    ['estado'=>'completado',      'label'=>'Certificado Emitido',           'icono'=>'file-earmark-check-fill',
     'desc'=>'El certificado de No Proceso fue emitido y firmado digitalmente.'],
];
$estadosOrden = array_column($pasos, 'estado');
$idxActual    = array_search($sol['estado'], $estadosOrden);
if ($idxActual === false) $idxActual = 0;
if (in_array($sol['estado'], ['no_aplica','tiene_proceso'])) $idxActual = 1;
?>
<?php include '../includes/header.php'; ?>

<div class="container py-4" style="max-width:760px;">

  <div class="d-flex align-items-center gap-2 mb-4">
    <a href="mis_solicitudes.php" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i></a>
    <div>
      <h1 class="h4 fw-bold mb-0"><i class="bi bi-clock-history me-2" style="color:#00897b;"></i>Seguimiento de Certificado</h1>
      <code class="text-muted small"><?= limpiar($sol['id']) ?></code>
      <span class="ms-2"><?= badgeEstadoCert($sol['estado']) ?></span>
    </div>
  </div>

  <!-- Datos -->
  <div class="card mb-4">
    <div class="card-body p-3">
      <div class="row g-2">
        <div class="col-sm-4">
          <div class="text-muted small">Solicitante</div>
          <div class="fw-semibold"><?= limpiar($sol['nombre_completo']) ?></div>
        </div>
        <div class="col-sm-4">
          <div class="text-muted small">C.I. / Ciudad</div>
          <div><?= limpiar($sol['carnet_identidad']) ?> / <?= limpiar($sol['expedido_en']) ?></div>
        </div>
        <div class="col-sm-4">
          <div class="text-muted small">Fecha de solicitud</div>
          <div><?= fechaLegible($sol['fecha_creacion']) ?></div>
        </div>
      </div>
    </div>
  </div>

  <!-- Alerta de resultado final -->
  <?php if ($sol['estado'] === 'completado'): ?>
  <div class="card mb-4 border-success" style="background:#f0fdf4;">
    <div class="card-body p-4">
      <div class="d-flex align-items-center gap-3 mb-3">
        <i class="bi bi-file-earmark-check-fill text-success" style="font-size:2.5rem;"></i>
        <div>
          <h2 class="h5 fw-bold text-success mb-0">¡Certificado Emitido Exitosamente!</h2>
          <p class="text-muted small mb-0">Tu certificado fue validado y firmado digitalmente por el Departamento Legal.</p>
        </div>
      </div>
      <div class="p-3 bg-white rounded border" style="font-family:serif;font-size:.85rem;line-height:1.8;">
        <?= nl2br(limpiar($sol['certificado_url'])) ?>
      </div>
      <div class="mt-3 text-center">
        <button class="btn btn-success fw-bold" onclick="window.print()">
          <i class="bi bi-printer me-1"></i>Imprimir Certificado
        </button>
      </div>
    </div>
  </div>
  <?php elseif ($sol['estado'] === 'no_aplica'): ?>
  <div class="alert alert-danger mb-4">
    <strong><i class="bi bi-x-circle-fill me-1"></i>Certificado No Aplica</strong>
    <p class="mb-0 mt-1">
      Secretaría encontró que tienes un proceso universitario activo en los registros.
      Por este motivo, no es posible emitir el Certificado de No Proceso Universitario.
    </p>
  </div>
  <?php endif; ?>

  <!-- Timeline de pasos -->
  <div class="card mb-4">
    <div class="card-header fw-semibold"><i class="bi bi-diagram-3 me-1"></i>Estado del Proceso</div>
    <div class="card-body py-4">
      <?php foreach ($pasos as $i => $paso):
            if ($sol['estado'] === 'no_aplica' && $i >= 2) continue; // No mostrar pasos del camino correcto si fue rechazado
            $done   = $i < $idxActual;
            $active = $i === $idxActual;
            $iconBg = $done ? '#198754' : ($active ? '#00695c' : '#dee2e6');
            $txtC   = ($done || $active) ? '#fff' : '#adb5bd';
            $lblC   = $done ? '#198754' : ($active ? '#00695c' : '#adb5bd');
            $hEntry = null;
            foreach ($sol['historial'] as $h) {
                if ($h['estado'] === $paso['estado']) { $hEntry = $h; break; }
            }
      ?>
      <div class="d-flex gap-3">
        <div class="d-flex flex-column align-items-center" style="min-width:44px;">
          <div class="d-flex align-items-center justify-content-center rounded-circle"
               style="width:44px;height:44px;background:<?= $iconBg ?>;color:<?= $txtC ?>;">
            <?php if ($done): ?>
            <i class="bi bi-check-lg"></i>
            <?php else: ?>
            <i class="bi bi-<?= $paso['icono'] ?>"></i>
            <?php endif; ?>
          </div>
          <?php if ($i < count($pasos)-1 && !($sol['estado'] === 'no_aplica' && $i >= 1)): ?>
          <div style="width:2px;flex:1;min-height:32px;background:<?= $done ? '#198754' : '#dee2e6' ?>;margin:4px 0;"></div>
          <?php endif; ?>
        </div>
        <div class="pb-4">
          <div class="fw-semibold" style="color:<?= $lblC ?>;"><?= limpiar($paso['label']) ?></div>
          <div class="text-muted small"><?= $paso['desc'] ?></div>
          <?php if ($hEntry): ?>
          <div class="mt-1">
            <span class="badge bg-light text-dark border small">
              <i class="bi bi-calendar3 me-1"></i><?= fechaLegible($hEntry['fecha']) ?>
            </span>
          </div>
          <?php elseif ($active && !in_array($sol['estado'],['no_aplica','completado'])): ?>
          <div class="mt-1">
            <span class="badge bg-opacity-10 small" style="background:#00695c;color:#00695c;border:1px solid #00695c;">
              <i class="bi bi-hourglass-split me-1"></i>En proceso...
            </span>
          </div>
          <?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Historial -->
  <div class="card">
    <div class="card-header fw-semibold"><i class="bi bi-journal-text me-1"></i>Historial Completo</div>
    <div class="card-body"><?= renderTimeline($sol['historial'] ?? []) ?></div>
  </div>

</div>

<?php include '../includes/footer.php'; ?>
