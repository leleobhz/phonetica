# Aplitudin keskiarvon mittaminen ja
# RMS (Root mean square amplitude)
#
# Jotta signaalia olisi helppo käsitellä, tulee se digitoida, eli muuttaa
# jaksoksi numeroita (näytteitä), joille voimme tehdä erillaisia 
# laskutoimituksia.
# Jos laskemme kaikkien näytteiden summan, 
# kertoo tämä mitta signaalin yleisen energiamäärän tai äänekkyyden.
# Jos laskemme signaalin keskiarvon 
# eli kaikkien näytteiden summa / näytteiden lukumäärällä,
# kertoo tämä meille näytteiden keskimääräisen äänekkyyden. 
# Ongelma on kuitenkin siinä, että osa arvoista voi olla negatiivia. Tämä
# vuoksi näytteiden arvot kumoavat toisiaan ja 
# lopputulos ei kuvaa todellisuutta.
# Jotta negatiivisia arvoja voitaisiin käsitellä, korotetaan summattavat
# näytteet toiseen potenssiin jolloin negatiiviset arvot muuttuvat 
# positiivisiksi. Jotta potenssiin korottamisen vaikutus saataisiin poistettu,
# kaikki arvothan ovat nyt toisessa potenssissa, otetaan lopuksi saadusta 
# luvusta (summa/näytteiden määrä) neliöjuuri. Tätä keskiarvon mittaamiseksi
# käytettyä menetelmää kutsutaan RMS:ksi.

#!/usr/bin/python
# -*- coding: utf-8 -*-

# Original C program Copyright by John Coleman in "Introducing Speech and Language Processing",
# Cambridge University Press, 2005
# Listing 3.1 on page 49

# rms.py -Reads a signal from a disk file into an array x.
# Calculates and prints out the root mean square amplitude of the signal.

from sys import argv, exit
from slputils import signal_in
from math import sqrt

if len(argv) != 2:
	exit("usage: " + argv[0] + " <input_file>")

rms, sum = 0.0, 0.0
infile = argv[1]
x = signal_in(infile)
n = len(x)

for i in range(n):
	sum += x[i]*x[i]

rms = sqrt(sum/n)

print("The RMS amplitude of %s is %.2f" % (infile, rms)) 

exit(0)
