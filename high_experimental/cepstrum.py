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

# A attempt to implement using libsnack the cepstum fundamental frequency extractor

from itertools import izip
from Tkinter import *
from tkSnack import *
from math import *
from scipy import fft
from pylab import *
import pylab
import numpy

root = Tk()
initializeSnack(root)

# Instancing...
mysound = Sound()

# Read Sound
#mysound.read('test_praat.wav')
mysound.read('Fn-ST-1.wav')

# New code attempt

cepstrum = dict()
cepstrum['fftlength'] = 256
cepstrum['windowtype'] = 'hamming'
cepstrum['winlength'] = 128
cepstrum['preemphasisfactor'] = 0.97

cepstrum['powerSpectrum'] = mysound.powerSpectrum(fftlength=cepstrum['fftlength'], windowlength=cepstrum['winlength'], windowtype=cepstrum['windowtype'], windowtype=cepstrum['windowtype'], preemphasisfactor=cepstrum['preemphasisfactor'], analysistype='FFT')

# What? http://www.phon.ucl.ac.uk/courses/spsci/matlab/lect10.html
# Above dont work
#cepstrum['cepstrum'] = fft(log(abs(cepstrum['fft'])))

info = mysound.info()
info_fields=('Length', 'Rate', 'Maxmimum Sample', 'Minimum Sample', 
	'Sample encoding', 'Channels', 'Format', 'Header size')


print 'Sound Info: '
for field, value in izip(info_fields, info):
	print '\t%s: %s' % (field, value)
print '\tFilename:', mysound.tk.call(mysound, 'cget', '-load')

mysound.destroy()
