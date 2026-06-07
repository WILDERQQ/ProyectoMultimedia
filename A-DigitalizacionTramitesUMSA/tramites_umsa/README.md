# Sistema de Trámites Digitales — UMSA
### Proyecto Multimedia · Actividad Grupal A

Sistema de gestión de inscripción de materias con almacenamiento en archivos JSON (sin base de datos tradicional).

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
├── index.php                 ← Dashboard principal con estadísticas
├── nueva_inscripcion.php     ← Formulario dinámico de inscripción
├── guardar_inscripcion.php   ← Procesa el POST y guarda en JSON
├── confirmacion.php          ← Confirmación tras enviar solicitud
├── consultar.php             ← Consulta de estado por ID
├── lista.php                 ← Lista/admin de todas las solicitudes
├── includes/
│   ├── funciones.php         ← Helpers: leerJSON, guardarJSON, etc.
│   ├── header.php            ← Navbar y head HTML
│   └── footer.php            ← Footer y scripts JS
└── data/
    ├── inscripciones.json    ← Base de datos de solicitudes
    └── materias.json         ← Catálogo de materias por carrera
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

### Opción B — XAMPP / WAMP / Laragon
1. Copiar la carpeta `tramites_umsa/` dentro de `htdocs/` (XAMPP) o `www/` (WAMP).
2. Iniciar Apache.
3. Abrir: `http://localhost/tramites_umsa/`

### Opción C — Hosting compartido
- Subir todos los archivos vía FTP.
- El directorio `data/` debe tener permisos de escritura (`chmod 755` o `775`).

---

## Flujo del trámite

```
Estudiante → Formulario → Validación → Guardar JSON → Confirmación + ID
                                                              ↓
Admin → lista.php → Filtrar → Cambiar estado → Historial actualizado
                                                              ↓
Estudiante → consultar.php → Ingresar ID → Ver estado actual
```

---

## Funcionalidades implementadas

- [x] Formulario dinámico (materias cambian según carrera seleccionada)
- [x] Límite de 5 materias por solicitud
- [x] Validación en frontend (JS) y backend (PHP)
- [x] Almacenamiento CRUD en JSON sin base de datos
- [x] Generación de ID único por solicitud
- [x] Consulta de estado por ID
- [x] Panel de administración con filtros
- [x] Cambio de estado con historial de movimientos
- [x] Interfaz responsive con Bootstrap 5

---

## Próximos pasos sugeridos

- Agregar trámite de emisión de certificados
- Exportar solicitudes a PDF desde lista.php
- Agregar autenticación básica para el panel admin
