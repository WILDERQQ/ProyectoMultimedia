<?php
/**
 * titulo/generar_cert.php — Registros: emitir resolución final
 */
require_once '../includes/auth.php';
require_once '../includes/funciones.php';
requiereLogin('registros','admin');

$titulo = 'Generar Resolución — Registros';
$prefix = '../';
$u      = usuarioActual();
$todas  = leerJSON('titulos.json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id  = $_POST['id'] ?? '';
    $idx = array_search($id, array_column($todas, 'id'));
    if ($idx !== false && $todas[$idx]['estado'] === 'en_registros') {
        $nro = 'RES-' . date('Y') . '-' . strtoupper(substr(uniqid(), -5));
        $texto = "Resolución N° {$nro}: Se OTORGA el Título Profesional a " .
                 $todas[$idx]['estudiante_nombre'] . " de la carrera de " .
                 $todas[$idx]['carrera'] . " con promedio " . $todas[$idx]['promedio'] .
                 ". Modalidad: " . $todas[$idx]['modalidad'] . ". Fecha: " . date('d/m/Y') . ".";
        $todas[$idx]['resolucion'] = $texto;
        agregarHistorial($todas[$idx], 'completado', $u['usuario'],
            "Resolución emitida. N° {$nro}. Título profesional generado.");
        guardarJSON('titulos.json', $todas);
        flashMsg('exito', "Resolución {$nro} emitida. El trámite de {$todas[$idx]['estudiante_nombre']} fue completado.");
    }
    header('Location: generar_cert.php');
    exit;
}

$enRegistros = array_values(array_filter($todas, fn($s) => $s['estado'] === 'en_registros'));
$completados = array_values(array_filter($todas, fn($s) => $s['estado'] === 'completado'));
?>
<?php include '../includes/header.php'; ?>

<div class="container py-4">

  <div class="d-flex align-items-center gap-3 mb-4">
    <div class="rounded-circle bg-success bg-opacity-15 d-flex align-items-center justify-content-center" style="width:52px;height:52px;">
      <i class="bi bi-archive-fill text-success fs-3"></i>
    </div>
    <div>
      <h1 class="h4 fw-bold mb-0">Generación de Resoluciones — Registros</h1>
      <p class="text-muted small mb-0"><?= count($enRegistros) ?> solicitudes aprobadas para emitir resolución</p>
    </div>
  </div>

  <!-- BPMN: Paso 5 -->
  <div class="card mb-4">
    <div class="card-body">
      <?= renderStepper([
          ['estado'=>'enviado',       'label'=>'1. Solicitud',      'icono'=>'send-fill'],
          ['estado'=>'en_revision',   'label'=>'2. Secretaría',     'icono'=>'search'],
          ['estado'=>'verificado',    'label'=>'3. Verificado',     'icono'=>'file-check'],
          ['estado'=>'en_decanatura', 'label'=>'4. Decano',         'icono'=>'award'],
          ['estado'=>'en_registros',  'label'=>'5. Tu Acción\n(Registros)', 'icono'=>'archive'],
          ['estado'=>'completado',    'label'=>'6. Completado',     'icono'=>'check-circle-fill'],
      ], 'en_registros') ?>
      <p class="text-muted small text-center mb-0">
        Estás en el <strong>último paso</strong>. Genera la resolución oficial y completa el trámite.
      </p>
    </div>
  </div>

  <?php if (empty($enRegistros)): ?>
  <div class="alert alert-info">
    <i class="bi bi-info-circle me-1"></i>No hay solicitudes pendientes de generación de resolución.
  </div>
  <?php endif; ?>

  <?php foreach ($enRegistros as $s): ?>
  <div class="card mb-4 border-success">
    <div class="card-header fw-semibold d-flex justify-content-between align-items-center"
         style="background:#f0fdf4;">
      <span>
        <i class="bi bi-check-circle-fill text-success me-2"></i>
        <code><?= limpiar($s['id']) ?></code> — Aprobado por Decano
      </span>
      <span class="text-muted small"><?= fechaLegible($s['fecha_actualizacion']) ?></span>
    </div>
    <div class="card-body">
      <div class="row g-3 mb-3">
        <div class="col-sm-4">
          <div class="text-muted small">Estudiante</div>
          <strong><?= limpiar($s['estudiante_nombre']) ?></strong><br>
          <small class="text-muted">CI: <?= limpiar($s['carnet']) ?></small>
        </div>
        <div class="col-sm-4">
          <div class="text-muted small">Carrera</div>
          <div><?= limpiar($s['carrera']) ?></div>
          <div class="small text-muted"><?= limpiar($s['modalidad']) ?></div>
        </div>
        <div class="col-sm-2">
          <div class="text-muted small">Promedio</div>
          <div class="fw-bold text-success fs-5"><?= limpiar($s['promedio']) ?></div>
        </div>
        <div class="col-sm-2 text-end">
          <a href="seguimiento.php?id=<?= urlencode($s['id']) ?>" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-clock-history"></i>
          </a>
        </div>
      </div>

      <!-- Vista previa de la resolución a emitir -->
      <div class="alert alert-light border py-3 mb-3">
        <p class="small fw-medium mb-1"><i class="bi bi-file-earmark-text me-1"></i>Vista previa de resolución:</p>
        <p class="small mb-0 fst-italic">
          "Se OTORGA el Título Profesional a <strong><?= limpiar($s['estudiante_nombre']) ?></strong>
          de la carrera de <strong><?= limpiar($s['carrera']) ?></strong>,
          con promedio de <strong><?= limpiar($s['promedio']) ?></strong>,
          modalidad <strong><?= limpiar($s['modalidad']) ?></strong>. Fecha: <?= date('d/m/Y') ?>."
        </p>
      </div>

      <form method="POST" onsubmit="return confirm('¿Generar y emitir resolución oficial?')">
        <input type="hidden" name="id" value="<?= limpiar($s['id']) ?>">
        <button type="submit" class="btn btn-success fw-bold" id="btn-emitir-<?= limpiar($s['id']) ?>">
          <i class="bi bi-file-earmark-check-fill me-1"></i>Emitir Resolución y Completar Trámite
        </button>
      </form>
    </div>
  </div>
  <?php endforeach; ?>

  <!-- Completados -->
  <?php if (!empty($completados)): ?>
  <div class="card mt-2">
    <div class="card-header fw-semibold"><i class="bi bi-check-all me-1 text-success"></i>Trámites Completados</div>
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead><tr><th>ID</th><th>Estudiante</th><th>Carrera</th><th>Resolución</th><th>Fecha</th></tr></thead>
        <tbody>
          <?php foreach ($completados as $s): ?>
          <tr>
            <td><code class="small"><?= limpiar($s['id']) ?></code></td>
            <td><?= limpiar($s['estudiante_nombre']) ?></td>
            <td><?= limpiar($s['carrera']) ?></td>
            <td class="small text-muted"><?= limpiar(substr($s['resolucion'],0,60)) ?>...</td>
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
