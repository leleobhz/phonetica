#!/usr/bin/env python
# Code based in http://www.onlamp.com/python/2001/01/31/graphics/pysono.py

from Numeric import *
from array import *
import numpy
import pylab
import wave
import sys
import struct

# open the wave file
fp = wave.open(sys.argv[1],"rb")

sample_rate = fp.getframerate()
total_num_samps = fp.getnframes()
fft_length = int(sys.argv[2])
num_fft = (total_num_samps / (fft_length*fp._nchannels) ) - 2

# create temporary working array
temp = zeros((num_fft,fft_length*2),Float)

# read in the data from the file
for i in range(num_fft):
    tempb = fp.readframes(fft_length);
    temp[i,:] = array(Float, struct.unpack("%dB"%(fft_length*2),tempb))
fp.close()

# Window the data
temp = temp * numpy.hamming(fft_length*2)

# Transform with the FFT, Return Power
cepstrum  = numpy.fft.fft(log10(1e-20+abs(temp)))
n_out_pts = (fft_length / 2) + 1

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


max_cepstrum = numpy.max([abs(x) for x in cepstrum[ms1:ms20]])
print 'Fx=%.0fHz\n' % (sample_rate / (ms1 + int(max_cepstrum) - 1))
