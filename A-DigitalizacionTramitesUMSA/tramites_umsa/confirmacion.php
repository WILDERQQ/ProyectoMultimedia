<?php
require_once 'includes/funciones.php';
$titulo = 'Solicitud Confirmada — UMSA';
$paginaActual = '';

$id = limpiar($_GET['id'] ?? '');
$solicitud = null;

if ($id) {
    $inscripciones = leerJSON('inscripciones.json');
    foreach ($inscripciones as $s) {
        if ($s['id'] === $id) { $solicitud = $s; break; }
    }
}

if (!$solicitud) {
    header('Location: index.php');
    exit;
}
?>
<?php include 'includes/header.php'; ?>

<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-lg-7 text-center">

      <!-- Icono de éxito -->
      <div class="mb-4">
        <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-success bg-opacity-10"
             style="width:96px;height:96px;">
          <i class="bi bi-check-circle-fill text-success" style="font-size:3rem;"></i>
        </div>
      </div>

      <h2 class="h3 mb-1">¡Solicitud enviada con éxito!</h2>
      <p class="text-muted mb-4">
        Su solicitud de inscripción ha sido registrada y está en revisión.
      </p>

      <!-- Tarjeta de resumen -->
      <div class="card text-start mb-4">
        <div class="card-header card-header-umsa d-flex justify-content-between align-items-center">
          <span><i class="bi bi-receipt me-1"></i> Comprobante de solicitud</span>
          <?= badgeEstado($solicitud['estado']) ?>
        </div>
        <div class="card-body">
          <div class="row g-2 mb-3">
            <div class="col-6">
              <small class="text-muted d-block">ID de solicitud</small>
              <code class="fw-bold"><?= limpiar($solicitud['id']) ?></code>
            </div>
            <div class="col-6">
              <small class="text-muted d-block">Fecha</small>
              <span><?= limpiar($solicitud['fecha']) ?></span>
            </div>
            <div class="col-6">
              <small class="text-muted d-block">Estudiante</small>
              <span><?= limpiar($solicitud['nombre']) ?></span>
            </div>
            <div class="col-6">
              <small class="text-muted d-block">Carrera</small>
              <span><?= limpiar($solicitud['carrera']) ?></span>
            </div>
            <div class="col-6">
              <small class="text-muted d-block">Semestre</small>
              <span><?= $solicitud['semestre'] ?>°</span>
            </div>
            <div class="col-6">
              <small class="text-muted d-block">Correo</small>
              <span><?= limpiar($solicitud['email']) ?></span>
            </div>
          </div>

          <hr>
          <h6 class="fw-semibold mb-3"><i class="bi bi-journal-bookmark me-1"></i> Materias inscritas</h6>
          <div class="list-group list-group-flush">
            <?php foreach ($solicitud['materias'] as $m): ?>
            <div class="list-group-item px-0 d-flex justify-content-between align-items-center">
              <div>
                <div class="fw-semibold small"><?= limpiar($m['nombre']) ?></div>
                <code class="text-muted" style="font-size:.75rem;"><?= limpiar($m['codigo']) ?></code>
              </div>
              <span class="badge bg-primary bg-opacity-10 text-primary border">
                <?= (int)$m['creditos'] ?> créditos
              </span>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
        <div class="card-footer text-muted small">
          <i class="bi bi-info-circle me-1"></i>
          Guarde su ID de solicitud para hacer seguimiento del estado.
        </div>
      </div>

      <!-- Botones -->
      <div class="d-flex gap-2 justify-content-center flex-wrap">
        <a href="consultar.php?id=<?= urlencode($solicitud['id']) ?>" class="btn btn-outline-primary">
          <i class="bi bi-search me-1"></i> Consultar estado
        </a>
        <a href="nueva_inscripcion.php" class="btn btn-outline-secondary">
          <i class="bi bi-plus-circle me-1"></i> Nueva solicitud
        </a>
        <a href="index.php" class="btn btn-primary">
          <i class="bi bi-house me-1"></i> Inicio
        </a>
      </div>

    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
