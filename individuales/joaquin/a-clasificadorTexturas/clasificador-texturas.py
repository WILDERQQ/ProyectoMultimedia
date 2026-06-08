import js
from pyodide.ffi import create_proxy
from PIL import Image
import io
import numpy as np

original_pillow_img = None
ventana = 10

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
        
        original_pillow_img = Image.open(io.BytesIO(byte_array)).convert("RGB")
        
        canvas1.width = original_pillow_img.width
        canvas1.height = original_pillow_img.height
        canvas2.width = original_pillow_img.width
        canvas2.height = original_pillow_img.height
        
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

def btn_classifier_textures_click(event):
    if original_pillow_img is None: 
        return
    img_np = np.array(original_pillow_img)
    h, w, _ = img_np.shape
    processed_np = img_np.copy()

    # Análisis estadístico espacial por bloques N x N
    for i in range(0, w - ventana, ventana):
        for j in range(0, h - ventana, ventana):
            bloque = img_np[j:j+ventana, i:i+ventana]
            prom = bloque.mean(axis=(0,1))
            var = bloque.var(axis=(0,1))[0] # Varianza del canal Rojo (Medidor de Rugosidad)
            
            # 1. Criterio para AGUA (Baja intensidad lumínica y superficie lisa)
            if prom[2] > prom[0] and var < 15 and prom[1] < 100:
                processed_np[j:j+ventana, i:i+ventana] = [0, 0, 255] # Azul
                
            # 2. Criterio para VEGETACIÓN / BOSQUES (Verde dominante con rugosidad natural)
            elif prom[1] > prom[0] and prom[1] > prom[2] and var > 20:
                processed_np[j:j+ventana, i:i+ventana] = [0, 255, 0] # Verde
                
            # 3. Criterio para ZONA URBANA (Fuerte dispersión y contrastes de píxeles)
            elif var > 150:
                processed_np[j:j+ventana, i:i+ventana] = [255, 0, 0] # Rojo
                
            # 4. Caso base residual: SUELO DESNUDO / TRANSICIONES
            else:
                processed_np[j:j+ventana, i:i+ventana] = [255, 255, 0] # Amarillo

    render_pillow_to_canvas(Image.fromarray(processed_np), canvas2)

# Vinculación del DOM
js.document.getElementById("btnUpload").addEventListener("click", create_proxy(lambda e: js.document.getElementById("fileUpload").click()))
js.document.getElementById("fileUpload").addEventListener("change", create_proxy(upload_image))
js.document.getElementById("btnClassifier").addEventListener("click", create_proxy(btn_classifier_textures_click))