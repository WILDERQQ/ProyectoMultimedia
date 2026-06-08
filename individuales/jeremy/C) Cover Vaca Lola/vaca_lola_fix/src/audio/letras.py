"""
Letras de la cancion con sus marcas de tiempo en segundos.
Formato: lista de tuplas (tiempo_segundos, texto).
Para ajustar la sincronizacion basta con editar los tiempos
sin tocar ningun otro archivo del proyecto.
"""

# (tiempo en segundos, texto a mostrar)
LETRAS: list[tuple[float, str]] = [
    ( 0.0,  "Tengo una vaca lechera..."),
    ( 3.5,  "No es una vaca cualquiera..."),
    ( 7.0,  "Me da leche condensada..."),
    (10.5,  "Ay que vaca tan salada..."),
    (14.0,  "Chin chin chin chin..."),
    (17.5,  "La Vaca Lola!"),
    (21.0,  "Chin chin chin chin..."),
    (24.5,  "La Vaca Lola!"),
    (28.0,  "Tengo una vaca lechera..."),
    (31.5,  "No es una vaca cualquiera..."),
    (35.0,  "Me da leche condensada..."),
    (38.5,  "Ay que vaca tan salada..."),
]

# Tolerancia en segundos para considerar que una letra esta activa
TOLERANCIA_SYNC = 0.15

# Intervalo en segundos para rotar letras en modo sin audio
INTERVALO_SIN_AUDIO = 3.5
