"""
src/utils/anim.py
=================
Funciones matematicas para animacion ritmica.
Todas retornan valores normalizados en el rango [0, 1].
"""

import math


def pulso(t, bpm=120, fase=0.0):
    """
    Oscilacion sinusoidal suave sincronizada al BPM.
    Retorna 0.0 - 1.0.
    """
    freq = bpm / 60.0
    return (math.sin(t * freq * 2 * math.pi + fase) + 1) / 2


def rebote(t, bpm=120, fase=0.0):
    """
    Oscilacion tipo rebote (valor absoluto de seno).
    Retorna 0.0 - 1.0, siempre positivo.
    """
    freq = bpm / 60.0
    return abs(math.sin(t * freq * math.pi + fase))
