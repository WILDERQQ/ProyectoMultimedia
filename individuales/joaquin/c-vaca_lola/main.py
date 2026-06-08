import cv2
import os

video_path = "assets/video/vacalola.mp4"
output_path = "output/video-letra.mp4"

cap = cv2.VideoCapture(video_path)

fps = cap.get(cv2.CAP_PROP_FPS)
width = int(cap.get(3))
height = int(cap.get(4))

out = cv2.VideoWriter(
    output_path,
    cv2.VideoWriter_fourcc(*'XVID'),
    fps,
    (width, height)
)

subtitles = [
    # Verso 1
    (0.16, 4.0, "La vaca feliz del campo esta"),
    (4.0, 8.0, "tiene cabeza y cola ya"),
    (8.0, 12.0, "Dice muuu, muy feliz sera"),
    (12.0, 16.0, "en la granja quiere jugar"),

    # coro
    (18.0, 21.0, "Muu muu muu, vamos a cantar"),
    (21.0, 26.0, "bajo el sol quiere descansar"),
    (26.0, 30.0, "Muu muu muu, puede bailar"),
    (30.0, 34.0, "en el campo sin parar"),

    # Verso 2
    (35.0, 40.0, "La vaca juega sin descansar"),
    (40.0, 43.0, "corre en el campo sin parar"),
    (43.0, 47.0, "Dice muuu al despertar"),
    (47.0, 53.0, "y sonrie al caminar"),

    # Coro
    (53.0, 57.0, "Muu muu muu, vamos a cantar"),
    (57.0, 62.0, "bajo el sol quiere descansar"),
    (62.0, 66.0, "Muu muu muu, puede bailar"),
    (66.0, 72.0, "en el campo sin parar"),
]



frame_index = 0

while True:
    ret, frame = cap.read()
    if not ret:
        break

    time = frame_index / fps

    text = ""

    for start, end, line in subtitles:
        if start <= time < end:
            text = line

    # Texto 
    if text:
        cv2.putText(
            frame,
            text,
            (50, height - 80),
            cv2.FONT_HERSHEY_SIMPLEX,
            1.2,
            (255, 255, 255),
            3,
            cv2.LINE_AA
        )

    out.write(frame)
    frame_index += 1

cap.release()
out.release()
cv2.destroyAllWindows()

print("listo")