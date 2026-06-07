<?php
/**
 * certificado/verificar.php — Secretaría: buscar en registros y decidir gateway
 * GATEWAY BPMN: ¿Tiene proceso? SÍ → no_aplica | NO → sin_proceso (pasa a Legal)
 */
require_once '../includes/auth.php';
require_once '../includes/funciones.php';
requiereLogin('secretaria','admin');

$titulo = 'Verificar Solicitudes de Certificado — Secretaría';
$prefix = '../';
$u      = usuarioActual();
$todas  = leerJSON('certificados.json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id       = $_POST['id'] ?? '';
    $accion   = $_POST['accion'] ?? '';
    $idx      = array_search($id, array_column($todas, 'id'));

    if ($idx !== false) {
        $comentario = trim($_POST['comentario'] ?? '');

        if ($accion === 'iniciar') {
            agregarHistorial($todas[$idx], 'en_verificacion', $u['usuario'],
                'Secretaría inició la búsqueda en registros universitarios.');
        } elseif ($accion === 'sin_proceso') {
            $todas[$idx]['tiene_proceso'] = false;
            agregarHistorial($todas[$idx], 'sin_proceso', $u['usuario'],
                'Verificación completada: NO se encontró proceso activo. Pasa a validación Legal.');
            agregarHistorial($todas[$idx], 'en_legal', $u['usuario'],
                'Enviado al Departamento Legal para validación y firma.');
        } elseif ($accion === 'tiene_proceso') {
            $todas[$idx]['tiene_proceso'] = true;
            agregarHistorial($todas[$idx], 'tiene_proceso', $u['usuario'],
                'Verificación: SÍ tiene proceso universitario activo. ' . $comentario);
            agregarHistorial($todas[$idx], 'no_aplica', $u['usuario'],
                'No aplica emisión de certificado. Se notifica al solicitante.');
        }

        guardarJSON('certificados.json', $todas);
        flashMsg($accion === 'tiene_proceso' ? 'aviso' : 'exito',
            $accion === 'tiene_proceso'
                ? "El solicitante tiene proceso activo. Certificado NO emitido."
                : "Verificado sin proceso. Enviado a Legal.");
    }
    header('Location: verificar.php');
    exit;
}

$pendientes = array_values(array_filter($todas, fn($s) => in_array($s['estado'], ['enviado','en_verificacion'])));
$procesados = array_values(array_filter($todas, fn($s) => in_array($s['estado'], ['sin_proceso','en_legal','tiene_proceso','no_aplica','completado'])));
?>
<?php include '../includes/header.php'; ?>

<div class="container py-4">

  <div class="d-flex align-items-center gap-3 mb-4">
    <div class="rounded-circle bg-info bg-opacity-15 d-flex align-items-center justify-content-center" style="width:52px;height:52px;">
      <i class="bi bi-search text-info fs-3"></i>
    </div>
    <div>
      <h1 class="h4 fw-bold mb-0">Verificación de Certificados — Secretaría</h1>
      <p class="text-muted small mb-0">Busca en registros si el solicitante tiene proceso universitario activo</p>
    </div>
  </div>

  <!-- BPMN: Secretaría en paso 2 + gateway -->
  <div class="card mb-4">
    <div class="card-body">
      <?= renderStepper([
          ['estado'=>'enviado',         'label'=>'1. Solicitud',         'icono'=>'send-fill'],
          ['estado'=>'en_verificacion', 'label'=>'2. Tu Verificación',   'icono'=>'search'],
          ['estado'=>'sin_proceso',     'label'=>'3. Sin Proceso\n→Legal','icono'=>'shield-check'],
          ['estado'=>'en_legal',        'label'=>'4. Legal Firma',       'icono'=>'shield-fill-check'],
          ['estado'=>'completado',      'label'=>'5. Certificado',       'icono'=>'file-earmark-check-fill'],
      ], 'en_verificacion') ?>

      <!-- Gateway visual -->
      <div class="row g-3 mt-2">
        <div class="col-sm-6">
          <div class="p-3 rounded border border-success bg-success bg-opacity-5">
            <div class="fw-semibold text-success"><i class="bi bi-check-circle me-1"></i>Gateway: NO tiene proceso</div>
            <div class="small text-muted mt-1">→ Pasa a Departamento Legal para validación y firma del certificado</div>
          </div>
        </div>
        <div class="col-sm-6">
          <div class="p-3 rounded border border-danger bg-danger bg-opacity-5">
            <div class="fw-semibold text-danger"><i class="bi bi-x-circle me-1"></i>Gateway: SÍ tiene proceso</div>
            <div class="small text-muted mt-1">→ No se emite certificado. Se notifica al solicitante.</div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Estadísticas -->
  <div class="row g-3 mb-4">
    <div class="col-sm-4">
      <div class="card text-center">
        <div class="card-body py-3">
          <div class="display-6 fw-bold text-warning"><?= count($pendientes) ?></div>
          <div class="text-muted small">Pendientes</div>
        </div>
      </div>
    </div>
    <div class="col-sm-4">
      <div class="card text-center">
        <div class="card-body py-3">
          <div class="display-6 fw-bold text-success"><?= count(array_filter($procesados, fn($s) => !in_array($s['estado'],['no_aplica','tiene_proceso']))) ?></div>
          <div class="text-muted small">Sin proceso (a Legal)</div>
        </div>
      </div>
    </div>
    <div class="col-sm-4">
      <div class="card text-center">
        <div class="card-body py-3">
          <div class="display-6 fw-bold text-danger"><?= count(array_filter($procesados, fn($s) => in_array($s['estado'],['no_aplica','tiene_proceso']))) ?></div>
          <div class="text-muted small">Tienen proceso</div>
        </div>
      </div>
    </div>
  </div>

  <?php if (empty($pendientes)): ?>
  <div class="alert alert-info"><i class="bi bi-check-all me-1"></i>No hay solicitudes pendientes de verificación.</div>
  <?php endif; ?>

  <?php foreach ($pendientes as $s): ?>
  <div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
      <div>
        <code class="small"><?= limpiar($s['id']) ?></code>
        <span class="ms-2"><?= badgeEstadoCert($s['estado']) ?></span>
      </div>
      <span class="text-muted small"><?= fechaLegible($s['fecha_creacion']) ?></span>
    </div>
    <div class="card-body">
      <div class="row g-3 mb-3">
        <div class="col-sm-4">
          <div class="text-muted small">Solicitante</div>
          <strong><?= limpiar($s['nombre_completo']) ?></strong>
        </div>
        <div class="col-sm-3">
          <div class="text-muted small">C.I.</div>
          <div><?= limpiar($s['carnet_identidad']) ?> / <?= limpiar($s['expedido_en']) ?></div>
        </div>
        <div class="col-sm-5">
          <div class="text-muted small">Motivo de la solicitud</div>
          <div><?= limpiar($s['motivo']) ?></div>
        </div>
      </div>

      <!-- Acciones: Iniciar verificación -->
      <?php if ($s['estado'] === 'enviado'): ?>
      <form method="POST" class="mb-3">
        <input type="hidden" name="id" value="<?= limpiar($s['id']) ?>">
        <input type="hidden" name="accion" value="iniciar">
        <button type="submit" class="btn btn-info text-white" id="btn-iniciar-verif-<?= limpiar($s['id']) ?>">
          <i class="bi bi-search me-1"></i>Iniciar Búsqueda en Registros
        </button>
      </form>
      <?php endif; ?>

      <!-- Gateway: decisión -->
      <?php if ($s['estado'] === 'en_verificacion'): ?>
      <div class="alert alert-light border mb-3">
        <i class="bi bi-hourglass-split me-1 text-info"></i>
        <strong>Verificando en registros...</strong>
        <p class="small text-muted mb-0 mt-1">Consulta el sistema de registros universitarios y determina si el solicitante tiene proceso activo.</p>
      </div>
      <div class="row g-3">
        <div class="col-sm-6">
          <form method="POST">
            <input type="hidden" name="id" value="<?= limpiar($s['id']) ?>">
            <input type="hidden" name="accion" value="sin_proceso">
            <button type="submit" class="btn btn-success w-100 fw-bold"
                    id="btn-sin-proceso-<?= limpiar($s['id']) ?>"
                    onclick="return confirm('¿Confirmas que NO tiene proceso activo?')">
              <i class="bi bi-check-circle me-1"></i>NO tiene proceso → Emitir certificado
            </button>
          </form>
        </div>
        <div class="col-sm-6">
          <form method="POST">
            <input type="hidden" name="id" value="<?= limpiar($s['id']) ?>">
            <input type="hidden" name="accion" value="tiene_proceso">
            <div class="mb-2">
              <input type="text" class="form-control form-control-sm" name="comentario"
                     placeholder="Detalle del proceso encontrado...">
            </div>
            <button type="submit" class="btn btn-danger w-100 fw-bold"
                    id="btn-tiene-proceso-<?= limpiar($s['id']) ?>"
                    onclick="return confirm('¿Confirmas que SÍ tiene proceso activo?')">
              <i class="bi bi-x-circle me-1"></i>SÍ tiene proceso → No aplica
            </button>
          </form>
        </div>
      </div>
      <?php endif; ?>
    </div>
  </div>
  <?php endforeach; ?>

  <!-- Historial de procesados -->
  <?php if (!empty($procesados)): ?>
  <div class="card mt-2">
    <div class="card-header fw-semibold"><i class="bi bi-archive me-1"></i>Solicitudes Procesadas</div>
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead><tr><th>ID</th><th>Solicitante</th><th>C.I.</th><th>Resultado</th><th>Estado</th><th>Fecha</th></tr></thead>
        <tbody>
          <?php foreach ($procesados as $s): ?>
          <tr>
            <td><code class="small"><?= limpiar($s['id']) ?></code></td>
            <td><?= limpiar($s['nombre_completo']) ?></td>
            <td><?= limpiar($s['carnet_identidad']) ?></td>
            <td>
              <?php if ($s['tiene_proceso'] === false): ?>
              <span class="badge bg-success">Sin proceso</span>
              <?php elseif ($s['tiene_proceso'] === true): ?>
              <span class="badge bg-danger">Tiene proceso</span>
              <?php else: ?>
              <span class="badge bg-secondary">—</span>
              <?php endif; ?>
            </td>
            <td><?= badgeEstadoCert($s['estado']) ?></td>
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
