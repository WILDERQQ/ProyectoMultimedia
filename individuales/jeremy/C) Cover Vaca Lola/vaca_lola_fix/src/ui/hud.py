"""
Heads-Up Display del modo show:
  - Barra de intensidad del beat
  - Estado del audio (activo / pausado / sin audio)
  - Barra de controles de teclado
"""

import pygame

from config import VERDE_NEO, AMARILLO, GRIS, BLANCO


class HUD:
    """
    Renderiza los elementos de interfaz superpuestos durante el show.

    Parametros
    ----------
    fuente_normal  : pygame.font.Font para texto de estado
    fuente_pequena : pygame.font.Font para etiquetas y controles
    ventana_w      : ancho de la ventana
    ventana_h      : alto de la ventana
    """

    def __init__(self, fuente_normal, fuente_pequena, ventana_w, ventana_h):
        self.fn  = fuente_normal
        self.fp  = fuente_pequena
        self.W   = ventana_w
        self.H   = ventana_h

    def dibujar(self, surf, intensidad: float, audio):
        """
        Renderiza el HUD completo.

        Parametros
        ----------
        surf       : superficie de destino
        intensidad : 0.0 - 1.0, valor actual del beat
        audio      : instancia de GestorAudio
        """
        self._barra_beat(surf, intensidad)
        self._estado_audio(surf, audio)
        self._controles(surf)

    # ------------------------------------------------------------------

    def _barra_beat(self, surf, intensidad):
        bar_w = int(intensidad * 200)
        pygame.draw.rect(surf, (30, 30, 40), (20, 55, 200, 12), border_radius=4)
        if bar_w > 0:
            pygame.draw.rect(surf, VERDE_NEO, (20, 55, bar_w, 12), border_radius=4)
        lbl = self.fp.render("BEAT", True, GRIS)
        surf.blit(lbl, (228, 54))

    def _estado_audio(self, surf, audio):
        if audio.cargado:
            nombre  = audio.nombre_archivo()
            activo  = audio.esta_activo()
            prefijo = "[>] " if activo else "[||] "
            color   = VERDE_NEO if activo else AMARILLO
        else:
            nombre  = "Animacion sin audio"
            prefijo = "[~] "
            color   = GRIS
        txt = self.fp.render(prefijo + nombre, True, color)
        surf.blit(txt, (20, 72))

    def _controles(self, surf):
        ctrl = self.fp.render(
            "ESPACIO: pausa/reanudar  |  ESC: salir",
            True, GRIS)
        surf.blit(ctrl, (self.W - ctrl.get_width() - 14, self.H - 20))
