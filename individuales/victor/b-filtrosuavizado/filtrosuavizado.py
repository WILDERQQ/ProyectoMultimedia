import tkinter as tk
from tkinter import filedialog, ttk
import cv2
import numpy as np
from PIL import Image, ImageTk

class AppFiltroGaussiano:
    def __init__(self, root):
        self.root = root
        self.root.title("Filtro de Suavizado y Detección de Bordes")
        self.root.geometry("950x610")
        self.root.configure(bg="#f5f5f5")

        self.imagen_original = None

        # --- PANEL SUPERIOR: CONTROLES ---
        frame_controles = tk.LabelFrame(root, text=" Control de Procesamiento ", font=("Arial", 10, "bold"), padx=15, pady=10, bg="#f5f5f5")
        frame_controles.pack(fill="x", padx=15, pady=10)

        self.btn_cargar = tk.Button(frame_controles, text="📁 Cargar Imagen", font=("Arial", 9, "bold"), bg="#4CAF50", fg="white", command=self.cargar_imagen, relief="flat", padx=10, pady=5)
        self.btn_cargar.pack(side="left", padx=10)

        ttk.Separator(frame_controles, orient="vertical").pack(side="left", fill="y", padx=15)

        tk.Label(frame_controles, text="Operación:", bg="#f5f5f5").pack(side="left", padx=5)
        self.combo_operacion = ttk.Combobox(frame_controles, values=["Solo Suavizado (Gaussiano 3x3)", "Suavizado + Detección de Bordes (Canny)"], state="readonly", width=35)
        self.combo_operacion.current(1) # Por defecto: Bordes
        self.combo_operacion.pack(side="left", padx=5)

        self.btn_filtrar = tk.Button(frame_controles, text="✨ Aplicar Algoritmo", font=("Arial", 9, "bold"), bg="#009688", fg="white", command=self.aplicar_filtro, relief="flat", padx=15, pady=5)
        self.btn_filtrar.pack(side="left", padx=10)

        # --- PANEL INFERIOR: VISUALIZACIÓN ---
        frame_imagenes = tk.Frame(root, bg="#f5f5f5")
        frame_imagenes.pack(fill="both", expand=True, padx=15, pady=5)

        frame_izq = tk.LabelFrame(frame_imagenes, text=" ANTES (Original) ", font=("Arial", 9, "bold"), bg="white")
        frame_izq.pack(side="left", fill="both", expand=True, padx=10, pady=10)
        self.lbl_antes = tk.Label(frame_izq, bg="white", text="Ninguna imagen cargada", fg="gray")
        self.lbl_antes.pack(fill="both", expand=True, padx=5, pady=5)

        frame_der = tk.LabelFrame(frame_imagenes, text=" DESPUÉS (Procesada) ", font=("Arial", 9, "bold"), bg="white")
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

        seleccion = self.combo_operacion.get()

        # PASO 1: Suavizado obligatorio en ambos casos (Ventana 3x3)
        imagen_suavizada = cv2.GaussianBlur(self.imagen_original, (3, 3), 0)

        if "Bordes" in seleccion:
           
            bordes_grises = cv2.Canny(imagen_suavizada, 50, 150)
            
        
            resultado_final = cv2.cvtColor(bordes_grises, cv2.COLOR_GRAY2BGR)
        else:
            
            resultado_final = imagen_suavizada

        self.mostrar_imagen(resultado_final, self.lbl_despues)

    def mostrar_imagen(self, img_cv, label_destino):
        img_rgb = cv2.cvtColor(img_cv, cv2.COLOR_BGR2RGB)
        img_pil = Image.fromarray(img_rgb)
        img_tk = ImageTk.PhotoImage(image=img_pil)
        label_destino.configure(image=img_tk)
        label_destino.image = img_tk

if __name__ == "__main__":
    ventana = tk.Tk()
    app = AppFiltroGaussiano(ventana)
    ventana.mainloop()
