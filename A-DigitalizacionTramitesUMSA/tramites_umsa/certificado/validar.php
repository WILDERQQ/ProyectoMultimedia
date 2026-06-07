<?php
/**
 * certificado/validar.php — Departamento Legal: validar y firmar certificados
 */
require_once '../includes/auth.php';
require_once '../includes/funciones.php';
requiereLogin('legal','admin');

$titulo = 'Validación Legal de Certificados — UMSA';
$prefix = '../';
$u      = usuarioActual();
$todas  = leerJSON('certificados.json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id  = $_POST['id'] ?? '';
    $idx = array_search($id, array_column($todas, 'id'));

    if ($idx !== false && $todas[$idx]['estado'] === 'en_legal') {
        $nroCert = 'CERT-NP-' . date('Y') . '-' . strtoupper(substr(uniqid(), -5));
        $texto   = "CERTIFICADO N° {$nroCert}: La Universidad Mayor de San Andrés CERTIFICA " .
                   "que el/la ciudadano/a {$todas[$idx]['nombre_completo']}, con C.I. " .
                   "{$todas[$idx]['carnet_identidad']} expedido en {$todas[$idx]['expedido_en']}, " .
                   "NO registra proceso universitario activo en esta institución. " .
                   "Firmado digitalmente por " . $u['nombre'] . ". Fecha: " . date('d/m/Y') . ".";
        $todas[$idx]['certificado_url'] = $texto;
        agregarHistorial($todas[$idx], 'completado', $u['usuario'],
            "Certificado validado y firmado. N° {$nroCert}. Disponible para descarga.");
        guardarJSON('certificados.json', $todas);
        flashMsg('exito', "Certificado {$nroCert} emitido y firmado digitalmente.");
    }
    header('Location: validar.php');
    exit;
}

$pendientes  = array_values(array_filter($todas, fn($s) => $s['estado'] === 'en_legal'));
$completados = array_values(array_filter($todas, fn($s) => $s['estado'] === 'completado'));
?>
<?php include '../includes/header.php'; ?>

<div class="container py-4">

  <div class="d-flex align-items-center gap-3 mb-4">
    <div class="rounded-circle bg-secondary bg-opacity-15 d-flex align-items-center justify-content-center" style="width:52px;height:52px;">
      <i class="bi bi-shield-fill-check text-secondary fs-3"></i>
    </div>
    <div>
      <h1 class="h4 fw-bold mb-0">Validación Legal — Certificados de No Proceso</h1>
      <p class="text-muted small mb-0"><?= count($pendientes) ?> certificados para validar y firmar</p>
    </div>
  </div>

  <!-- BPMN: Legal en paso 4 -->
  <div class="card mb-4">
    <div class="card-body">
      <?= renderStepper([
          ['estado'=>'enviado',         'label'=>'1. Solicitud',       'icono'=>'send-fill'],
          ['estado'=>'en_verificacion', 'label'=>'2. Secretaría',      'icono'=>'search'],
          ['estado'=>'sin_proceso',     'label'=>'3. Sin Proceso',     'icono'=>'shield-check'],
          ['estado'=>'en_legal',        'label'=>'4. Tu Firma\n(Legal)', 'icono'=>'shield-fill-check'],
          ['estado'=>'completado',      'label'=>'5. Certificado\nEmitido', 'icono'=>'file-earmark-check-fill'],
      ], 'en_legal') ?>
      <p class="text-muted small text-center mb-0">
        Secretaría confirmó que el solicitante <strong>no tiene proceso</strong>.
        Valida el resultado y firma digitalmente el certificado.
      </p>
    </div>
  </div>

  <?php if (empty($pendientes)): ?>
  <div class="alert alert-info"><i class="bi bi-info-circle me-1"></i>No hay certificados pendientes de validación.</div>
  <?php endif; ?>

  <?php foreach ($pendientes as $s): ?>
  <div class="card mb-4 border-secondary">
    <div class="card-header fw-semibold d-flex justify-content-between align-items-center"
         style="background:#f8f9fa;">
      <span>
        <i class="bi bi-shield-check text-secondary me-2"></i>
        <code><?= limpiar($s['id']) ?></code> — Sin proceso confirmado por Secretaría
      </span>
      <span class="text-muted small"><?= fechaLegible($s['fecha_actualizacion']) ?></span>
    </div>
    <div class="card-body">
      <div class="row g-3 mb-3">
        <div class="col-sm-4">
          <div class="text-muted small">Solicitante</div>
          <strong><?= limpiar($s['nombre_completo']) ?></strong>
        </div>
        <div class="col-sm-3">
          <div class="text-muted small">C.I. / Expedido</div>
          <div><?= limpiar($s['carnet_identidad']) ?> / <?= limpiar($s['expedido_en']) ?></div>
        </div>
        <div class="col-sm-5">
          <div class="text-muted small">Motivo de solicitud</div>
          <div><?= limpiar($s['motivo']) ?></div>
        </div>
      </div>

      <!-- Vista previa del certificado -->
      <div class="border rounded p-3 mb-3" style="background:#f0f4f8;">
        <p class="small fw-semibold mb-2"><i class="bi bi-file-earmark-text me-1"></i>Vista previa del certificado a firmar:</p>
        <div class="p-3 bg-white rounded border" style="font-family:serif;font-size:.9rem;line-height:1.8;">
          <p class="text-center fw-bold mb-2">UNIVERSIDAD MAYOR DE SAN ANDRÉS</p>
          <p class="text-center mb-3">CERTIFICADO DE NO PROCESO UNIVERSITARIO</p>
          <p>La Universidad Mayor de San Andrés <strong>CERTIFICA</strong> que el/la ciudadano/a
          <strong><?= limpiar($s['nombre_completo']) ?></strong>, con Cédula de Identidad
          <strong><?= limpiar($s['carnet_identidad']) ?> <?= limpiar($s['expedido_en']) ?></strong>,
          <strong>NO registra proceso universitario activo</strong> en esta institución.</p>
          <p>Este certificado se extiende a solicitud del/la interesado/a para fines de:
          <em><?= limpiar($s['motivo']) ?></em>.</p>
          <p class="text-end mt-3 text-muted small">[Firma digital del Departamento Legal]<br>
          Fecha: <?= date('d/m/Y') ?></p>
        </div>
      </div>

      <!-- Historial del proceso -->
      <details class="mb-3">
        <summary class="small text-muted" style="cursor:pointer;">Ver historial del proceso</summary>
        <div class="mt-2"><?= renderTimeline($s['historial']) ?></div>
      </details>

      <!-- Botón firmar -->
      <form method="POST" onsubmit="return confirm('¿Firmar digitalmente y emitir el certificado oficial?')">
        <input type="hidden" name="id" value="<?= limpiar($s['id']) ?>">
        <button type="submit" class="btn btn-secondary fw-bold" id="btn-firmar-<?= limpiar($s['id']) ?>">
          <i class="bi bi-pen-fill me-1"></i>Validar y Firmar Digitalmente — Emitir Certificado
        </button>
      </form>
    </div>
  </div>
  <?php endforeach; ?>

  <!-- Certificados emitidos -->
  <?php if (!empty($completados)): ?>
  <div class="card mt-2">
    <div class="card-header fw-semibold"><i class="bi bi-check-all me-1 text-success"></i>Certificados Emitidos</div>
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead><tr><th>ID</th><th>Solicitante</th><th>C.I.</th><th>Motivo</th><th>Fecha Emisión</th></tr></thead>
        <tbody>
          <?php foreach ($completados as $s): ?>
          <tr>
            <td><code class="small"><?= limpiar($s['id']) ?></code></td>
            <td><?= limpiar($s['nombre_completo']) ?></td>
            <td><?= limpiar($s['carnet_identidad']) ?></td>
            <td class="small text-muted"><?= limpiar(substr($s['motivo'],0,40)) ?></td>
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
