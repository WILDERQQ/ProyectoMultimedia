"""
src/ui/particulas.py
====================
Particulas flotantes decorativas sincronizadas con la intensidad del beat.
"""

import random

from config import (
    VENTANA_W, VENTANA_H,
    CYAN, ROSA, AMARILLO, VERDE_NEO, MORADO
)
from src.utils.draw import circulo

_COLORES = [CYAN, ROSA, AMARILLO, VERDE_NEO, MORADO]


class Particula:
    """Una sola particula con ciclo de vida, velocidad y fade-out."""

    def __init__(self):
        self.reset()

    def reset(self):
        self.x        = random.randint(0, VENTANA_W)
        self.y        = random.randint(-50, VENTANA_H + 50)
        self.vx       = random.uniform(-1.0, 1.0)
        self.vy       = random.uniform(-3.0, -0.5)
        self.r        = random.randint(2, 6)
        self.color    = random.choice(_COLORES)
        self.vida     = random.uniform(0.5, 2.5)
        self.vida_max = self.vida

    def actualizar(self, dt: float, intensidad: float):
        self.x    += self.vx * (1 + intensidad * 3)
        self.y    += self.vy * (1 + intensidad * 4)
        self.vida -= dt
        if self.vida <= 0 or self.y < -20:
            self.reset()

    def dibujar(self, surf):
        alpha = max(0.0, self.vida / self.vida_max)
        r = int(self.r * alpha)
        if r > 0:
            circulo(surf, self.color, self.x, self.y, r)
