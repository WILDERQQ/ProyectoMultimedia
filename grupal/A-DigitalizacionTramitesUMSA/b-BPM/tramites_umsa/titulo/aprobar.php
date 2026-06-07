<?php
/**
 * titulo/aprobar.php — Decanatura: aprobar o rechazar solicitudes
 */
require_once '../includes/auth.php';
require_once '../includes/funciones.php';
requiereLogin('decano','admin');

$titulo = 'Aprobación de Solicitudes — Decanatura';
$prefix = '../';
$u      = usuarioActual();

$todas = leerJSON('titulos.json');

// Procesar POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id     = $_POST['id'] ?? '';
    $accion = $_POST['accion'] ?? '';
    $idx    = array_search($id, array_column($todas, 'id'));

    if ($idx !== false && in_array($accion, ['aprobar','rechazar'])) {
        $comentario = trim($_POST['comentario'] ?? '');
        if ($accion === 'aprobar') {
            agregarHistorial($todas[$idx], 'aprobado', $u['usuario'],
                'Aprobado por Decanatura. ' . $comentario);
            agregarHistorial($todas[$idx], 'en_registros', $u['usuario'],
                'Enviado a Registros para generación de resolución.');
            flashMsg('exito', "Solicitud {$id} aprobada. Pasó a Registros.");
        } else {
            $todas[$idx]['observaciones'] = $comentario;
            agregarHistorial($todas[$idx], 'rechazado', $u['usuario'],
                'Rechazado por Decanatura. ' . $comentario);
            flashMsg('error', "Solicitud {$id} rechazada.");
        }
        guardarJSON('titulos.json', $todas);
    }
    header('Location: aprobar.php');
    exit;
}

$pendientes = array_values(array_filter($todas, fn($s) => in_array($s['estado'], ['en_decanatura'])));
$historial  = array_values(array_filter($todas, fn($s) => in_array($s['estado'], ['aprobado','rechazado','en_registros','completado'])));
?>
<?php include '../includes/header.php'; ?>

<div class="container py-4">

  <div class="d-flex align-items-center gap-3 mb-4">
    <div class="rounded-circle d-flex align-items-center justify-content-center"
         style="width:52px;height:52px;background:linear-gradient(135deg,#e65100,#ef6c00);">
      <i class="bi bi-award-fill text-white fs-3"></i>
    </div>
    <div>
      <h1 class="h4 fw-bold mb-0">Aprobación de Solicitudes — Decanatura</h1>
      <p class="text-muted small mb-0"><?= count($pendientes) ?> solicitudes esperan tu resolución</p>
    </div>
  </div>

  <!-- BPMN: Paso 4 activo -->
  <div class="card mb-4">
    <div class="card-body">
      <?= renderStepper([
          ['estado'=>'enviado',       'label'=>'1. Solicitud',          'icono'=>'send-fill'],
          ['estado'=>'en_revision',   'label'=>'2. Secretaría',         'icono'=>'search'],
          ['estado'=>'verificado',    'label'=>'3. Verificado',         'icono'=>'file-check'],
          ['estado'=>'en_decanatura', 'label'=>'4. Tu Decisión\n(Decano)', 'icono'=>'award'],
          ['estado'=>'en_registros',  'label'=>'5. Registros',          'icono'=>'archive'],
          ['estado'=>'completado',    'label'=>'6. Completado',         'icono'=>'check-circle-fill'],
      ], 'en_decanatura') ?>
      <p class="text-muted small text-center mb-0">
        <i class="bi bi-info-circle me-1"></i>Revisa el expediente y decide: <strong>Aprobar</strong> (pasa a Registros) o <strong>Rechazar</strong>
      </p>
    </div>
  </div>

  <?php if (empty($pendientes)): ?>
  <div class="alert alert-info">
    <i class="bi bi-check-all me-1"></i>No hay solicitudes pendientes de aprobación para Decanatura.
  </div>
  <?php else: ?>
  <?php foreach ($pendientes as $s): ?>
  <div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
      <div>
        <code class="small"><?= limpiar($s['id']) ?></code>
        <span class="ms-2"><?= badgeEstadoTitulo($s['estado']) ?></span>
      </div>
      <span class="text-muted small"><?= fechaLegible($s['fecha_creacion']) ?></span>
    </div>
    <div class="card-body">
      <div class="row g-3 mb-3">
        <div class="col-sm-3">
          <div class="text-muted small">Estudiante</div>
          <strong><?= limpiar($s['estudiante_nombre']) ?></strong>
        </div>
        <div class="col-sm-3">
          <div class="text-muted small">Carrera</div>
          <div><?= limpiar($s['carrera']) ?></div>
        </div>
        <div class="col-sm-2">
          <div class="text-muted small">Promedio</div>
          <div class="fw-bold fs-5 text-primary"><?= limpiar($s['promedio']) ?></div>
        </div>
        <div class="col-sm-2">
          <div class="text-muted small">Modalidad</div>
          <div><?= limpiar($s['modalidad']) ?></div>
        </div>
        <div class="col-sm-2 text-end">
          <a href="detalle.php?id=<?= urlencode($s['id']) ?>" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-eye me-1"></i>Ver expediente
          </a>
        </div>
      </div>
      <?php if ($s['titulo_proyecto']): ?>
      <div class="alert alert-light py-2 mb-3">
        <span class="text-muted small fw-medium">Proyecto:</span> <?= limpiar($s['titulo_proyecto']) ?>
        <?php if ($s['tutor']): ?> <span class="text-muted ms-2">— Tutor: <?= limpiar($s['tutor']) ?></span><?php endif; ?>
      </div>
      <?php endif; ?>

      <!-- Formulario de decisión -->
      <form method="POST" class="border-top pt-3" onsubmit="return confirm('¿Confirmas esta decisión?')">
        <input type="hidden" name="id" value="<?= limpiar($s['id']) ?>">
        <div class="mb-3">
          <label class="form-label small fw-medium">Comentario / Resolución del Decano:</label>
          <textarea class="form-control" name="comentario" rows="2"
                    placeholder="Ej: Se aprueba por cumplir todos los requisitos académicos..."></textarea>
        </div>
        <div class="d-flex gap-2">
          <button type="submit" name="accion" value="aprobar" class="btn btn-success fw-bold" id="btn-aprobar-<?= limpiar($s['id']) ?>">
            <i class="bi bi-check-lg me-1"></i>Aprobar
          </button>
          <button type="submit" name="accion" value="rechazar" class="btn btn-danger fw-bold" id="btn-rechazar-<?= limpiar($s['id']) ?>">
            <i class="bi bi-x-lg me-1"></i>Rechazar
          </button>
        </div>
      </form>
    </div>
  </div>
  <?php endforeach; ?>
  <?php endif; ?>

  <!-- Historial de decisiones -->
  <?php if (!empty($historial)): ?>
  <div class="card mt-2">
    <div class="card-header fw-semibold"><i class="bi bi-archive me-1"></i>Historial de Resoluciones</div>
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead><tr><th>ID</th><th>Estudiante</th><th>Carrera</th><th>Estado</th><th>Fecha</th></tr></thead>
        <tbody>
          <?php foreach ($historial as $s): ?>
          <tr>
            <td><code class="small"><?= limpiar($s['id']) ?></code></td>
            <td><?= limpiar($s['estudiante_nombre']) ?></td>
            <td><?= limpiar($s['carrera']) ?></td>
            <td><?= badgeEstadoTitulo($s['estado']) ?></td>
            <td class="text-muted small"><?= fechaLegible($s['fecha_actualizacion']) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php endif; ?>

</div>

<?php include '../includes/footer.php'; ?>
