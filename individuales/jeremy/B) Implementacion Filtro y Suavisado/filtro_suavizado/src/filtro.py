import numpy as np

VENTANA = 3 

# ---------------------------------------------------------------------------
# Filtro principal
# ---------------------------------------------------------------------------

def filtro_promedio_3x3(
    img_array: np.ndarray,
    iteraciones: int = 1,
    callback: callable = None,
) -> np.ndarray:

    resultado = img_array.astype(np.float32).copy()

    for it in range(iteraciones):
        alto, ancho, _ = resultado.shape

        # Padding espejo de 1 píxel en todos los bordes
        img_pad = np.pad(resultado, ((1, 1), (1, 1), (0, 0)), mode="reflect")
        salida = np.zeros_like(resultado)

        total = alto * ancho
        paso = max(1, total // 100)
        procesados = 0

        # Recorrido píxel a píxel — núcleo del algoritmo
        for i in range(alto):
            for j in range(ancho):
                # Ventana 3x3 centrada en (i, j) — shape (3, 3, C)
                ventana = img_pad[i : i + VENTANA, j : j + VENTANA, :]
                # Promedio de los 9 píxeles por canal
                salida[i, j, :] = np.mean(ventana, axis=(0, 1))

                procesados += 1
                if callback and procesados % paso == 0:
                    pct_base = (it / iteraciones) * 100
                    pct_px = (procesados / total) * (100 / iteraciones)
                    callback(pct_base + pct_px)

        resultado = salida

    return np.clip(resultado, 0, 255).astype(np.uint8)


# ---------------------------------------------------------------------------
# Imagen de diferencia
# ---------------------------------------------------------------------------

def calcular_diferencia(
    original: np.ndarray,
    suavizada: np.ndarray,
    amplificacion: int = 5,
) -> np.ndarray:
    
    diff = np.abs(original.astype(np.int16) - suavizada.astype(np.int16))
    return np.clip(diff * amplificacion, 0, 255).astype(np.uint8)


# ---------------------------------------------------------------------------
# Métricas de calidad
# ---------------------------------------------------------------------------

def calcular_metricas(original: np.ndarray, suavizada: np.ndarray) -> dict:
   
    orig_f = original.astype(np.float32)
    suav_f = suavizada.astype(np.float32)

    mse = float(np.mean((orig_f - suav_f) ** 2))
    mae = float(np.mean(np.abs(orig_f - suav_f)))

    if mse > 0:
        psnr = 10 * np.log10((255.0 ** 2) / mse)
    else:
        psnr = float("inf")

    diff = np.abs(original.astype(np.int16) - suavizada.astype(np.int16))
    pixeles_modificados = float(np.mean(diff > 5) * 100)

    return {
        "mse": mse,
        "mae": mae,
        "psnr": psnr,
        "pixeles_modificados_pct": pixeles_modificados,
    }
