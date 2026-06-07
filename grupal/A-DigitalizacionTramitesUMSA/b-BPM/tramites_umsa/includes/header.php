<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= $titulo ?? 'Sistema de Trámites UMSA' ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    :root {
      --umsa-blue: #003082;
      --umsa-blue-light: #0d47a1;
      --umsa-gold: #ffc107;
    }
    body { background:#f0f4f8; font-family:'Inter',sans-serif; }
    .navbar-umsa { background: linear-gradient(135deg,var(--umsa-blue) 0%,var(--umsa-blue-light) 100%); }
    .navbar-brand .brand-name { color: var(--umsa-gold); font-weight:700; }
    .card { border:none; border-radius:12px; box-shadow:0 2px 12px rgba(0,0,0,.08); }
    .card-header-umsa { background:linear-gradient(135deg,var(--umsa-blue) 0%,var(--umsa-blue-light) 100%); color:#fff; border-radius:12px 12px 0 0 !important; }
    .bpmn-stepper { padding:.75rem 1rem; background:#fff; border-radius:12px; box-shadow:0 1px 6px rgba(0,0,0,.06); }
    .step-circle { transition:all .3s; }
    .badge { font-size:.76rem; }
    .table th { background:#e9ecef; font-size:.82rem; text-transform:uppercase; letter-spacing:.04em; }
    .rol-badge { background:rgba(255,255,255,.2); border-radius:20px; padding:3px 10px; font-size:.8rem; }
    .alert-flash { animation: fadeIn .4s ease; }
    @keyframes fadeIn { from {opacity:0;transform:translateY(-8px);} to {opacity:1;transform:translateY(0);} }
  </style>
</head>
<body>

<?php
// Cargar auth solo si no está cargado
$base = $prefix ?? '';
?>

<nav class="navbar navbar-expand-lg navbar-dark navbar-umsa">
  <div class="container">
    <a class="navbar-brand d-flex align-items-center gap-2" href="<?= $base ?>index.php" id="nav-home-link">
      <i class="bi bi-mortarboard-fill fs-4"></i>
      <div>
        <span class="brand-name">UMSA</span>
        <small class="fw-normal text-white-50 ms-1" style="font-size:.78rem;">Sistema de Trámites</small>
      </div>
    </a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navMenu">
      <ul class="navbar-nav ms-auto align-items-lg-center gap-1">

        <?php if (function_exists('estaLogueado') && estaLogueado()):
              $u   = usuarioActual();
              $rol = $u['rol'];
        ?>
          <!-- Links según rol -->
          <li class="nav-item">
            <a class="nav-link" href="<?= $base ?>index.php" id="nav-inicio">
              <i class="bi bi-house"></i> Inicio
            </a>
          </li>

          <?php if (tieneRol('estudiante','admin')): ?>
          <li class="nav-item">
            <a class="nav-link" href="<?= $base ?>titulo/index.php" id="nav-titulo">
              <i class="bi bi-mortarboard"></i> Trámite Título
            </a>
          </li>
          <?php endif; ?>

          <?php if (tieneRol('estudiante','admin')): ?>
          <li class="nav-item">
            <a class="nav-link" href="<?= $base ?>certificado/index.php" id="nav-cert">
              <i class="bi bi-file-earmark-text"></i> Certificado
            </a>
          </li>
          <?php endif; ?>

          <?php if (tieneRol('secretaria','decano','registros','legal','admin')): ?>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown" id="nav-tramites">
              <i class="bi bi-ui-checks"></i> Mis Trámites
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
              <?php if (tieneRol('secretaria','admin')): ?>
              <li><a class="dropdown-item" href="<?= $base ?>titulo/revisar.php" id="nav-revisar-titulo">
                <i class="bi bi-check2-square me-2"></i>Revisar Títulos</a></li>
              <li><a class="dropdown-item" href="<?= $base ?>certificado/verificar.php" id="nav-verificar-cert">
                <i class="bi bi-search me-2"></i>Verificar Certificados</a></li>
              <?php endif; ?>
              <?php if (tieneRol('decano','admin')): ?>
              <li><a class="dropdown-item" href="<?= $base ?>titulo/aprobar.php" id="nav-aprobar">
                <i class="bi bi-award me-2"></i>Aprobar Solicitudes</a></li>
              <?php endif; ?>
              <?php if (tieneRol('registros','admin')): ?>
              <li><a class="dropdown-item" href="<?= $base ?>titulo/generar_cert.php" id="nav-generar">
                <i class="bi bi-file-earmark-plus me-2"></i>Generar Resoluciones</a></li>
              <?php endif; ?>
              <?php if (tieneRol('legal','admin')): ?>
              <li><a class="dropdown-item" href="<?= $base ?>certificado/validar.php" id="nav-validar">
                <i class="bi bi-shield-check me-2"></i>Validar Certificados</a></li>
              <?php endif; ?>
            </ul>
          </li>
          <?php endif; ?>

          <!-- Usuario logueado -->
          <li class="nav-item ms-2 d-flex align-items-center">
            <span class="rol-badge text-white" title="<?= limpiar($u['nombre']) ?>">
              <i class="bi bi-<?= iconoRol($rol) ?> me-1"></i>
              <?= limpiar($u['nombre']) ?>
              <small class="opacity-75 ms-1">(<?= etiquetaRol($rol) ?>)</small>
            </span>
          </li>
          <li class="nav-item">
            <a href="<?= $base ?>logout.php" class="btn btn-sm btn-outline-warning ms-2" id="btn-logout">
              <i class="bi bi-box-arrow-right"></i> Salir
            </a>
          </li>

        <?php else: ?>
          <li class="nav-item">
            <a href="<?= $base ?>login.php" class="btn btn-warning btn-sm fw-bold" id="btn-login-nav">
              <i class="bi bi-box-arrow-in-right me-1"></i>Ingresar
            </a>
          </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<!-- Flash messages -->
<?php
if (function_exists('getFlash') && $flash = getFlash()):
    $flashTipo = ['exito'=>'success','error'=>'danger','info'=>'info','aviso'=>'warning'][$flash['tipo']] ?? $flash['tipo'];
?>
<div class="container mt-3">
  <div class="alert alert-<?= $flashTipo ?> alert-dismissible fade show alert-flash" role="alert">
    <?= htmlspecialchars($flash['mensaje'], ENT_QUOTES, 'UTF-8') ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
</div>
<?php endif; ?>
