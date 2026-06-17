import sys
import numpy as np

# Detectar contexto: navegador (PyScript / Pyodide) vs CLI local (Python estándar)
try:
    import js
    from pyodide.ffi import create_proxy
    from PIL import Image
    import io
    in_browser = True
except ImportError:
    in_browser = False

# Kernels Sobel para detección de bordes horizontal (X) y vertical (Y)
kernelX = np.array([
    [-1, 0, 1],
    [-2, 0, 2],
    [-1, 0, 1]
])

kernelY = np.array([
    [-1, -2, -1],
    [0, 0, 0],
    [1, 2, 1]
])

if in_browser:
    original_pillow_img = None

    canvas1 = js.document.getElementById("canvas1")
    canvas2 = js.document.getElementById("canvas2")

    def upload_image(event):
        global original_pillow_img
        file_list = event.target.files
        if len(file_list) == 0:
            return
        
        file = file_list.item(0)
        reader = js.FileReader.new()
        
        def on_load_end(e):
            global original_pillow_img
            result = reader.result
            base64_data = result.split(",")[1]
            img_bytes = js.atob(base64_data)
            byte_array = bytearray([ord(c) for c in img_bytes])
            
            # Cargar imagen en formato RGB con Pillow
            original_pillow_img = Image.open(io.BytesIO(byte_array)).convert("RGB")
            
            # Ajustar dimensiones de canvas
            canvas1.width = original_pillow_img.width
            canvas1.height = original_pillow_img.height
            canvas2.width = original_pillow_img.width
            canvas2.height = original_pillow_img.height
            
            # Dibujar imagen en ambos lienzos
            render_pillow_to_canvas(original_pillow_img, canvas1)
            render_pillow_to_canvas(original_pillow_img, canvas2)

        reader.onloadend = create_proxy(on_load_end)
        reader.readAsDataURL(file)

    def render_pillow_to_canvas(pil_img, target_canvas):
        buf = io.BytesIO()
        pil_img.save(buf, format="PNG")
        img_bytes = buf.getvalue()
        
        binary_str = "".join([chr(b) for b in img_bytes])
        base64_str = js.btoa(binary_str)
        data_url = f"data:image/png;base64,{base64_str}"
        
        ctx = target_canvas.getContext("2d")
        img_html = js.Image.new()
        
        def on_image_loaded(e):
            ctx.clearRect(0, 0, target_canvas.width, target_canvas.height)
            ctx.drawImage(img_html, 0, 0)
            
        img_html.onload = create_proxy(on_image_loaded)
        img_html.src = data_url

    def btn_edge_filter_click(event):
        if original_pillow_img is None: 
            return
        
        # Convertir a escala de grises para detección de bordes
        gray_pil = original_pillow_img.convert("L")
        img_np = np.array(gray_pil).astype(np.float32)
        alto, ancho = img_np.shape

        # Convolución paralela vectorizada mediante rodajas de matrices (NumPy Slicing)
        gx = (
            -1 * img_np[0:-2, 0:-2] + 0 * img_np[0:-2, 1:-1] + 1 * img_np[0:-2, 2:] +
            -2 * img_np[1:-1, 0:-2] + 0 * img_np[1:-1, 1:-1] + 2 * img_np[1:-1, 2:] +
            -1 * img_np[2:, 0:-2]   + 0 * img_np[2:, 1:-1]   + 1 * img_np[2:, 2:]
        )
        
        gy = (
            -1 * img_np[0:-2, 0:-2] + -2 * img_np[0:-2, 1:-1] + -1 * img_np[0:-2, 2:] +
             0 * img_np[1:-1, 0:-2] +  0 * img_np[1:-1, 1:-1] +  0 * img_np[1:-1, 2:] +
             1 * img_np[2:, 0:-2]   +  2 * img_np[2:, 1:-1]   +  1 * img_np[2:, 2:]
        )
        
        # Calcular magnitud de gradiente aproximada
        absX = np.abs(gx)
        absY = np.abs(gy)
        absXY = (absX * 0.5 + absY * 0.5)
        
        # Colocar el resultado en una matriz del mismo tamaño con bordes en 0 (negro)
        processed_np = np.zeros((alto, ancho), dtype=np.uint8)
        processed_np[1:-1, 1:-1] = np.clip(absXY, 0, 255).astype(np.uint8)
        
        # Renderizar de vuelta a canvas2
        render_pillow_to_canvas(Image.fromarray(processed_np, mode="L"), canvas2)

    # Registro de eventos en el DOM (PyScript)
    js.document.getElementById("btnUpload").addEventListener("click", create_proxy(lambda e: js.document.getElementById("fileUpload").click()))
    js.document.getElementById("fileUpload").addEventListener("change", create_proxy(upload_image))
    js.document.getElementById("btnBordes").addEventListener("click", create_proxy(btn_edge_filter_click))

else:
    # Código CLI local - ejecutándose en consola con Python estándar
    import cv2
    import os
    
    # Nombre de la imagen por defecto o pasado por argumentos
    nombre_archivo = "images/filtro.jpg" if os.path.exists("images/filtro.jpg") else "peaches.jpg"
    if len(sys.argv) > 1:
        nombre_archivo = sys.argv[1]
        
    print(f"Ejecutando Detección de Bordes Sobel en CLI sobre: {nombre_archivo}")
    
    # Cargar en escala de grises
    img = cv2.imread(nombre_archivo, 0)
    if img is None:
        print(f"Error: No se pudo cargar la imagen '{nombre_archivo}'. Asegúrate de que existe.")
        sys.exit(1)
        
    alto, ancho = img.shape
    
    gx = np.zeros((alto, ancho))
    gy = np.zeros((alto, ancho))
    
    # Convolución manual solicitada originalmente
    print("Aplicando convolución manual Sobel 3x3 (esto puede tardar unos segundos)...")
    for i in range(1, alto-1):
        for j in range(1, ancho-1):
            region = img[i-1:i+2, j-1:j+2]
            gx[i, j] = np.sum(region * kernelX)
            gy[i, j] = np.sum(region * kernelY)
            
    # Magnitud aproximada de los gradientes
    absX = np.abs(gx)
    absY = np.abs(gy)
    absXY = (absX * 0.5 + absY * 0.5)
    
    # Limitar y convertir a uint8
    absX = np.clip(absX, 0, 255).astype(np.uint8)
    absY = np.clip(absY, 0, 255).astype(np.uint8)
    absXY = np.clip(absXY, 0, 255).astype(np.uint8)
    
    print("Mostrando resultados gráficamente. Presiona cualquier tecla sobre las ventanas para salir.")
    
    # Mostrar resultados en pantalla
    cv2.imshow("Imagen Original", img)
    cv2.imshow("Sobel X (Bordes Horizontales)", absX)
    cv2.imshow("Sobel Y (Bordes Verticales)", absY)
    cv2.imshow("Resultado Sobel (Bordes Combinados)", absXY)
    
    cv2.waitKey(0)
    cv2.destroyAllWindows()