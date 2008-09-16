##!/usr/bin/python
# -*- coding: utf-8 -*-

# Spektrin analyysi ja
# FFT
#
# Yksinkertainen tapa saada tietoa ääntöväylän resonansseista on spektrografia,
# mutta näiden resonanssien eristämien signaalista onkin hieman vaikeampaa.
# Signaali, oli se sitten kuinka hankala tahansa, voidaan aina muodostaa
# summana yksinkertaisempia signaaleja, kuten cosini-aaltoja,
# joilla on oikea taajuus, kaistanleveys, amplitudi ja vaihe. Niiden on myös 
# oltava oikeassa suhteessa toisiinsa nähden. 
# Jotta nämä resonanssit voitaisiin 
# tunnistaa, olisi osattava hajoittaa signaali kasaksi yksinkertaisempia
# signaaleja (aaltoja).
# Yksi tapa eristää nämä yksinkertaiset aallot tai taajuus komponentit 
# on FFT eli fast fourier transformation, jonka avulla signaalin spektri
# voidaan muodostaa. Signaalin spektristä voimme nähdä millä taajuksilla
# signaalilla on energiaa ja kuinka paljon.

# Original C program Copyright by John Coleman in "Introducing Speech and 
# Language Processing",
# Cambridge University Press, 2005
# Listing 4.1 on page 73

# spectrum.py -Spectral analysis using a 512-point FFT

from sys import argv, exit
from math import pi, cos, log10, log, fabs
from slputils import signal_in
from four1 import four1
from windowing import hanning

def spektri(x_in, sample, scale="hertz"): 
	data = []
	mel = []
	data = hanning(x_in)
	data.insert(0,0.0)
	four1(data,512,1)
	data[0:1]=[]

	# In the log power spectral density, magnitudes are in dB in steps of
	# SampleRate/512 Hz (31.25 Hz at 16000 samples/s).

	for i in range(0,255):
		l = data[2*i]
		l *= l
		l += data[2*i+1]**2
		l = 10*log10(l)
	if scale == "hertz":
		return l
	elif scale == "mel":
		#
	else:
		print "väärä scale: %s" % (scale)
		return l

#if len(argv) != 3:
#	exit("usage: " + argv[0] + " <input_file> <sample_number>")

#infile = argv[1]
#sample = long(argv[2])
#x_in = signal_in(infile)
#spectrum(x_in, sample)


