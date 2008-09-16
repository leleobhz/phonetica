#!/usr/bin/python
# -*- coding: utf-8 -*-

# Original C program Copyright by John Coleman in "Introducing Speech and 
# Language Processing",
# Cambridge University Press, 2005
# Listing 2.1 on page 34

# coswave.py -Generates a cosine wave into file cosine.raw: 8000 samples, 1 s,
# 200 Hz (with 8000 samples/s).

from math import cos, pi
from array import array
from sys import exit

length = 8000
freq = 0.025
arg = 2 * pi * freq	# Radian angle between samples
fn = 'cosine.raw'

x = array('h')		# signed short int array
			# (2 bytes/item, complement of 2)

# Creates #length samples of a cosine wave into the array
for i in range(length):
	x.append(int(32000 * cos(i * arg)))

try:
	file_id = open(fn,'wb')
except IOError:
	exit("Unable to open file " + fn)

x.tofile(file_id)

file_id.close()
exit(0)
