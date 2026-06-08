"""
src/escena.py
=============
Controlador principal. Arranca directo al show con audio automatico.
SPACE congela todo (animacion + audio). ESC cierra.
"""

import os
import random
import pygame
from pygame.locals import QUIT, KEYDOWN, K_ESCAPE, K_SPACE

from config import (
    VENTANA_W, VENTANA_H, FPS, TITULO,
    BPM_DEFAULT, NUM_PARTICULAS, NUM_ESTRELLAS,
    MORADO, AMARILLO, ROSA, CYAN, VERDE_NEO
)
from src.utils.anim import rebote
from src.personajes.monigote import Monigote
from src.personajes.vaca import VacaMonigote
from src.ui.particulas import Particula
from src.ui.pantalla_show import PantallaShow
from src.audio.gestor_audio import GestorAudio
from src.audio.letras import LETRAS, TOLERANCIA_SYNC, INTERVALO_SIN_AUDIO

AUDIO_PATH = os.path.join("src", "audio", "audio.mp3")


class EscenaVacaLola:

    def __init__(self):
        pygame.init()
        self.screen  = pygame.display.set_mode((VENTANA_W, VENTANA_H))
        pygame.display.set_caption(TITULO)
        self.clock   = pygame.time.Clock()

        self._init_fuentes()
        self._init_personajes()
        self._init_particulas()
        self._init_estado()
        self._init_pantallas()

        if self.audio.cargar(AUDIO_PATH):
            self.audio.reproducir()
        else:
            print(f"[Audio] No se pudo cargar: {AUDIO_PATH}")

    def _init_fuentes(self):
        self.fuentes = {
            "titulo":  pygame.font.SysFont("couriernew", 36, bold=True),
            "normal":  pygame.font.SysFont("couriernew", 18, bold=True),
            "pequena": pygame.font.SysFont("couriernew", 13),
            "nombre":  pygame.font.SysFont("couriernew", 13, bold=True),
        }

    def _init_personajes(self):
        self.monigotes = [
            Monigote(200, 380, escala=1.1,
                     color_ropa=MORADO, color_acc=AMARILLO,
                     nombre="Bad Bunny"),
            Monigote(700, 380, escala=1.0,
                     color_ropa=ROSA, color_acc=VERDE_NEO,
                     nombre="J Balvin", espejo=True),
        ]
        self.vaca = VacaMonigote(450, 350, escala=0.85)

    def _init_particulas(self):
        self.particulas = [Particula() for _ in range(NUM_PARTICULAS)]

    def _init_estado(self):
        self.audio        = GestorAudio()
        self.t            = 0.0
        self.running      = True
        self.pausado      = False          # pausa global (animacion + audio)
        self.bpm          = BPM_DEFAULT
        self.intensidad   = 0.5
        self.letra_actual = ""
        self.letra_timer  = 0.0
        self.estrellas = [
            (random.randint(0, VENTANA_W),
             random.randint(0, VENTANA_H // 2),
             random.randint(1, 3))
            for _ in range(NUM_ESTRELLAS)
        ]

    def _init_pantallas(self):
        self.show = PantallaShow(
            fuentes=self.fuentes,
            monigotes=self.monigotes,
            vaca=self.vaca,
            particulas=self.particulas,
            estrellas=self.estrellas,
        )

    # ------------------------------------------------------------------

    def _toggle_pausa(self):
        self.pausado = not self.pausado
        self.audio.pausar_reanudar()

    def _actualizar(self, dt):
        if self.pausado:
            return                         # todo congelado
        self.t         += dt
        self.intensidad = 0.35 + rebote(self.t, self.bpm) * 0.65
        for p in self.particulas:
            p.actualizar(dt, self.intensidad)
        self._actualizar_letras()
        self.letra_timer += dt

    def _actualizar_letras(self):
        if self.audio.esta_activo():
            t_song      = self.audio.tiempo_reproduccion()
            letra_nueva = ""
            for ts, texto in LETRAS:
                if t_song >= ts - TOLERANCIA_SYNC:
                    letra_nueva = texto
            if letra_nueva and self.letra_actual != letra_nueva:
                self.letra_actual = letra_nueva
                self.letra_timer  = 0.0
        else:
            idx  = int(self.t / INTERVALO_SIN_AUDIO) % len(LETRAS)
            nueva = LETRAS[idx][1]
            if self.letra_actual != nueva:
                self.letra_actual = nueva
                self.letra_timer  = 0.0

    def _manejar_eventos(self):
        for ev in pygame.event.get():
            if ev.type == QUIT:
                self.running = False
            elif ev.type == KEYDOWN:
                if ev.key == K_ESCAPE:
                    self.running = False
                elif ev.key == K_SPACE:
                    self._toggle_pausa()

    # ------------------------------------------------------------------

    def run(self):
        dt = 0.0
        while self.running:
            self._manejar_eventos()
            self._actualizar(dt)

            self.show.dibujar(
                surf=self.screen,
                t=self.t,
                intensidad=self.intensidad,
                letra_actual=self.letra_actual,
                letra_timer=self.letra_timer,
                bpm=self.bpm,
                audio=self.audio,
                dt=0.0 if self.pausado else dt,  # video tambien se congela
            )

            pygame.display.flip()
            dt = self.clock.tick(FPS) / 1000.0

        pygame.quit()
