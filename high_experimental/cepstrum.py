#!/usr/bin/env python

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


# Code based in http://www.onlamp.com/python/2001/01/31/graphics/pysono.py

from sys import stdout, argv
from wave import open

def cepstrum_f0(self, audiodata):

	from itertools import izip
	from numpy import array, log, zeros
	from pylab import fft, plot, xlabel, ylabel, show, hamming
	from struct import unpack

	def progress_bar(value, max, barsize):
		chars = int(value * barsize / float(max))
		percent = int((value / float(max)) * 100)
		stdout.write("#" * chars)
		stdout.write(" " * (barsize - chars + 2))
		if value >= max:
			stdout.write("done. \n\n")
		else:
			stdout.write("[%3i%%]\r" % (percent))
			stdout.flush()
	cepstrum = []
	def readsound(place):
		tempb = fp.readframes(window_length);
		temp[i,:] = array(unpack("%dB"%(window_length*sample_width),tempb), dtype=float)
	#	progress_bar(frame, total_num_samps, 80)
	#	frame = frame + window_length

	def cepstrum_process(data):
		# Window the data
		data = data * hamming(len(data))
		data = fft(data, fft_length)
		cepstrum.append(fft(log(1e-20+abs(fft(data)))))
	#	progress_bar(frame, total_num_samps, 80)
	#	frame = frame + window_length

	# open the wave file
	fp = open(audiodata,"rb")

	sample_rate = fp.getframerate()
	sample_width = fp.getsampwidth()
	total_num_samps = fp.getnframes()
	fft_length = 256
	window_length = 256
	num_window = (total_num_samps / window_length ) - 2
	
	# create temporary working array
	temp = zeros((num_window,window_length*sample_width),'f')
	
	# read in the data from the file
	#frame = 0
	#stdout.write('Reading sound...\n')
	for i in range(num_window):
		readsound(i)
	fp.close()
	
	# Start with FFT
	#frame = 0
	#stdout.write('\nProcessing Graph...\n')
	for i in temp:
#		t = Thread(target=cepstrum_process, args=(i,))
#		t.start()
		# At moment, threads is slower than normal processing
		cepstrum_process(i)
	#stdout.write('\n')
	# Limits
	ms1=sample_rate/500;                 # maximum speech Fx at 1000Hz
	ms20=sample_rate/70;                  # minimum speech Fx at 50Hz

	# Plot the result
	#q = [float(i)/sample_rate for i in range(ms1, ms20)]
	#plot(q, [abs(x) for x in cepstrum[ms1:ms20]])
	#xlabel('Quefrency (s)')
	#ylabel('Amplitude')
	#show()
	
	f0 = []
	
	for i, j in izip (cepstrum, range(0, num_window*window_length, window_length)):
		max_cepstrum = max([abs(x) for x in i[ms1:ms20]])
		#print 'Frame %d (Sec. %.2f)  F0=%.0fHz' % (j, (float(j) / sample_rate), sample_rate / (ms1 + int(max_cepstrum) - 1))
		f0.append([j, sample_rate / (ms1 + int(max_cepstrum) - 1)])
	return f0

if __name__ == "__main__":
#	import psyco
#	psyco.bind(main)

#	from timeit import Timer
#	print Timer('cProfile.run(\'main(argv[1])\')', 'import cProfile').timeit(1)
#	print 'This script takes %.3f seconds to complete' % Timer('main(argv[1])', 'from __main__ import main; from sys import argv').timeit(1)

	print cepstrum_f0('main', argv[1])
