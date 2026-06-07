<?php
require_once 'includes/funciones.php';
$titulo = 'Nueva Inscripción — UMSA';
$paginaActual = 'nueva';

$materias_data = leerJSON('materias.json');
$carreras = array_keys($materias_data);
?>
<?php include 'includes/header.php'; ?>

<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-lg-8">

      <!-- Título -->
      <div class="d-flex align-items-center gap-3 mb-4">
        <div class="step-badge bg-primary text-white" style="width:48px;height:48px;font-size:1.3rem;">
          <i class="bi bi-journal-plus"></i>
        </div>
        <div>
          <h2 class="h4 mb-0">Inscripción de Materias</h2>
          <p class="text-muted small mb-0">Complete el formulario para registrar su solicitud</p>
        </div>
      </div>

      <!-- Alertas de validación -->
      <?php if (!empty($_GET['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
          <i class="bi bi-exclamation-triangle me-1"></i>
          <?= limpiar($_GET['error']) ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      <?php endif; ?>

      <form action="guardar_inscripcion.php" method="POST" id="formInscripcion">

        <!-- PASO 1: Datos personales -->
        <div class="card mb-3">
          <div class="card-header card-header-umsa d-flex align-items-center gap-2">
            <span class="badge bg-warning text-dark">1</span>
            <span>Datos del estudiante</span>
          </div>
          <div class="card-body">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label fw-semibold">Nombre completo <span class="text-danger">*</span></label>
                <input type="text" name="nombre" class="form-control" placeholder="Ej: Juan Pérez Mamani" required>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold">Carnet de identidad <span class="text-danger">*</span></label>
                <input type="text" name="carnet" class="form-control" placeholder="Ej: 12345678" required pattern="\d{7,10}">
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold">Código de estudiante <span class="text-danger">*</span></label>
                <input type="text" name="codigo_estudiante" class="form-control" placeholder="Ej: 2021-12345" required>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold">Correo electrónico <span class="text-danger">*</span></label>
                <input type="email" name="email" class="form-control" placeholder="correo@umsa.bo" required>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold">Semestre actual <span class="text-danger">*</span></label>
                <select name="semestre" class="form-select" required>
                  <option value="">-- Seleccione --</option>
                  <?php for ($i = 1; $i <= 10; $i++): ?>
                    <option value="<?= $i ?>"><?= $i ?>° semestre</option>
                  <?php endfor; ?>
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold">Carrera <span class="text-danger">*</span></label>
                <select name="carrera" id="selectCarrera" class="form-select" required>
                  <option value="">-- Seleccione su carrera --</option>
                  <?php foreach ($carreras as $c): ?>
                    <option value="<?= htmlspecialchars($c) ?>"><?= htmlspecialchars($c) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
          </div>
        </div>

        <!-- PASO 2: Selección de materias (dinámica con JS) -->
        <div class="card mb-3">
          <div class="card-header card-header-umsa d-flex align-items-center gap-2">
            <span class="badge bg-warning text-dark">2</span>
            <span>Selección de materias</span>
          </div>
          <div class="card-body">
            <div id="materiasPlaceholder" class="text-center text-muted py-4">
              <i class="bi bi-arrow-up-circle fs-2 d-block mb-2"></i>
              Primero seleccione una carrera para ver las materias disponibles
            </div>
            <div id="materiasContenido" class="d-none">
              <p class="text-muted small mb-3">
                <i class="bi bi-info-circle me-1"></i>
                Puede seleccionar hasta <strong>5 materias</strong>. Verifique los cupos disponibles.
              </p>
              <div id="listaMaterias" class="row g-2"></div>
              <div id="alertaMaximo" class="alert alert-warning mt-3 d-none">
                <i class="bi bi-exclamation-triangle me-1"></i>
                Máximo 5 materias por solicitud.
              </div>
            </div>
          </div>
        </div>

        <!-- PASO 3: Observaciones -->
        <div class="card mb-4">
          <div class="card-header card-header-umsa d-flex align-items-center gap-2">
            <span class="badge bg-warning text-dark">3</span>
            <span>Observaciones (opcional)</span>
          </div>
          <div class="card-body">
            <textarea name="observaciones" class="form-control" rows="3"
              placeholder="Indique si tiene alguna situación especial, equivalencias previas, etc."></textarea>
          </div>
        </div>

        <!-- Botones -->
        <div class="d-flex gap-2 justify-content-end">
          <a href="index.php" class="btn btn-outline-secondary">
            <i class="bi bi-x-circle me-1"></i> Cancelar
          </a>
          <button type="submit" class="btn btn-primary px-4" id="btnEnviar">
            <i class="bi bi-send me-1"></i> Enviar solicitud
          </button>
        </div>

      </form>
    </div>
  </div>
</div>

<!-- Datos de materias para JS -->
<script>
const materiasData = <?= json_encode($materias_data, JSON_UNESCAPED_UNICODE) ?>;

document.getElementById('selectCarrera').addEventListener('change', function () {
  const carrera = this.value;
  const placeholder = document.getElementById('materiasPlaceholder');
  const contenido   = document.getElementById('materiasContenido');
  const lista       = document.getElementById('listaMaterias');

  if (!carrera || !materiasData[carrera]) {
    placeholder.classList.remove('d-none');
    contenido.classList.add('d-none');
    return;
  }

  placeholder.classList.add('d-none');
  contenido.classList.remove('d-none');
  lista.innerHTML = '';

  materiasData[carrera].forEach(mat => {
    const col = document.createElement('div');
    col.className = 'col-md-6';
    col.innerHTML = `
      <div class="form-check border rounded p-3 h-100 materia-card" style="cursor:pointer;">
        <input class="form-check-input materia-check" type="checkbox"
               name="materias[]" value="${mat.codigo}"
               id="mat_${mat.codigo}"
               data-nombre="${mat.nombre}">
        <label class="form-check-label w-100" for="mat_${mat.codigo}" style="cursor:pointer;">
          <div class="fw-semibold">${mat.nombre}</div>
          <div class="text-muted small mt-1">
            <span class="badge bg-light text-dark border me-1">
              <i class="bi bi-hash"></i> ${mat.codigo}
            </span>
            <span class="badge bg-light text-dark border me-1">
              <i class="bi bi-bookmark"></i> ${mat.creditos} créditos
            </span>
            <span class="badge bg-${mat.cupos > 10 ? 'success' : 'warning'} bg-opacity-10 text-${mat.cupos > 10 ? 'success' : 'warning'} border">
              <i class="bi bi-people"></i> ${mat.cupos} cupos
            </span>
          </div>
        </label>
      </div>`;
    lista.appendChild(col);
  });

  // Limitar a 5 materias
  lista.addEventListener('change', () => {
    const checks  = lista.querySelectorAll('.materia-check:checked');
    const alerta  = document.getElementById('alertaMaximo');
    alerta.classList.toggle('d-none', checks.length <= 5);
    lista.querySelectorAll('.materia-check:not(:checked)').forEach(cb => {
      cb.disabled = checks.length >= 5;
      cb.closest('.materia-card').style.opacity = (checks.length >= 5) ? '.5' : '1';
    });
  });
});

// Validar que se haya elegido al menos 1 materia antes de enviar
document.getElementById('formInscripcion').addEventListener('submit', function (e) {
  const checks = document.querySelectorAll('.materia-check:checked');
  if (checks.length === 0) {
    e.preventDefault();
    alert('Debe seleccionar al menos una materia.');
  }
});
</script>

<?php include 'includes/footer.php'; ?>
