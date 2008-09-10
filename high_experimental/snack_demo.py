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

from Tkinter import *
root = Tk()
import tkSnack
tkSnack.initializeSnack(root)

# Instancing...
mysound = tkSnack.Sound()

# Read Sound
#mysound.read('test_praat.wav')
mysound.read('Fn-ST-1.wav')

# Python Snack have the function formant, but dont accept any options, so we use a tk.call direct, giving access to all powerfull tk implementation. Above is all default values, and have too -windowlength, -start, -end and -lpctype 0 (autocorrelation) or 1 (stabilized covariance)

formant = dict()
formant['windowlenght'] = 0.049
formant['framelenght'] = 0.01
formant['fields'] = ('F1', 'F2', 'F3', 'F4', 'BW1', 'BW2', 'BW3', 'BW4')
formant['time'] = formant['windowlenght'] / 2

formant['processed'] = mysound.tk.call(mysound, 'formant', '-numformants', '4', '-framelength', formant['framelenght'], '-windowtype', 'Cos^4', '-windowlength', formant['windowlenght'], '-preemphasisfactor', '0.7', '-lpcorder', '12', '-ds_freq', '10000', '-nom_f1_freq', '500')

for formant_local in formant['processed']:

	formants = dict(zip(formant['fields'], formant_local))
	print '\tSecond %.2f\n\t\tF1: %.0f Hz\n\t\tF2: %.0f Hz\n\t\tF3: %.0f Hz\n\t\tF4: %.0f Hz\n\t\tBW1: %.0f Hz\n\t\tBW2: %.0f Hz\n\t\tBW3: %.0f Hz\n\t\tBW4: %.0f Hz' % (formant['time'], formants['F1'], formants['F2'], formants['F3'], formants['F4'], formants['BW1'], formants['BW2'], formants['BW3'], formants['BW4'])
	formant['time'] = formant['time'] + formant['framelenght']

# Extract F0.
f0 = dict()
f0['method'] = 'esps'
f0['windowlenght'] = 0.049
f0['framelenght'] = 0.01
f0['minpitch'] = 70
f0['maxpitch'] = 1000

f0['processed'] = mysound.tk.call(mysound, 'pitch', '-method', f0['method'] , '-framelength', f0['framelenght'], '-windowlength', f0['windowlenght'], '-minpitch', f0['minpitch'], '-maxpitch', f0['maxpitch'])
f0['fields'] = ('Pitch', 'Prob. Voicing', 'Local root mean squared mensurements', 'Peak Normalized cross-correlation')

if f0['method'] == 'esps':
	f0['time'] = f0['windowlenght'] / 2

#elif f0['method'] == 'amdf':
else:
	f0['time'] = 0

print 'Pitchs: '
for f0_local in f0['processed']:

	pitch = dict(zip(f0['fields'], f0_local))
	if pitch['Prob. Voicing']:
		print '\tSecond %.2f --> Pitch: %.0f Hz' % (f0['time'], pitch['Pitch'])
	f0['time'] = f0['time'] + f0['framelenght']

#	for field, value in pitch.iteritems():
#		print '\t%s: %s' % (field, value)
#	print ''

info = mysound.info()
info_fields=('Length', 'Rate', 'Maxmimum Sample', 'Minimum Sample', 'Sample encoding', 'Channels', 'Format', 'Header size')

print 'Sound Info: '
information = dict(zip(info_fields, info))
for field, value in information.iteritems():
	print '\t%s: %s' % (field, value)
print '\tFilename:', mysound.tk.call(mysound, 'cget', '-load')

mysound.destroy()
