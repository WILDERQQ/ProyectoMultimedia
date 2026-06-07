import tkinter as tk
from tkinter import filedialog, ttk
import cv2
import numpy as np
from PIL import Image, ImageTk

class AppClasificador:
    def __init__(self, root):
        self.root = root
        self.root.title("Sistema de Clasificación de Superficies - Visión Artificial")
        self.root.geometry("950x610")
        self.root.configure(bg="#f5f5f5") # Fondo gris claro institucional

        self.imagen_original = None

        # --- PANEL SUPERIOR: CONTROLES ---
        frame_controles = tk.LabelFrame(root, text=" Panel de Control y Configuración ", font=("Arial", 10, "bold"), padx=15, pady=10, bg="#f5f5f5")
        frame_controles.pack(fill="x", padx=15, pady=10)

        # Botón Cargar
        self.btn_cargar = tk.Button(frame_controles, text="📁 Cargar Imagen", font=("Arial", 9, "bold"), bg="#4CAF50", fg="white", command=self.cargar_imagen, relief="flat", padx=10, pady=5)
        self.btn_cargar.pack(side="left", padx=10)

        # Separador visual
        ttk.Separator(frame_controles, orient="vertical").pack(side="left", fill="y", padx=15)

        # Menú Desplegable
        self.combo_superficie = ttk.Combobox(frame_controles, values=["Césped (Verde)", "Tierra (Marrón)", "Asfalto/Cemento (Gris)"], state="readonly", width=20)
        self.combo_superficie.current(0)
        self.combo_superficie.pack(side="left", padx=5)

        # Botón Filtro Individual
        self.btn_filtrar = tk.Button(frame_controles, text="🔍 Filtrar Selección", font=("Arial", 9), bg="#2196F3", fg="white", command=self.filtrar_individual, relief="flat", padx=10, pady=5)
        self.btn_filtrar.pack(side="left", padx=10)

        # Botón Clasificación Total
        self.btn_total = tk.Button(frame_controles, text="📊 Clasificación Total (Mapa)", font=("Arial", 9, "bold"), bg="#9C27B0", fg="white", command=self.clasificacion_total, relief="flat", padx=10, pady=5)
        self.btn_total.pack(side="left", padx=10)


        # --- PANEL INFERIOR: VISUALIZACIÓN (ANTES Y DESPUÉS) ---
        frame_imagenes = tk.Frame(root, bg="#f5f5f5")
        frame_imagenes.pack(fill="both", expand=True, padx=15, pady=5)

        # Contenedor Izquierdo (Antes)
        frame_izq = tk.LabelFrame(frame_imagenes, text=" ANTES (Imagen Original) ", font=("Arial", 9, "bold"), bg="white")
        frame_izq.pack(side="left", fill="both", expand=True, padx=10, pady=10)
        
        self.lbl_antes = tk.Label(frame_izq, bg="white", text="Ninguna imagen cargada", fg="gray")
        self.lbl_antes.pack(fill="both", expand=True, padx=5, pady=5)

        # Contenedor Derecho (Después)
        frame_der = tk.LabelFrame(frame_imagenes, text=" DESPUÉS (Resultado del Análisis) ", font=("Arial", 9, "bold"), bg="white")
        frame_der.pack(side="right", fill="both", expand=True, padx=10, pady=10)
        
        self.lbl_despues = tk.Label(frame_der, bg="white", text="Esperando procesamiento...", fg="gray")
        self.lbl_despues.pack(fill="both", expand=True, padx=5, pady=5)

    def cargar_imagen(self):
        ruta = filedialog.askopenfilename(filetypes=[("Imágenes", "*.jpg *.jpeg *.png")])
        if ruta:
            self.imagen_original = cv2.imread(ruta)
            # Redimensionar dinámicamente para la interfaz manteniendo un tamaño estándar
            self.imagen_original = cv2.resize(self.imagen_original, (420, 420))
            self.mostrar_imagen(self.imagen_original, self.lbl_antes)
            self.lbl_despues.config(image='', text="Esperando procesamiento...")

    def obtener_umbrales(self, tipo):
        """Centraliza los rangos matemáticos HSV para facilitar la explicación técnica"""
        if tipo == "cesped":
            return np.array([35, 40, 40]), np.array([85, 255, 255])     # Tonos Verdes
        elif tipo == "tierra":
            return np.array([10, 40, 30]), np.array([25, 255, 255])     # Tonos Marrones/Ocres
        elif tipo == "asfalto":
            return np.array([0, 0, 40]), np.array([180, 50, 200])       # Escala de Grises (Baja Saturación)
        return None, None

    def filtrar_individual(self):
        if self.imagen_original is None: return
        
        # Segmentación en el espacio HSV
        hsv = cv2.cvtColor(self.imagen_original, cv2.COLOR_BGR2HSV)
        seleccion = self.combo_superficie.get()

        if "Césped" in seleccion:
            bajo, alto = self.obtener_umbrales("cesped")
        elif "Tierra" in seleccion:
            bajo, alto = self.obtener_umbrales("tierra")
        else:
            bajo, alto = self.obtener_umbrales("asfalto")

        # Generación de la máscara binaria e intersección lógica (AND)
        mascara = cv2.inRange(hsv, bajo, alto)
        resultado = cv2.bitwise_and(self.imagen_original, self.imagen_original, mask=mascara)
        
        self.mostrar_imagen(resultado, self.lbl_despues)

    def clasificacion_total(self):
        if self.imagen_original is None: return

        hsv = cv2.cvtColor(self.imagen_original, cv2.COLOR_BGR2HSV)
        
        # Clonamos la imagen original para crear la capa del mapa semántico
        capa_mapa = self.imagen_original.copy()

        # 1. Detectar y pintar Césped (Verde brillante)
        b_cesped, a_cesped = self.obtener_umbrales("cesped")
        mask_cesped = cv2.inRange(hsv, b_cesped, a_cesped)
        capa_mapa[mask_cesped > 0] = [0, 255, 0] # Formato BGR

        # 2. Detectar y pintar Tierra (Marrón/Naranja en el mapa)
        b_tierra, a_tierra = self.obtener_umbrales("tierra")
        mask_tierra = cv2.inRange(hsv, b_tierra, a_tierra)
        capa_mapa[mask_tierra > 0] = [0, 128, 255] 

        # 3. Detectar y pintar Asfalto/Cemento (Azul eléctrico)
        b_asfalto, a_asfalto = self.obtener_umbrales("asfalto")
        mask_asfalto = cv2.inRange(hsv, b_asfalto, a_asfalto)
        capa_mapa[mask_asfalto > 0] = [255, 0, 0] 

        # Fusión matemática: Superposición transparente (50% original, 50% mapa de colores)
        resultado_final = cv2.addWeighted(capa_mapa, 0.5, self.imagen_original, 0.5, 0)
        
        self.mostrar_imagen(resultado_final, self.lbl_despues)

    def mostrar_imagen(self, img_cv, label_destino):
        """Parsea matrices de OpenCV (BGR) a objetos compatibles con Tkinter (RGB)"""
        img_rgb = cv2.cvtColor(img_cv, cv2.COLOR_BGR2RGB)
        img_pil = Image.fromarray(img_rgb)
        img_tk = ImageTk.PhotoImage(image=img_pil)
        label_destino.configure(image=img_tk)
        label_destino.image = img_tk

if __name__ == "__main__":
    ventana = tk.Tk()
    app = AppClasificador(ventana)
    ventana.mainloop()