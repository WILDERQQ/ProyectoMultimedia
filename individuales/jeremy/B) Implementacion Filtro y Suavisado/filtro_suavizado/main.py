"""
Filtro de Suavizado 3x3 - Actividad Individual - Multimedia - UMSA
Interfaz gráfica construida con Tkinter + PIL.

b) Permite cargar una imagen propia o imágenes de ejemplo,
seleccionar el numero de pasadas y aplicar el filtro de
promedio 3x3 a nivel de pixel con visualizacion comparativa.
"""

import tkinter as tk
from tkinter import filedialog, ttk
import threading
import os
import sys

from PIL import Image, ImageTk
import numpy as np

BASE_DIR = os.path.dirname(os.path.abspath(__file__))
sys.path.insert(0, BASE_DIR)

from src.filtro import filtro_promedio_3x3, calcular_diferencia, calcular_metricas

# ---------------------------------------------------------------------------
# Rutas de imagenes de ejemplo
# ---------------------------------------------------------------------------

EJEMPLOS = {
    "Rostro":  os.path.join(BASE_DIR, "assets", "images", "ejemplo_rostro.jpg"),
    "Ciudad":  os.path.join(BASE_DIR, "assets", "images", "ejemplo_ciudad.jpg"),
}

MAX_DIM = 440

# ---------------------------------------------------------------------------
# Paleta de colores
# ---------------------------------------------------------------------------

COLOR_BG        = "#0d1117"
COLOR_PANEL     = "#161b22"
COLOR_BORDE     = "#2a2d3a"
COLOR_ACENTO    = "#f97316"
COLOR_ACENTO2   = "#22d3ee"
COLOR_ACENTO3   = "#a78bfa"
COLOR_TEXTO     = "#d4d8e8"
COLOR_TEXTO_SEC = "#6b7280"
COLOR_BTN       = "#21262d"
COLOR_BTN_HOV   = "#2d3340"


# ---------------------------------------------------------------------------
# Aplicacion principal
# ---------------------------------------------------------------------------

class AplicacionFiltroSuavizado(tk.Tk):
    """Ventana principal del filtro de suavizado 3x3."""

    def __init__(self):
        super().__init__()
        self.title("Filtro de Suavizado 3x3 - Multimedia UMSA")
        self.configure(bg=COLOR_BG)
        self.resizable(True, True)
        self.minsize(1150, 680)

        self.img_original  = None
        self.img_suavizada = None
        self.img_diff      = None
        self.img_array     = None
        self.iter_var      = tk.IntVar(value=1)

        self._configurar_estilos()
        self._construir_ui()

    # -----------------------------------------------------------------------
    # Estilos
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

    # -----------------------------------------------------------------------
    # Construccion de la interfaz
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
            text="FILTRO DE SUAVIZADO 3x3",
            font=("Courier New", 16, "bold"),
            bg=COLOR_BG, fg=COLOR_ACENTO,
        ).pack(side="left")

        tk.Label(
            frame,
            text="Filtro de promedio — operacion a nivel de pixel  —  Actividad Individual b)  —  Multimedia UMSA",
            font=("Courier New", 9),
            bg=COLOR_BG, fg=COLOR_TEXTO_SEC,
        ).pack(side="left", padx=16)

        tk.Frame(self, bg=COLOR_BORDE, height=1).pack(fill="x", padx=24, pady=(0, 8))

    def _construir_barra_controles(self):
        barra = tk.Frame(self, bg=COLOR_PANEL, pady=10)
        barra.pack(fill="x", padx=24, pady=(0, 10))

        # ── Grupo: Cargar imagen ──
        grp_carga = self._grupo(barra, "IMAGEN")
        grp_carga.pack(side="left", padx=(12, 0))

        self._boton(grp_carga, "Cargar imagen", self._cargar_imagen_propia).pack(
            side="left", padx=(0, 8)
        )

        tk.Label(
            grp_carga, text="Ejemplo:",
            font=("Courier New", 8),
            bg=COLOR_PANEL, fg=COLOR_TEXTO_SEC,
        ).pack(side="left", padx=(4, 4))

        self.combo_ejemplo = ttk.Combobox(
            grp_carga,
            values=list(EJEMPLOS.keys()),
            state="readonly",
            width=9,
            font=("Courier New", 9),
        )
        self.combo_ejemplo.set("Rostro")
        self.combo_ejemplo.pack(side="left")

        self._boton(grp_carga, "Cargar ejemplo", self._cargar_ejemplo).pack(
            side="left", padx=6
        )

        # Separador
        tk.Frame(barra, bg=COLOR_BORDE, width=1).pack(
            side="left", fill="y", padx=14, pady=4
        )

        # ── Grupo: Pasadas ──
        grp_pas = self._grupo(barra, "PASADAS DEL FILTRO")
        grp_pas.pack(side="left")

        for val, lbl in [(1, "1 pasada"), (2, "2 pasadas"), (3, "3 pasadas")]:
            tk.Radiobutton(
                grp_pas,
                text=lbl,
                variable=self.iter_var,
                value=val,
                font=("Courier New", 9),
                bg=COLOR_PANEL, fg=COLOR_TEXTO,
                activebackground=COLOR_PANEL,
                activeforeground=COLOR_ACENTO,
                selectcolor=COLOR_BG,
                indicatoron=True,
                relief="flat",
                cursor="hand2",
            ).pack(side="left", padx=8)

        # Separador
        tk.Frame(barra, bg=COLOR_BORDE, width=1).pack(
            side="left", fill="y", padx=14, pady=4
        )

        # ── Grupo: Acciones ──
        grp_acc = self._grupo(barra, "ACCIONES")
        grp_acc.pack(side="left")

        self.btn_aplicar = self._boton(
            grp_acc, "Aplicar filtro", self._iniciar_filtro,
            disabled=True, acento=True,
        )
        self.btn_aplicar.pack(side="left", padx=(0, 8))

        self.btn_guardar = self._boton(
            grp_acc, "Guardar resultado", self._guardar_resultado,
            disabled=True,
        )
        self.btn_guardar.pack(side="left")

        # ── Estado y progreso (derecha) ──
        lado_der = tk.Frame(barra, bg=COLOR_PANEL)
        lado_der.pack(side="right", padx=12)

        self.lbl_estado = tk.Label(
            lado_der, text="Cargue una imagen para comenzar",
            font=("Courier New", 8),
            bg=COLOR_PANEL, fg=COLOR_TEXTO_SEC,
        )
        self.lbl_estado.pack(anchor="e")

        self.progreso_var = tk.DoubleVar(value=0)
        ttk.Progressbar(
            lado_der,
            variable=self.progreso_var,
            maximum=100, length=200,
            style="Progreso.Horizontal.TProgressbar",
        ).pack(anchor="e", pady=(4, 0))

    def _construir_panel_imagenes(self):
        panel = tk.Frame(self, bg=COLOR_BG)
        panel.pack(fill="both", expand=True, padx=24, pady=0)

        for col in range(3):
            panel.columnconfigure(col, weight=1)
        panel.rowconfigure(1, weight=1)

        secciones = [
            ("ANTES  —  Original",       COLOR_ACENTO),
            ("DESPUES  —  Suavizado",    COLOR_ACENTO2),
            ("DIFERENCIA  x5",           COLOR_ACENTO3),
        ]

        self.canvases = []
        for col, (titulo, color) in enumerate(secciones):
            tk.Label(
                panel, text=titulo,
                font=("Courier New", 9, "bold"),
                bg=COLOR_BG, fg=color,
            ).grid(row=0, column=col, sticky="w",
                   padx=(0 if col == 0 else 10, 0), pady=(0, 4))

            c = tk.Canvas(
                panel, bg=COLOR_PANEL,
                highlightthickness=1,
                highlightbackground=COLOR_BORDE,
            )
            c.grid(row=1, column=col, sticky="nsew",
                   padx=(0 if col == 0 else 10, 0))
            self.canvases.append(c)

    def _construir_panel_inferior(self):
        # Separador
        tk.Frame(self, bg=COLOR_BORDE, height=1).pack(
            fill="x", padx=24, pady=(8, 0)
        )

        panel = tk.Frame(self, bg=COLOR_BG)
        panel.pack(fill="x", padx=24, pady=8)

        # Leyenda de paneles
        leyenda = tk.Frame(panel, bg=COLOR_BG)
        leyenda.pack(side="left")

        tk.Label(
            leyenda, text="PANELES:",
            font=("Courier New", 8, "bold"),
            bg=COLOR_BG, fg=COLOR_TEXTO_SEC,
        ).pack(side="left", padx=(0, 10))

        for texto, color in [
            ("Antes: imagen original con ruido", COLOR_ACENTO),
            ("Despues: imagen suavizada",        COLOR_ACENTO2),
            ("Diferencia x5: ruido eliminado",   COLOR_ACENTO3),
        ]:
            tk.Label(
                leyenda, text="  |  " + texto,
                font=("Courier New", 8),
                bg=COLOR_BG, fg=color,
            ).pack(side="left")

        # Metricas a la derecha
        self.lbl_metricas = tk.Label(
            panel, text="",
            font=("Courier New", 8),
            bg=COLOR_BG, fg=COLOR_TEXTO_SEC,
            justify="right",
        )
        self.lbl_metricas.pack(side="right")

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
    # Carga de imagenes
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
        nombre = self.combo_ejemplo.get()
        ruta = EJEMPLOS.get(nombre, "")
        if os.path.exists(ruta):
            self._cargar_imagen(ruta, etiqueta=f"Ejemplo: {nombre}")
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
        self.img_suavizada = None
        self.img_diff      = None

        nombre = etiqueta or os.path.basename(ruta)
        self._estado(f"{nombre}  ({img.size[0]} x {img.size[1]} px)")

        self._mostrar_imagen(self.canvases[0], img)
        self._limpiar_canvas(self.canvases[1])
        self._limpiar_canvas(self.canvases[2])

        self.btn_aplicar.config(state="normal")
        self.btn_guardar.config(state="disabled")
        self.progreso_var.set(0)
        self.lbl_metricas.config(text="")

    # -----------------------------------------------------------------------
    # Procesamiento
    # -----------------------------------------------------------------------

    def _iniciar_filtro(self):
        if self.img_array is None:
            return
        self.btn_aplicar.config(state="disabled")
        self.btn_guardar.config(state="disabled")
        iteraciones = self.iter_var.get()
        self._estado(f"Aplicando filtro 3x3 — {iteraciones} pasada{'s' if iteraciones > 1 else ''}...")
        self.progreso_var.set(0)

        hilo = threading.Thread(
            target=self._ejecutar_filtro,
            args=(iteraciones,),
            daemon=True,
        )
        hilo.start()

    def _ejecutar_filtro(self, iteraciones: int):
        try:
            suavizada = filtro_promedio_3x3(
                self.img_array,
                iteraciones=iteraciones,
                callback=lambda pct: self.after(0, self.progreso_var.set, pct),
            )
            diferencia = calcular_diferencia(self.img_array, suavizada)
            metricas   = calcular_metricas(self.img_array, suavizada)

            self.img_suavizada = Image.fromarray(suavizada)
            self.img_diff      = Image.fromarray(diferencia)

            self.after(0, self._mostrar_resultado, metricas, iteraciones)

        except Exception as e:
            self.after(0, self._estado, f"Error durante el procesamiento: {e}")
            self.after(0, self.btn_aplicar.config, {"state": "normal"})

    def _mostrar_resultado(self, metricas: dict, iteraciones: int):
        self._mostrar_imagen(self.canvases[1], self.img_suavizada)
        self._mostrar_imagen(self.canvases[2], self.img_diff)
        self.progreso_var.set(100)
        self.btn_aplicar.config(state="normal")
        self.btn_guardar.config(state="normal")
        self._estado(
            f"Filtro aplicado — {iteraciones} pasada{'s' if iteraciones > 1 else ''}"
        )

        psnr_txt = (
            f"{metricas['psnr']:.2f} dB"
            if metricas["psnr"] != float("inf")
            else "inf"
        )
        self.lbl_metricas.config(
            text=(
                f"MSE: {metricas['mse']:.2f}  |  "
                f"MAE: {metricas['mae']:.2f}  |  "
                f"PSNR: {psnr_txt}  |  "
                f"Pixeles modificados: {metricas['pixeles_modificados_pct']:.1f}%"
            )
        )

    # -----------------------------------------------------------------------
    # Guardar resultado
    # -----------------------------------------------------------------------

    def _guardar_resultado(self):
        if self.img_suavizada is None:
            return
        ruta = filedialog.asksaveasfilename(
            defaultextension=".png",
            filetypes=[("PNG", "*.png"), ("JPEG", "*.jpg")],
            title="Guardar imagen suavizada",
        )
        if ruta:
            self.img_suavizada.save(ruta)
            base, ext = os.path.splitext(ruta)
            self.img_diff.save(base + "_diferencia" + ext)
            self._estado(
                f"Guardado: {os.path.basename(ruta)}  +  {os.path.basename(base)}_diferencia{ext}"
            )

    # -----------------------------------------------------------------------
    # Helpers canvas
    # -----------------------------------------------------------------------

    def _mostrar_imagen(self, canvas: tk.Canvas, img_pil: Image.Image):
        canvas.update_idletasks()
        cw = canvas.winfo_width()  or 360
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
    app = AplicacionFiltroSuavizado()
    app.mainloop()
