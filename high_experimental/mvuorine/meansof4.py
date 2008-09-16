
#!/usr/bin/python
# -*- coding: utf-8 -*-

from array import array
from sys import argv, exit
from slputils import signal_in, signal_out

# Coefficients for running means of 4
b = [0.25, 0.25, 0.25, 0.25]

if len(argv) != 3:
	exit("usage: " + argv[0] + " <input_file> <output_file>")

infile = argv[1]
outfile = argv[2]

x = signal_in(infile)

# NB! In the C code, the iteration is started from the fifth element, ie.
# element indexed by '4'. It seems to me to be a bug in the program listing, so
# I start from the fourth element...
for i in range(3, len(x)):
	y.append(int(b[0]*x[i] + b[1]*x[i-1] + b[2]*x[i-2] + b[3]*x[i-3]))

signal_out(y,outfile)
exit(0)
