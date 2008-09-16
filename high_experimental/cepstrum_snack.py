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
from Tkinter import *
from tkSnack import *
from scipy.fftpack import fft, ifft
from math import *
import pylab
import sys

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

import time

root = Tk()
initializeSnack(root)

# Instancing...
mysound = Sound()

# Read Sound
#mysound.read('test_praat.wav')
mysound.read('Fn-ST-1.wav')

# New code attempt

cepstrum = dict()
cepstrum['length'] = 256
cepstrum['windowtype'] = 'hamming'
cepstrum['preemphasisfactor'] = 0.97
cepstrum['powerSpectrum'] = []
cepstrum['result'] = []
frames = []
pos = 0

for frame in xrange(0, mysound.length()):
	if ((mysound.length() - pos) > cepstrum['length']):

		cepstrum['powerSpectrum'].append(mysound.dBPowerSpectrum(start=pos, fftlength=cepstrum['length'], windowlength=cepstrum['length'], windowtype=cepstrum['windowtype'], windowtype=cepstrum['windowtype'], preemphasisfactor=cepstrum['preemphasisfactor'], analysistype='FFT'))

'''
		cepstrum['result'].append(
			ifft(map(lambda x: log(x), abs(fft(
			mysound.dBPowerSpectrum(start=pos, fftlength=cepstrum['length'], 
			windowlength=cepstrum['length'], windowtype=cepstrum['windowtype'], 
			windowtype=cepstrum['windowtype'], 
			preemphasisfactor=cepstrum['preemphasisfactor'], analysistype='FFT')
		)))))
'''
		frames.append(pos)
		pos = pos + cepstrum['length']
#		print 'Frame %d done' % pos
	else:
		break


pylab.plot(cepstrum['result'])
pylab.show()

info = mysound.info()
info_fields=('Length', 'Rate', 'Maxmimum Sample', 'Minimum Sample', 
	'Sample encoding', 'Channels', 'Format', 'Header size')


print 'Sound Info: '
for field, value in izip(info_fields, info):
	print '\t%s: %s' % (field, value)
print '\tFilename:', mysound.tk.call(mysound, 'cget', '-load')

mysound.destroy()
