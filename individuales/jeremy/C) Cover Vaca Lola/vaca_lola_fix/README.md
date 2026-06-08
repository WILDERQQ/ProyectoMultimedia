# La Vaca Lola — Cover Multimedia
**Actividad Individual c) — Multimedia UMSA**

Produccion multimedia de la cancion infantil "La Vaca Lola" al estilo
urbano de Bad Bunny / J Balvin, con monigotes animados sincronizados
al ritmo del audio.

---

## Requisitos

```
Python 3.10+
pygame
```

Instalar dependencias:
```bash
pip install pygame
```

---

## Ejecucion

```bash
python main.py
```

---

## Estructura del proyecto

```
vaca_lola/
│
├── main.py                  # Punto de entrada
├── config.py                # Constantes globales (ventana, colores, BPM)
├── README.md
│
├── assets/
│   ├── audio/               # Colocar aqui el MP3 de la cancion
│   └── fonts/               # Fuentes adicionales (opcional)
│
└── src/
    ├── escena.py            # Controlador principal / bucle de juego
    │
    ├── personajes/
    │   ├── monigote.py      # Stick-figure humano animado (Bad Bunny, J Balvin)
    │   └── vaca.py          # Vaca animada con bounce y cola oscilante
    │
    ├── audio/
    │   ├── gestor_audio.py  # Carga, reproduccion y control de audio
    │   └── letras.py        # Letras sincronizadas con marcas de tiempo
    │
    ├── ui/
    │   ├── pantalla_menu.py # Pantalla de inicio y seleccion de archivo
    │   ├── pantalla_show.py # Show principal con escenario y particulas
    │   ├── hud.py           # HUD superpuesto (beat, estado audio, controles)
    │   └── particulas.py    # Sistema de particulas decorativas
    │
    └── utils/
        ├── draw.py          # Funciones de dibujo de bajo nivel (pygame)
        └── anim.py          # Funciones matematicas de animacion (pulso, rebote)
```

---

## Controles

| Tecla     | Accion                        |
|-----------|-------------------------------|
| ESPACIO   | Pausar / Reanudar             |
| R         | Volver al menu                |
| ESC       | Menu (en show) / Salir        |

---

## Ajustar sincronizacion de letras

Editar `src/audio/letras.py` y modificar los tiempos en segundos
de cada linea sin tocar ningun otro archivo.

---

## Creditos

- Curso: Multimedia — UMSA
- Identidad artistica: Bad Bunny / J Balvin
