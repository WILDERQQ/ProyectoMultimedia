import tkinter as tk
from tkinter import filedialog, messagebox
from PIL import Image, ImageTk
import numpy as np

class FiltroBordesApp:
    def __init__(self, root):
        # --- CONFIGURACIÓN PRINCIPAL DE LA VENTANA ---
        self.root = root
        self.root.title("Detección de Bordes - Operador Sobel")
        self.root.geometry("1200x750") 
        
        self.ruta_imagen = None
        self.img_original = None
        self.img_procesada = None
        
        # Tamaño fijo para las imágenes
        self.ancho_img = 500
        self.alto_img = 500

        # --- KERNELS DE SOBEL (como se vio en clase) ---
        self.kernel_sobel_x = np.array([
            [-1, 0, 1],
            [-2, 0, 2],
            [-1, 0, 1]
        ])
        
        self.kernel_sobel_y = np.array([
            [-1, -2, -1],
            [0, 0, 0],
            [1, 2, 1]
        ])

        # --- INTERFAZ DE USUARIO: PANEL DE BOTONES ---
        frame_botones = tk.Frame(self.root)
        frame_botones.pack(pady=10)

        btn_cargar = tk.Button(frame_botones, text="1. Cargar Imagen", command=self.cargar_imagen)
        btn_cargar.grid(row=0, column=0, padx=10)

        btn_procesar = tk.Button(frame_botones, text="2. Detectar Bordes", command=self.procesar_imagen)
        btn_procesar.grid(row=0, column=1, padx=10)

        btn_guardar = tk.Button(frame_botones, text="3. Guardar Resultado", command=self.guardar_imagen)
        btn_guardar.grid(row=0, column=2, padx=10)

        # --- INTERFAZ DE USUARIO: PANEL DE IMÁGENES ---
        frame_imagenes = tk.Frame(self.root)
        frame_imagenes.pack(pady=10)

        # Píxel invisible
        self.pixel_vacio = tk.PhotoImage(width=self.ancho_img, height=self.alto_img)

        # Etiqueta Izquierda: Imagen Original
        self.lbl_original = tk.Label(frame_imagenes, text="[Imagen Original]", bg="#404040", fg="white",
                                     image=self.pixel_vacio, compound="center", width=self.ancho_img, height=self.alto_img)
        self.lbl_original.grid(row=0, column=0, padx=20)

        # Etiqueta Derecha: Resultado final (bordes detectados)
        self.lbl_resultado = tk.Label(frame_imagenes, text="[Bordes Detectados]", bg="#404040", fg="white",
                                      image=self.pixel_vacio, compound="center", width=self.ancho_img, height=self.alto_img)
        self.lbl_resultado.grid(row=0, column=1, padx=20)

        # --- INTERFAZ DE USUARIO: INFORMACIÓN DEL FILTRO ---
        frame_info = tk.Frame(self.root)
        frame_info.pack(pady=20)
        
        info_texto = (
            "¿Cómo funciona el operador Sobel para detección de bordes?\n"
            "El algoritmo calcula el gradiente de intensidad en direcciones X (horizontal) e Y (vertical).\n"
            "Los bordes se detectan donde hay cambios bruscos en la intensidad de los píxeles.\n\n"
            "Kernels de Sobel utilizados (como se vio en clase):\n"
            "Gx (Horizontal):     Gy (Vertical):\n"
            "┌─────────┐          ┌─────────┐\n"
            "│-1  0  1 │          │-1 -2 -1 │\n"
            "│-2  0  2 │          │ 0  0  0 │\n"
            "│-1  0  1 │          │ 1  2  1 │\n"
            "└─────────┘          └─────────┘"
        )
        tk.Label(frame_info, text=info_texto, font=("Arial", 11), justify="center").pack()

    def cargar_imagen(self):
        self.ruta_imagen = filedialog.askopenfilename(filetypes=[("Imágenes", "*.jpg *.png *.jpeg")])
        if not self.ruta_imagen: 
            return

        img = Image.open(self.ruta_imagen).convert("RGB")
        
        # Redimensionamos exactamente a 500x500
        self.img_original = img.resize((self.ancho_img, self.alto_img), Image.Resampling.LANCZOS)
        
        img_tk = ImageTk.PhotoImage(self.img_original)
        self.lbl_original.config(image=img_tk, compound="none")
        self.lbl_original.image = img_tk
        
        # Resetear la etiqueta de resultado
        self.lbl_resultado.config(image=self.pixel_vacio, text="[Bordes Detectados]", compound="center")
        self.img_procesada = None

    def procesar_imagen(self):
        if not self.img_original:
            messagebox.showwarning("Advertencia", "Primero debes cargar una imagen.")
            return
        
        self.lbl_resultado.config(text="Detectando bordes...\nEsto tomará unos segundos.")
        self.root.update()

        # --- INICIO DEL ALGORITMO DE DETECCIÓN DE BORDES (SOBEL) ---
        
        # 1. Convertir la imagen a escala de grises
        img_gris = self.img_original.convert("L")
        img_array = np.array(img_gris, dtype=np.float32)
        alto, ancho = img_array.shape
        
        # 2. Crear matrices para los gradientes
        grad_x = np.zeros((alto, ancho), dtype=np.float32)
        grad_y = np.zeros((alto, ancho), dtype=np.float32)
        grad_magnitud = np.zeros((alto, ancho), dtype=np.float32)

        # 3. Aplicar convolución con los kernels de Sobel
        for y in range(1, alto - 1):
            for x in range(1, ancho - 1):
                # Extraemos la ventana de 3x3
                ventana = img_array[y-1:y+2, x-1:x+2]
                
                # Aplicamos el kernel Sobel X (bordes horizontales)
                grad_x[y, x] = np.sum(ventana * self.kernel_sobel_x)
                
                # Aplicamos el kernel Sobel Y (bordes verticales)
                grad_y[y, x] = np.sum(ventana * self.kernel_sobel_y)
                
                # Calculamos la magnitud del gradiente
                grad_magnitud[y, x] = abs(grad_x[y, x]) + abs(grad_y[y, x])

        # 4. Normalizar los resultados para visualización
        def normalizar(matriz):
            matriz = np.clip(matriz, 0, 255)
            return matriz.astype(np.uint8)
        
        grad_magnitud_norm = normalizar(grad_magnitud)
        
        # 5. Convertir a imagen PIL
        img_resultado = Image.fromarray(grad_magnitud_norm, mode='L')
        
        # 6. Mostrar resultado en la interfaz
        img_tk_result = ImageTk.PhotoImage(img_resultado)
        self.lbl_resultado.config(image=img_tk_result, compound="none")
        self.lbl_resultado.image = img_tk_result
        
        # Guardamos la imagen procesada
        self.img_procesada = img_resultado
        
        messagebox.showinfo("Éxito", "La detección de bordes se ha completado correctamente.")

    def guardar_imagen(self):
        if not self.img_procesada:
            messagebox.showwarning("Advertencia", "No hay ninguna imagen procesada para guardar.")
            return
            
        ruta_guardado = filedialog.asksaveasfilename(defaultextension=".jpg", 
                                                    filetypes=[("JPEG", "*.jpg"), ("PNG", "*.png")])
        if ruta_guardado:
            self.img_procesada.save(ruta_guardado)
            messagebox.showinfo("Guardado", f"Imagen guardada correctamente en:\n{ruta_guardado}")

if __name__ == "__main__":
    root = tk.Tk()
    app = FiltroBordesApp(root)
    root.mainloop()