"""
src/ui/pantalla_show.py
=======================
Renderiza el show principal:
  - Fondo con estrellas y lineas de neon
  - Video en bucle centrado
  - Escenario (piso, sombras)
  - Particulas
  - Personajes
  - HUD
"""

import os
import math
import numpy as np
import pygame
import cv2

from config import (
    NEGRO, CYAN, AMARILLO, BLANCO, GRIS,
    VENTANA_W, VENTANA_H, FPS
)
from src.utils.draw import circulo, linea, rect_r, elipse
from src.utils.anim import pulso
from src.ui.hud import HUD

# Ruta al video (relativa al main.py)
VIDEO_PATH = os.path.join("src", "assets", "video", "video.mp4")

# Tamanio del video en pantalla
VIDEO_W = 340
VIDEO_H = 260


class PantallaShow:

    def __init__(self, fuentes, monigotes, vaca, particulas, estrellas):
        self.fn         = fuentes["normal"]
        self.fp         = fuentes["pequena"]
        self.fnombre    = fuentes["nombre"]
        self.monigotes  = monigotes
        self.vaca       = vaca
        self.particulas = particulas
        self.estrellas  = estrellas
        self.hud        = HUD(self.fn, self.fp, VENTANA_W, VENTANA_H)

        self._cap         = None
        self._video_ok    = False
        self._frame_surf  = None
        self._fps_video   = 30.0
        self._frame_timer = 0.0
        self._cargar_video()

    def _cargar_video(self):
        if not os.path.exists(VIDEO_PATH):
            print(f"[Video] No encontrado: {VIDEO_PATH}")
            return
        cap = cv2.VideoCapture(VIDEO_PATH)
        if not cap.isOpened():
            print(f"[Video] No se pudo abrir: {VIDEO_PATH}")
            return
        self._cap       = cap
        self._video_ok  = True
        self._fps_video = cap.get(cv2.CAP_PROP_FPS) or 30.0
        self._avanzar_frame()

    def _avanzar_frame(self):
        if not self._video_ok:
            return
        ret, frame = self._cap.read()
        if not ret:
            self._cap.set(cv2.CAP_PROP_POS_FRAMES, 0)
            ret, frame = self._cap.read()
            if not ret:
                return
        frame_rgb     = cv2.cvtColor(frame, cv2.COLOR_BGR2RGB)
        frame_resized = cv2.resize(frame_rgb, (VIDEO_W, VIDEO_H))
        self._frame_surf = pygame.surfarray.make_surface(
            np.transpose(frame_resized, (1, 0, 2))
        )

    # ------------------------------------------------------------------

    def dibujar(self, surf, t, intensidad, letra_actual, letra_timer, bpm, audio, dt=0.016):
        surf.fill(NEGRO)
        self._fondo_estrellas(surf, t, bpm)
        self._lineas_piso(surf, t, bpm)
        self._escenario(surf, intensidad)
        self._dibujar_video(surf, dt)

        for p in self.particulas:
            p.dibujar(surf)

        self.vaca.dibujar(surf, t, intensidad)
        for m in self.monigotes:
            m.dibujar(surf, t, intensidad, self.fnombre)

        self.hud.dibujar(surf, intensidad, audio)

    def _dibujar_video(self, surf, dt):
        if not self._video_ok or self._frame_surf is None:
            return
        self._frame_timer += dt
        intervalo = 1.0 / self._fps_video
        if self._frame_timer >= intervalo:
            self._frame_timer -= intervalo
            self._avanzar_frame()
        cx = (VENTANA_W - VIDEO_W) // 2
        cy = (VENTANA_H - VIDEO_H) // 2 - 20
        surf.blit(self._frame_surf, (cx, cy))

    def _fondo_estrellas(self, surf, t, bpm):
        pb = pulso(t, bpm) * 0.15
        for i, (sx, sy, sr) in enumerate(self.estrellas):
            b = int(100 + math.sin(t * 2 + i) * 80 + pb * 100)
            b = max(0, min(255, b))
            circulo(surf, (b, b, b), sx, sy, sr)

    def _lineas_piso(self, surf, t, bpm):
        piso_y = VENTANA_H - 80
        for i in range(0, VENTANA_W, 40):
            alpha = int(30 + pulso(t + i * 0.01, bpm) * 60)
            col   = (0, alpha, min(255, alpha + 40))
            linea(surf, col, i, piso_y, VENTANA_W // 2, piso_y - 60, 1)
            linea(surf, col, VENTANA_W - i, piso_y, VENTANA_W // 2, piso_y - 60, 1)

    def _escenario(self, surf, intensidad):
        pygame.draw.rect(surf, (15, 15, 25), (0, VENTANA_H - 80, VENTANA_W, 80))
        reflex = tuple(int(c * (0.3 + intensidad * 0.4)) for c in CYAN)
        linea(surf, reflex, 0, VENTANA_H - 80, VENTANA_W, VENTANA_H - 80, 2)
        for m in self.monigotes:
            elipse(surf, (30, 0, 60), (int(m.x - 40), VENTANA_H - 88, 80, 18))
        elipse(surf, (30, 0, 60),
               (int(self.vaca.x - 60), VENTANA_H - 88, 120, 18))
