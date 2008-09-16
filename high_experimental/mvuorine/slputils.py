#!/usr/bin/python
# -*- coding: utf-8 -*-
# slputils.py - Utility functions for signal processing examples

from array import array
from sys import exit

# Returns a short int array of data from the file
# named "infilename"
def signal_in(infilename):
	try:
		file_id = open(infilename,'rb')
	except IOError:
		exit("Unable to open file " + infilename)
	x = array('h')
	x.fromstring(file_id.read())

	return x

def signal_out(x,outfilename):
	try:
		file_id = open(outfilename,'wb')
	except IOError:
		exit("Unable to open file " + outfilename)

	x.tofile(file_id)

	file_id.close()
