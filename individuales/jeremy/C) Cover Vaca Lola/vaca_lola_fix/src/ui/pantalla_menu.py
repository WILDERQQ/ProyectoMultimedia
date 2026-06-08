"""
Renderiza la pantalla de inicio / menu principal:
  - Titulos
  - Botones (Cargar MP3 / Solo animacion)
  - Preview de personajes animados
  - Instrucciones
"""

import pygame

from config import (
    NEGRO, MORADO, MORADO_OSC, CYAN, AMARILLO, GRIS, ROSA,
    VENTANA_W, VENTANA_H
)
from src.utils.draw import rect_r, circulo, linea
from src.utils.anim import pulso


# Coordenadas Y de los botones (centro)
BTN_Y_MP3   = 285
BTN_Y_ANIM  = 355
BTN_W, BTN_H = 380, 48


class PantallaMenu:
    """
    Gestiona el renderizado de la pantalla de menu.

    Recibe referencias a los personajes para mostrar el preview,
    y a las fuentes ya inicializadas.
    """

    def __init__(self, fuente_titulo, fuente_normal, fuente_pequena,
                 fuente_nombre, monigotes, vaca, estrellas):
        self.ft         = fuente_titulo
        self.fn         = fuente_normal
        self.fp         = fuente_pequena
        self.fnombre    = fuente_nombre
        self.monigotes  = monigotes
        self.vaca       = vaca
        self.estrellas  = estrellas

    # ------------------------------------------------------------------
    # API publica
    # ------------------------------------------------------------------

    def dibujar(self, surf, t: float, msg_estado: str):
        surf.fill(NEGRO)
        self._fondo_estrellas(surf, t)
        self._titulos(surf)
        self._botones(surf)
        self._mensaje_estado(surf, msg_estado)
        self._preview_personajes(surf, t)
        self._instrucciones(surf)

    @staticmethod
    def en_boton_mp3(mx, my) -> bool:
        return _en_boton(mx, my, VENTANA_W // 2, BTN_Y_MP3)

    @staticmethod
    def en_boton_anim(mx, my) -> bool:
        return _en_boton(mx, my, VENTANA_W // 2, BTN_Y_ANIM)

    # ------------------------------------------------------------------
    # Renderizado interno
    # ------------------------------------------------------------------

    def _fondo_estrellas(self, surf, t):
        import math
        pb = pulso(t) * 0.15
        for i, (sx, sy, sr) in enumerate(self.estrellas):
            b = int(100 + math.sin(t * 2 + i) * 80 + pb * 100)
            b = max(0, min(255, b))
            circulo(surf, (b, b, b), sx, sy, sr)

    def _titulos(self, surf):
        t1 = self.ft.render("LA VACA LOLA", True, AMARILLO)
        t2 = self.fn.render("Bad Bunny  ft.  J Balvin  (Cover Multimedia)", True, CYAN)
        t3 = self.fp.render("Actividad Individual c)  --  Multimedia UMSA", True, GRIS)
        surf.blit(t1, (VENTANA_W // 2 - t1.get_width() // 2, 120))
        surf.blit(t2, (VENTANA_W // 2 - t2.get_width() // 2, 175))
        surf.blit(t3, (VENTANA_W // 2 - t3.get_width() // 2, 205))

    def _botones(self, surf):
        _dibujar_boton(surf, self.fn,
                       VENTANA_W // 2, BTN_Y_MP3,
                       "Cargar MP3 y animar",
                       MORADO, AMARILLO)
        _dibujar_boton(surf, self.fn,
                       VENTANA_W // 2, BTN_Y_ANIM,
                       "Animar sin audio",
                       MORADO_OSC, CYAN)

    def _mensaje_estado(self, surf, msg):
        col = ROSA if msg else GRIS
        txt = msg if msg else "Selecciona una opcion para comenzar"
        s   = self.fp.render(txt, True, col)
        surf.blit(s, (VENTANA_W // 2 - s.get_width() // 2, 415))

    def _preview_personajes(self, surf, t):
        # Guardar posiciones originales
        ox, oy, oesc = self.vaca.x, self.vaca.y, self.vaca.esc
        orig_m = [(m.x, m.y) for m in self.monigotes]

        # Posiciones de preview (esquinas inferiores)
        self.vaca.x, self.vaca.y, self.vaca.esc = VENTANA_W // 2, VENTANA_H - 80, 0.55
        self.monigotes[0].x, self.monigotes[0].y = 80,              VENTANA_H - 72
        self.monigotes[1].x, self.monigotes[1].y = VENTANA_W - 80,  VENTANA_H - 72

        self.vaca.dibujar(surf, t, 0.25)
        for m in self.monigotes:
            m.dibujar(surf, t, 0.25, self.fnombre)

        # Restaurar
        self.vaca.x, self.vaca.y, self.vaca.esc = ox, oy, oesc
        for m, (px, py) in zip(self.monigotes, orig_m):
            m.x, m.y = px, py

    def _instrucciones(self, surf):
        inst = self.fp.render(
            "ESC para salir  |  ESPACIO para pausar  |  R para reiniciar",
            True, GRIS)
        surf.blit(inst, (VENTANA_W // 2 - inst.get_width() // 2, VENTANA_H - 30))


# ------------------------------------------------------------------
# Helpers de modulo
# ------------------------------------------------------------------

def _en_boton(mx, my, cx, cy) -> bool:
    return (cx - BTN_W // 2 <= mx <= cx + BTN_W // 2 and
            cy - BTN_H // 2 <= my <= cy + BTN_H // 2)


def _dibujar_boton(surf, fuente, cx, cy, texto, color_fondo, color_borde):
    bx = cx - BTN_W // 2
    by = cy - BTN_H // 2
    rect_r(surf, color_fondo, bx, by, BTN_W, BTN_H, 10)
    pygame.draw.rect(surf, color_borde, (bx, by, BTN_W, BTN_H), 2, border_radius=10)
    lbl = fuente.render(texto, True, color_borde)
    surf.blit(lbl, (cx - lbl.get_width() // 2, cy - lbl.get_height() // 2))
