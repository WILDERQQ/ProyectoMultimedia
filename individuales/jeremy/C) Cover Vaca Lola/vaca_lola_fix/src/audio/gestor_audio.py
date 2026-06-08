"""
Encapsula la carga, reproduccion y control de audio via pygame.mixer.
Expone una interfaz simple para el resto de la aplicacion.
"""

import os
import pygame


class GestorAudio:
    """
    Gestor de audio basado en pygame.mixer.

    Uso tipico
    ----------
    gestor = GestorAudio()
    if gestor.cargar("ruta/cancion.mp3"):
        gestor.reproducir()
    gestor.pausar_reanudar()
    gestor.detener()
    """

    def __init__(self):
        self.cargado       = False
        self.reproduciendo = False
        self.ruta          = ""

    # ------------------------------------------------------------------
    # Control
    # ------------------------------------------------------------------

    def cargar(self, ruta: str) -> bool:
        """
        Carga un archivo de audio.
        Retorna True si fue exitoso, False en caso de error.
        """
        try:
            pygame.mixer.init(frequency=44100, size=-16, channels=2, buffer=512)
            pygame.mixer.music.load(ruta)
            self.ruta    = ruta
            self.cargado = True
            return True
        except Exception as e:
            print(f"[GestorAudio] Error al cargar '{ruta}': {e}")
            self.cargado = False
            return False

    def reproducir(self):
        """Inicia la reproduccion desde el principio."""
        if not self.cargado:
            return
        pygame.mixer.music.play()
        self.reproduciendo = True

    def pausar_reanudar(self):
        """Alterna entre pausa y reproduccion."""
        if not self.cargado:
            return
        if self.reproduciendo:
            pygame.mixer.music.pause()
        else:
            pygame.mixer.music.unpause()
        self.reproduciendo = not self.reproduciendo

    def detener(self):
        """Detiene la reproduccion completamente."""
        if self.cargado:
            pygame.mixer.music.stop()
        self.reproduciendo = False

    # ------------------------------------------------------------------
    # Consultas de estado
    # ------------------------------------------------------------------

    def esta_activo(self) -> bool:
        """True si el mixer esta reproduciendo actualmente."""
        return self.cargado and pygame.mixer.music.get_busy()

    def tiempo_reproduccion(self) -> float:
        """Segundos transcurridos de la cancion (correcto incluso tras pausar/reanudar)."""
        if not self.cargado:
            return 0.0
        pos_ms = pygame.mixer.music.get_pos()
        if pos_ms < 0:
            # get_pos() retorna -1 si no esta reproduciendo
            return 0.0
        return pos_ms / 1000.0

    def nombre_archivo(self) -> str:
        """Nombre corto del archivo cargado (max 32 caracteres)."""
        return os.path.basename(self.ruta)[:32] if self.ruta else ""
