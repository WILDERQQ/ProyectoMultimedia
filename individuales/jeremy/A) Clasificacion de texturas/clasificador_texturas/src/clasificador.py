"""
Modulo de procesamiento de imágenes para clasificación de texturas.
Implementa clasificación a nivel de píxel usando:
  - Análisis de color en espacio HSV
  - Varianza local (ventana 3x3) como descriptor de textura
"""
import numpy as np


# ---------------------------------------------------------------------------
# Definición de clases y parámetros
# ---------------------------------------------------------------------------

CLASES = ["Cesped", "Tierra", "Cemento", "Asfalto", "Otro"]

# Color de visualización por clase (RGB)
COLORES_CLASE = {
    "Cesped":  (34,  139, 34),
    "Tierra":  (139, 90,  43),
    "Cemento": (180, 180, 180),
    "Asfalto": (50,  50,  50),
    "Otro":    (190, 90,  190),
}

# Rangos HSV de referencia (H: 0-360, S: 0-1, V: 0-1)
HSV_RANGOS = {
    "Cesped":  {"h": (55,  175), "s": (0.12, 1.00), "v": (0.08, 0.88)},
    "Tierra":  {"h": (8,   42),  "s": (0.18, 0.88), "v": (0.12, 0.78)},
    "Cemento": {"h": (0,   360), "s": (0.00, 0.18), "v": (0.38, 0.94)},
    "Asfalto": {"h": (0,   360), "s": (0.00, 0.32), "v": (0.00, 0.36)},
}

VENTANA = 3  # Tamaño de ventana para cálculo de varianza local


# ---------------------------------------------------------------------------
# Funciones de bajo nivel (operación a nivel de píxel)
# ---------------------------------------------------------------------------

def rgb_a_hsv(r: int, g: int, b: int) -> tuple:
    """
    Convierte un píxel RGB (0-255) a espacio HSV.
    H en [0, 360], S y V en [0.0, 1.0].
    """
    r, g, b = r / 255.0, g / 255.0, b / 255.0
    mx = max(r, g, b)
    mn = min(r, g, b)
    delta = mx - mn

    v = mx
    s = (delta / mx) if mx != 0 else 0.0

    if delta == 0:
        h = 0.0
    elif mx == r:
        h = 60.0 * (((g - b) / delta) % 6)
    elif mx == g:
        h = 60.0 * (((b - r) / delta) + 2)
    else:
        h = 60.0 * (((r - g) / delta) + 4)

    return h, s, v


def calcular_varianza_local(canal_gris: np.ndarray) -> np.ndarray:
    """
    Calcula la varianza dentro de una ventana NxN para cada píxel.
    Se usa padding espejo en los bordes.

    Args:
        canal_gris: array 2D (H, W) con valores de luminancia.
    Returns:
        array 2D (H, W) con varianza local por píxel.
    """
    alto, ancho = canal_gris.shape
    varianza = np.zeros((alto, ancho), dtype=np.float32)
    pad = VENTANA // 2
    img_pad = np.pad(canal_gris.astype(np.float32), pad, mode="reflect")

    for i in range(alto):
        for j in range(ancho):
            bloque = img_pad[i : i + VENTANA, j : j + VENTANA]
            varianza[i, j] = float(np.var(bloque))

    return varianza


def clasificar_pixel(h: float, s: float, v: float, varianza: float) -> str:
    """
    Clasifica un píxel asignando puntajes según rangos HSV y varianza local.
    Combina ambos criterios para mayor precisión.

    Args:
        h, s, v  : valores HSV del píxel.
        varianza : varianza local calculada en ventana 3x3.
    Returns:
        Nombre de la clase asignada.
    """
    puntajes = {}

    for clase, rango in HSV_RANGOS.items():
        h_ok = rango["h"][0] <= h <= rango["h"][1]
        s_ok = rango["s"][0] <= s <= rango["s"][1]
        v_ok = rango["v"][0] <= v <= rango["v"][1]
        puntajes[clase] = int(h_ok) + int(s_ok) + int(v_ok)

    # Ajuste por textura (varianza local)
    if varianza > 600:
        puntajes["Cesped"]  = puntajes.get("Cesped",  0) + 1
        puntajes["Tierra"]  = puntajes.get("Tierra",  0) + 1
    elif varianza > 200:
        puntajes["Tierra"]  = puntajes.get("Tierra",  0) + 1
        puntajes["Cemento"] = puntajes.get("Cemento", 0) + 1
    else:
        puntajes["Cemento"] = puntajes.get("Cemento", 0) + 1
        puntajes["Asfalto"] = puntajes.get("Asfalto", 0) + 2

    mejor = max(puntajes, key=puntajes.get)
    return "Otro" if puntajes[mejor] <= 1 else mejor


# ---------------------------------------------------------------------------
# Clasificación de imagen completa
# ---------------------------------------------------------------------------

def clasificar_imagen(
    img_rgb: np.ndarray,
    filtro_clase: str = "Todas",
    callback: callable = None,
) -> tuple:
    """
    Clasifica cada píxel de la imagen y construye el mapa de texturas.

    Args:
        img_rgb       : array (H, W, 3) uint8 en espacio RGB.
        filtro_clase  : clase a destacar o "Todas" para mostrar todo.
        callback      : función(pct: float) para reportar progreso (0-100).
    Returns:
        resultado (H, W, 3) uint8 : imagen con colores por clase.
        conteos   dict            : cantidad de píxeles por clase.
    """
    alto, ancho, _ = img_rgb.shape
    canal_gris = np.mean(img_rgb, axis=2)
    varianza_mapa = calcular_varianza_local(canal_gris)

    resultado = np.zeros((alto, ancho, 3), dtype=np.uint8)
    conteos = {k: 0 for k in CLASES}

    total = alto * ancho
    paso = max(1, total // 100)

    for i in range(alto):
        for j in range(ancho):
            r, g, b = int(img_rgb[i, j, 0]), int(img_rgb[i, j, 1]), int(img_rgb[i, j, 2])
            h, s, v = rgb_a_hsv(r, g, b)
            var = float(varianza_mapa[i, j])
            clase = clasificar_pixel(h, s, v, var)
            conteos[clase] += 1

            if filtro_clase == "Todas":
                resultado[i, j] = COLORES_CLASE[clase]
            else:
                # Destacar solo la clase seleccionada; el resto en gris oscuro
                if clase == filtro_clase:
                    resultado[i, j] = COLORES_CLASE[clase]
                else:
                    gris = int(0.299 * r + 0.587 * g + 0.114 * b)
                    gris_atenuado = max(0, gris - 60)
                    resultado[i, j] = (gris_atenuado, gris_atenuado, gris_atenuado)

            if callback and (i * ancho + j) % paso == 0:
                pct = ((i * ancho + j) / total) * 100
                callback(pct)

    return resultado, conteos
