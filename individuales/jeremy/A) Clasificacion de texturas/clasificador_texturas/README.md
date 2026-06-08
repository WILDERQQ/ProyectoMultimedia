# Clasificador de Texturas

**Actividad Individual a) — Curso de Multimedia**  
Universidad Mayor de San Andrés (UMSA)

---

## Descripcion

Aplicación de escritorio para la clasificación automática de texturas en imágenes digitales. El sistema analiza cada píxel individualmente y lo asigna a una de las siguientes categorías:

| Clase    | Descripción                          |
|----------|--------------------------------------|
| Cesped   | Superficies con vegetación           |
| Tierra   | Suelo descubierto, caminos de tierra |
| Cemento  | Pavimento claro, concreto            |
| Asfalto  | Carreteras oscuras, pavimento negro  |

El algoritmo combina dos criterios:
- **Analisis de color HSV**: evalúa el tono, saturación y valor de cada píxel.
- **Varianza local 3x3**: mide la textura del vecindario de cada píxel para distinguir superficies homogéneas de rugosas.

---

## Estructura del proyecto

```
clasificador_texturas/
├── assets/
│   └── images/
│       ├── ejemplo_cesped.jpg
│       ├── ejemplo_tierra.jpg
│       ├── ejemplo_cemento.jpg
│       └── ejemplo_asfalto.jpg
├── src/
│   ├── __init__.py
│   └── clasificador.py
├── main.py
├── requirements.txt
└── README.md
```

---

## Requisitos

- Python 3.9 o superior
- Las siguientes librerías (ver `requirements.txt`):

```
Pillow
numpy
```

---

## Instalacion

**1. Clonar o descargar el repositorio**

```bash
git clone https://github.com/JohanRaccon/clasificador-texturas.git
cd clasificador_texturas
```

**2. Instalar dependencias**

```bash
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

- **Cargar imagen**: abre un explorador de archivos para seleccionar cualquier imagen propia (JPG, PNG, BMP, WEBP).
- **Cargar ejemplo**: selecciona una textura del menú desplegable (Cesped, Tierra, Cemento, Asfalto) y carga automáticamente la imagen de muestra correspondiente desde `assets/images/`.

### Seleccionar clase a visualizar

Los botones de radio permiten elegir qué se muestra en el mapa de texturas:

- **Todas**: cada píxel se colorea según su clase detectada.
- **Cesped / Tierra / Cemento / Asfalto**: solo se resalta la clase seleccionada; el resto de la imagen se muestra en escala de grises atenuada.

### Clasificar

Presionar el botón **Clasificar** ejecuta el análisis píxel a píxel. Una barra de progreso indica el avance del procesamiento.

### Guardar resultado

El mapa de texturas generado puede exportarse como PNG o JPG mediante el botón **Guardar resultado**.

---

## Metodologia

### Espacio de color HSV

Cada píxel RGB se convierte a HSV (Hue, Saturation, Value) para separar el tono cromático de la luminosidad, lo que mejora la robustez ante cambios de iluminación.

### Varianza local

Para cada píxel se calcula la varianza de intensidad en su vecindario de 3x3 píxeles. Los valores altos indican superficies rugosas o con patrones (césped, tierra) y los valores bajos indican superficies homogéneas (asfalto, cemento liso).

### Clasificacion por puntaje combinado

Cada píxel recibe puntajes según:
1. Si su tono (H), saturación (S) y valor (V) caen dentro de los rangos de cada clase.
2. Si su varianza local es consistente con la textura esperada de cada clase.

La clase con mayor puntaje acumulado es la asignada.

---

## Herramientas utilizadas

| Herramienta | Version     | Uso                              |
|-------------|-------------|----------------------------------|
| Python      | 3.9+        | Lenguaje principal               |
| Pillow      | 9.0+        | Carga y manipulación de imágenes |
| NumPy       | 1.21+       | Operaciones matriciales          |
| Tkinter     | (incluido)  | Interfaz gráfica                 |

---

## Autores

Actividad desarrollada para el curso de **Multimedia** — Carrera de Informática, UMSA.
