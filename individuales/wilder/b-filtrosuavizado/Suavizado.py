import tkinter as tk
from tkinter import filedialog, messagebox
from PIL import Image, ImageTk
import numpy as np

class FiltroSuavizadoApp:
    def __init__(self, root):
        # --- CONFIGURACIÓN PRINCIPAL DE LA VENTANA ---
        self.root = root
        self.root.title("Filtro de Suavizado (Promedio 3x3) - Procesamiento a nivel de píxel")
        self.root.geometry("1200x750") 
        
        self.ruta_imagen = None
        self.img_original = None
        self.img_procesada = None
        
        # Forzamos un tamaño fijo de 500x500 para evitar que imágenes gigantes rompan la interfaz
        self.ancho_img = 500
        self.alto_img = 500

        # --- INTERFAZ DE USUARIO: PANEL DE BOTONES ---
        frame_botones = tk.Frame(self.root)
        frame_botones.pack(pady=10)

        btn_cargar = tk.Button(frame_botones, text="1. Cargar Imagen", command=self.cargar_imagen)
        btn_cargar.grid(row=0, column=0, padx=10)

        btn_procesar = tk.Button(frame_botones, text="2. Aplicar Filtro 3x3", command=self.procesar_imagen)
        btn_procesar.grid(row=0, column=1, padx=10)

        btn_guardar = tk.Button(frame_botones, text="3. Guardar Resultado", command=self.guardar_imagen)
        btn_guardar.grid(row=0, column=2, padx=10)

        # --- INTERFAZ DE USUARIO: PANEL DE IMÁGENES ---
        frame_imagenes = tk.Frame(self.root)
        frame_imagenes.pack(pady=10)

        # "Píxel invisible" para mantener fijos los cuadros grises desde el inicio
        self.pixel_vacio = tk.PhotoImage(width=self.ancho_img, height=self.alto_img)

        # Etiqueta Izquierda: Imagen Original
        self.lbl_antes = tk.Label(frame_imagenes, text="[Imagen Original]", bg="#404040", fg="white",
                                  image=self.pixel_vacio, compound="center", width=self.ancho_img, height=self.alto_img)
        self.lbl_antes.grid(row=0, column=0, padx=20)

        # Etiqueta Derecha: Imagen Procesada (Suavizada)
        self.lbl_despues = tk.Label(frame_imagenes, text="[Resultado Suavizado]", bg="#404040", fg="white",
                                    image=self.pixel_vacio, compound="center", width=self.ancho_img, height=self.alto_img)
        self.lbl_despues.grid(row=0, column=1, padx=20)

        # --- INTERFAZ DE USUARIO: INFORMACIÓN DEL FILTRO ---
        frame_info = tk.Frame(self.root)
        frame_info.pack(pady=20)
        
        info_texto = (
            "¿Cómo funciona este filtro de suavizado?\n"
            "El algoritmo recorre la imagen tomando ventanas de 3x3 píxeles.\n"
            "Calcula el color promedio de esos 9 píxeles y se lo asigna al píxel central, reduciendo así el ruido visual."
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
        self.lbl_antes.config(image=img_tk, compound="none")
        self.lbl_antes.image = img_tk
        
        self.lbl_despues.config(image=self.pixel_vacio, text="[Esperando procesamiento...]", compound="center")

    def procesar_imagen(self):
        if not self.img_original:
            messagebox.showwarning("Advertencia", "Primero debes cargar una imagen.")
            return
        
        self.lbl_despues.config(text="Aplicando filtro de promedio...\nEsto tomará unos segundos.")
        self.root.update()

        # --- INICIO DEL ALGORITMO A NIVEL DE PÍXEL (FILTRO DE PROMEDIO 3x3) ---
        
        # 1. Convertimos la imagen a matriz. 
        # Usamos 'float32' temporalmente porque al sumar 9 píxeles de valor 255 podríamos 
        # superar el límite de 255 de los enteros de 8 bits (uint8) y generar errores de color.
        img_array = np.array(self.img_original, dtype=np.float32)
        alto, ancho, canales = img_array.shape
        
        # 2. Creamos un lienzo para el resultado copiando la imagen original.
        # Esto sirve para que los píxeles de los bordes externos (que no podemos procesar 
        # porque no tienen vecinos de un lado) no queden de color negro.
        resultado = np.copy(img_array)

        # 3. Definimos el radio de la ventana. Offset 1 = ventana de 3x3.
        offset = 3 
        
        # 4. Recorremos píxel por píxel ignorando el borde externo
        for y in range(offset, alto - offset):
            for x in range(offset, ancho - offset):
                
                # Extraemos la sub-matriz de 3x3 píxeles (los 9 vecinos)
                ventana = img_array[y-offset : y+offset+1, x-offset : x+offset+1]
                
                # Calculamos el PROMEDIO de los colores en esa ventana de 3x3.
                # axis=(0,1) significa que promediamos los valores de alto y ancho,
                # manteniendo separados los canales Rojo, Verde y Azul.
                promedio_RGB = np.mean(ventana, axis=(0, 1))
                
                # Asignamos el nuevo valor suavizado al píxel central
                resultado[y, x] = promedio_RGB

        # 5. Aseguramos que los valores no se salgan del rango 0-255 y volvemos a formato de imagen (uint8)
        resultado = np.clip(resultado, 0, 255).astype(np.uint8)
        
        # --- FIN DEL ALGORITMO ---

        self.img_procesada = Image.fromarray(resultado)
        img_res_tk = ImageTk.PhotoImage(self.img_procesada)
        
        self.lbl_despues.config(image=img_res_tk, compound="none")
        self.lbl_despues.image = img_res_tk
        messagebox.showinfo("Éxito", "El filtro de suavizado se ha aplicado correctamente.")

    def guardar_imagen(self):
        if not self.img_procesada:
            messagebox.showwarning("Advertencia", "No hay ninguna imagen procesada para guardar.")
            return
            
        ruta_guardado = filedialog.asksaveasfilename(defaultextension=".jpg", filetypes=[("JPEG", "*.jpg"), ("PNG", "*.png")])
        if ruta_guardado:
            self.img_procesada.save(ruta_guardado)
            messagebox.showinfo("Guardado", f"Imagen guardada correctamente en:\n{ruta_guardado}")

if __name__ == "__main__":
    root = tk.Tk()
    app = FiltroSuavizadoApp(root)
    root.mainloop()