<?php
require_once 'includes/funciones.php';
$titulo = 'Consultar Estado — UMSA';
$paginaActual = 'consultar';

$id = limpiar($_GET['id'] ?? '');
$solicitud = null;

if ($id) {
    foreach (leerJSON('inscripciones.json') as $s) {
        if ($s['id'] === $id) { $solicitud = $s; break; }
    }
}
?>
<?php include 'includes/header.php'; ?>

<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-lg-7">

      <h2 class="h4 mb-4"><i class="bi bi-search me-2"></i>Consultar estado de solicitud</h2>

      <!-- Formulario de búsqueda -->
      <div class="card mb-4">
        <div class="card-body">
          <form method="GET" class="d-flex gap-2">
            <input type="text" name="id" class="form-control"
                   placeholder="Ingrese su ID de solicitud (ej: SOL-ABC1234)"
                   value="<?= htmlspecialchars($id) ?>" required>
            <button type="submit" class="btn btn-primary px-4">
              <i class="bi bi-search"></i>
            </button>
          </form>
        </div>
      </div>

      <?php if ($id && !$solicitud): ?>
        <div class="alert alert-warning">
          <i class="bi bi-exclamation-triangle me-1"></i>
          No se encontró ninguna solicitud con el ID <strong><?= htmlspecialchars($id) ?></strong>.
        </div>

      <?php elseif ($solicitud): ?>

        <!-- Resultado -->
        <div class="card">
          <div class="card-header card-header-umsa d-flex justify-content-between align-items-center">
            <span><i class="bi bi-file-earmark-text me-1"></i> <?= limpiar($solicitud['id']) ?></span>
            <?= badgeEstado($solicitud['estado']) ?>
          </div>
          <div class="card-body">
            <div class="row g-3 mb-4">
              <div class="col-sm-6">
                <small class="text-muted">Estudiante</small>
                <p class="fw-semibold mb-0"><?= limpiar($solicitud['nombre']) ?></p>
              </div>
              <div class="col-sm-6">
                <small class="text-muted">Carrera</small>
                <p class="fw-semibold mb-0"><?= limpiar($solicitud['carrera']) ?></p>
              </div>
              <div class="col-sm-6">
                <small class="text-muted">Semestre</small>
                <p class="fw-semibold mb-0"><?= $solicitud['semestre'] ?>°</p>
              </div>
              <div class="col-sm-6">
                <small class="text-muted">Fecha de solicitud</small>
                <p class="fw-semibold mb-0"><?= limpiar($solicitud['fecha']) ?></p>
              </div>
            </div>

            <!-- Materias -->
            <h6 class="fw-semibold mb-2"><i class="bi bi-journal-bookmark me-1"></i> Materias</h6>
            <ul class="list-group list-group-flush mb-4">
              <?php foreach ($solicitud['materias'] as $m): ?>
                <li class="list-group-item px-0 d-flex justify-content-between">
                  <span><?= limpiar($m['nombre']) ?> <code class="text-muted small"><?= limpiar($m['codigo']) ?></code></span>
                  <span class="text-muted small"><?= (int)$m['creditos'] ?> cred.</span>
                </li>
              <?php endforeach; ?>
            </ul>

            <!-- Historial -->
            <?php if (!empty($solicitud['historial'])): ?>
            <h6 class="fw-semibold mb-3"><i class="bi bi-clock-history me-1"></i> Historial de estado</h6>
            <div class="timeline">
              <?php foreach (array_reverse($solicitud['historial']) as $h): ?>
                <div class="d-flex gap-3 mb-3">
                  <div class="step-badge bg-primary bg-opacity-10 text-primary flex-shrink-0" style="width:36px;height:36px;">
                    <i class="bi bi-circle-fill" style="font-size:.5rem;"></i>
                  </div>
                  <div>
                    <?= badgeEstado($h['estado']) ?>
                    <small class="text-muted ms-2"><?= limpiar($h['fecha']) ?></small>
                    <?php if (!empty($h['nota'])): ?>
                      <p class="small text-muted mb-0 mt-1"><?= limpiar($h['nota']) ?></p>
                    <?php endif; ?>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
            <?php endif; ?>

          </div>
          <div class="card-footer d-flex gap-2 justify-content-end">
            <a href="lista.php" class="btn btn-outline-secondary btn-sm">Ver todas las solicitudes</a>
            <a href="index.php" class="btn btn-primary btn-sm">Inicio</a>
          </div>
        </div>

      <?php endif; ?>

    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
