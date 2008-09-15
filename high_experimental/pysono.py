#!/usr/bin/env python
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

# Plot the result
#y_axis = 0.5*float(sample_rate) / n_out_pts * arange(n_out_pts)
#x_axis = (total_num_samps / float(sample_rate)) / num_fft * arange(num_fft)

#pylab.plot(cepstrum)
#pylab.legend('Cepstrum')
#pylab.xlabel('Quefrency (s)')
#pylab.ylabel('Amplitude')
#pylab.show()

#setvar('X',"Time (sec)")
#setvar('Y',"Frequency (Hertz)")
#conshade(freq_pwr,x_axis,y_axis)
#disfin()
