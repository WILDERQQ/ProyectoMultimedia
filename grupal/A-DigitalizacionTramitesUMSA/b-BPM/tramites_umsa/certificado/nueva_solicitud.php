<?php
/**
 * certificado/nueva_solicitud.php — Solicitante llena datos para el certificado
 */
require_once '../includes/auth.php';
require_once '../includes/funciones.php';
requiereLogin('estudiante','admin');

$titulo  = 'Nueva Solicitud de Certificado — UMSA';
$prefix  = '../';
$u       = usuarioActual();
$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $requeridos = ['nombre_completo','carnet_identidad','expedido_en','motivo'];
    foreach ($requeridos as $r) {
        if (empty(trim($_POST[$r] ?? ''))) {
            $errores[] = "El campo '{$r}' es obligatorio.";
        }
    }

    if (empty($errores)) {
        $nueva = [
            'id'                  => generarID('CERT'),
            'solicitante_usuario' => $u['usuario'],
            'solicitante_nombre'  => $u['nombre'],
            'nombre_completo'     => trim($_POST['nombre_completo']),
            'carnet_identidad'    => trim($_POST['carnet_identidad']),
            'expedido_en'         => trim($_POST['expedido_en']),
            'telefono'            => trim($_POST['telefono'] ?? ''),
            'correo'              => trim($_POST['correo'] ?? ''),
            'motivo'              => trim($_POST['motivo']),
            'estado'              => 'enviado',
            'tiene_proceso'       => null,
            'historial'           => [[
                'estado'     => 'enviado',
                'fecha'      => fechaHora(),
                'usuario'    => $u['usuario'],
                'comentario' => 'Solicitud de certificado enviada.',
            ]],
            'certificado_url'     => '',
            'fecha_creacion'      => fechaHora(),
            'fecha_actualizacion' => fechaHora(),
        ];
        $todas = leerJSON('certificados.json');
        $todas[] = $nueva;
        guardarJSON('certificados.json', $todas);
        flashMsg('exito', "Solicitud {$nueva['id']} enviada. La Secretaría verificará en registros.");
        header('Location: mis_solicitudes.php');
        exit;
    }
}

$ciudades = ['La Paz','El Alto','Cochabamba','Santa Cruz','Oruro','Potosí','Sucre','Tarija','Trinidad','Cobija'];
$motivos  = [
    'Postulación a trabajo',
    'Continuación de estudios de postgrado',
    'Trámite de visa',
    'Concurso de méritos',
    'Requisito institucional',
    'Otro',
];
?>
<?php include '../includes/header.php'; ?>

<div class="container py-4" style="max-width:700px;">

  <div class="card mb-4" style="background:linear-gradient(135deg,#00695c,#00897b);color:#fff;border-radius:14px;">
    <div class="card-body p-4">
      <h1 class="h4 fw-bold mb-1">
        <i class="bi bi-file-earmark-check-fill me-2" style="color:#a5d6a7;"></i>
        Nueva Solicitud — Certificado de No Proceso
      </h1>
      <p class="mb-0 opacity-75">Completa tus datos personales. Secretaría verificará en los registros universitarios.</p>
    </div>
  </div>

  <!-- Stepper: Paso 1 activo -->
  <div class="card mb-4">
    <div class="card-body">
      <?= renderStepper([
          ['estado'=>'enviado',         'label'=>'1. Tu Solicitud',    'icono'=>'person-badge'],
          ['estado'=>'en_verificacion', 'label'=>'2. Secretaría\nVerifica', 'icono'=>'search'],
          ['estado'=>'sin_proceso',     'label'=>'3. Resultado\nConfirmado', 'icono'=>'shield-check'],
          ['estado'=>'en_legal',        'label'=>'4. Legal\nFirma',   'icono'=>'shield-fill-check'],
          ['estado'=>'completado',      'label'=>'5. Certificado\nEmitido', 'icono'=>'file-earmark-check-fill'],
      ], 'enviado') ?>
      <p class="text-muted small text-center mb-0">
        <i class="bi bi-info-circle me-1"></i>Estás en el <strong>Paso 1</strong>. Secretaría verificará si tienes proceso universitario.
      </p>
    </div>
  </div>

  <?php if (!empty($errores)): ?>
  <div class="alert alert-danger">
    <ul class="mb-0">
      <?php foreach ($errores as $e): ?><li><?= limpiar($e) ?></li><?php endforeach; ?>
    </ul>
  </div>
  <?php endif; ?>

  <form method="POST" action="nueva_solicitud.php" id="form-nueva-cert">

    <!-- Datos personales -->
    <div class="card mb-3">
      <div class="card-header fw-semibold"><i class="bi bi-person me-1"></i>Datos Personales</div>
      <div class="card-body">
        <div class="row g-3">
          <div class="col-12">
            <label for="nombre_completo" class="form-label small fw-medium">Nombre Completo <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="nombre_completo" name="nombre_completo"
                   value="<?= limpiar($_POST['nombre_completo'] ?? $u['nombre']) ?>"
                   placeholder="Nombre completo como en el carnet" required>
          </div>
          <div class="col-sm-6">
            <label for="carnet_identidad" class="form-label small fw-medium">Carnet de Identidad <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="carnet_identidad" name="carnet_identidad"
                   value="<?= limpiar($_POST['carnet_identidad'] ?? ($u['carnet'] ?? '')) ?>"
                   placeholder="Ej: 12345678" required>
          </div>
          <div class="col-sm-6">
            <label for="expedido_en" class="form-label small fw-medium">Expedido en <span class="text-danger">*</span></label>
            <select class="form-select" id="expedido_en" name="expedido_en" required>
              <option value="">— Ciudad —</option>
              <?php foreach ($ciudades as $c): ?>
              <option value="<?= htmlspecialchars($c) ?>" <?= ($_POST['expedido_en']??'')===$c?'selected':'' ?>><?= $c ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-sm-6">
            <label for="telefono" class="form-label small fw-medium">Teléfono / Celular</label>
            <input type="tel" class="form-control" id="telefono" name="telefono"
                   value="<?= limpiar($_POST['telefono']??'') ?>" placeholder="Ej: 71234567">
          </div>
          <div class="col-sm-6">
            <label for="correo" class="form-label small fw-medium">Correo Electrónico</label>
            <input type="email" class="form-control" id="correo" name="correo"
                   value="<?= limpiar($_POST['correo']??'') ?>" placeholder="tu@correo.com">
          </div>
        </div>
      </div>
    </div>

    <!-- Motivo -->
    <div class="card mb-4">
      <div class="card-header fw-semibold"><i class="bi bi-card-text me-1"></i>Motivo de la Solicitud</div>
      <div class="card-body">
        <div class="mb-3">
          <label for="motivo_select" class="form-label small fw-medium">Motivo principal <span class="text-danger">*</span></label>
          <select class="form-select" id="motivo_select" name="motivo_select" onchange="
            var custom = document.getElementById('motivo_custom');
            var val    = this.value;
            if(val === 'Otro') { custom.style.display='block'; document.getElementById('motivo').value=''; }
            else { custom.style.display='none'; document.getElementById('motivo').value=val; }
          ">
            <option value="">— Seleccionar motivo —</option>
            <?php foreach ($motivos as $m): ?>
            <option value="<?= htmlspecialchars($m) ?>"><?= $m ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div id="motivo_custom" style="display:none;">
          <label for="motivo" class="form-label small fw-medium">Especificar motivo:</label>
          <textarea class="form-control" id="motivo" name="motivo" rows="3"
          placeholder="Describe el motivo de tu solicitud..."><?= limpiar($_POST['motivo']??'') ?></textarea>
        </div>
        <!-- <input type="hidden" id="motivo-hidden" name="motivo" value="<?= limpiar($_POST['motivo']??'') ?>"> -->
      </div>
    </div>

    <div class="d-flex gap-2 justify-content-end">
      <a href="index.php" class="btn btn-outline-secondary" id="btn-cancelar-cert">Cancelar</a>
      <button type="submit" class="btn fw-bold text-white" id="btn-enviar-cert"
              style="background:linear-gradient(135deg,#00695c,#00897b);">
        <i class="bi bi-send me-1"></i>Enviar Solicitud
      </button>
    </div>
  </form>

</div>
<script>
// Sincronizar campo motivo
document.getElementById('motivo_select').addEventListener('change', function() {
    if (this.value && this.value !== 'Otro') {
        document.getElementById('motivo-hidden').value = this.value;
        document.getElementById('motivo').value = this.value;
    }
});
</script>

<?php include '../includes/footer.php'; ?>
