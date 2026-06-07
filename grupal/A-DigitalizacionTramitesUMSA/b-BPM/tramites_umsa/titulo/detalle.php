<?php
/**
 * titulo/detalle.php — Detalle de solicitud con acciones según rol
 * Secretaría: puede marcar en revisión, devolver con obs, verificar
 * Decano: ve el expediente completo
 * Registros: ve el expediente completo
 */
require_once '../includes/auth.php';
require_once '../includes/funciones.php';
requiereLogin();

$titulo = 'Detalle de Solicitud — UMSA';
$prefix = '../';
$u      = usuarioActual();
$rol    = rolActual();
$id     = $_GET['id'] ?? '';

$todas = leerJSON('titulos.json');
$idx   = array_search($id, array_column($todas, 'id'));

if ($idx === false) {
    flashMsg('error', 'Solicitud no encontrada.');
    header('Location: index.php');
    exit;
}
$sol = &$todas[$idx];

// ── Procesar acciones POST ─────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    switch ($accion) {
        case 'iniciar_revision':
            if ($rol === 'secretaria' || $rol === 'admin') {
                agregarHistorial($sol, 'en_revision', $u['usuario'], 'Secretaría inició la revisión de la solicitud.');
                guardarJSON('titulos.json', $todas);
                flashMsg('exito', 'Solicitud marcada como En Revisión.');
            }
            break;

        case 'devolver_observaciones':
            if (($rol === 'secretaria' || $rol === 'admin') && !empty(trim($_POST['observaciones']??''))) {
                $obs = trim($_POST['observaciones']);
                $sol['observaciones'] = $obs;
                agregarHistorial($sol, 'observado', $u['usuario'], "Observaciones: {$obs}");
                guardarJSON('titulos.json', $todas);
                flashMsg('aviso', 'Solicitud devuelta al estudiante con observaciones.');
            }
            break;

        case 'verificar':
            if ($rol === 'secretaria' || $rol === 'admin') {
                agregarHistorial($sol, 'verificado', $u['usuario'], 'Documentación verificada. Se envía a Decanatura.');
                guardarJSON('titulos.json', $todas);
                flashMsg('exito', 'Solicitud verificada. Pasó a Decanatura.');
            }
            break;

        case 'enviar_decano':
            if ($rol === 'secretaria' || $rol === 'admin') {
                agregarHistorial($sol, 'en_decanatura', $u['usuario'], 'Expediente enviado a Decanatura para aprobación.');
                guardarJSON('titulos.json', $todas);
                flashMsg('exito', 'Expediente enviado a Decanatura.');
            }
            break;
    }

    header('Location: detalle.php?id=' . urlencode($id));
    exit;
}

$pasos = [
    ['estado'=>'enviado',       'label'=>'1. Solicitud\nEnviada',   'icono'=>'send-fill'],
    ['estado'=>'en_revision',   'label'=>'2. Revisión\nSecretaría', 'icono'=>'search'],
    ['estado'=>'verificado',    'label'=>'3. Docs\nVerificados',    'icono'=>'file-check'],
    ['estado'=>'en_decanatura', 'label'=>'4. Decanatura',           'icono'=>'award'],
    ['estado'=>'en_registros',  'label'=>'5. Registros',            'icono'=>'archive'],
    ['estado'=>'completado',    'label'=>'6. Completado',           'icono'=>'check-circle-fill'],
];
$docsList = [
    'diploma_bachiller'    => 'Diploma de Bachiller',
    'certificado_notas'    => 'Certificado de Notas',
    'libreta_universitaria'=> 'Libreta Universitaria',
    'carnet_identidad'     => 'Fotocopia Carnet de Identidad',
    'foto_carnet'          => 'Fotografías Tamaño Carnet',
    'deposito_bancario'    => 'Depósito Bancario',
];
?>
<?php include '../includes/header.php'; ?>

<div class="container py-4" style="max-width:900px;">

  <div class="d-flex align-items-center gap-2 mb-4">
    <a href="<?= in_array($rol,['secretaria','admin']) ? 'revisar.php' : 'index.php' ?>" class="btn btn-sm btn-outline-secondary">
      <i class="bi bi-arrow-left"></i>
    </a>
    <div>
      <h1 class="h4 fw-bold mb-0">Detalle de Solicitud</h1>
      <code class="text-muted small"><?= limpiar($sol['id']) ?></code>
      <span class="ms-2"><?= badgeEstadoTitulo($sol['estado']) ?></span>
    </div>
  </div>

  <!-- Stepper con estado actual -->
  <div class="card mb-4">
    <div class="card-body">
      <?= renderStepper($pasos, in_array($sol['estado'],['rechazado','observado']) ? 'en_revision' : $sol['estado']) ?>
    </div>
  </div>

  <div class="row g-4">
    <div class="col-md-7">
      <!-- Datos del expediente -->
      <div class="card mb-4">
        <div class="card-header fw-semibold"><i class="bi bi-person me-1"></i>Datos del Estudiante</div>
        <div class="card-body">
          <dl class="row mb-0">
            <dt class="col-sm-5 text-muted small">Nombre</dt>
            <dd class="col-sm-7"><?= limpiar($sol['estudiante_nombre']) ?></dd>
            <dt class="col-sm-5 text-muted small">Carnet</dt>
            <dd class="col-sm-7"><?= limpiar($sol['carnet']) ?></dd>
            <dt class="col-sm-5 text-muted small">Carrera</dt>
            <dd class="col-sm-7"><?= limpiar($sol['carrera']) ?></dd>
            <dt class="col-sm-5 text-muted small">Facultad</dt>
            <dd class="col-sm-7"><?= limpiar($sol['facultad'] ?: '—') ?></dd>
            <dt class="col-sm-5 text-muted small">Fecha de Egreso</dt>
            <dd class="col-sm-7"><?= limpiar($sol['fecha_egreso']) ?></dd>
            <dt class="col-sm-5 text-muted small">Promedio</dt>
            <dd class="col-sm-7"><strong><?= limpiar($sol['promedio']) ?></strong></dd>
            <dt class="col-sm-5 text-muted small">Modalidad</dt>
            <dd class="col-sm-7"><?= limpiar($sol['modalidad']) ?></dd>
            <?php if ($sol['titulo_proyecto']): ?>
            <dt class="col-sm-5 text-muted small">Proyecto/Tesis</dt>
            <dd class="col-sm-7"><?= limpiar($sol['titulo_proyecto']) ?></dd>
            <dt class="col-sm-5 text-muted small">Tutor</dt>
            <dd class="col-sm-7"><?= limpiar($sol['tutor'] ?: '—') ?></dd>
            <?php endif; ?>
            <dt class="col-sm-5 text-muted small">Fecha Solicitud</dt>
            <dd class="col-sm-7"><?= fechaLegible($sol['fecha_creacion']) ?></dd>
          </dl>
        </div>
      </div>

      <!-- Documentos adjuntos -->
      <div class="card mb-4">
        <div class="card-header fw-semibold"><i class="bi bi-paperclip me-1"></i>Documentos Adjuntos</div>
        <div class="card-body">
          <?php foreach ($docsList as $key => $label): ?>
          <div class="d-flex align-items-center gap-2 mb-2">
            <?php if (in_array($key, $sol['documentos'] ?? [])): ?>
              <i class="bi bi-check-circle-fill text-success"></i>
              <span class="small"><?= $label ?></span>
            <?php else: ?>
              <i class="bi bi-x-circle text-muted"></i>
              <span class="small text-muted"><?= $label ?></span>
            <?php endif; ?>
          </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Observaciones previas -->
      <?php if ($sol['observaciones']): ?>
      <div class="alert alert-warning">
        <strong><i class="bi bi-exclamation-triangle me-1"></i>Observaciones registradas:</strong>
        <p class="mb-0 mt-1"><?= limpiar($sol['observaciones']) ?></p>
      </div>
      <?php endif; ?>

      <!-- Acciones de Secretaría -->
      <?php if (in_array($rol, ['secretaria','admin'])): ?>
      <div class="card">
        <div class="card-header fw-semibold text-info">
          <i class="bi bi-person-check me-1"></i>Acciones de Secretaría
        </div>
        <div class="card-body">

          <?php if ($sol['estado'] === 'enviado'): ?>
          <form method="POST">
            <input type="hidden" name="accion" value="iniciar_revision">
            <button type="submit" class="btn btn-info text-white w-100 mb-2" id="btn-iniciar-revision">
              <i class="bi bi-search me-1"></i>Iniciar Revisión
            </button>
          </form>
          <?php endif; ?>

          <?php if (in_array($sol['estado'], ['en_revision','observado'])): ?>
          <!-- Devolver con observaciones -->
          <form method="POST" class="mb-3">
            <input type="hidden" name="accion" value="devolver_observaciones">
            <label class="form-label small fw-medium">Devolver con observaciones:</label>
            <textarea class="form-control mb-2" name="observaciones" rows="3" required
                      placeholder="Describe qué documentos faltan o qué corregir..." id="obs-text"><?= limpiar($sol['observaciones']) ?></textarea>
            <button type="submit" class="btn btn-warning w-100" id="btn-devolver">
              <i class="bi bi-arrow-return-left me-1"></i>Devolver al Estudiante
            </button>
          </form>

          <!-- Verificar y pasar a Decano -->
          <form method="POST">
            <input type="hidden" name="accion" value="verificar">
            <button type="submit" class="btn btn-success w-100" id="btn-verificar"
                    onclick="return confirm('¿Confirmas que toda la documentación está completa y correcta?')">
              <i class="bi bi-check2-all me-1"></i>Verificar y Enviar a Decanatura
            </button>
          </form>
          <?php endif; ?>

          <?php if ($sol['estado'] === 'verificado'): ?>
          <form method="POST">
            <input type="hidden" name="accion" value="enviar_decano">
            <button type="submit" class="btn btn-primary w-100" id="btn-enviar-decano">
              <i class="bi bi-award me-1"></i>Enviar a Decanatura
            </button>
          </form>
          <?php endif; ?>

          <?php if (!in_array($sol['estado'], ['enviado','en_revision','verificado','observado'])): ?>
          <p class="text-muted small mb-0">Esta solicitud está en manos de <?= $sol['estado'] ?>. Sin acciones disponibles.</p>
          <?php endif; ?>
        </div>
      </div>
      <?php endif; ?>

    </div>

    <!-- Historial -->
    <div class="col-md-5">
      <div class="card">
        <div class="card-header fw-semibold"><i class="bi bi-clock-history me-1"></i>Historial del Proceso</div>
        <div class="card-body">
          <?= renderTimeline($sol['historial'] ?? []) ?>
        </div>
      </div>
    </div>
  </div>

</div>

<?php include '../includes/footer.php'; ?>
