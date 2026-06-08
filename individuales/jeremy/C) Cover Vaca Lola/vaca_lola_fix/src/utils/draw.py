"""
src/utils/draw.py
=================
Funciones de dibujo de bajo nivel sobre superficies pygame.
Todas las coordenadas se convierten a enteros internamente.
"""

import pygame
import math


def circulo(surf, color, cx, cy, r, ancho=0):
    pygame.draw.circle(surf, color, (int(cx), int(cy)), max(1, int(r)), ancho)


def rect_r(surf, color, x, y, w, h, radio=6):
    pygame.draw.rect(surf, color, (int(x), int(y), int(w), int(h)),
                     border_radius=radio)


def linea(surf, color, x1, y1, x2, y2, ancho=2):
    pygame.draw.line(surf, color,
                     (int(x1), int(y1)), (int(x2), int(y2)), ancho)


def arco(surf, color, rect, ang_inicio, ang_fin, ancho=2):
    pygame.draw.arc(surf, color, rect, ang_inicio, ang_fin, ancho)


def elipse(surf, color, rect):
    pygame.draw.ellipse(surf, color, rect)
