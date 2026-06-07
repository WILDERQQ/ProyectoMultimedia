<?php

/**
 * titulo/nueva_solicitud.php — Formulario de solicitud de título (Estudiante)
 */
require_once '../includes/auth.php';
require_once '../includes/funciones.php';
requiereLogin('estudiante', 'admin');

$titulo = 'Nueva Solicitud de Título — UMSA';
$prefix = '../';
$u      = usuarioActual();
$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Validar
  $campos = ['carrera', 'fecha_egreso', 'promedio', 'modalidad'];
  foreach ($campos as $c) {
    if (empty(trim($_POST[$c] ?? ''))) {
      $errores[] = "El campo '{$c}' es obligatorio.";
    }
  }
  if (empty($errores)) {
    $nueva = [
      'id'                 => generarID('TIT'),
      'estudiante_usuario' => $u['usuario'],
      'estudiante_nombre'  => $u['nombre'],
      'carnet'             => $u['carnet'] ?? '',
      'carrera'            => trim($_POST['carrera']),
      'facultad'           => trim($_POST['facultad'] ?? ''),
      'fecha_egreso'       => trim($_POST['fecha_egreso']),
      'promedio'           => trim($_POST['promedio']),
      'modalidad'          => trim($_POST['modalidad']),
      'titulo_proyecto'    => trim($_POST['titulo_proyecto'] ?? ''),
      'tutor'              => trim($_POST['tutor'] ?? ''),
      'documentos'         => array_filter(explode(',', $_POST['documentos'] ?? '')),
      'estado'             => 'enviado',
      'historial'          => [[
        'estado'     => 'enviado',
        'fecha'      => fechaHora(),
        'usuario'    => $u['usuario'],
        'comentario' => 'Solicitud enviada por el estudiante.',
      ]],
      'observaciones'      => '',
      'resolucion'         => '',
      'fecha_creacion'     => fechaHora(),
      'fecha_actualizacion' => fechaHora(),
    ];

    $todas = leerJSON('titulos.json');
    $todas[] = $nueva;
    guardarJSON('titulos.json', $todas);

    flashMsg('exito', "Solicitud {$nueva['id']} enviada correctamente. La Secretaría revisará tu documentación.");
    header('Location: mis_solicitudes.php');
    exit;
  }
}

$carreras = [
  'Informática',
  'Sistemas Informáticos',
  'Administración de Empresas',
  'Derecho',
  'Medicina',
  'Ingeniería Civil',
  'Contaduría Pública',
  'Comunicación Social',
  'Economía',
  'Filosofía y Letras',
];
$modalidades = ['Tesis', 'Proyecto de Grado', 'Excelencia Académica', 'Trabajo Dirigido', 'Internado Rotatorio'];
?>
<?php include '../includes/header.php'; ?>

<div class="container py-4" style="max-width:760px;">

  <!-- Stepper de pasos del formulario -->
  <div class="card mb-4" style="background:linear-gradient(135deg,#003082,#0d47a1);color:#fff;border-radius:14px;">
    <div class="card-body p-4">
      <h1 class="h4 fw-bold mb-1">
        <i class="bi bi-mortarboard-fill me-2" style="color:#ffc107;"></i>Nueva Solicitud de Título Profesional
      </h1>
      <p class="mb-0 opacity-75">Completa todos los datos. La Secretaría revisará tu documentación.</p>
    </div>
  </div>

  <!-- Indicador del flujo (paso actual: Estudiante) -->
  <div class="card mb-4">
    <div class="card-body">
      <?= renderStepper([
        ['estado' => 'enviado',       'label' => '1. Tu Solicitud',     'icono' => 'person-fill'],
        ['estado' => 'en_revision',   'label' => '2. Secretaría',       'icono' => 'search'],
        ['estado' => 'verificado',    'label' => '3. Verificación',     'icono' => 'file-check'],
        ['estado' => 'en_decanatura', 'label' => '4. Decanatura',       'icono' => 'award'],
        ['estado' => 'en_registros',  'label' => '5. Registros',        'icono' => 'archive'],
        ['estado' => 'completado',    'label' => '6. Completado',       'icono' => 'check-circle-fill'],
      ], 'enviado') ?>
      <p class="text-muted small mb-0 text-center">
        <i class="bi bi-info-circle me-1"></i>
        Estás en el <strong>Paso 1</strong>. Tras enviar tu solicitud, la Secretaría la revisará (Paso 2).
      </p>
    </div>
  </div>

  <?php if (!empty($errores)): ?>
    <div class="alert alert-danger">
      <strong><i class="bi bi-exclamation-triangle me-1"></i>Corrige los siguientes errores:</strong>
      <ul class="mb-0 mt-1">
        <?php foreach ($errores as $e): ?>
          <li><?= limpiar($e) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <form method="POST" action="nueva_solicitud.php" id="form-nueva-solicitud">
    <!-- Datos del solicitante (auto) -->
    <div class="card mb-3">
      <div class="card-header fw-semibold"><i class="bi bi-person me-1"></i>Datos del Estudiante</div>
      <div class="card-body">
        <div class="row g-3">
          <div class="col-sm-6">
            <label class="form-label small fw-medium">Nombre Completo</label>
            <input type="text" class="form-control" value="<?= limpiar($u['nombre']) ?>" readonly>
          </div>
          <div class="col-sm-6">
            <label class="form-label small fw-medium">Carnet de Identidad</label>
            <input type="text" class="form-control" value="<?= limpiar($u['carnet'] ?? 'N/A') ?>" readonly>
          </div>
        </div>
      </div>
    </div>

    <!-- Datos académicos -->
    <div class="card mb-3">
      <div class="card-header fw-semibold"><i class="bi bi-journal-bookmark me-1"></i>Datos Académicos</div>
      <div class="card-body">
        <div class="row g-3">
          <div class="col-sm-6">
            <label for="carrera" class="form-label small fw-medium">Carrera <span class="text-danger">*</span></label>
            <select class="form-select" id="carrera" name="carrera" required>
              <option value="">— Seleccionar —</option>
              <?php foreach ($carreras as $c): ?>
                <option value="<?= htmlspecialchars($c) ?>" <?= ($_POST['carrera'] ?? '') === $c ? 'selected' : '' ?>><?= $c ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-sm-6">
            <label for="facultad" class="form-label small fw-medium">Facultad</label>
            <input type="text" class="form-control" id="facultad" name="facultad"
              value="<?= limpiar($_POST['facultad'] ?? '') ?>" placeholder="Ej: Ciencias Puras y Naturales">
          </div>
          <div class="col-sm-4">
            <label for="fecha_egreso" class="form-label small fw-medium">Fecha de Egreso <span class="text-danger">*</span></label>
            <input type="date" class="form-control" id="fecha_egreso" name="fecha_egreso"
              value="<?= limpiar($_POST['fecha_egreso'] ?? '') ?>" required>
          </div>
          <div class="col-sm-4">
            <label for="promedio" class="form-label small fw-medium">Promedio General <span class="text-danger">*</span></label>
            <input type="number" class="form-control" id="promedio" name="promedio" min="51" max="100" step="0.01"
              value="<?= limpiar($_POST['promedio'] ?? '') ?>" placeholder="Ej: 72.5" required>
          </div>
          <div class="col-sm-4">
            <label for="modalidad" class="form-label small fw-medium">Modalidad de Grado <span class="text-danger">*</span></label>
            <select class="form-select" id="modalidad" name="modalidad" required>
              <option value="">— Seleccionar —</option>
              <?php foreach ($modalidades as $m): ?>
                <option value="<?= htmlspecialchars($m) ?>" <?= ($_POST['modalidad'] ?? '') === $m ? 'selected' : '' ?>><?= $m ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
      </div>
    </div>

    <!-- Datos del proyecto (si aplica) -->
    <div class="card mb-3">
      <div class="card-header fw-semibold">
        <i class="bi bi-file-text me-1"></i>Proyecto / Trabajo de Grado
      </div>
      <div class="card-body">
        <div class="row g-3">
          <div class="col-sm-8">
            <label for="titulo_proyecto" class="form-label small fw-medium">
              Título del Proyecto / Tesis
            </label>
            <input type="text"
              class="form-control"
              id="titulo_proyecto"
              name="titulo_proyecto"
              value="<?= limpiar($_POST['titulo_proyecto'] ?? '') ?>"
              placeholder="Ej: Sistema de gestión de inventarios con IA"
              disabled>
          </div>

          <div class="col-sm-4">
            <label for="tutor" class="form-label small fw-medium">
              Nombre del Tutor
            </label>
            <input type="text"
              class="form-control"
              id="tutor"
              name="tutor"
              value="<?= limpiar($_POST['tutor'] ?? '') ?>"
              placeholder="Ej: Dr. Juan Rodríguez"
              disabled>
          </div>
        </div>
      </div>
    </div>

    <!-- Documentos adjuntos -->
    <div class="card mb-4">
      <div class="card-header fw-semibold"><i class="bi bi-paperclip me-1"></i>Documentos Requeridos</div>
      <div class="card-body">
        <p class="small text-muted mb-3">Marca los documentos que adjuntas (simulado — en producción se subirían archivos):</p>
        <div class="row g-2" id="docs-check">
          <?php
          $docsList = [
            'diploma_bachiller'    => 'Diploma de Bachiller',
            'certificado_notas'    => 'Certificado de Notas',
            'libreta_universitaria' => 'Libreta Universitaria',
            'carnet_identidad'     => 'Fotocopia Carnet de Identidad',
            'foto_carnet'          => 'Fotografías Tamaño Carnet (4)',
            'deposito_bancario'    => 'Depósito Bancario (pago de arancel)',
          ];
          $docsSel = explode(',', $_POST['documentos'] ?? '');
          foreach ($docsList as $key => $label):
          ?>
            <div class="col-sm-6">
              <div class="form-check">
                <input class="form-check-input doc-check" type="checkbox" id="doc_<?= $key ?>" name="doc[]"
                  value="<?= $key ?>" <?= in_array($key, $docsSel) ? 'checked' : '' ?>>
                <label class="form-check-label small" for="doc_<?= $key ?>"><?= $label ?></label>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
        <input type="hidden" name="documentos" id="documentos-hidden">
      </div>
    </div>

    <div class="d-flex gap-2 justify-content-end">
      <a href="index.php" class="btn btn-outline-secondary" id="btn-cancelar-titulo">Cancelar</a>
      <button type="submit" class="btn btn-primary fw-bold" id="btn-enviar-solicitud">
        <i class="bi bi-send me-1"></i>Enviar Solicitud
      </button>
    </div>
  </form>

</div>

<script>
  // Recolectar checkboxes en campo oculto antes de enviar
  document.getElementById('form-nueva-solicitud').addEventListener('submit', function() {
    const checked = [...document.querySelectorAll('.doc-check:checked')].map(c => c.value);
    document.getElementById('documentos-hidden').value = checked.join(',');
  });


  document.addEventListener('DOMContentLoaded', function() {
    const modalidad = document.getElementById('modalidad');
    const titulo = document.getElementById('titulo_proyecto');
    const tutor = document.getElementById('tutor');

    function actualizarCampos() {
      const habilitar =
        modalidad.value === 'Tesis' ||
        modalidad.value === 'Proyecto de Grado';

      titulo.disabled = !habilitar;
      tutor.disabled = !habilitar;

      if (!habilitar) {
        titulo.value = '';
        tutor.value = '';
      }
    }

    modalidad.addEventListener('change', actualizarCampos);
    actualizarCampos();
  });
</script>

<?php include '../includes/footer.php'; ?>