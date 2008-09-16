#!/usr/bin/python
# -*- coding: utf-8 -*-

from sys import argv, exit
from math import sqrt
from spectrum import spectrum
from cepstrum import kepstri
from slputils import signal_in

def eukliidinen(x, y):
	summa = 0
	pituus = min(len(x), len(y))
	for i in range(pituus):
		summa += pow(x[i]-y[i], 2)
	return sqrt(summa)


if len(argv) != 5:
	exit("usage: " + argv[0] + " <input_file> <sample_number> <input_file> <sample_number> <outfile>")

infile = argv[1]
sample = long(argv[2])
infile2 = argv[3]
sample2 = long(argv[4])
x_in = signal_in(infile)
x_in2 = signal_in(infile2)
sdata1 = spectrum(x_in, sample)
sdata2 = spectrum(x_in2, sample2)
smdata1 = spectrum(x_in, sample, "mel")
smdata2 = spectrum(x_in2, sample, "mel")
#cdata1 = kepstri(x_in, sample)
#cdata2 = kepstri(x_in2, sample2)
print "%s v. %s" % ( infile, infile2)
print "Mitta	| spektrin etäisyys | mel-spektrin etäisyys"
print "eukliidinen | %f	 | %f" % (
		eukliidinen(sdata1, sdata2), eukliidinen(smdata1, smdata2))
#print "%f" %( eukliidinen(sdata1, sdata2))

exit(0)
