extends Node2D

@onready var vaca = $Vaca/AnimationPlayer
@onready var audio = $AudioStreamPlayer

var anim_actual = ""

func cambiar_anim(nombre):

	if anim_actual != nombre:
		anim_actual = nombre
		vaca.play(nombre)

func _ready():

	audio.play()

func _process(delta):

	var t = audio.get_playback_position()

	# ------------------------
	# VERSE 1 (0 - 16)
	# ------------------------

	if t < 4:
		cambiar_anim("idle")

	elif t < 8:
		cambiar_anim("sing1")

	elif t < 12:
		cambiar_anim("sing2")

	elif t < 16:
		cambiar_anim("happy")

	# ------------------------
	# CHORUS 1 (16 - 34)
	# ------------------------

	elif t < 20:
		cambiar_anim("happy")

	elif t < 24:
		cambiar_anim("sing2")

	elif t < 28:
		cambiar_anim("happy")

	elif t < 34:
		cambiar_anim("sing2")

	# ------------------------
	# VERSE 2 (34 - 51)
	# ------------------------

	elif t < 38:
		cambiar_anim("idle")

	elif t < 42:
		cambiar_anim("sing1")

	elif t < 46:
		cambiar_anim("sing2")

	elif t < 51:
		cambiar_anim("happy")

	# ------------------------
	# CHORUS 2 (51 - 70)
	# ------------------------

	elif t < 56:
		cambiar_anim("happy")

	elif t < 60:
		cambiar_anim("sing2")

	elif t < 65:
		cambiar_anim("happy")

	elif t < 70:
		cambiar_anim("sing2")

	# ------------------------
	# INSTRUMENTAL (70+)
	# ------------------------

	elif t < 80:
		cambiar_anim("idle")

	elif t < 87:
		cambiar_anim("happy")

	else:
		cambiar_anim("idle")
