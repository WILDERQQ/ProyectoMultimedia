"""
Clasificador de Texturas - Actividad Individual - Multimedia
Interfaz gráfica construida con Tkinter + PIL.
a) Desarrollar una aplicación capaz de diferenciar superficies dentro de una imagen, por ejemplo:
césped, tierra, cemento o asfalto, aplicando principios similares a los utilizados en clasificación
de imágenes satelitales.
"""

import tkinter as tk
from tkinter import filedialog, ttk
import threading
import os
import sys

from PIL import Image, ImageTk
import numpy as np

# Ajustar ruta para importar desde src/
BASE_DIR = os.path.dirname(os.path.abspath(__file__))
sys.path.insert(0, BASE_DIR)

from src.clasificador import (
    clasificar_imagen,
    CLASES,
    COLORES_CLASE,
)

# ---------------------------------------------------------------------------
# Rutas de imágenes de ejemplo
# ---------------------------------------------------------------------------

EJEMPLOS = {
    "Cesped":  os.path.join(BASE_DIR, "assets", "images", "ejemplo_cesped.jpg"),
    "Tierra":  os.path.join(BASE_DIR, "assets", "images", "ejemplo_tierra.jpg"),
    "Cemento": os.path.join(BASE_DIR, "assets", "images", "ejemplo_cemento.jpg"),
    "Asfalto": os.path.join(BASE_DIR, "assets", "images", "ejemplo_asfalto.jpg"),
}

MAX_DIM = 420   # Dimensión máxima para procesamiento

# ---------------------------------------------------------------------------
# Paleta de colores de la UI
# ---------------------------------------------------------------------------

COLOR_BG        = "#0f1117"
COLOR_PANEL     = "#1a1d27"
COLOR_BORDE     = "#2a2d3a"
COLOR_ACENTO    = "#4f8ef7"
COLOR_TEXTO     = "#d4d8e8"
COLOR_TEXTO_SEC = "#6b7280"
COLOR_BTN       = "#252838"
COLOR_BTN_HOV   = "#323650"
COLOR_SEL       = "#2a3a6a"


# ---------------------------------------------------------------------------
# Clase principal de la aplicación
# ---------------------------------------------------------------------------

class AplicacionClasificador(tk.Tk):
    """Ventana principal del clasificador de texturas."""

    def __init__(self):
        super().__init__()
        self.title("Clasificador de Texturas - Multimedia UMSA")
        self.configure(bg=COLOR_BG)
        self.resizable(True, True)
        self.minsize(1000, 660)

        # Estado interno
        self.img_original  = None   # PIL Image original
        self.img_resultado = None   # PIL Image resultado clasificado
        self.img_array     = None   # numpy array de la imagen cargada
        self.filtro_clase  = tk.StringVar(value="Todas")

        self._configurar_estilos()
        self._construir_ui()

    # -----------------------------------------------------------------------
    # Estilos ttk
    # -----------------------------------------------------------------------

    def _configurar_estilos(self):
        style = ttk.Style()
        style.theme_use("default")

        style.configure(
            "Progreso.Horizontal.TProgressbar",
            troughcolor=COLOR_PANEL,
            background=COLOR_ACENTO,
            darkcolor=COLOR_ACENTO,
            lightcolor=COLOR_ACENTO,
            bordercolor=COLOR_BG,
        )
        style.configure(
            "TCombobox",
            fieldbackground=COLOR_BTN,
            background=COLOR_BTN,
            foreground=COLOR_TEXTO,
            selectbackground=COLOR_SEL,
            selectforeground=COLOR_TEXTO,
        )

    # -----------------------------------------------------------------------
    # Construcción de la interfaz
    # -----------------------------------------------------------------------

    def _construir_ui(self):
        self._construir_encabezado()
        self._construir_barra_controles()
        self._construir_panel_imagenes()
        self._construir_panel_inferior()

    def _construir_encabezado(self):
        frame = tk.Frame(self, bg=COLOR_BG)
        frame.pack(fill="x", padx=24, pady=(18, 6))

        tk.Label(
            frame,
            text="CLASIFICADOR DE TEXTURAS",
            font=("Courier New", 16, "bold"),
            bg=COLOR_BG, fg=COLOR_ACENTO,
        ).pack(side="left")

        tk.Label(
            frame,
            text="Actividad Individual a)  —  Multimedia UMSA",
            font=("Courier New", 9),
            bg=COLOR_BG, fg=COLOR_TEXTO_SEC,
        ).pack(side="left", padx=16)

        # Separador
        sep = tk.Frame(self, bg=COLOR_BORDE, height=1)
        sep.pack(fill="x", padx=24, pady=(0, 8))

    def _construir_barra_controles(self):
        barra = tk.Frame(self, bg=COLOR_PANEL, pady=10)
        barra.pack(fill="x", padx=24, pady=(0, 10))

        # ── Grupo: Cargar imagen ──
        grp_carga = self._grupo(barra, "IMAGEN")
        grp_carga.pack(side="left", padx=(12, 0))

        self._boton(grp_carga, "Cargar imagen", self._cargar_imagen_propia).pack(
            side="left", padx=(0, 6)
        )

        # Selector de ejemplo
        tk.Label(
            grp_carga, text="Ejemplo:",
            font=("Courier New", 8), bg=COLOR_PANEL, fg=COLOR_TEXTO_SEC,
        ).pack(side="left", padx=(6, 4))

        self.combo_ejemplo = ttk.Combobox(
            grp_carga,
            values=list(EJEMPLOS.keys()),
            state="readonly",
            width=10,
            font=("Courier New", 9),
        )
        self.combo_ejemplo.set("Cesped")
        self.combo_ejemplo.pack(side="left")

        self._boton(grp_carga, "Cargar ejemplo", self._cargar_ejemplo).pack(
            side="left", padx=6
        )

        # Separador vertical
        tk.Frame(barra, bg=COLOR_BORDE, width=1).pack(
            side="left", fill="y", padx=14, pady=4
        )

        # ── Grupo: Filtro de clase ──
        grp_filtro = self._grupo(barra, "VISUALIZAR CLASE")
        grp_filtro.pack(side="left")

        opciones = ["Todas"] + [c for c in CLASES if c != "Otro"]
        for opcion in opciones:
            rb = tk.Radiobutton(
                grp_filtro,
                text=opcion,
                variable=self.filtro_clase,
                value=opcion,
                font=("Courier New", 9),
                bg=COLOR_PANEL, fg=COLOR_TEXTO,
                activebackground=COLOR_PANEL,
                activeforeground=COLOR_ACENTO,
                selectcolor=COLOR_BG,
                indicatoron=True,
                relief="flat",
                cursor="hand2",
                command=self._on_filtro_cambio,
            )
            rb.pack(side="left", padx=6)

        # Separador vertical
        tk.Frame(barra, bg=COLOR_BORDE, width=1).pack(
            side="left", fill="y", padx=14, pady=4
        )

        # ── Grupo: Acciones ──
        grp_acc = self._grupo(barra, "ACCIONES")
        grp_acc.pack(side="left")

        self.btn_clasificar = self._boton(
            grp_acc, "Clasificar", self._iniciar_clasificacion,
            disabled=True, acento=True,
        )
        self.btn_clasificar.pack(side="left", padx=(0, 6))

        self.btn_guardar = self._boton(
            grp_acc, "Guardar resultado", self._guardar_resultado, disabled=True
        )
        self.btn_guardar.pack(side="left")

        # ── Progreso y estado (derecha) ──
        lado_der = tk.Frame(barra, bg=COLOR_PANEL)
        lado_der.pack(side="right", padx=12)

        self.lbl_estado = tk.Label(
            lado_der, text="Cargue una imagen para comenzar",
            font=("Courier New", 8), bg=COLOR_PANEL, fg=COLOR_TEXTO_SEC,
        )
        self.lbl_estado.pack(anchor="e")

        self.progreso_var = tk.DoubleVar(value=0)
        ttk.Progressbar(
            lado_der, variable=self.progreso_var,
            maximum=100, length=200,
            style="Progreso.Horizontal.TProgressbar",
        ).pack(anchor="e", pady=(4, 0))

    def _construir_panel_imagenes(self):
        panel = tk.Frame(self, bg=COLOR_BG)
        panel.pack(fill="both", expand=True, padx=24, pady=0)
        panel.columnconfigure(0, weight=1)
        panel.columnconfigure(1, weight=1)
        panel.rowconfigure(1, weight=1)

        # Etiquetas de sección
        tk.Label(
            panel, text="IMAGEN ORIGINAL",
            font=("Courier New", 9, "bold"),
            bg=COLOR_BG, fg=COLOR_TEXTO_SEC,
        ).grid(row=0, column=0, sticky="w", pady=(0, 4))

        self.lbl_titulo_resultado = tk.Label(
            panel, text="MAPA DE TEXTURAS",
            font=("Courier New", 9, "bold"),
            bg=COLOR_BG, fg=COLOR_TEXTO_SEC,
        )
        self.lbl_titulo_resultado.grid(row=0, column=1, sticky="w", padx=(12, 0), pady=(0, 4))

        # Canvas original
        self.canvas_orig = tk.Canvas(
            panel, bg=COLOR_PANEL,
            highlightthickness=1, highlightbackground=COLOR_BORDE,
        )
        self.canvas_orig.grid(row=1, column=0, sticky="nsew")

        # Canvas resultado
        self.canvas_res = tk.Canvas(
            panel, bg=COLOR_PANEL,
            highlightthickness=1, highlightbackground=COLOR_BORDE,
        )
        self.canvas_res.grid(row=1, column=1, sticky="nsew", padx=(12, 0))

    def _construir_panel_inferior(self):
        panel = tk.Frame(self, bg=COLOR_BG)
        panel.pack(fill="x", padx=24, pady=10)

        # Leyenda
        self._construir_leyenda(panel)

        # Estadísticas a la derecha
        self.lbl_stats = tk.Label(
            panel, text="",
            font=("Courier New", 8),
            bg=COLOR_BG, fg=COLOR_TEXTO_SEC,
            justify="right",
        )
        self.lbl_stats.pack(side="right")

    def _construir_leyenda(self, parent):
        frame = tk.Frame(parent, bg=COLOR_BG)
        frame.pack(side="left")

        tk.Label(
            frame, text="LEYENDA",
            font=("Courier New", 8, "bold"),
            bg=COLOR_BG, fg=COLOR_TEXTO_SEC,
        ).pack(side="left", padx=(0, 12))

        for clase, (r, g, b) in COLORES_CLASE.items():
            if clase == "Otro":
                continue
            hex_color = f"#{r:02x}{g:02x}{b:02x}"
            contenedor = tk.Frame(frame, bg=COLOR_BG)
            contenedor.pack(side="left", padx=(0, 14))

            tk.Canvas(
                contenedor, width=12, height=12,
                bg=COLOR_BG, highlightthickness=0,
            ).pack(side="left")
            # Dibujar cuadrado de color
            c = tk.Canvas(contenedor, width=12, height=12,
                          bg=hex_color, highlightthickness=0)
            c.pack(side="left", padx=(0, 4))

            tk.Label(
                contenedor, text=clase,
                font=("Courier New", 8),
                bg=COLOR_BG, fg=COLOR_TEXTO,
            ).pack(side="left")

    # -----------------------------------------------------------------------
    # Helpers de widgets
    # -----------------------------------------------------------------------

    def _grupo(self, parent, titulo):
        frame = tk.Frame(parent, bg=COLOR_PANEL)
        tk.Label(
            frame, text=titulo,
            font=("Courier New", 7, "bold"),
            bg=COLOR_PANEL, fg=COLOR_TEXTO_SEC,
        ).pack(anchor="w", pady=(0, 4))
        return frame

    def _boton(self, parent, texto, comando, disabled=False, acento=False):
        fg = COLOR_ACENTO if acento else COLOR_TEXTO
        return tk.Button(
            parent, text=texto,
            font=("Courier New", 9),
            bg=COLOR_BTN, fg=fg,
            activebackground=COLOR_BTN_HOV,
            activeforeground=fg,
            relief="flat", bd=0,
            padx=12, pady=6,
            cursor="hand2",
            state="disabled" if disabled else "normal",
            command=comando,
        )

    # -----------------------------------------------------------------------
    # Carga de imágenes
    # -----------------------------------------------------------------------

    def _cargar_imagen_propia(self):
        ruta = filedialog.askopenfilename(
            title="Seleccionar imagen",
            filetypes=[
                ("Imagenes", "*.jpg *.jpeg *.png *.bmp *.tiff *.webp"),
                ("Todos", "*.*"),
            ],
        )
        if ruta:
            self._cargar_imagen(ruta)

    def _cargar_ejemplo(self):
        clase = self.combo_ejemplo.get()
        ruta = EJEMPLOS.get(clase, "")
        if os.path.exists(ruta):
            self._cargar_imagen(ruta, etiqueta=f"Ejemplo: {clase}")
        else:
            self._estado(f"No se encontro el archivo: {ruta}")

    def _cargar_imagen(self, ruta: str, etiqueta: str = ""):
        img = Image.open(ruta).convert("RGB")
        w, h = img.size
        if w > MAX_DIM or h > MAX_DIM:
            factor = MAX_DIM / max(w, h)
            img = img.resize((int(w * factor), int(h * factor)), Image.LANCZOS)

        self.img_original  = img
        self.img_array     = np.array(img)
        self.img_resultado = None

        nombre = etiqueta or os.path.basename(ruta)
        self._estado(f"{nombre}  ({img.size[0]} x {img.size[1]} px)")
        self._mostrar_imagen(self.canvas_orig, img)
        self._limpiar_canvas(self.canvas_res)
        self._actualizar_titulo_resultado()

        self.btn_clasificar.config(state="normal")
        self.btn_guardar.config(state="disabled")
        self.lbl_stats.config(text="")
        self.progreso_var.set(0)

    # -----------------------------------------------------------------------
    # Clasificación
    # -----------------------------------------------------------------------

    def _on_filtro_cambio(self):
        """Reclasifica automáticamente si ya hay una imagen procesada."""
        if self.img_array is not None and self.img_resultado is not None:
            self._iniciar_clasificacion()
        self._actualizar_titulo_resultado()

    def _actualizar_titulo_resultado(self):
        filtro = self.filtro_clase.get()
        if filtro == "Todas":
            titulo = "MAPA DE TEXTURAS  —  Todas las clases"
        else:
            titulo = f"MAPA DE TEXTURAS  —  Destacando: {filtro}"
        self.lbl_titulo_resultado.config(text=titulo)

    def _iniciar_clasificacion(self):
        if self.img_array is None:
            return
        self.btn_clasificar.config(state="disabled")
        self.btn_guardar.config(state="disabled")
        self.progreso_var.set(0)
        self._estado("Clasificando... espere un momento")

        hilo = threading.Thread(target=self._ejecutar_clasificacion, daemon=True)
        hilo.start()

    def _ejecutar_clasificacion(self):
        try:
            filtro = self.filtro_clase.get()

            resultado, conteos = clasificar_imagen(
                self.img_array,
                filtro_clase=filtro,
                callback=lambda pct: self.after(0, self.progreso_var.set, pct),
            )

            self.img_resultado = Image.fromarray(resultado)
            self.after(0, self._mostrar_resultado, conteos, filtro)

        except Exception as e:
            self.after(0, self._estado, f"Error durante la clasificacion: {e}")
            self.after(0, self.btn_clasificar.config, {"state": "normal"})

    def _mostrar_resultado(self, conteos: dict, filtro: str):
        self._mostrar_imagen(self.canvas_res, self.img_resultado)
        self.progreso_var.set(100)
        self.btn_clasificar.config(state="normal")
        self.btn_guardar.config(state="normal")
        self._estado("Clasificacion completada")

        total = sum(conteos.values()) or 1
        partes = [
            f"{k}: {v / total * 100:.1f}%"
            for k, v in conteos.items()
            if v > 0
        ]
        self.lbl_stats.config(text="  |  ".join(partes))

    # -----------------------------------------------------------------------
    # Guardar resultado
    # -----------------------------------------------------------------------

    def _guardar_resultado(self):
        if self.img_resultado is None:
            return
        ruta = filedialog.asksaveasfilename(
            defaultextension=".png",
            filetypes=[("PNG", "*.png"), ("JPEG", "*.jpg")],
            title="Guardar mapa de texturas",
        )
        if ruta:
            self.img_resultado.save(ruta)
            self._estado(f"Guardado: {os.path.basename(ruta)}")

    # -----------------------------------------------------------------------
    # Helpers de canvas
    # -----------------------------------------------------------------------

    def _mostrar_imagen(self, canvas: tk.Canvas, img_pil: Image.Image):
        canvas.update_idletasks()
        cw = canvas.winfo_width()  or 460
        ch = canvas.winfo_height() or 420
        img_fit = img_pil.copy()
        img_fit.thumbnail((cw, ch), Image.LANCZOS)
        tk_img = ImageTk.PhotoImage(img_fit)
        canvas._tk_img = tk_img
        canvas.delete("all")
        canvas.create_image(cw // 2, ch // 2, anchor="center", image=tk_img)

    def _limpiar_canvas(self, canvas: tk.Canvas):
        canvas.delete("all")

    def _estado(self, mensaje: str):
        self.lbl_estado.config(text=mensaje)


# ---------------------------------------------------------------------------
# Entry point
# ---------------------------------------------------------------------------

if __name__ == "__main__":
    app = AplicacionClasificador()
    app.mainloop()
