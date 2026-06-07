<?php
/**
 * auth.php — Gestión de sesiones y autenticación
 * Sistema de Trámites UMSA
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ── Usuarios del sistema ───────────────────────────────────────────
function getUsuarios(): array {
    return [
        ['usuario'=>'estudiante', 'password'=>'est123', 'rol'=>'estudiante',
         'nombre'=>'Juan Carlos Pérez Mamani', 'carnet'=>'12345678'],
        ['usuario'=>'secretaria', 'password'=>'sec123', 'rol'=>'secretaria',
         'nombre'=>'María Elena López García'],
        ['usuario'=>'decano',     'password'=>'dec123', 'rol'=>'decano',
         'nombre'=>'Dr. Roberto Silva Condori'],
        ['usuario'=>'registros',  'password'=>'reg123', 'rol'=>'registros',
         'nombre'=>'Carmen Flores Quispe'],
        ['usuario'=>'legal',      'password'=>'leg123', 'rol'=>'legal',
         'nombre'=>'Lic. Alberto Mamani Rojas'],
        ['usuario'=>'admin',      'password'=>'adm123', 'rol'=>'admin',
         'nombre'=>'Administrador del Sistema'],
    ];
}

// ── Funciones de sesión ────────────────────────────────────────────
function estaLogueado(): bool {
    return isset($_SESSION['usuario']);
}

function usuarioActual(): array {
    return $_SESSION['usuario'] ?? [];
}

function rolActual(): string {
    return $_SESSION['usuario']['rol'] ?? '';
}

function tieneRol(string ...$roles): bool {
    return in_array(rolActual(), $roles);
}

function requiereLogin(string ...$roles): void {
    if (!estaLogueado()) {
        $base = calcularBase();
        header("Location: {$base}login.php");
        exit;
    }
    if (!empty($roles) && !tieneRol(...$roles)) {
        $base = calcularBase();
        header("Location: {$base}acceso_denegado.php");
        exit;
    }
}

// Calcula el prefijo relativo ('' o '../') según la profundidad
function calcularBase(): string {
    $dir = basename(dirname($_SERVER['SCRIPT_FILENAME']));
    return in_array($dir, ['titulo','certificado']) ? '../' : '';
}

function iniciarSesion(array $usuario): void {
    $_SESSION['usuario'] = $usuario;
    $_SESSION['login_time'] = time();
}

function cerrarSesion(): void {
    $_SESSION = [];
    session_destroy();
}

function verificarCredenciales(string $user, string $pass): ?array {
    foreach (getUsuarios() as $u) {
        if ($u['usuario'] === $user && $u['password'] === $pass) {
            return $u;
        }
    }
    return null;
}

// ── Etiquetas e íconos por rol ─────────────────────────────────────
function etiquetaRol(string $rol): string {
    return [
        'estudiante'  => 'Estudiante',
        'secretaria'  => 'Secretaría de Carrera',
        'decano'      => 'Decanatura',
        'registros'   => 'Registros y RRHH',
        'legal'       => 'Departamento Legal',
        'admin'       => 'Administrador',
    ][$rol] ?? ucfirst($rol);
}

function iconoRol(string $rol): string {
    return [
        'estudiante' => 'person-fill',
        'secretaria' => 'person-check-fill',
        'decano'     => 'award-fill',
        'registros'  => 'archive-fill',
        'legal'      => 'shield-fill-check',
        'admin'      => 'gear-fill',
    ][$rol] ?? 'person';
}

// ── Mensajes flash ─────────────────────────────────────────────────
function flashMsg(string $tipo, string $mensaje): void {
    $_SESSION['flash'] = ['tipo' => $tipo, 'mensaje' => $mensaje];
}

function getFlash(): ?array {
    if (isset($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $f;
    }
    return null;
}
