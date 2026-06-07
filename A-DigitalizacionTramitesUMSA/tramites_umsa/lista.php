<?php
require_once 'includes/funciones.php';
$titulo = 'Lista de Solicitudes — UMSA';
$paginaActual = 'lista';

// ── Cambiar estado desde formulario admin ─────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cambiar_estado'])) {
    $id_cambiar  = limpiar($_POST['id_solicitud'] ?? '');
    $nuevo_estado= limpiar($_POST['nuevo_estado'] ?? '');
    $nota        = limpiar($_POST['nota'] ?? '');
    $estados_validos = ['pendiente', 'en_revision', 'aprobado', 'rechazado'];

    if ($id_cambiar && in_array($nuevo_estado, $estados_validos)) {
        $inscripciones = leerJSON('inscripciones.json');
        foreach ($inscripciones as &$s) {
            if ($s['id'] === $id_cambiar) {
                $s['estado'] = $nuevo_estado;
                $s['historial'][] = [
                    'estado' => $nuevo_estado,
                    'fecha'  => date('Y-m-d H:i:s'),
                    'nota'   => $nota ?: 'Estado actualizado'
                ];
                break;
            }
        }
        guardarJSON('inscripciones.json', $inscripciones);
        header('Location: lista.php?ok=1');
        exit;
    }
}

$inscripciones = leerJSON('inscripciones.json');

// ── Filtros ───────────────────────────────────────────────────
$filtro_estado  = limpiar($_GET['estado'] ?? '');
$filtro_carrera = limpiar($_GET['carrera'] ?? '');
$busqueda       = limpiar($_GET['q'] ?? '');

$filtradas = array_filter($inscripciones, function($s) use ($filtro_estado, $filtro_carrera, $busqueda) {
    if ($filtro_estado  && $s['estado']  !== $filtro_estado)  return false;
    if ($filtro_carrera && $s['carrera'] !== $filtro_carrera) return false;
    if ($busqueda && !str_contains(strtolower($s['nombre'] . $s['id']), strtolower($busqueda))) return false;
    return true;
});
$filtradas = array_reverse($filtradas); // más recientes primero

$carreras_unicas = array_unique(array_column($inscripciones, 'carrera'));
?>
<?php include 'includes/header.php'; ?>

<div class="container py-5">

  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h4 mb-0"><i class="bi bi-list-ul me-2"></i>Solicitudes de inscripción</h2>
    <a href="nueva_inscripcion.php" class="btn btn-primary btn-sm">
      <i class="bi bi-plus-circle me-1"></i> Nueva
    </a>
  </div>

  <?php if (!empty($_GET['ok'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
      <i class="bi bi-check-circle me-1"></i> Estado actualizado correctamente.
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <!-- Filtros -->
  <div class="card mb-4">
    <div class="card-body">
      <form method="GET" class="row g-2 align-items-end">
        <div class="col-md-4">
          <label class="form-label small text-muted mb-1">Buscar</label>
          <input type="text" name="q" class="form-control form-control-sm"
                 placeholder="Nombre o ID..." value="<?= htmlspecialchars($busqueda) ?>">
        </div>
        <div class="col-md-3">
          <label class="form-label small text-muted mb-1">Estado</label>
          <select name="estado" class="form-select form-select-sm">
            <option value="">Todos</option>
            <?php foreach (['pendiente','en_revision','aprobado','rechazado'] as $e): ?>
              <option value="<?= $e ?>" <?= $filtro_estado === $e ? 'selected' : '' ?>>
                <?= ucfirst(str_replace('_',' ',$e)) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label small text-muted mb-1">Carrera</label>
          <select name="carrera" class="form-select form-select-sm">
            <option value="">Todas</option>
            <?php foreach ($carreras_unicas as $c): ?>
              <option value="<?= htmlspecialchars($c) ?>" <?= $filtro_carrera === $c ? 'selected' : '' ?>>
                <?= htmlspecialchars($c) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-2 d-flex gap-1">
          <button type="submit" class="btn btn-primary btn-sm flex-fill">
            <i class="bi bi-funnel"></i> Filtrar
          </button>
          <a href="lista.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-x"></i></a>
        </div>
      </form>
    </div>
  </div>

  <!-- Tabla -->
  <?php if (empty($filtradas)): ?>
    <div class="alert alert-info">No hay solicitudes que coincidan con los filtros.</div>
  <?php else: ?>
    <div class="card">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead>
            <tr>
              <th>ID</th>
              <th>Estudiante</th>
              <th>Carrera</th>
              <th>Semestre</th>
              <th>Materias</th>
              <th>Estado</th>
              <th>Fecha</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($filtradas as $s): ?>
            <tr>
              <td><code class="small"><?= limpiar($s['id']) ?></code></td>
              <td>
                <div class="fw-semibold"><?= limpiar($s['nombre']) ?></div>
                <small class="text-muted"><?= limpiar($s['email']) ?></small>
              </td>
              <td><?= limpiar($s['carrera']) ?></td>
              <td class="text-center"><?= $s['semestre'] ?>°</td>
              <td>
                <?php foreach ($s['materias'] as $m): ?>
                  <span class="badge bg-light text-dark border d-block mb-1" style="font-size:.72rem; text-align:left;">
                    <?= limpiar($m['codigo']) ?> — <?= limpiar($m['nombre']) ?>
                  </span>
                <?php endforeach; ?>
              </td>
              <td><?= badgeEstado($s['estado']) ?></td>
              <td class="text-muted small"><?= limpiar($s['fecha']) ?></td>
              <td>
                <!-- Botón cambiar estado -->
                <button class="btn btn-outline-secondary btn-sm"
                        data-bs-toggle="modal" data-bs-target="#modalEstado"
                        data-id="<?= limpiar($s['id']) ?>"
                        data-nombre="<?= limpiar($s['nombre']) ?>"
                        data-estado="<?= limpiar($s['estado']) ?>">
                  <i class="bi bi-pencil"></i>
                </button>
                <a href="consultar.php?id=<?= urlencode($s['id']) ?>"
                   class="btn btn-outline-primary btn-sm">
                  <i class="bi bi-eye"></i>
                </a>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <div class="card-footer text-muted small">
        Mostrando <?= count($filtradas) ?> de <?= count($inscripciones) ?> solicitudes
      </div>
    </div>
  <?php endif; ?>

</div>

<!-- Modal: cambiar estado -->
<div class="modal fade" id="modalEstado" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST">
        <div class="modal-header">
          <h5 class="modal-title"><i class="bi bi-pencil-square me-1"></i> Actualizar estado</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <p class="text-muted small mb-3">Solicitud de: <strong id="modalNombre"></strong></p>
          <input type="hidden" name="id_solicitud" id="modalIdInput">
          <input type="hidden" name="cambiar_estado" value="1">
          <div class="mb-3">
            <label class="form-label fw-semibold">Nuevo estado</label>
            <select name="nuevo_estado" id="modalSelectEstado" class="form-select">
              <option value="pendiente">Pendiente</option>
              <option value="en_revision">En revisión</option>
              <option value="aprobado">Aprobado</option>
              <option value="rechazado">Rechazado</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Nota (opcional)</label>
            <input type="text" name="nota" class="form-control"
                   placeholder="Ej: Verificado por secretaría">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary">Guardar cambio</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.getElementById('modalEstado').addEventListener('show.bs.modal', function(e) {
  const btn = e.relatedTarget;
  document.getElementById('modalIdInput').value    = btn.dataset.id;
  document.getElementById('modalNombre').textContent = btn.dataset.nombre;
  document.getElementById('modalSelectEstado').value = btn.dataset.estado;
});
</script>

<?php include 'includes/footer.php'; ?>
