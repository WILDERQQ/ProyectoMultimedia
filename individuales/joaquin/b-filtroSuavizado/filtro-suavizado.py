import js
from pyodide.ffi import create_proxy
from PIL import Image
import io
import numpy as np

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

def btn_smooth_filter_click(event):
    if original_pillow_img is None: 
        return
    img_np = np.array(original_pillow_img)
    suavizado = img_np.astype(np.float32)
    
    # Convolución paralela vectorizada mediante rodajas de matrices (NumPy Slicing)
    recorte = (
        suavizado[0:-2, 0:-2] + suavizado[0:-2, 1:-1] + suavizado[0:-2, 2:] +  # Fila superior
        suavizado[1:-1, 0:-2] + suavizado[1:-1, 1:-1] + suavizado[1:-1, 2:] +  # Fila del medio
        suavizado[2:, 0:-2]   + suavizado[2:, 1:-1]   + suavizado[2:, 2:]      # Fila inferior
    ) / 9.0
    
    processed_np = img_np.copy()
    processed_np[1:-1, 1:-1] = recorte.astype(np.uint8)
    
    render_pillow_to_canvas(Image.fromarray(processed_np), canvas2)

# Vinculación del DOM
js.document.getElementById("btnUpload").addEventListener("click", create_proxy(lambda e: js.document.getElementById("fileUpload").click()))
js.document.getElementById("fileUpload").addEventListener("change", create_proxy(upload_image))
js.document.getElementById("btnSmooth").addEventListener("click", create_proxy(btn_smooth_filter_click))