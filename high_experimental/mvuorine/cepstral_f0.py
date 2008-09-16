# -*- coding: utf-8 -*-
# Perustaajuuden löytäminen Kepstrianalyysin avulla (Pitch tracking) 
#
# Koska Kepstrianalyysi antaa meille tavan poimia kukkuloita signaalista,
# on sen avulla myös mahdollista etsiä perustaajuutta f0.
# Tämä tehdään jakamalla signaali osiin ja laskemalla kullekin
# osalle signaalia f0. Nämä lasketut arviot muodostavat arvion f0:sta
# kullekin ajanhetkelle (tai numeroidulle näytteelle). Menetelmän
# ongelma on se, että f0 voidaan löytää vain soinnillisista osista signaalia.
# Kuvattu menetelmä ei kuitenkaan anna mitään tapaa erotella soinnillisia 
# osia soinnittomista, ja menetelmällä löydetään jonkinlainen arvio 
# f0:lle myös soinittomista osioista. Tämä aiheuttaa suuria heilahteluja 
# f0 arvioissa ajanhetkestä toiseen.

#!/usr/bin/python
# -*- coding: utf-8 -*-

# Original C program Copyright by John Coleman in "Introducing Speech and 
# Language Processing",
# Cambridge University Press, 2005
# Listing 4.3 on page 83

# cepstral_f0.py -Pitch (f0) tracking using cepstral analysis
# Reads a signal from a disk file into a variable, x_in. f0 is written as a
# *text* stream to the standard output.

from sys import argv, exit
from math import pi, cos, log10
from slputils import signal_in
from four1 import four1

# Given a frame number and an array 'x_in' containing a signal, prints out the
# pitch
def print_cepstral_f0(frame, x_in):
	data = []
	logpsd = []

	# Windowing using 512-point Hanning window and coercion to floats
	arg = 2 * pi / 511.0	# This is as in the original, but should this be? This
				# results in a division of 511 parts of one cycle, and
				# wvalue[0] ends up the same as wvalue[511]...
	for i in range(0, 512):
		wvalue = 0.5 - 0.5*cos(arg*i)
		data.append(float(x_in[frame+i-256] * wvalue))
		data.append(0.0)

	data.insert(0,0.0)
	four1(data,512,1)
	data[0:1]=[]

	for i in range(0, 512):
		l = data[2*i]
		l *= l
		l += data[2*i+1]**2
		l = 10*log10(l)
		logpsd.append(l)
		logpsd.append(0.0)
	
	logpsd.insert(0,0.0)
	four1(logpsd, 512, -1)
	logpsd[0:1]=[]

	max = 0.0
	max_f0 = 0.0
	for i in range(88, 256):
		# Work down from an upper limit of 180Hz (88 = 16000/180)
		if logpsd[2*i] > max:
			max = logpsd[2*i]
			max_f0 = 16000/i

	print "%d\t%.1f" % (frame, max_f0)

	return


if len(argv) != 2:
	exit("usage: " + argv[0] + " <input_file>")

infile = argv[1]
x_in = signal_in(infile)

print "Sample\tf_0 (Hz)"
for i in range(79, 319, 80): print "%d\t0" % i
for i in range(319, len(x_in)-256, 80):
	print_cepstral_f0(i, x_in)

exit(0)
