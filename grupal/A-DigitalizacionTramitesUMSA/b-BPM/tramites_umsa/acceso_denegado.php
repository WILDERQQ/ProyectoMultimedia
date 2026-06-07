<?php
require_once 'includes/auth.php';
require_once 'includes/funciones.php';
$titulo = 'Acceso Denegado — UMSA';
$prefix = '';
?>
<?php include 'includes/header.php'; ?>
<div class="container py-5 text-center">
  <i class="bi bi-shield-lock text-danger" style="font-size:4rem;"></i>
  <h1 class="h3 mt-3">Acceso Denegado</h1>
  <p class="text-muted">No tienes permisos para acceder a esta página con tu rol actual.</p>
  <a href="index.php" class="btn btn-primary">
    <i class="bi bi-house me-1"></i>Volver al Inicio
  </a>
</div>
<?php include 'includes/footer.php'; ?>
