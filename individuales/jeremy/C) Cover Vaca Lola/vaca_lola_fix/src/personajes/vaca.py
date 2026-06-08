"""
Vaca animada estilo caricatura con manchas, cola y patas en bounce.
"""

import math

from config import (
    NEGRO, AMARILLO, ROSA, BLANCO,
    VACA_BLANCO, VACA_NEGRO
)
from src.utils.draw import circulo, rect_r, linea


class VacaMonigote:
    """
    Vaca animada con bounce corporal, cola oscilante y patas en movimiento.

    Parametros
    ----------
    x, y   : posicion central del cuerpo
    escala : factor de tamano (1.0 = tamano base)
    """

    def __init__(self, x, y, escala=1.0):
        self.x   = x
        self.y   = y
        self.esc = escala

    def _s(self, v):
        return v * self.esc

    def dibujar(self, surf, t, intensidad=0.5):
        """
        Renderiza la vaca animada sobre 'surf'.

        Parametros
        ----------
        t          : tiempo global en segundos
        intensidad : 0.0 - 1.0, amplitud del bounce
        """
        amp = intensidad * self._s(10)
        by  = self.y + math.sin(t * 3) * amp

        self._dibujar_cuerpo(surf, by)
        self._dibujar_patas(surf, t, by, amp)
        self._dibujar_cabeza(surf, by)
        self._dibujar_cola(surf, t, by)

    # ------------------------------------------------------------------

    def _dibujar_cuerpo(self, surf, by):
        rect_r(surf, VACA_BLANCO,
               self.x - self._s(55), by - self._s(30),
               self._s(110), self._s(60), int(self._s(12)))
        # Manchas
        circulo(surf, VACA_NEGRO, self.x - self._s(20), by - self._s(10), self._s(16))
        circulo(surf, VACA_NEGRO, self.x + self._s(25), by,               self._s(12))
        circulo(surf, VACA_NEGRO, self.x,               by - self._s(22), self._s(9))

    def _dibujar_patas(self, surf, t, by, amp):
        for dx in [-self._s(35), -self._s(15), self._s(15), self._s(35)]:
            fase  = dx * 0.05
            pat_y = by + self._s(30)
            pat_h = self._s(35) + math.sin(t * 4 + fase) * amp * 0.3
            rect_r(surf, VACA_BLANCO,
                   self.x + dx - self._s(7), pat_y,
                   self._s(14), pat_h, int(self._s(4)))
            rect_r(surf, VACA_NEGRO,
                   self.x + dx - self._s(7), pat_y + pat_h - self._s(8),
                   self._s(14), self._s(8), int(self._s(3)))

    def _dibujar_cabeza(self, surf, by):
        cx = self.x + self._s(60)
        cy = by - self._s(15)

        circulo(surf, VACA_BLANCO, cx, cy, self._s(30))
        # Oreja
        circulo(surf, VACA_BLANCO, cx, cy - self._s(26), self._s(10))
        circulo(surf, ROSA,        cx, cy - self._s(26), self._s(6))
        # Cuernos
        linea(surf, AMARILLO,
              cx - self._s(10), cy - self._s(28),
              cx - self._s(18), cy - self._s(44), 3)
        linea(surf, AMARILLO,
              cx + self._s(2),  cy - self._s(30),
              cx + self._s(8),  cy - self._s(46), 3)
        # Mancha cara
        circulo(surf, VACA_NEGRO, cx + self._s(12), cy - self._s(5), self._s(8))
        # Ojos
        circulo(surf, BLANCO,     cx - self._s(8),  cy - self._s(8), self._s(7))
        circulo(surf, NEGRO,      cx - self._s(7),  cy - self._s(8), self._s(4))
        # Nariz
        rect_r(surf, ROSA,
               cx - self._s(12), cy + self._s(8),
               self._s(24), self._s(16), int(self._s(6)))
        circulo(surf, VACA_NEGRO, cx - self._s(5), cy + self._s(14), self._s(3))
        circulo(surf, VACA_NEGRO, cx + self._s(5), cy + self._s(14), self._s(3))

    def _dibujar_cola(self, surf, t, by):
        cx  = self.x - self._s(55)
        cy  = by - self._s(10)
        tip_x = cx - self._s(20) + math.sin(t * 5) * self._s(12)
        tip_y = cy - self._s(25)
        linea(surf, VACA_NEGRO, cx, cy, tip_x, tip_y, 3)
        circulo(surf, VACA_NEGRO, tip_x, tip_y, self._s(5))
