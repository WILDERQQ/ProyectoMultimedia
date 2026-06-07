import tkinter as tk
from tkinter import filedialog, ttk
import cv2
import numpy as np
from PIL import Image, ImageTk

class AppFiltroGaussiano:
    def __init__(self, root):
        self.root = root
        self.root.title("Filtro de Suavizado (Gaussiano 3x3)")
        self.root.geometry("950x610")
        self.root.configure(bg="#f5f5f5")

        self.imagen_original = None

        # --- PANEL SUPERIOR: CONTROLES ---
        frame_controles = tk.LabelFrame(root, text=" Control de Suavizado ", font=("Arial", 10, "bold"), padx=15, pady=10, bg="#f5f5f5")
        frame_controles.pack(fill="x", padx=15, pady=10)

        # Botón Cargar
        self.btn_cargar = tk.Button(frame_controles, text="📁 Cargar Imagen", font=("Arial", 9, "bold"), bg="#4CAF50", fg="white", command=self.cargar_imagen, relief="flat", padx=10, pady=5)
        self.btn_cargar.pack(side="left", padx=10)

        ttk.Separator(frame_controles, orient="vertical").pack(side="left", fill="y", padx=15)

        # Botón Aplicar Filtro (Directo al Gaussiano)
        self.btn_filtrar = tk.Button(frame_controles, text="✨ Aplicar Filtro Gaussiano (3x3)", font=("Arial", 9, "bold"), bg="#009688", fg="white", command=self.aplicar_filtro, relief="flat", padx=15, pady=5)
        self.btn_filtrar.pack(side="left", padx=10)

        # --- PANEL INFERIOR: VISUALIZACIÓN ---
        frame_imagenes = tk.Frame(root, bg="#f5f5f5")
        frame_imagenes.pack(fill="both", expand=True, padx=15, pady=5)

        # Panel Izquierdo
        frame_izq = tk.LabelFrame(frame_imagenes, text=" ANTES (Original con Ruido) ", font=("Arial", 9, "bold"), bg="white")
        frame_izq.pack(side="left", fill="both", expand=True, padx=10, pady=10)
        
        self.lbl_antes = tk.Label(frame_izq, bg="white", text="Ninguna imagen cargada", fg="gray")
        self.lbl_antes.pack(fill="both", expand=True, padx=5, pady=5)

        # Panel Derecho
        frame_der = tk.LabelFrame(frame_imagenes, text=" DESPUÉS (Suavizado Gaussiano) ", font=("Arial", 9, "bold"), bg="white")
        frame_der.pack(side="right", fill="both", expand=True, padx=10, pady=10)
        
        self.lbl_despues = tk.Label(frame_der, bg="white", text="Esperando procesamiento...", fg="gray")
        self.lbl_despues.pack(fill="both", expand=True, padx=5, pady=5)

    def cargar_imagen(self):
        ruta = filedialog.askopenfilename(filetypes=[("Imágenes", "*.jpg *.jpeg *.png")])
        if ruta:
            self.imagen_original = cv2.imread(ruta)
            self.imagen_original = cv2.resize(self.imagen_original, (420, 420))
            self.mostrar_imagen(self.imagen_original, self.lbl_antes)
            self.lbl_despues.config(image='', text="Esperando procesamiento...")

    def aplicar_filtro(self):
        if self.imagen_original is None: return

        # Aplicación directa del Filtro Gaussiano con ventana 3x3
        resultado = cv2.GaussianBlur(self.imagen_original, (3, 3), 0)

        self.mostrar_imagen(resultado, self.lbl_despues)

    def mostrar_imagen(self, img_cv, label_destino):
        img_rgb = cv2.cvtColor(img_cv, cv2.COLOR_BGR2RGB)
        img_pil = Image.fromarray(img_rgb)
        img_tk = ImageTk.PhotoImage(image=img_pil)
        label_destino.configure(image=img_tk)
        label_destino.image = img_tk # Evita que el recolector de basura lo borre

if __name__ == "__main__":
    ventana = tk.Tk()
    app = AppFiltroGaussiano(ventana)
    ventana.mainloop()