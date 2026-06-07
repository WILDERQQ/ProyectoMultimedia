<?php
require_once 'includes/auth.php';
require_once 'includes/funciones.php';

$titulo      = 'Portal de Trámites — UMSA';
$prefix      = '';
$paginaActual= 'inicio';

// Si no está logueado, mostrar el portal público igualmente (puede ver pero no actuar)
?>
<?php include 'includes/header.php'; ?>

<div class="container py-5">

  <!-- Hero Banner -->
  <div class="card mb-5" style="background:linear-gradient(135deg,#003082 0%,#1565c0 100%);color:#fff;border-radius:16px;overflow:hidden;">
    <div class="card-body p-4 p-md-5">
      <div class="row align-items-center">
        <div class="col-md-8">
          <h1 class="fw-bold mb-2" style="font-size:2rem;">
            <i class="bi bi-mortarboard-fill me-2" style="color:#ffc107;"></i>
            Digitalización de Trámites
          </h1>
          <p class="mb-0 opacity-75">
            Sistema BPM de trámites universitarios — Universidad Mayor de San Andrés.<br>
            Gestiona tus trámites de forma digital, rápida y transparente.
          </p>
        </div>
        <div class="col-md-4 text-md-end mt-4 mt-md-0">
          <?php if (!estaLogueado()): ?>
          <a href="login.php" class="btn btn-warning btn-lg fw-bold" id="btn-portal-login">
            <i class="bi bi-box-arrow-in-right me-1"></i>Ingresar
          </a>
          <?php else: ?>
          <div class="text-center">
            <i class="bi bi-person-check-fill" style="font-size:3rem;opacity:.6;"></i>
            <div class="small opacity-75 mt-1">Sesión activa como<br><strong><?= limpiar(usuarioActual()['nombre']) ?></strong></div>
          </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <h2 class="fw-bold mb-4 text-center">Selecciona el Trámite</h2>

  <div class="row g-4 justify-content-center">

    <!-- Card 1: Trámite Solicitud de Título -->
    <div class="col-md-5">
      <div class="card h-100" id="card-titulo" style="border-radius:16px;transition:transform .2s,box-shadow .2s;"
           onmouseover="this.style.transform='translateY(-6px)';this.style.boxShadow='0 12px 32px rgba(0,48,130,.18)'"
           onmouseout="this.style.transform='translateY(0)';this.style.boxShadow=''">
        <!-- Header del card -->
        <div class="card-header text-white p-4"
             style="background:linear-gradient(135deg,#003082 0%,#0d47a1 100%);border-radius:16px 16px 0 0;">
          <div class="d-flex align-items-center gap-3">
            <div style="width:60px;height:60px;background:rgba(255,255,255,.2);border-radius:12px;display:flex;align-items:center;justify-content:center;">
              <i class="bi bi-mortarboard-fill" style="font-size:1.8rem;color:#ffc107;"></i>
            </div>
            <div>
              <h3 class="h5 fw-bold mb-0">Solicitud de Título Profesional</h3>
              <small class="opacity-75">Proceso de titulación universitaria</small>
            </div>
          </div>
        </div>
        <!-- Body -->
        <div class="card-body p-4">
          <p class="text-muted mb-3">
            Gestiona tu solicitud de título profesional de forma digital. El trámite pasa por
            Secretaría, Decanatura y Registros con seguimiento en tiempo real.
          </p>
          <!-- Flujo resumido -->
          <div class="mb-4">
            <p class="small fw-semibold text-muted mb-2">FLUJO DEL PROCESO:</p>
            <?php
            $pasosTitulo = [
              ['icono'=>'person-fill',        'label'=>'Estudiante',   'color'=>'primary'],
              ['icono'=>'person-check-fill',   'label'=>'Secretaría',  'color'=>'info'],
              ['icono'=>'award-fill',          'label'=>'Decano',      'color'=>'warning'],
              ['icono'=>'archive-fill',        'label'=>'Registros',   'color'=>'success'],
              ['icono'=>'check-circle-fill',   'label'=>'Completado',  'color'=>'success'],
            ];
            ?>
            <div class="d-flex align-items-center gap-1 flex-wrap">
              <?php foreach ($pasosTitulo as $i => $p): ?>
              <div class="text-center">
                <div class="rounded-circle d-inline-flex align-items-center justify-content-center bg-<?= $p['color'] ?> bg-opacity-15 text-<?= $p['color'] ?>"
                     style="width:38px;height:38px;">
                  <i class="bi bi-<?= $p['icono'] ?>"></i>
                </div>
                <div style="font-size:.65rem;color:#6c757d;"><?= $p['label'] ?></div>
              </div>
              <?php if ($i < count($pasosTitulo)-1): ?>
              <i class="bi bi-arrow-right text-muted small mb-3"></i>
              <?php endif; ?>
              <?php endforeach; ?>
            </div>
          </div>
          <!-- Roles involucrados -->
          <div class="d-flex flex-wrap gap-1 mb-4">
            <span class="badge bg-primary bg-opacity-10 text-primary">Estudiante</span>
            <span class="badge bg-info bg-opacity-10 text-info">Secretaría</span>
            <span class="badge bg-warning bg-opacity-10 text-warning">Decanatura</span>
            <span class="badge bg-success bg-opacity-10 text-success">Registros</span>
          </div>
        </div>
        <div class="card-footer bg-transparent p-4 pt-0">
          <a href="titulo/index.php" class="btn btn-primary w-100 fw-bold" id="btn-acceder-titulo">
            <i class="bi bi-arrow-right-circle me-1"></i>Acceder al Trámite
          </a>
        </div>
      </div>
    </div>

    <!-- Card 2: Certificado de No Proceso -->
    <div class="col-md-5">
      <div class="card h-100" id="card-certificado" style="border-radius:16px;transition:transform .2s,box-shadow .2s;"
           onmouseover="this.style.transform='translateY(-6px)';this.style.boxShadow='0 12px 32px rgba(21,101,192,.18)'"
           onmouseout="this.style.transform='translateY(0)';this.style.boxShadow=''">
        <!-- Header del card -->
        <div class="card-header text-white p-4"
             style="background:linear-gradient(135deg,#00695c 0%,#00897b 100%);border-radius:16px 16px 0 0;">
          <div class="d-flex align-items-center gap-3">
            <div style="width:60px;height:60px;background:rgba(255,255,255,.2);border-radius:12px;display:flex;align-items:center;justify-content:center;">
              <i class="bi bi-file-earmark-check-fill" style="font-size:1.8rem;color:#a5d6a7;"></i>
            </div>
            <div>
              <h3 class="h5 fw-bold mb-0">Certificado de No Proceso Universitario</h3>
              <small class="opacity-75">Certificación de historial académico</small>
            </div>
          </div>
        </div>
        <!-- Body -->
        <div class="card-body p-4">
          <p class="text-muted mb-3">
            Obtén tu certificado que acredita que no tienes proceso universitario pendiente.
            Verificación en registros + firma legal + emisión del documento oficial.
          </p>
          <!-- Flujo resumido -->
          <div class="mb-4">
            <p class="small fw-semibold text-muted mb-2">FLUJO DEL PROCESO:</p>
            <?php
            $pasosCert = [
              ['icono'=>'person-badge',        'label'=>'Solicitante', 'color'=>'primary'],
              ['icono'=>'person-check-fill',   'label'=>'Secretaría',  'color'=>'info'],
              ['icono'=>'shield-fill-check',   'label'=>'Legal',       'color'=>'secondary'],
              ['icono'=>'file-earmark-check',  'label'=>'Certificado', 'color'=>'success'],
            ];
            ?>
            <div class="d-flex align-items-center gap-1 flex-wrap">
              <?php foreach ($pasosCert as $i => $p): ?>
              <div class="text-center">
                <div class="rounded-circle d-inline-flex align-items-center justify-content-center bg-<?= $p['color'] ?> bg-opacity-15 text-<?= $p['color'] ?>"
                     style="width:38px;height:38px;">
                  <i class="bi bi-<?= $p['icono'] ?>"></i>
                </div>
                <div style="font-size:.65rem;color:#6c757d;"><?= $p['label'] ?></div>
              </div>
              <?php if ($i < count($pasosCert)-1): ?>
              <i class="bi bi-arrow-right text-muted small mb-3"></i>
              <?php endif; ?>
              <?php endforeach; ?>
            </div>
          </div>
          <div class="d-flex flex-wrap gap-1 mb-4">
            <span class="badge bg-primary bg-opacity-10 text-primary">Solicitante</span>
            <span class="badge bg-info bg-opacity-10 text-info">Secretaría</span>
            <span class="badge bg-secondary bg-opacity-10 text-secondary">Legal</span>
          </div>
        </div>
        <div class="card-footer bg-transparent p-4 pt-0">
          <a href="certificado/index.php" class="btn fw-bold w-100 text-white" id="btn-acceder-certificado"
             style="background:linear-gradient(135deg,#00695c,#00897b);">
            <i class="bi bi-arrow-right-circle me-1"></i>Acceder al Trámite
          </a>
        </div>
      </div>
    </div>

  </div><!-- /.row -->

  <!-- Info de credenciales (solo si no está logueado) -->
  <?php if (!estaLogueado()): ?>
  <div class="card mt-5">
    <div class="card-header fw-semibold">
      <i class="bi bi-info-circle me-1"></i>Credenciales de Acceso por Rol
    </div>
    <div class="card-body">
      <div class="row g-3">
        <?php
        $credenciales = [
          ['rol'=>'Estudiante',         'user'=>'estudiante', 'pass'=>'est123', 'color'=>'primary',   'icono'=>'person-fill'],
          ['rol'=>'Secretaría',         'user'=>'secretaria', 'pass'=>'sec123', 'color'=>'info',      'icono'=>'person-check-fill'],
          ['rol'=>'Decanatura',         'user'=>'decano',     'pass'=>'dec123', 'color'=>'warning',   'icono'=>'award-fill'],
          ['rol'=>'Registros',          'user'=>'registros',  'pass'=>'reg123', 'color'=>'success',   'icono'=>'archive-fill'],
          ['rol'=>'Departamento Legal', 'user'=>'legal',      'pass'=>'leg123', 'color'=>'secondary', 'icono'=>'shield-fill-check'],
          ['rol'=>'Administrador',      'user'=>'admin',      'pass'=>'adm123', 'color'=>'dark',      'icono'=>'gear-fill'],
        ];
        foreach ($credenciales as $c):
        ?>
        <div class="col-sm-6 col-md-4">
          <div class="d-flex align-items-center gap-2 p-2 rounded border bg-<?= $c['color'] ?> bg-opacity-10">
            <i class="bi bi-<?= $c['icono'] ?> text-<?= $c['color'] ?> fs-5"></i>
            <div>
              <div class="fw-semibold small"><?= $c['rol'] ?></div>
              <code class="small"><?= $c['user'] ?> / <?= $c['pass'] ?></code>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
  <?php endif; ?>

</div><!-- /.container -->

<?php include 'includes/footer.php'; ?>
