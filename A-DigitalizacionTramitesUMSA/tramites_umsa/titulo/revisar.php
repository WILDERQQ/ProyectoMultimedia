<?php
/**
 * titulo/revisar.php — Vista de Secretaría: revisar solicitudes enviadas
 */
require_once '../includes/auth.php';
require_once '../includes/funciones.php';
requiereLogin('secretaria','admin');

$titulo = 'Revisar Solicitudes de Título — Secretaría';
$prefix = '../';
$u      = usuarioActual();

$todas       = leerJSON('titulos.json');
$pendientes  = array_values(array_filter($todas, fn($s) => in_array($s['estado'], ['enviado','en_revision'])));
$observadas  = array_values(array_filter($todas, fn($s) => $s['estado'] === 'observado'));
$verificadas = array_values(array_filter($todas, fn($s) => $s['estado'] === 'verificado'));
?>
<?php include '../includes/header.php'; ?>

<div class="container py-4">

  <div class="d-flex align-items-center gap-3 mb-4">
    <div class="rounded-circle bg-info bg-opacity-15 d-flex align-items-center justify-content-center" style="width:52px;height:52px;">
      <i class="bi bi-person-check-fill text-info fs-3"></i>
    </div>
    <div>
      <h1 class="h4 fw-bold mb-0">Revisión de Solicitudes — Secretaría</h1>
      <p class="text-muted small mb-0">Verifica documentación y decide si pasan a Decanatura</p>
    </div>
  </div>

  <!-- Flujo BPMN (Secretaría está en paso 2) -->
  <div class="card mb-4">
    <div class="card-body">
      <?= renderStepper([
          ['estado'=>'enviado',       'label'=>'1. Solicitud\nEstudiante',  'icono'=>'person-fill'],
          ['estado'=>'en_revision',   'label'=>'2. Tu Revisión',            'icono'=>'search'],
          ['estado'=>'verificado',    'label'=>'3. Verificado\ny a Decano', 'icono'=>'file-check'],
          ['estado'=>'en_decanatura', 'label'=>'4. Decanatura',             'icono'=>'award'],
          ['estado'=>'en_registros',  'label'=>'5. Registros',              'icono'=>'archive'],
          ['estado'=>'completado',    'label'=>'6. Completado',             'icono'=>'check-circle-fill'],
      ], 'en_revision') ?>
      <p class="text-muted small text-center mb-0">
        <i class="bi bi-arrow-down me-1"></i>Tu acción: <strong>Verificar documentos</strong> → Aprobar (pasa a Decano) o Devolver con observaciones
      </p>
    </div>
  </div>

  <!-- Contadores -->
  <div class="row g-3 mb-4">
    <div class="col-sm-4">
      <div class="card text-center h-100">
        <div class="card-body py-3">
          <div class="display-6 fw-bold text-warning"><?= count($pendientes) ?></div>
          <div class="text-muted small"><i class="bi bi-hourglass-split me-1"></i>Pendientes de revisión</div>
        </div>
      </div>
    </div>
    <div class="col-sm-4">
      <div class="card text-center h-100">
        <div class="card-body py-3">
          <div class="display-6 fw-bold text-danger"><?= count($observadas) ?></div>
          <div class="text-muted small"><i class="bi bi-exclamation-triangle me-1"></i>Con observaciones</div>
        </div>
      </div>
    </div>
    <div class="col-sm-4">
      <div class="card text-center h-100">
        <div class="card-body py-3">
          <div class="display-6 fw-bold text-success"><?= count($verificadas) ?></div>
          <div class="text-muted small"><i class="bi bi-check-circle me-1"></i>Verificadas (en Decano)</div>
        </div>
      </div>
    </div>
  </div>

  <!-- Lista de pendientes -->
  <div class="card">
    <div class="card-header fw-semibold">
      <i class="bi bi-hourglass-split me-1 text-warning"></i>Solicitudes Pendientes de Revisión
    </div>
    <?php if (empty($pendientes)): ?>
    <div class="card-body text-center py-5">
      <i class="bi bi-check-all text-success" style="font-size:3rem;"></i>
      <p class="text-muted mt-2 mb-0">No hay solicitudes pendientes.</p>
    </div>
    <?php else: ?>
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead>
          <tr>
            <th>ID Solicitud</th><th>Estudiante</th><th>Carnet</th><th>Carrera</th>
            <th>Modalidad</th><th>Docs Adjuntos</th><th>Estado</th><th>Fecha</th><th>Acción</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($pendientes as $s): ?>
          <tr>
            <td><code class="small"><?= limpiar($s['id']) ?></code></td>
            <td><?= limpiar($s['estudiante_nombre']) ?></td>
            <td><?= limpiar($s['carnet']) ?></td>
            <td><?= limpiar($s['carrera']) ?></td>
            <td><?= limpiar($s['modalidad']) ?></td>
            <td>
              <span class="badge bg-secondary"><?= count($s['documentos'] ?? []) ?> doc(s)</span>
            </td>
            <td><?= badgeEstadoTitulo($s['estado']) ?></td>
            <td class="text-muted small"><?= fechaLegible($s['fecha_creacion']) ?></td>
            <td>
              <a href="detalle.php?id=<?= urlencode($s['id']) ?>" class="btn btn-sm btn-primary" id="btn-revisar-<?= limpiar($s['id']) ?>">
                <i class="bi bi-eye me-1"></i>Revisar
              </a>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>

  <!-- Con observaciones (estudiante debe corregir) -->
  <?php if (!empty($observadas)): ?>
  <div class="card mt-4">
    <div class="card-header fw-semibold">
      <i class="bi bi-exclamation-triangle me-1 text-danger"></i>Con Observaciones (esperando corrección del estudiante)
    </div>
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead>
          <tr><th>ID</th><th>Estudiante</th><th>Carrera</th><th>Observación</th><th>Acción</th></tr>
        </thead>
        <tbody>
          <?php foreach ($observadas as $s): ?>
          <tr>
            <td><code class="small"><?= limpiar($s['id']) ?></code></td>
            <td><?= limpiar($s['estudiante_nombre']) ?></td>
            <td><?= limpiar($s['carrera']) ?></td>
            <td class="text-muted small" style="max-width:200px;"><?= limpiar(substr($s['observaciones'],0,80)) ?>...</td>
            <td>
              <a href="detalle.php?id=<?= urlencode($s['id']) ?>" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-eye"></i>
              </a>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php endif; ?>

</div>

<?php include '../includes/footer.php'; ?>
