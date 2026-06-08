"""
Stick-figure articulado con animacion de baile.
El movimiento es impulsado por tiempo y BPM; la amplitud
escala con la intensidad del beat.
"""

import math
import random

from config import (
    NEGRO, MORADO, CYAN, BLANCO, PIEL, PIEL_OSC
)
from src.utils.draw import circulo, rect_r, linea, arco


class Monigote:
    """
    Personaje humano animado estilo stick-figure con articulaciones.

    Parametros
    ----------
    x, y        : posicion central del personaje
    escala      : factor de tamano (1.0 = tamano base)
    color_ropa  : color del traje / pantalon
    color_acc   : color de accesorios (zapatos, gorra, rayas)
    espejo      : voltea horizontalmente la orientacion
    nombre      : etiqueta que se muestra debajo del personaje
    """

    def __init__(self, x, y, escala=1.0, color_ropa=MORADO,
                 color_acc=CYAN, espejo=False, nombre=""):
        self.x          = x
        self.y          = y
        self.esc        = escala
        self.color_ropa = color_ropa
        self.color_acc  = color_acc
        self.espejo     = espejo
        self.nombre     = nombre
        self.offset_t   = random.uniform(0, 10)   # desfase de fase individual

    # ------------------------------------------------------------------
    # Utilidad interna
    # ------------------------------------------------------------------

    def _s(self, v):
        """Aplica la escala del personaje a un valor."""
        return v * self.esc

    # ------------------------------------------------------------------
    # Renderizado
    # ------------------------------------------------------------------

    def dibujar(self, surf, t, intensidad=0.5, fuente=None):
        """
        Renderiza el monigote animado sobre 'surf'.

        Parametros
        ----------
        t          : tiempo global en segundos
        intensidad : 0.0 - 1.0, amplitud del movimiento segun el beat
        fuente     : pygame.font.Font para el nombre (opcional)
        """
        ta  = t + self.offset_t
        sgn = -1 if self.espejo else 1
        amp = intensidad * self._s(18)

        # Bounce vertical global
        base_y = self.y + math.sin(ta * 4) * amp * 0.4

        # --- Piernas ---
        pie_ix, pie_iy, pie_dx, pie_dy = self._calcular_piernas(ta, base_y, intensidad)

        # --- Torso ---
        torso_top = base_y - self._s(28)
        cadera_y  = base_y + self._s(28)
        rect_r(surf, self.color_ropa,
               self.x - self._s(20), torso_top,
               self._s(40), self._s(56), int(self._s(8)))
        linea(surf, self.color_acc,
              self.x + sgn * self._s(14), torso_top + self._s(6),
              self.x + sgn * self._s(14), cadera_y - self._s(4), int(self._s(3)))

        # --- Brazos ---
        self._dibujar_brazos(surf, ta, base_y, intensidad)

        # --- Cabeza y gorra ---
        self._dibujar_cabeza(surf, ta, base_y, sgn)

        # --- Etiqueta ---
        if False:  # nombres desactivados
            txt = fuente.render(self.nombre, True, self.color_acc)
            surf.blit(txt, (self.x - txt.get_width() // 2,
                            pie_iy + self._s(12)))

    # ------------------------------------------------------------------
    # Sub-metodos de renderizado
    # ------------------------------------------------------------------

    def _calcular_piernas(self, ta, base_y, intensidad):
        """Calcula y dibuja piernas; retorna posiciones de pies."""
        ang_pi      = math.sin(ta * 4) * 0.5 * intensidad
        ang_pd      = -ang_pi
        sep         = self._s(18)
        long        = self._s(55)
        cadera_y    = base_y + self._s(28)

        rod_ix = self.x - sep + math.sin(ang_pi) * long * 0.5
        rod_iy = cadera_y    + math.cos(ang_pi) * long * 0.5
        rod_dx = self.x + sep + math.sin(ang_pd) * long * 0.5
        rod_dy = cadera_y    + math.cos(ang_pd) * long * 0.5

        pie_ix = rod_ix + math.sin(ang_pi) * long * 0.5
        pie_iy = rod_iy + math.cos(ang_pi) * long * 0.5
        pie_dx = rod_dx + math.sin(ang_pd) * long * 0.5
        pie_dy = rod_dy + math.cos(ang_pd) * long * 0.5

        # Usar surf que viene del llamador no es posible aqui;
        # este metodo se llama desde dibujar() que recibe surf.
        # Refactorizado: devuelve datos y el llamador dibuja.
        # (se mantiene aqui para legibilidad, surf se pasa abajo)
        return pie_ix, pie_iy, pie_dx, pie_dy

    def _dibujar_piernas(self, surf, ta, base_y, intensidad):
        """Dibuja piernas y retorna posicion de pies."""
        ang_pi   = math.sin(ta * 4) * 0.5 * intensidad
        ang_pd   = -ang_pi
        sep      = self._s(18)
        long     = self._s(55)
        cadera_y = base_y + self._s(28)

        rod_ix = self.x - sep + math.sin(ang_pi) * long * 0.5
        rod_iy = cadera_y    + math.cos(ang_pi) * long * 0.5
        rod_dx = self.x + sep + math.sin(ang_pd) * long * 0.5
        rod_dy = cadera_y    + math.cos(ang_pd) * long * 0.5

        pie_ix = rod_ix + math.sin(ang_pi) * long * 0.5
        pie_iy = rod_iy + math.cos(ang_pi) * long * 0.5
        pie_dx = rod_dx + math.sin(ang_pd) * long * 0.5
        pie_dy = rod_dy + math.cos(ang_pd) * long * 0.5

        linea(surf, self.color_ropa, self.x, cadera_y, rod_ix, rod_iy, int(self._s(9)))
        linea(surf, self.color_ropa, self.x, cadera_y, rod_dx, rod_dy, int(self._s(9)))
        linea(surf, self.color_ropa, rod_ix, rod_iy, pie_ix, pie_iy, int(self._s(8)))
        linea(surf, self.color_ropa, rod_dx, rod_dy, pie_dx, pie_dy, int(self._s(8)))
        circulo(surf, self.color_acc, pie_ix, pie_iy, self._s(7))
        circulo(surf, self.color_acc, pie_dx, pie_dy, self._s(7))

        return pie_ix, pie_iy

    def _dibujar_brazos(self, surf, ta, base_y, intensidad):
        hombro_y   = base_y - self._s(20)
        long_brazo = self._s(40)
        ang_bi     = math.sin(ta * 4 + math.pi) * 0.8 * intensidad
        ang_bd     = math.sin(ta * 4) * 0.8 * intensidad

        cod_ix = self.x - self._s(22) + math.cos(ang_bi - 0.3) * long_brazo * 0.5
        cod_iy = hombro_y             + math.sin(ang_bi - 0.3) * long_brazo * 0.5
        man_ix = cod_ix               + math.cos(ang_bi + 0.2) * long_brazo * 0.5
        man_iy = cod_iy               + math.sin(ang_bi + 0.2) * long_brazo * 0.5

        cod_dx = self.x + self._s(22) + math.cos(ang_bd + 0.3) * long_brazo * 0.5
        cod_dy = hombro_y             + math.sin(ang_bd + 0.3) * long_brazo * 0.5
        man_dx = cod_dx               + math.cos(ang_bd - 0.2) * long_brazo * 0.5
        man_dy = cod_dy               + math.sin(ang_bd - 0.2) * long_brazo * 0.5

        linea(surf, PIEL_OSC, self.x - self._s(20), hombro_y, cod_ix, cod_iy, int(self._s(7)))
        linea(surf, PIEL_OSC, cod_ix, cod_iy, man_ix, man_iy, int(self._s(6)))
        linea(surf, PIEL_OSC, self.x + self._s(20), hombro_y, cod_dx, cod_dy, int(self._s(7)))
        linea(surf, PIEL_OSC, cod_dx, cod_dy, man_dx, man_dy, int(self._s(6)))
        circulo(surf, PIEL, man_ix, man_iy, self._s(6))
        circulo(surf, PIEL, man_dx, man_dy, self._s(6))

    def _dibujar_cabeza(self, surf, ta, base_y, sgn):
        cab_y = base_y - self._s(62)
        cab_r = self._s(24)

        # Sombra + cara
        circulo(surf, self.color_ropa, self.x + self._s(3), cab_y + self._s(3), cab_r)
        circulo(surf, PIEL, self.x, cab_y, cab_r)

        # Ojos
        ojo_ox = self._s(8)
        circulo(surf, BLANCO, self.x - ojo_ox, cab_y - self._s(2), self._s(6))
        circulo(surf, BLANCO, self.x + ojo_ox, cab_y - self._s(2), self._s(6))
        circulo(surf, NEGRO,  self.x - ojo_ox + sgn * self._s(2), cab_y - self._s(2), self._s(3))
        circulo(surf, NEGRO,  self.x + ojo_ox + sgn * self._s(2), cab_y - self._s(2), self._s(3))
        circulo(surf, BLANCO, self.x - ojo_ox + sgn * self._s(1), cab_y - self._s(4), self._s(1))
        circulo(surf, BLANCO, self.x + ojo_ox + sgn * self._s(1), cab_y - self._s(4), self._s(1))

        # Nariz y boca
        circulo(surf, PIEL_OSC, self.x, cab_y + self._s(6), self._s(4))
        import math as _m
        arco(surf, NEGRO,
             (int(self.x - self._s(9)), int(cab_y + self._s(8)),
              int(self._s(18)), int(self._s(12))),
             _m.pi + 0.2, 2 * _m.pi - 0.2, 2)

        # Gorra
        gorra_y = cab_y - cab_r - self._s(2)
        rect_r(surf, NEGRO, self.x - cab_r, gorra_y, cab_r * 2, cab_r, int(self._s(5)))
        rect_r(surf, self.color_acc,
               self.x - cab_r, gorra_y + cab_r - self._s(8),
               cab_r * 2, self._s(8), int(self._s(2)))
        rect_r(surf, NEGRO,
               self.x + sgn * self._s(10), gorra_y + cab_r - self._s(10),
               sgn * self._s(20), self._s(10), int(self._s(3)))

    # Override dibujar para llamar correctamente a _dibujar_piernas
    def dibujar(self, surf, t, intensidad=0.5, fuente=None):
        ta  = t + self.offset_t
        sgn = -1 if self.espejo else 1
        amp = intensidad * self._s(18)

        base_y   = self.y + math.sin(ta * 4) * amp * 0.4
        cadera_y = base_y + self._s(28)
        torso_top = base_y - self._s(28)

        pie_ix, pie_iy = self._dibujar_piernas(surf, ta, base_y, intensidad)

        rect_r(surf, self.color_ropa,
               self.x - self._s(20), torso_top,
               self._s(40), self._s(56), int(self._s(8)))
        linea(surf, self.color_acc,
              self.x + sgn * self._s(14), torso_top + self._s(6),
              self.x + sgn * self._s(14), cadera_y  - self._s(4), int(self._s(3)))

        self._dibujar_brazos(surf, ta, base_y, intensidad)
        self._dibujar_cabeza(surf, ta, base_y, sgn)

        if False:  # nombres desactivados
            txt = fuente.render(self.nombre, True, self.color_acc)
            surf.blit(txt, (self.x - txt.get_width() // 2,
                            pie_iy + self._s(12)))
