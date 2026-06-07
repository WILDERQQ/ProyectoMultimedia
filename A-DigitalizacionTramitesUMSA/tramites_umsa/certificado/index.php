<?php
/**
 * certificado/index.php — Dashboard del Certificado de No Proceso
 */
require_once '../includes/auth.php';
require_once '../includes/funciones.php';
requiereLogin();

$titulo = 'Certificado de No Proceso Universitario — UMSA';
$prefix = '../';
$rol    = rolActual();
$u      = usuarioActual();

$solicitudes = leerJSON('certificados.json');

$pasos = [
    ['estado'=>'enviado',         'label'=>'1. Solicitud\nEnviada',      'icono'=>'send-fill'],
    ['estado'=>'en_verificacion', 'label'=>'2. Verificación\nSecretaría','icono'=>'search'],
    ['estado'=>'sin_proceso',     'label'=>'3. Sin Proceso\nConfirmado', 'icono'=>'shield-check'],
    ['estado'=>'en_legal',        'label'=>'4. Validación\nLegal',       'icono'=>'shield-fill-check'],
    ['estado'=>'completado',      'label'=>'5. Certificado\nEmitido',    'icono'=>'file-earmark-check-fill'],
];

$misSolicitudes = [];
switch ($rol) {
    case 'estudiante':
    case 'admin':
        $misSolicitudes = array_filter($solicitudes, fn($s) => $s['solicitante_usuario'] === $u['usuario'] || $rol === 'admin');
        break;
    case 'secretaria':
        $misSolicitudes = array_filter($solicitudes, fn($s) => in_array($s['estado'], ['enviado','en_verificacion']));
        break;
    case 'legal':
        $misSolicitudes = array_filter($solicitudes, fn($s) => in_array($s['estado'], ['sin_proceso','en_legal']));
        break;
}
$misSolicitudes = array_values($misSolicitudes);
?>
<?php include '../includes/header.php'; ?>

<div class="container py-4">

  <!-- Banner -->
  <div class="card mb-4" style="background:linear-gradient(135deg,#00695c 0%,#00897b 100%);color:#fff;border-radius:14px;">
    <div class="card-body p-4">
      <div class="row align-items-center">
        <div class="col">
          <div class="d-flex align-items-center gap-3">
            <i class="bi bi-file-earmark-check-fill" style="font-size:2.5rem;color:#a5d6a7;"></i>
            <div>
              <h1 class="h4 fw-bold mb-0">Certificado de No Proceso Universitario</h1>
              <p class="mb-0 opacity-75">Bienvenido, <?= limpiar($u['nombre']) ?> — <?= etiquetaRol($rol) ?></p>
            </div>
          </div>
        </div>
        <?php if ($rol === 'estudiante'): ?>
        <div class="col-auto">
          <a href="nueva_solicitud.php" class="btn fw-bold text-white" id="btn-nueva-cert"
             style="background:rgba(255,255,255,.2);border:1px solid rgba(255,255,255,.4);">
            <i class="bi bi-plus-circle me-1"></i>Nueva Solicitud
          </a>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Flujo BPMN -->
  <div class="card mb-4">
    <div class="card-header fw-semibold"><i class="bi bi-diagram-3 me-1"></i>Flujo del Proceso (BPMN)</div>
    <div class="card-body">
      <?= renderStepper($pasos, 'enviado') ?>
      <div class="row g-3 mt-1">
        <?php
        $fases = [
          ['rol'=>'Solicitante', 'paso'=>'1. Llena datos personales y motivo de la solicitud',  'color'=>'primary',   'icono'=>'person-badge'],
          ['rol'=>'Secretaría',  'paso'=>'2. Verifica en registros si el solicitante tiene proceso activo', 'color'=>'info',  'icono'=>'search'],
          ['rol'=>'Legal',       'paso'=>'3-4. Valida resultado, firma digitalmente el certificado', 'color'=>'secondary','icono'=>'shield-fill-check'],
        ];
        foreach ($fases as $f):
        ?>
        <div class="col-sm-4">
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
      <!-- Gateway note -->
      <div class="alert alert-light border mt-3 mb-0 py-2">
        <i class="bi bi-signpost-2 me-1 text-warning"></i>
        <strong>Gateway exclusivo:</strong> Si el solicitante <strong>SÍ tiene proceso</strong>, se notifica que el certificado no aplica.
        Si <strong>NO tiene proceso</strong>, pasa a validación legal y se emite el certificado.
      </div>
    </div>
  </div>

  <!-- Acciones rápidas -->
  <div class="row g-3 mb-4">
    <?php if ($rol === 'estudiante'): ?>
    <div class="col-sm-6">
      <a href="nueva_solicitud.php" class="card text-decoration-none h-100" id="card-link-nueva-cert">
        <div class="card-body d-flex align-items-center gap-3 p-3">
          <div class="rounded-circle d-flex align-items-center justify-content-center"
               style="width:48px;height:48px;background:#e0f2f1;">
            <i class="bi bi-plus-circle-fill" style="color:#00897b;font-size:1.4rem;"></i>
          </div>
          <div>
            <div class="fw-bold">Nueva Solicitud</div>
            <div class="text-muted small">Solicitar certificado de no proceso</div>
          </div>
        </div>
      </a>
    </div>
    <div class="col-sm-6">
      <a href="mis_solicitudes.php" class="card text-decoration-none h-100" id="card-link-mis-cert">
        <div class="card-body d-flex align-items-center gap-3 p-3">
          <div class="rounded-circle d-flex align-items-center justify-content-center"
               style="width:48px;height:48px;background:#e0f2f1;">
            <i class="bi bi-list-ul" style="color:#00897b;font-size:1.4rem;"></i>
          </div>
          <div>
            <div class="fw-bold">Mis Solicitudes</div>
            <div class="text-muted small">Ver estado de mis certificados</div>
          </div>
        </div>
      </a>
    </div>
    <?php elseif ($rol === 'secretaria' || $rol === 'admin'): ?>
    <div class="col-sm-6">
      <a href="verificar.php" class="card text-decoration-none h-100" id="card-link-verificar-cert">
        <div class="card-body d-flex align-items-center gap-3 p-3">
          <div class="rounded-circle bg-info bg-opacity-10 d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
            <i class="bi bi-search text-info fs-4"></i>
          </div>
          <div>
            <div class="fw-bold">Verificar Solicitudes</div>
            <div class="text-muted small"><?= count($misSolicitudes) ?> pendientes de verificación</div>
          </div>
        </div>
      </a>
    </div>
    <?php elseif ($rol === 'legal'): ?>
    <div class="col-sm-6">
      <a href="validar.php" class="card text-decoration-none h-100" id="card-link-validar-cert">
        <div class="card-body d-flex align-items-center gap-3 p-3">
          <div class="rounded-circle bg-secondary bg-opacity-10 d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
            <i class="bi bi-shield-fill-check text-secondary fs-4"></i>
          </div>
          <div>
            <div class="fw-bold">Validar y Firmar</div>
            <div class="text-muted small"><?= count($misSolicitudes) ?> para validación legal</div>
          </div>
        </div>
      </a>
    </div>
    <?php endif; ?>
  </div>

  <!-- Tabla de solicitudes -->
  <?php if (!empty($misSolicitudes)): ?>
  <div class="card">
    <div class="card-header fw-semibold"><i class="bi bi-clock-history me-1"></i>Solicitudes Recientes</div>
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead>
          <tr><th>ID</th><th>Solicitante</th><th>C.I.</th><th>Motivo</th><th>Estado</th><th>Fecha</th><th></th></tr>
        </thead>
        <tbody>
          <?php foreach (array_slice($misSolicitudes, 0, 8) as $s): ?>
          <tr>
            <td><code class="small"><?= limpiar($s['id']) ?></code></td>
            <td><?= limpiar($s['solicitante_nombre']) ?></td>
            <td><?= limpiar($s['carnet_identidad']) ?></td>
            <td class="small text-muted"><?= limpiar(substr($s['motivo'],0,40)) ?></td>
            <td><?= badgeEstadoCert($s['estado']) ?></td>
            <td class="text-muted small"><?= fechaLegible($s['fecha_creacion']) ?></td>
            <td>
              <a href="seguimiento.php?id=<?= urlencode($s['id']) ?>" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-eye"></i>
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
    <?= $rol === 'estudiante' ? 'No tienes solicitudes. <a href="nueva_solicitud.php">Crear primera solicitud</a>.' : 'No hay solicitudes pendientes para tu rol.' ?>
  </div>
  <?php endif; ?>

</div>

<?php include '../includes/footer.php'; ?>
