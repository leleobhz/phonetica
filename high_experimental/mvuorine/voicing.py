# Soinnillisuuden havaitseminen (Voising detection)
#
# Soinnillisuuden havaitseminen antaa meille keinon suodattaa f0 arviosta
# ne osat jotka ovat soinnittomia. Tällöin voisimme F0:a arvioidessamme
# joko jättää nämä osat analysoimatta tai jättää niiden analyysi huomioitta.
# Soinnillisuuden havaitsemisen idea on siinä, että soinnillisessa puheessa
# on paljon energiaa matalilla taajuuksilla (noin < 400 Hz) ja soinnittomassa
# puheessa energiaa tällä alueella ei juuri ole.
# (Se mitä taajuuksia puheesta löytyy on puhuja kohtaista.)
# Tällöin voimme löytää soinnilliset alueet esimerkiksi suodattamalla pois
# korkeammat taajuudet ja ottamalla huomioon f0 arviot vain niiltä
# alueilta joilla näitä matalia taajuuksia löytyy.
# Menetelmän ongelma on selkeästi se, että nauhoituksen laadulla on 
# suuri merkitys sen toimivuuteen. Jos signaali sisältää matalataajuista
# melua joka on tullut nauhoituksen yhteydessä, ei sen antamia tuloksia
# voida pitää luotettavina.

#!/usr/bin/python
# -*- coding: utf-8 -*-

# Original C program Copyright by John Coleman in "Introducing Speech and Language Processing",
# Cambridge University Press, 2005
# Listing 4.4 on page 87

# voicing.py - Low-pass filters infile using a 400 Hz low-pass filter
# Calculates running rms amplitude over a 100-sample window.
# Outputs voiced/unvoiced decision per sample to outfile.

from array import array
from sys import argv, exit
from slputils import signal_in, signal_out
from math import sqrt

# Coefficients for a low-pass filter 
# (<400 Hz at 16000 samples/s)
a = [0, -4.4918, 8.0941, -7.3121, 3.311, -0.6011]
b = [2.34E-6, 1.17E-5, 2.34E-5, 2.34E-5, 1.17E-5, 2.34E-6]

if len(argv) != 3:
	exit("usage: " + argv[0] + " <input_file> <output_file>")

infile = argv[1]
outfile = argv[2]

x = signal_in(infile)
y = array('h')
yf = array('f', [0.0, 0.0, 0.0, 0.0, 0.0, 0.0])
yfsqr = array('f', [0.0, 0.0, 0.0, 0.0, 0.0, 0.0])
sumsq = array('f', [0.0, 0.0, 0.0, 0.0, 0.0, 0.0])

for i in range(6, len(x)):
	yf.append((b[0]*x[i] + b[1]*x[i-1] + b[2]*x[i-2] + b[3]*x[i-3] +
		   b[4]*x[i-4] + b[5]*x[i-5] - a[1]*yf[i-1] - a[2]*yf[i-2] -
		   a[3]*yf[i-3] - a[4]*yf[i-4] - a[5]*yf[i-5])
                  )
	yfsqr.append(yf[i]*yf[i])


for i in range(1,158):
	sumsq.append(sumsq[i-1]+yfsqr[i])

for i in range(159,len(x)):
	sumsq.append((sumsq[i-1] - yfsqr[i-160]) + yfsqr[i])

for i in range(0,len(x)):
	if(sqrt(sumsq[i]/160) > 600):
		y.append(1)
	else:
		y.append(0)

signal_out(y, outfile)

exit(0)
