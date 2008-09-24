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

# A Sample code for Python Snack

from itertools import izip
from Tkinter import *
from sys import argv

root = Tk()
import tkSnack
tkSnack.initializeSnack(root)

# Instancing...
mysound = tkSnack.Sound()

# Read Sound
#mysound.read('test_praat.wav')
mysound.read(argv[1])

# Extract Formants
'''
Python Snack have the function formant, but dont accept any options, 
so we use a tk.call direct, giving access to all powerfull tk implementation.
Above is all default values, and have too -windowlength, -start, -end and 
-lpctype 0 (autocorrelation) or 1 (stabilized covariance)
'''

formant = dict()
formant['windowlenght'] = 0.049
formant['framelenght'] = 0.01
formant['fields'] = ('F1', 'F2', 'F3', 'F4', 'BW1', 'BW2', 'BW3', 'BW4')
formant['time'] = formant['windowlenght'] / 2

formant['processed'] = mysound.tk.call(mysound, 'formant', '-numformants', '4', 
	'-framelength', formant['framelenght'], '-windowtype', 'Cos^4', 
	'-windowlength', formant['windowlenght'], '-preemphasisfactor', '0.7', 
	'-lpcorder', '12', '-ds_freq', '10000', '-nom_f1_freq', '500')

# Extract F0.
f0 = dict()
f0['method'] = 'esps'
f0['windowlenght'] = 0.049
f0['framelenght'] = 0.01
f0['minpitch'] = 70
f0['maxpitch'] = 1000

f0['processed'] = mysound.tk.call(mysound, 'pitch', '-method', f0['method'] , 
	'-framelength', f0['framelenght'], '-windowlength', f0['windowlenght'],
	'-minpitch', f0['minpitch'], '-maxpitch', f0['maxpitch'])
f0['fields'] = ('Pitch', 'Prob. Voicing', 
	'Local root mean squared mensurements', 
	'Peak Normalized cross-correlation')

if f0['method'] == 'esps':
	f0['time'] = f0['windowlenght'] / 2

else:
	f0['time'] = 0

# Time to print!!!!
for formant_local, f0_local in izip (formant['processed'], f0['processed']):
	pitch = dict(izip(f0['fields'], f0_local))
	formants = dict(izip(formant['fields'], formant_local))
	if pitch['Prob. Voicing']:
		print '\tSecond %.2f' % formant['time']
		print '\t\tF0: %.0f Hz' % pitch['Pitch']
		for tipo in ('F', 'BW'):
			for elem in xrange(1, 5):
				print '\t\t%s%d: %.0f Hz' % (tipo, elem, 
					formants['%s%d' % (tipo, elem)])
	f0['time'] = f0['time'] + f0['framelenght']
	formant['time'] = formant['time'] + formant['framelenght']

info = mysound.info()
info_fields=('Length', 'Rate', 'Maxmimum Sample', 'Minimum Sample', 
	'Sample encoding', 'Channels', 'Format', 'Header size')

print 'Sound Info: '
for field, value in izip(info_fields, info):
	print '\t%s: %s' % (field, value)
print '\tFilename:', mysound.tk.call(mysound, 'cget', '-load')

mysound.destroy()
