<?php
/**
 * titulo/index.php — Dashboard del Trámite de Título Profesional
 * Muestra vista diferente según el rol del usuario
 */
require_once '../includes/auth.php';
require_once '../includes/funciones.php';
requiereLogin(); // cualquier rol logueado

$titulo = 'Trámite de Título Profesional — UMSA';
$prefix = '../';
$rol    = rolActual();
$u      = usuarioActual();

$solicitudes = leerJSON('titulos.json');

// Pasos del flujo BPMN (para el stepper visual)
$pasos = [
    ['estado'=>'enviado',       'label'=>'1. Solicitud\nEnviada',     'icono'=>'send-fill'],
    ['estado'=>'en_revision',   'label'=>'2. Revisión\nSecretaría',   'icono'=>'search'],
    ['estado'=>'verificado',    'label'=>'3. Docs.\nVerificados',     'icono'=>'file-check'],
    ['estado'=>'en_decanatura', 'label'=>'4. Revisión\nDecanatura',   'icono'=>'award'],
    ['estado'=>'en_registros',  'label'=>'5. Generación\nResolución', 'icono'=>'file-earmark-text'],
    ['estado'=>'completado',    'label'=>'6. Trámite\nCompletado',    'icono'=>'check-circle-fill'],
];

// Filtrar solicitudes por rol
$misSolicitudes = [];
switch ($rol) {
    case 'estudiante':
        $misSolicitudes = array_filter($solicitudes, fn($s) => $s['estudiante_usuario'] === $u['usuario']);
        break;
    case 'secretaria':
    case 'admin':
        $misSolicitudes = array_filter($solicitudes, fn($s) => in_array($s['estado'], ['enviado','en_revision','observado','verificado']));
        break;
    case 'decano':
        $misSolicitudes = array_filter($solicitudes, fn($s) => in_array($s['estado'], ['verificado','en_decanatura']));
        break;
    case 'registros':
        $misSolicitudes = array_filter($solicitudes, fn($s) => in_array($s['estado'], ['aprobado','en_registros']));
        break;
}
$misSolicitudes = array_values($misSolicitudes);
?>
<?php include '../includes/header.php'; ?>

<div class="container py-4">

  <!-- Banner del trámite -->
  <div class="card mb-4" style="background:linear-gradient(135deg,#003082 0%,#0d47a1 100%);color:#fff;border-radius:14px;">
    <div class="card-body p-4">
      <div class="row align-items-center">
        <div class="col">
          <div class="d-flex align-items-center gap-3">
            <i class="bi bi-mortarboard-fill" style="font-size:2.5rem;color:#ffc107;"></i>
            <div>
              <h1 class="h4 fw-bold mb-0">Solicitud de Título Profesional</h1>
              <p class="mb-0 opacity-75">Bienvenido, <?= limpiar($u['nombre']) ?> — Rol: <strong><?= etiquetaRol($rol) ?></strong></p>
            </div>
          </div>
        </div>
        <?php if ($rol === 'estudiante'): ?>
        <div class="col-auto">
          <a href="nueva_solicitud.php" class="btn btn-warning fw-bold" id="btn-nueva-solicitud-titulo">
            <i class="bi bi-plus-circle me-1"></i>Nueva Solicitud
          </a>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Flujo BPMN Visual -->
  <div class="card mb-4">
    <div class="card-header fw-semibold">
      <i class="bi bi-diagram-3 me-1"></i>Flujo del Proceso (BPMN)
    </div>
    <div class="card-body">
      <?= renderStepper($pasos, 'enviado') /* muestra el flujo completo en gris */ ?>
      <div class="row g-3 mt-1">
        <?php
        $fases = [
          ['rol'=>'Estudiante',   'paso'=>'1. Llena formulario y adjunta documentos',              'color'=>'primary',   'icono'=>'person-fill'],
          ['rol'=>'Secretaría',   'paso'=>'2. Verifica documentos, puede devolver con obs.',        'color'=>'info',      'icono'=>'person-check-fill'],
          ['rol'=>'Decanatura',   'paso'=>'3. Revisa expediente y aprueba o rechaza',              'color'=>'warning',   'icono'=>'award-fill'],
          ['rol'=>'Registros',    'paso'=>'4. Genera resolución y emite título',                   'color'=>'success',   'icono'=>'archive-fill'],
        ];
        foreach ($fases as $f):
        ?>
        <div class="col-sm-6 col-md-3">
          <div class="p-3 rounded border border-<?= $f['color'] ?> bg-<?= $f['color'] ?> bg-opacity-5 h-100">
            <div class="d-flex align-items-center gap-2 mb-1">
              <i class="bi bi-<?= $f['icono'] ?> text-<?= $f['color'] ?>"></i>
              <span class="fw-semibold small text-<?= $f['color'] ?>"><?= $f['rol'] ?></span>
            </div>
            <p class="small text-muted mb-0"><?= $f['paso'] ?></p>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <!-- Acción principal según rol -->
  <div class="row g-3 mb-4">
    <?php if ($rol === 'estudiante'): ?>
    <div class="col-sm-6">
      <a href="nueva_solicitud.php" class="card text-decoration-none h-100" id="card-link-nueva">
        <div class="card-body d-flex align-items-center gap-3 p-3">
          <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
            <i class="bi bi-plus-circle-fill text-primary fs-4"></i>
          </div>
          <div>
            <div class="fw-bold">Nueva Solicitud</div>
            <div class="text-muted small">Iniciar trámite de título</div>
          </div>
        </div>
      </a>
    </div>
    <div class="col-sm-6">
      <a href="mis_solicitudes.php" class="card text-decoration-none h-100" id="card-link-mis-sol">
        <div class="card-body d-flex align-items-center gap-3 p-3">
          <div class="rounded-circle bg-info bg-opacity-10 d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
            <i class="bi bi-list-ul text-info fs-4"></i>
          </div>
          <div>
            <div class="fw-bold">Mis Solicitudes</div>
            <div class="text-muted small">Ver estado de mis trámites</div>
          </div>
        </div>
      </a>
    </div>
    <?php elseif ($rol === 'secretaria' || $rol === 'admin'): ?>
    <div class="col-sm-6">
      <a href="revisar.php" class="card text-decoration-none h-100" id="card-link-revisar">
        <div class="card-body d-flex align-items-center gap-3 p-3">
          <div class="rounded-circle bg-info bg-opacity-10 d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
            <i class="bi bi-check2-square text-info fs-4"></i>
          </div>
          <div>
            <div class="fw-bold">Revisar Solicitudes</div>
            <div class="text-muted small"><?= count($misSolicitudes) ?> pendientes de revisión</div>
          </div>
        </div>
      </a>
    </div>
    <?php elseif ($rol === 'decano'): ?>
    <div class="col-sm-6">
      <a href="aprobar.php" class="card text-decoration-none h-100" id="card-link-aprobar">
        <div class="card-body d-flex align-items-center gap-3 p-3">
          <div class="rounded-circle bg-warning bg-opacity-10 d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
            <i class="bi bi-award-fill text-warning fs-4"></i>
          </div>
          <div>
            <div class="fw-bold">Aprobar Solicitudes</div>
            <div class="text-muted small"><?= count($misSolicitudes) ?> para revisión del decano</div>
          </div>
        </div>
      </a>
    </div>
    <?php elseif ($rol === 'registros'): ?>
    <div class="col-sm-6">
      <a href="generar_cert.php" class="card text-decoration-none h-100" id="card-link-generar">
        <div class="card-body d-flex align-items-center gap-3 p-3">
          <div class="rounded-circle bg-success bg-opacity-10 d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
            <i class="bi bi-file-earmark-plus text-success fs-4"></i>
          </div>
          <div>
            <div class="fw-bold">Generar Resoluciones</div>
            <div class="text-muted small"><?= count($misSolicitudes) ?> aprobados por Decano</div>
          </div>
        </div>
      </a>
    </div>
    <?php endif; ?>
  </div>

  <!-- Solicitudes recientes (tabla) -->
  <?php if (!empty($misSolicitudes)): ?>
  <div class="card">
    <div class="card-header fw-semibold d-flex justify-content-between align-items-center">
      <span><i class="bi bi-clock-history me-1"></i>Solicitudes recientes</span>
      <?php if ($rol === 'secretaria'): ?>
      <a href="revisar.php" class="btn btn-sm btn-outline-primary">Ver todas</a>
      <?php endif; ?>
    </div>
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead>
          <tr>
            <th>ID</th><th>Nombre</th><th>Carrera</th><th>Modalidad</th><th>Estado</th><th>Fecha</th><th></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach (array_slice($misSolicitudes, 0, 8) as $s): ?>
          <tr>
            <td><code class="small"><?= limpiar($s['id']) ?></code></td>
            <td><?= limpiar($s['estudiante_nombre']) ?></td>
            <td><?= limpiar($s['carrera']) ?></td>
            <td><?= limpiar($s['modalidad']) ?></td>
            <td><?= badgeEstadoTitulo($s['estado']) ?></td>
            <td class="text-muted small"><?= fechaLegible($s['fecha_creacion']) ?></td>
            <td>
              <a href="detalle.php?id=<?= urlencode($s['id']) ?>" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-eye"></i> Ver
              </a>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php else: ?>
  <div class="alert alert-info">
    <i class="bi bi-info-circle me-1"></i>
    <?php if ($rol === 'estudiante'): ?>
      No tienes solicitudes registradas. <a href="nueva_solicitud.php">Crear una nueva solicitud</a>.
    <?php else: ?>
      No hay solicitudes pendientes para tu rol en este momento.
    <?php endif; ?>
  </div>
  <?php endif; ?>

</div>

<?php include '../includes/footer.php'; ?>
