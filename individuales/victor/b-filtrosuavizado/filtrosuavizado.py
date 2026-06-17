import tkinter as tk
from tkinter import filedialog, ttk
import cv2
import numpy as np
from PIL import Image, ImageTk

class AppFiltroGaussiano:
    def __init__(self, root):
        self.root = root
        self.root.title("Filtro de Suavizado y Detección de Bordes (Matricial)")
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
        self.combo_operacion = ttk.Combobox(frame_controles, values=["Solo Suavizado (Gaussiano 3x3)", "Suavizado + Detección de Bordes (Sobel 3x3)"], state="readonly", width=38)
        self.combo_operacion.current(1) 
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

    # --- NUEVOS MÉTODOS DE CONVOLUCIÓN MATRICIAL EXPLÍCITA ---
    def _convolucion_3x3(self, img, kernel):
        """ Aplica un kernel de 3x3 de forma explícita y vectorizada sobre canales 2D """
        h, w = img.shape[:2]
        resultado = np.zeros((h, w), dtype=np.float32)
        
        # Desplazamientos matriciales para evitar bucles for píxel por píxel
        for i in range(3):
            for j in range(3):
                valor_kernel = kernel[i, j]
                if valor_kernel != 0:
                    resultado[1:h-1, 1:w-1] += img[i:h-2+i, j:w-2+j] * valor_kernel
                    
        return resultado

    def aplicar_gaussiano_manual(self, img_bgr):
        """ Matriz Gaussiana explícita 3x3 distribuida en los 3 canales BGR """
        kernel_gauss = np.array([[1, 2, 1],
                                 [2, 4, 2],
                                 [1, 2, 1]], dtype=np.float32) / 16.0
        
        canales = cv2.split(img_bgr)
        canales_procesados = []
        
        for canal in canales:
            res_canal = self._convolucion_3x3(canal.astype(np.float32), kernel_gauss)
            canales_procesados.append(np.clip(res_canal, 0, 255).astype(np.uint8))
            
        return cv2.merge(canales_procesados)

    def aplicar_sobel_manual(self, img_bgr):
        """ Filtro de bordes Sobel utilizando matrices de gradiente Gx y Gy """
        # Convierte a escala de grises manualmente usando la fórmula de luminancia estándar
        img_gris = 0.299 * img_bgr[:,:,2] + 0.587 * img_bgr[:,:,1] + 0.114 * img_bgr[:,:,0]
        
        # Matrices Sobel explícitas
        kernel_x = np.array([[-1, 0, 1],
                             [-2, 0, 2],
                             [-1, 0, 1]], dtype=np.float32)
        
        kernel_y = np.array([[-1, -2, -1],
                             [ 0,  0,  0],
                             [ 1,  2,  1]], dtype=np.float32)
        
        gx = self._convolucion_3x3(img_gris, kernel_x)
        gy = self._convolucion_3x3(img_gris, kernel_y)
        
        # Magnitud del gradiente de forma explícita: sqrt(Gx^2 + Gy^2)
        magnitud = np.sqrt(gx**2 + gy**2)
        magnitud = np.clip(magnitud, 0, 255).astype(np.uint8)
        
        # Reconvertir a 3 canales para mostrar en la interfaz
        return cv2.merge([magnitud, magnitud, magnitud])

    def aplicar_filtro(self):
        if self.imagen_original is None: return

        seleccion = self.combo_operacion.get()

        # PASO 1: Suavizado Gaussiano Matricial
        imagen_suavizada = self.aplicar_gaussiano_manual(self.imagen_original)

        # PASO 2: Selección de Algoritmo
        if "Bordes" in seleccion:
            resultado_final = self.aplicar_sobel_manual(imagen_suavizada)
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
