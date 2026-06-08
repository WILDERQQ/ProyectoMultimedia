# Filtro de Suavizado 3x3

**Actividad Individual b) — Curso de Multimedia**  
Universidad Mayor de San Andrés (UMSA)

---

## Descripcion

Aplicación de escritorio que implementa un **filtro de promedio con ventana 3x3** aplicado directamente a nivel de píxel. El filtro reduce el ruido en imágenes digitales suavizando las transiciones abruptas de color entre píxeles vecinos.

El programa permite comparar visualmente el resultado en tres paneles simultáneos:

| Panel       | Contenido                                              |
|-------------|--------------------------------------------------------|
| Antes       | Imagen original (con ruido si aplica)                  |
| Despues     | Imagen procesada por el filtro                         |
| Diferencia  | Diferencia amplificada x5 — muestra el ruido eliminado |

---

## Estructura del proyecto

```
filtro_suavizado/
├── assets/
│   └── images/
│       ├── ejemplo_rostro.jpg     <- imagen de rostro con ruido
│       └── ejemplo_ciudad.jpg     <- imagen de ciudad con ruido
├── src/
│   ├── __init__.py
│   └── filtro.py                  <- logica del filtro a nivel de pixel
├── main.py                        <- interfaz grafica
├── requirements.txt
└── README.md
```

---

## Requisitos

- Python 3.9 o superior
- Librerias listadas en `requirements.txt`

---

## Instalacion

```bash
git clone https://github.com/JohanRaccon/filtro-suavizado.git
cd filtro_suavizado
pip install -r requirements.txt
```

---

## Ejecucion

```bash
python main.py
```

---

## Uso de la aplicacion

### Cargar una imagen

- **Cargar imagen**: abre un explorador para seleccionar cualquier imagen propia (JPG, PNG, BMP, WEBP).
- **Cargar ejemplo**: selecciona entre dos imágenes predefinidas con ruido artificial:
  - **Rostro**: imagen de cara sintetica con ruido gaussiano.
  - **Ciudad**: paisaje urbano con edificios y ruido gaussiano.

### Seleccionar numero de pasadas

El filtro puede aplicarse 1, 2 o 3 veces de manera consecutiva:

| Pasadas | Efecto                                          |
|---------|-------------------------------------------------|
| 1       | Suavizado leve, conserva detalles               |
| 2       | Suavizado moderado, reduce ruido notablemente   |
| 3       | Suavizado fuerte, imagen muy difuminada         |

### Aplicar filtro

El boton **Aplicar filtro** ejecuta el procesamiento en un hilo separado para no bloquear la interfaz. Una barra de progreso muestra el avance.

### Metricas de calidad

Al finalizar el procesamiento se muestran las siguientes métricas:

| Metrica             | Descripcion                                              |
|---------------------|----------------------------------------------------------|
| MSE                 | Error Cuadratico Medio — magnitud total del cambio       |
| MAE                 | Error Absoluto Medio — cambio promedio por pixel         |
| PSNR                | Relacion Señal-Ruido de Pico en dB — calidad percibida   |
| Pixeles modificados | Porcentaje de pixeles con cambio mayor a 5 niveles       |

### Guardar resultado

El boton **Guardar resultado** exporta dos archivos:
- `nombre.png` — imagen suavizada.
- `nombre_diferencia.png` — imagen de diferencia amplificada.

---

## Metodologia

### Filtro de promedio (box filter)

Para cada pixel (i, j) de la imagen, el nuevo valor se calcula como:

```
pixel_nuevo[i,j] = promedio( vecindario 3x3 centrado en (i,j) )
```

El vecindario incluye el pixel central y sus 8 vecinos inmediatos (9 pixeles en total).

### Manejo de bordes — padding espejo

Los pixeles en los bordes de la imagen no tienen vecindario completo. Para evitar artefactos, se aplica **padding espejo (reflect)**: los bordes se extienden replicando los pixeles adyacentes de manera especular.

### Aplicacion en multiples pasadas

Cada pasada toma como entrada el resultado de la anterior, logrando un suavizado acumulativo. Con 3 pasadas, el efecto es equivalente a un filtro gaussiano aproximado.

---

## Herramientas utilizadas

| Herramienta | Version    | Uso                              |
|-------------|------------|----------------------------------|
| Python      | 3.9+       | Lenguaje principal               |
| Pillow      | 9.0+       | Carga y exportacion de imagenes  |
| NumPy       | 1.21+      | Operaciones matriciales          |
| Tkinter     | (incluido) | Interfaz grafica                 |

---

## Autores

Actividad desarrollada para el curso de **Multimedia** — Carrera de Informatica, UMSA.
