#!/usr/bin/python
# -*- coding: utf-8 -*-


# Kepstri analyysi (Cepstral analysis)
# 
# Kun olemme saaneet signaalille voimaspektrin FFT:llä
#(ääntöväylän resonanssitaajuudet), pystyisimme arvioimaan siitä
# formanttien taajuudet, jos meillä olisi joku keino poimia kukkuloita
# spektristä.
# Kepstri analyysin tarkoitus on saada esille nuo formanttitaajuudet.
# Se on tietyssä mielessä kaksinkertainen FFT.
# Kun olemme soveltaneet FFT:tä signaaliin, olemme saaneet signaalin
# voima tai äänekkyys spektrin. Kun suoritamme FFT toiseen kertaan, tällä
# olemassa olevalle voima spektrille, pystymme mittaamaan minkä kokoisia
# pienet ja isot kukkulat spektrissä ovat. 
# Isot kukkulat kuvaavat harmonisia säveliä ja pienet kukkulat resonansseja.
# Koska signaalien alkuolettamukset
# ovat hieman erillaiset alkuperäisen ja kertaalleen FFT läpi kulkeneen
# signaalin välillä, käytetään toisella kerralla FFT sijasta operaatiota, 
# jonka nimi on käänteinen FFT (inverse fast fourier transformation). 


# Original C program Copyright by John Coleman in "Introducing Speech and Language Processing",
# Cambridge University Press, 2005
# Listing 4.2 on page 81

# cepstrum.py - Spectral analysis
# Reads a signal from a disk file into a variable, x_in, and a	
# frame number n. Windows the signal using a Hanning window,	
# calculates the FFT, the log power spectral density, and then	
# the inverse Fourier transform. The lower half is written as a 
# text stream to the standard output.				

from sys import argv, exit
from math import pi, cos, log10
from slputils import signal_in
from array import array
from four1 import four1

def kepstri(x_in, frame):
	data = []

#if len(argv) != 3:
#	exit("usage: " + argv[0] + " <input_file> <frame_number>")

#infile = argv[1]
#frame = long(argv[2])
#x_in = signal_in(infile)

	data = hanning(x_in)

	data = four1(data, 256, 1)
	data[0:1] = []
	# So far, this is all the same as spectrum.py	

	logpsd=[]

	for i in range(0,256):
		l = data[i*2]
		l *= l
		l += data[i*2+1]**2
		l = 10*log10(l)
		logpsd.append(l)

	logpsd = four1(logpsd, 256, -1)

	print "Quefrency (ms)\tf (Hz)\tAmplitude (dB)"

	for i in range(1,256):
        	print "%f\t%.2f\t%f" % (i*0.0625, 16000.0/i, logpsd[i].real)
	return logpsd

#exit(0)


