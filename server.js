/**
 * Servidor de Orquestación y Desarrollo - Proyecto Multimedia
 * Levanta un servidor estático para la raíz (puerto 8080) y un servidor PHP para Trámites UMSA (puerto 8000).
 * No tiene dependencias externas, corre nativamente con Node.js.
 */

const http = require('http');
const fs = require('fs');
const path = require('path');
const { spawn, exec } = require('child_process');

const STATIC_PORT = 8080;
const PHP_PORT = 8000;
const ROOT_DIR = __dirname;
const PHP_DIR = path.join(ROOT_DIR, 'grupal', 'A-DigitalizacionTramitesUMSA', 'b-BPM', 'tramites_umsa');

const MIME_TYPES = {
    '.html': 'text/html; charset=utf-8',
    '.css': 'text/css',
    '.js': 'application/javascript',
    '.json': 'application/json',
    '.png': 'image/png',
    '.jpg': 'image/jpeg',
    '.jpeg': 'image/jpeg',
    '.gif': 'image/gif',
    '.svg': 'image/svg+xml',
    '.glb': 'model/gltf-binary',
    '.gltf': 'model/gltf+json',
    '.ico': 'image/x-icon',
    '.mp3': 'audio/mpeg',
    '.wav': 'audio/wav',
    '.ogg': 'audio/ogg'
};

// --- 1. LEVANTAR SERVIDOR PHP ---
console.log('\x1b[36m%s\x1b[0m', '==================================================');
console.log('\x1b[36m%s\x1b[0m', '   INICIANDO ORQUESTRADOR DE SERVIDORES MULTIMEDIA  ');
console.log('\x1b[36m%s\x1b[0m', '==================================================');

console.log(`\n[PHP] Iniciando servidor PHP en http://localhost:${PHP_PORT}...`);
const phpProcess = spawn('php', ['-S', `localhost:${PHP_PORT}`, '-t', PHP_DIR]);

phpProcess.stdout.on('data', (data) => {
    console.log(`[PHP Log]: ${data.toString().trim()}`);
});

phpProcess.stderr.on('data', (data) => {
    // El servidor PHP de desarrollo escribe logs de peticiones en stderr por defecto
    const log = data.toString().trim();
    if (log.includes('Accepted') || log.includes('Closing') || log.includes('Development Server')) {
        console.log(`[PHP Server]: ${log}`);
    } else {
        console.error(`[PHP Error]: ${log}`);
    }
});

phpProcess.on('error', (err) => {
    console.error('\x1b[31m%s\x1b[0m', `[PHP Falló]: No se pudo iniciar PHP. Asegúrate de tener PHP instalado y en tu variable PATH.`);
    console.error(err.message);
});

// --- 2. CREAR SERVIDOR ESTÁTICO (NODE.JS NATIVO) ---
const staticServer = http.createServer((req, res) => {
    // Decodificar URI para soportar nombres de carpetas con espacios
    let safeUrl = decodeURIComponent(req.url);
    
    // Evitar salir del directorio raíz (seguridad básica)
    let filePath = path.join(ROOT_DIR, safeUrl.split('?')[0]);
    if (!filePath.startsWith(ROOT_DIR)) {
        res.statusCode = 403;
        res.end('Acceso denegado');
        return;
    }

    // Si es un directorio, buscar index.html
    fs.stat(filePath, (err, stats) => {
        if (!err && stats.isDirectory()) {
            filePath = path.join(filePath, 'index.html');
        }

        const ext = path.extname(filePath).toLowerCase();
        const contentType = MIME_TYPES[ext] || 'application/octet-stream';

        fs.readFile(filePath, (error, content) => {
            if (error) {
                if (error.code === 'ENOENT') {
                    res.writeHead(404, { 'Content-Type': 'text/html' });
                    res.end('<h1>404 Archivo no encontrado</h1><p>El recurso solicitado no existe en el proyecto.</p>', 'utf-8');
                } else {
                    res.writeHead(500);
                    res.end(`Error interno del servidor: ${error.code}`);
                }
            } else {
                res.writeHead(200, { 'Content-Type': contentType });
                res.end(content, 'utf-8');
            }
        });
    });
});

staticServer.listen(STATIC_PORT, () => {
    console.log('\x1b[32m%s\x1b[0m', `[Static Web] Servidor estático corriendo en http://localhost:${STATIC_PORT}`);
    console.log(`[Static Web] Sirviendo archivos desde: ${ROOT_DIR}`);
    
    // Abrir navegador automáticamente en Windows
    const url = `http://localhost:${STATIC_PORT}`;
    const startCmd = process.platform === 'win32' ? 'start' : process.platform === 'darwin' ? 'open' : 'xdg-open';
    
    setTimeout(() => {
        exec(`${startCmd} ${url}`, (err) => {
            if (err) {
                console.log(`[Navegador] Por favor abre manualmente: ${url}`);
            } else {
                console.log('[Navegador] Panel principal abierto con éxito.');
            }
        });
    }, 1000);
});

// --- 3. MANEJO DE SALIDA ---
function cleanup() {
    console.log('\n[Orquestador] Cerrando servidores...');
    if (phpProcess) {
        phpProcess.kill();
        console.log('[Orquestador] Servidor PHP finalizado.');
    }
    staticServer.close(() => {
        console.log('[Orquestador] Servidor estático finalizado.');
        process.exit();
    });
}

process.on('SIGINT', cleanup);
process.on('SIGTERM', cleanup);
process.on('exit', () => {
    if (phpProcess) phpProcess.kill();
});
