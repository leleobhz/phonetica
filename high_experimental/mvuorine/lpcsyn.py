# Lineaarinen ennustaminen (Linear predictive coding LPC)
# ja uudelleen syntetisointi
#
# Ajatus lineaarisen ennustamisen takana tulee televisiosta ja
# sen kautta se on helpoin tajuta. Jos ajattelemme televisio ruutua 
# mustavalkoisena kuvana, joka sisältää vaaleampia ja tummempia
# pisteitä. Kaikkien näiden pisteiden värin talentaminen vie paljon
# tilaa ja tilanteen palauttaminen vie paljon aikaa. Koska pisteet
# eivät ole toisistaan riippumattomia, voidaan sanoa että pisteen ja  
# sen naapuri pisteen kirkkauden välillä vallitsee vahva korrelaatio.
# Samoin puhesignaalin näytteen magnitudi korreloi vahvasti naapurinäytteen
# magnitudin kanssa. Useimmiten näytteen magnitudi on pääteltävissä
# muutaman sitä edeltävän näytteen magnitudista. Jos kuitenkin päättelemme 
# magnitudin edeltävien näytteiden, edeltävistä näytteistä 
# arvioiduista magnitudeista, tulee väkisinkin arviointi virheitä.
# Arvioiminen arvioista ei yleensä tuota päteviä arvoja, josta 
# pääsemmekin itse menetelmäämme.
# Jos otamme talteen ennustetun arvon ja todellisen arvon erotuksen, 
# voimme näiden tietojen avulla generoida alkuperäisen signaalin.
# Hyöty tässä tulee siitä, että erotus on huomattavasti pienempi
# luku, kuin signaalin magnitudi tuolla ajanhetkellä olisi.  

#!/usr/bin/python
# -*- coding: utf-8 -*-

# Original C program Copyright by John Coleman in "Introducing Speech and 
# Language Processing",
# Cambridge University Press, 2005
# Listing 4.7 on page 105

# lpcsyn.py -Synthesizes a signal based on LPC coefficients read from a file
# (named by coeffs) and residual from errfile

from sys import argv, exit
from slputils import signal_in, signal_out
from array import array
from correl import correl_offset

K = 14	# Number of coefficients

if len(argv) != 4:
	exit("usage: " + argv[0] + " <lp_errors_file> <lp_coeffs_file> <output_file>")

errfile = argv[1]
coeffs = argv[2]
outfile = argv[3]

try:
	fid = open(coeffs,'rb')
except IOError:
	exit("Unable to open coefficient file " + coeffs)
craw = array('f')
craw.fromstring(fid.read())

n_frames = int(len(craw)/K)	# As many frames as complete sets of
				# coefficients read
c = []

# For the first frame (samples 0..79), use the first read-in vector for every
# sample
for i in range(0, 80):
	c.append(craw[:K].tolist())


# For frames 1..n_frames, interpolate the intermediate LPC vectors
for frame in range(1, n_frames):
	prev = c[-1]
	next = craw[frame*K:(frame+1)*K].tolist()
	for i in range(1, 81):
		c.append([p + i/80.0*(n - p) for p, n in zip(prev, next)])
craw=[]

# Read in the error signal
e_in = signal_in(errfile)

# Calculate the predicted signal based on the coefficients
lp = array('f',K * [0])
x_out = array('h', K * [0])
for i in range(K, len(c)):
	lp.append(0)
	for n in range(0, K):
		lp[-1] = lp[-1] - c[i][n]*lp[i-(n+1)]
	lp[-1] = -lp[-1]
	lp[-1] += e_in[i]
	x_out.append(int(lp[-1]))

signal_out(x_out, outfile)
exit(0)
