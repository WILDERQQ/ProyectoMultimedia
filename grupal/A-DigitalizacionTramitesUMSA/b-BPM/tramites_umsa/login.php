<?php
require_once 'includes/auth.php';
require_once 'includes/funciones.php';

// Si ya está logueado, redirigir
if (estaLogueado()) {
    header('Location: index.php');
    exit;
}

$error  = '';
$prefix = '';
$titulo = 'Ingresar — Sistema de Trámites UMSA';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = trim($_POST['usuario'] ?? '');
    $pass = trim($_POST['password'] ?? '');

    $u = verificarCredenciales($user, $pass);
    if ($u) {
        iniciarSesion($u);
        flashMsg('exito', '¡Bienvenido, ' . $u['nombre'] . '! (' . etiquetaRol($u['rol']) . ')');
        header('Location: index.php');
        exit;
    } else {
        $error = 'Usuario o contraseña incorrectos.';
    }
}

// Usuarios de demo para mostrar en pantalla
$demos = [
    ['rol'=>'Estudiante',         'user'=>'estudiante', 'pass'=>'est123', 'icono'=>'person-fill',      'color'=>'primary'],
    ['rol'=>'Secretaría',         'user'=>'secretaria', 'pass'=>'sec123', 'icono'=>'person-check-fill','color'=>'info'],
    ['rol'=>'Decanatura',         'user'=>'decano',     'pass'=>'dec123', 'icono'=>'award-fill',       'color'=>'warning'],
    ['rol'=>'Registros',          'user'=>'registros',  'pass'=>'reg123', 'icono'=>'archive-fill',     'color'=>'success'],
    ['rol'=>'Departamento Legal', 'user'=>'legal',      'pass'=>'leg123', 'icono'=>'shield-fill-check','color'=>'secondary'],
    ['rol'=>'Administrador',      'user'=>'admin',      'pass'=>'adm123', 'icono'=>'gear-fill',        'color'=>'dark'],
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= $titulo ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    body { background:linear-gradient(135deg,#003082 0%,#0d47a1 60%,#1565c0 100%); min-height:100vh; font-family:'Inter',sans-serif; }
    .login-card { border:none; border-radius:16px; box-shadow:0 8px 40px rgba(0,0,0,.25); max-width:440px; }
    .demo-badge { cursor:pointer; transition:transform .15s,box-shadow .15s; border:1px solid #dee2e6; border-radius:8px; padding:6px 10px; font-size:.78rem; }
    .demo-badge:hover { transform:translateY(-2px); box-shadow:0 4px 12px rgba(0,0,0,.1); }
    .form-control:focus { border-color:#003082; box-shadow:0 0 0 .2rem rgba(0,48,130,.2); }
    .btn-login { background:linear-gradient(135deg,#003082,#0d47a1); border:none; padding:.6rem 1.5rem; }
    .btn-login:hover { opacity:.9; }
  </style>
</head>
<body class="d-flex align-items-center justify-content-center p-3">

<div class="w-100" style="max-width:440px;">
  <!-- Brand -->
  <div class="text-center mb-4">
    <i class="bi bi-mortarboard-fill text-white" style="font-size:3rem;"></i>
    <h1 class="text-white fw-bold mt-2" style="font-size:1.5rem;">Sistema de Trámites</h1>
    <p class="text-white-50 mb-0">Universidad Mayor de San Andrés</p>
  </div>

  <!-- Login Card -->
  <div class="card login-card mx-auto">
    <div class="card-body p-4">
      <h2 class="h5 fw-bold mb-1 text-center">Iniciar Sesión</h2>
      <p class="text-muted text-center small mb-4">Ingresa tus credenciales para continuar</p>

      <?php if ($error): ?>
      <div class="alert alert-danger py-2 small">
        <i class="bi bi-exclamation-triangle me-1"></i><?= htmlspecialchars($error) ?>
      </div>
      <?php endif; ?>

      <form method="POST" action="login.php" id="form-login">
        <div class="mb-3">
          <label for="usuario" class="form-label fw-medium small">Usuario</label>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-person"></i></span>
            <input type="text" class="form-control" id="usuario" name="usuario"
                   placeholder="Ingresa tu usuario" required autocomplete="username">
          </div>
        </div>
        <div class="mb-4">
          <label for="password" class="form-label fw-medium small">Contraseña</label>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-lock"></i></span>
            <input type="password" class="form-control" id="password" name="password"
                   placeholder="Ingresa tu contraseña" required autocomplete="current-password">
          </div>
        </div>
        <button type="submit" class="btn btn-login btn-primary w-100 fw-bold" id="btn-ingresar">
          <i class="bi bi-box-arrow-in-right me-1"></i> Ingresar al Sistema
        </button>
      </form>

      <!-- Cuentas de demo -->
      <hr class="my-4">
      <p class="text-muted text-center small mb-3">
        <i class="bi bi-info-circle me-1"></i>Cuentas de demostración (clic para autocompletar):
      </p>
      <div class="d-flex flex-wrap gap-2 justify-content-center">
        <?php foreach ($demos as $d): ?>
        <div class="demo-badge bg-<?= $d['color'] ?> bg-opacity-10 text-<?= $d['color'] ?>"
             onclick="document.getElementById('usuario').value='<?= $d['user'] ?>';document.getElementById('password').value='<?= $d['pass'] ?>';"
             title="<?= $d['user'] ?> / <?= $d['pass'] ?>" id="demo-<?= $d['user'] ?>">
          <i class="bi bi-<?= $d['icono'] ?> me-1"></i><?= $d['rol'] ?>
          <span class="opacity-50 ms-1">(<?= $d['user'] ?>)</span>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
