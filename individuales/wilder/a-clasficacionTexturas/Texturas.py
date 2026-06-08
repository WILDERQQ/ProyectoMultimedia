import tkinter as tk
from tkinter import filedialog, messagebox
from PIL import Image, ImageTk
import numpy as np

class TexturaApp:
    def __init__(self, root):
        # --- CONFIGURACIÓN PRINCIPAL DE LA VENTANA ---
        self.root = root
        self.root.title("Clasificación de Texturas - Procesamiento a nivel de píxel")
        self.root.geometry("1100x700") # Tamaño de ventana lo suficientemente amplio
        
        # Variables para almacenar las rutas y las imágenes en memoria
        self.ruta_imagen = None
        self.img_original = None
        self.img_procesada = None
        
        # --- TAMAÑO FIJO PREDETERMINADO PARA LAS IMÁGENES ---
        # Definimos que todas las imágenes serán forzadas a este tamaño exacto
        self.ancho_img = 500
        self.alto_img = 500

        # --- INTERFAZ DE USUARIO: PANEL DE BOTONES ---
        # Contenedor superior para los botones
        frame_botones = tk.Frame(self.root)
        frame_botones.pack(pady=10)

        # Botón 1: Buscar y cargar la imagen
        btn_cargar = tk.Button(frame_botones, text="1. Cargar Imagen", command=self.cargar_imagen)
        btn_cargar.grid(row=0, column=0, padx=10)

        # Botón 2: Iniciar el algoritmo de clasificación
        btn_procesar = tk.Button(frame_botones, text="2. Procesar Texturas", command=self.procesar_imagen)
        btn_procesar.grid(row=0, column=1, padx=10)

        # Botón 3: Guardar el resultado en la computadora
        btn_guardar = tk.Button(frame_botones, text="3. Guardar Resultado", command=self.guardar_imagen)
        btn_guardar.grid(row=0, column=2, padx=10)

        # --- INTERFAZ DE USUARIO: PANEL DE IMÁGENES ---
        frame_imagenes = tk.Frame(self.root)
        frame_imagenes.pack(pady=10)

        # TRUCO: Creamos un "píxel invisible" del tamaño deseado (500x500).
        # Esto obliga a las cajas grises a tener el tamaño correcto desde que se abre la app,
        # evitando que la ventana cambie de forma bruscamente al cargar una foto.
        self.pixel_vacio = tk.PhotoImage(width=self.ancho_img, height=self.alto_img)

        # Etiqueta Izquierda: Muestra la Imagen Original
        self.lbl_antes = tk.Label(frame_imagenes, text="[Imagen Original]", bg="#404040", fg="white",
                                  image=self.pixel_vacio, compound="center", width=self.ancho_img, height=self.alto_img)
        self.lbl_antes.grid(row=0, column=0, padx=20)

        # Etiqueta Derecha: Muestra la Imagen Procesada
        self.lbl_despues = tk.Label(frame_imagenes, text="[Resultado de Clasificación]", bg="#404040", fg="white",
                                    image=self.pixel_vacio, compound="center", width=self.ancho_img, height=self.alto_img)
        self.lbl_despues.grid(row=0, column=1, padx=20)

        # --- INTERFAZ DE USUARIO: LEYENDA DE COLORES ---
        # Contenedor inferior para explicar qué color representa cada textura
        frame_leyenda = tk.Frame(self.root)
        frame_leyenda.pack(pady=20)
        
        tk.Label(frame_leyenda, text="Leyenda de Clasificación:", font=("Arial", 12, "bold")).grid(row=0, column=0, columnspan=8, pady=5)

        # Césped -> Verde
        tk.Label(frame_leyenda, bg="#00FF00", width=4).grid(row=1, column=0, padx=(10, 0))
        tk.Label(frame_leyenda, text="Césped").grid(row=1, column=1, padx=(5, 15))

        # Tierra -> Marrón
        tk.Label(frame_leyenda, bg="#8B4513", width=4).grid(row=1, column=2, padx=(10, 0))
        tk.Label(frame_leyenda, text="Tierra").grid(row=1, column=3, padx=(5, 15))

        # Cemento -> Gris Claro
        tk.Label(frame_leyenda, bg="#B4B4B4", width=4).grid(row=1, column=4, padx=(10, 0))
        tk.Label(frame_leyenda, text="Cemento").grid(row=1, column=5, padx=(5, 15))

        # Asfalto/Otros -> Gris Oscuro
        tk.Label(frame_leyenda, bg="#323232", width=4).grid(row=1, column=6, padx=(10, 0))
        tk.Label(frame_leyenda, text="Asfalto / Otros").grid(row=1, column=7, padx=(5, 15))

    def cargar_imagen(self):
        # 1. Abre la ventana para seleccionar el archivo
        self.ruta_imagen = filedialog.askopenfilename(filetypes=[("Imágenes", "*.jpg *.png *.jpeg")])
        if not self.ruta_imagen: 
            return # Si el usuario cancela, no hacemos nada

        # 2. Cargar imagen en memoria y forzar formato RGB
        img = Image.open(self.ruta_imagen).convert("RGB")
        
        # 3. Forzar el tamaño exacto a 500x500 píxeles usando 'resize'
        # Esto deforma un poco la imagen si no era cuadrada, pero garantiza que
        # el procesamiento tarde lo mismo y la interfaz no se desconfigure.
        self.img_original = img.resize((self.ancho_img, self.alto_img), Image.Resampling.LANCZOS)
        
        # 4. Mostrar la imagen en la interfaz (Etiqueta izquierda)
        img_tk = ImageTk.PhotoImage(self.img_original)
        self.lbl_antes.config(image=img_tk, compound="none") # compound="none" borra el texto de fondo
        self.lbl_antes.image = img_tk
        
        # Limpiar la etiqueta derecha por si se había procesado otra imagen antes
        self.lbl_despues.config(image=self.pixel_vacio, text="[Esperando procesamiento...]", compound="center")

    def procesar_imagen(self):
        # Validación: Asegurarnos de que haya una imagen cargada
        if not self.img_original:
            messagebox.showwarning("Advertencia", "Primero debes cargar una imagen.")
            return
        
        # Avisar al usuario que el proceso comenzó (evita que piense que se trabó)
        self.lbl_despues.config(text="Procesando píxel por píxel...\nEsto tomará unos segundos.")
        self.root.update() # Forzamos a la pantalla a actualizarse para mostrar el mensaje

        # --- INICIO DEL ALGORITMO A NIVEL DE PÍXEL ---
        # Convertimos la imagen a una matriz tridimensional de Numpy [alto, ancho, colores RGB]
        img_array = np.array(self.img_original)
        alto, ancho, canales = img_array.shape
        
        # Creamos un lienzo negro (matriz de ceros) del mismo tamaño para dibujar los resultados
        resultado = np.zeros((alto, ancho, 3), dtype=np.uint8)

        # Definimos el rango de la ventana a analizar. 
        # offset = 1 crea un cuadrado de 3x3 píxeles alrededor del píxel central.
        offset = 1 
        
        # Recorremos la imagen excluyendo el borde exterior (offset)
        # Esto evita errores de "índice fuera de rango" al pedir píxeles vecinos
        for y in range(offset, alto - offset):
            for x in range(offset, ancho - offset):
                
                # Extraemos la ventana de 3x3 píxeles
                ventana = img_array[y-offset : y+offset+1, x-offset : x+offset+1]
                
                # CARACTERÍSTICA 1: COLOR (Promediamos R, G, B de la ventana 3x3)
                R, G, B = np.mean(ventana, axis=(0, 1))
                
                # CARACTERÍSTICA 2: TEXTURA (Varianza matemática)
                # Primero convertimos la ventana a escala de grises promediando el RGB (axis=2)
                # Luego calculamos la varianza (np.var) para ver qué tan rugosa/cambiante es la zona
                varianza = np.var(np.mean(ventana, axis=2))
                
                # --- REGLAS DE CLASIFICACIÓN EMPÍRICAS ---
                # Si predomina el Verde y hay cierta textura rugosa -> Césped
                if G > R and G > B and varianza > 30:
                    resultado[y, x] = [0, 255, 0]        
                
                # Si predomina el Rojo (que mezclado con verde da tonos tierra) y es rugoso -> Tierra
                elif R > G and R > 80 and varianza > 50:
                    resultado[y, x] = [139, 69, 19]      
                
                # Si no es rugoso (liso, varianza baja) y los colores son claros/neutros -> Cemento
                elif varianza < 20 and (R < 180 and G < 180 and B < 180):
                    resultado[y, x] = [180, 180, 180]    
                
                # Si no encaja en lo anterior (generalmente oscuro y rugoso) -> Asfalto u otros
                else:
                    resultado[y, x] = [50, 50, 50]       

        # --- FIN DEL ALGORITMO ---

        # Convertimos la matriz resultante nuevamente a un formato de imagen visual
        self.img_procesada = Image.fromarray(resultado)
        img_res_tk = ImageTk.PhotoImage(self.img_procesada)
        
        # Mostramos la imagen final en la etiqueta derecha
        self.lbl_despues.config(image=img_res_tk, compound="none")
        self.lbl_despues.image = img_res_tk
        messagebox.showinfo("Éxito", "El procesamiento ha finalizado.")

    def guardar_imagen(self):
        # Validación de seguridad
        if not self.img_procesada:
            messagebox.showwarning("Advertencia", "No hay ninguna imagen procesada para guardar.")
            return
            
        # Abrimos el cuadro de diálogo para preguntar dónde y cómo guardar la imagen
        ruta_guardado = filedialog.asksaveasfilename(defaultextension=".jpg", filetypes=[("JPEG", "*.jpg"), ("PNG", "*.png")])
        
        # Si eligió una ruta, la guardamos
        if ruta_guardado:
            self.img_procesada.save(ruta_guardado)
            messagebox.showinfo("Guardado", f"Imagen guardada correctamente en:\n{ruta_guardado}")

if __name__ == "__main__":
    # Arrancamos la aplicación
    root = tk.Tk()
    app = TexturaApp(root)
    root.mainloop()