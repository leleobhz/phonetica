#!/usr/bin/env python
# Code based in http://www.onlamp.com/python/2001/01/31/graphics/pysono.py

from Numeric import *
from array import *
from itertools import izip
import numpy
import pylab
import wave
import sys
import struct

# open the wave file
fp = wave.open(sys.argv[1],"rb")

sample_rate = fp.getframerate()
sample_width = fp.getsampwidth()
total_num_samps = fp.getnframes()
#fft_length = int(sys.argv[2])
fft_length = 512
num_fft = (total_num_samps / fft_length ) - 2

# create temporary working array
temp = zeros((num_fft,fft_length*sample_width),Float)

# read in the data from the file
for i in range(num_fft):
	tempb = fp.readframes(fft_length);
	temp[i,:] = array(Float, struct.unpack("%dB"%(fft_length*sample_width),tempb))
fp.close()

cepstrum = []

for i in temp:
	# Window the data
	i = i * numpy.hamming(fft_length*sample_width)
	cepstrum.append(numpy.fft.fft(log10(1e-20+abs(i))))

# Limits
ms1=sample_rate/1000;                 # maximum speech Fx at 1000Hz
ms20=sample_rate/50;                  # minimum speech Fx at 50Hz

q = [i/sample_rate for i in range(ms1, ms20)]

# Plot the result
#pylab.plot(q, [abs(x) for x in cepstrum[ms1:ms20]])
#pylab.legend('Cepstrum')
#pylab.xlabel('Quefrency (s)')
#pylab.ylabel('Amplitude')
#pylab.show()

for i, j in izip (cepstrum, range(0, num_fft*fft_length, fft_length)):
	max_cepstrum = numpy.max([abs(x) for x in i[ms1:ms20]])
	print 'Frame %d Fx=%.0fHz' % (j, sample_rate / (ms1 + int(max_cepstrum) - 1))

#max_cepstrum = numpy.max([abs(x) for x in cepstrum[ms1:ms20]])
#print 'Fx=%.0fHz\n' % (sample_rate / (ms1 + int(max_cepstrum) - 1))
