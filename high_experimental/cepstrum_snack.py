#!/usr/bin/env python
# -*- coding: utf-8 -*-

# This program is free software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License
# as published by the Free Software Foundation; either version 2
# of the License, or (at your option) any later version.

# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.

# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.

# A attempt to implement using libsnack the cepstrum fundamental frequency extractor

from itertools import izip
from numpy import array, log, zeros, float
from pylab import fft, plot, xlabel, ylabel, show, hamming
from struct import unpack
from sys import stdout, argv
from Tkinter import *
from tkSnack import *

def progress_bar(value, max, barsize):
   chars = int(value * barsize / float(max))
   percent = int((value / float(max)) * 100)
   sys.stdout.write("#" * chars)
   sys.stdout.write(" " * (barsize - chars + 2))
   if value >= max:
      sys.stdout.write("done. \n\n")
   else:
      sys.stdout.write("[%3i%%]\r" % (percent))
      sys.stdout.flush()

root = Tk()
initializeSnack(root)

# Instancing...
mysound = Sound()

# Read Sound
#mysound.read('test_praat.wav')
mysound.read(argv[1])

# New code attempt

sample_rate = mysound.info()[1]
total_num_samps = mysound.info()[0]
fft_length = int(512)
num_fft = (total_num_samps / (fft_length*mysound.info()[5]) ) - 2
#num_fft = (total_num_samps / fft_length) - 2

# create temporary working array
temp = zeros((num_fft,fft_length*mysound.info()[5]),unicode)
pos = 0

# read in the data from the file
for i in range(num_fft):
	pos = pos + fft_length
	tempb = mysound.data(start=pos, end=(pos+fft_length*mysound.info()[5]),fileformat='RAW');
	print type(tempb), len(tempb)
#	temp[i,:] = array(Float, struct.unpack("%dB"%(fft_length*2),tempb))
	'''
	Ok ok, Tcl 1 x 0 leleobhz
	'''
#	temp.insert(array(unpack("%dB"%(fft_length*2),tempb), float))
	temp[i,:] = array(tempb, unicode)
#	temp.insert(i, array(unpack("%dB"%(len(tempb.encode('utf-8'))),tempb.encode('utf-8')), float))

# Window the data
temp = temp * hamming(fft_length*2)

# Transform with the FFT, Return Power
cepstrum  = fft(log10(1e-20+abs(temp)))
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


# EOC
info = mysound.info()
info_fields=('Length', 'Rate', 'Maxmimum Sample', 'Minimum Sample', 
	'Sample encoding', 'Channels', 'Format', 'Header size')


print 'Sound Info: '
for field, value in izip(info_fields, info):
	print '\t%s: %s' % (field, value)
print '\tFilename:', mysound.tk.call(mysound, 'cget', '-load')

mysound.destroy()
