# Sistema de Trámites Digitales — UMSA
### Proyecto Multimedia · Actividad Grupal A

Plataforma de trámites BPM para títulos y certificados con datos almacenados en JSON.

---

## Tecnologías usadas

- **PHP 8+** — lógica del servidor y manejo de archivos JSON
- **Bootstrap 5.3** — interfaz responsive (CDN, sin instalación)
- **Bootstrap Icons** — iconografía (CDN)
- **JSON** — almacenamiento de datos (sin MySQL/PostgreSQL)

---

## Estructura del proyecto

```
tramites_umsa/
├── certificado/              ← Módulo de certificados y verificación
├── titulo/                   ← Módulo de trámites de título profesional
├── index.php                 ← Dashboard principal con estadísticas
├── includes/
│   ├── funciones.php         ← Helpers: leerJSON, guardarJSON, etc.
│   ├── header.php            ← Navbar y head HTML
│   └── footer.php            ← Footer y scripts JS
└── data/
    ├── certificados.json    ← Datos de solicitudes de certificados
    └── titulos.json         ← Datos de solicitudes de título
```

---

## Instalación local

### Opción A — PHP built-in server (más simple)
```bash
# Clonar o descomprimir el proyecto
cd tramites_umsa

# Levantar servidor PHP (requiere PHP 8+ instalado)
php -S localhost:8000

# Abrir en el navegador:
# http://localhost:8000
```


## Flujo del proyecto

```
Usuario → Dashboard → Selección de trámite → Certificado / Título
```

---

## Funcionalidades implementadas

- Dashboard responsive con navegación por roles
- Trámite de título profesional con seguimiento por etapas
- Trámite de certificado con verificación y emisión
- Interfaz responsive con Bootstrap 5
- Estructura modular por directorios de trámite

---
