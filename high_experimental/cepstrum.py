#!/usr/bin/env python
# Code based in http://www.onlamp.com/python/2001/01/31/graphics/pysono.py

from itertools import izip
from numpy import array, log, zeros
from pylab import fft, plot, xlabel, ylabel, show, hamming
from wave import open
from struct import unpack
from sys import stdout, argv
from threading import Thread

def main(audiofile):

	from itertools import izip
	from numpy import array, log, zeros
	from pylab import fft, plot, xlabel, ylabel, show, hamming
	from wave import open
	from struct import unpack
	from sys import stdout, argv

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
	fp = open(audiofile,"rb")

	sample_rate = fp.getframerate()
	sample_width = fp.getsampwidth()
	total_num_samps = fp.getnframes()
	fft_length = 256
	window_length = 512
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
	ms1=sample_rate/1000;                 # maximum speech Fx at 1000Hz
	ms20=sample_rate/50;                  # minimum speech Fx at 50Hz

	# Plot the result
	#q = [float(i)/sample_rate for i in range(ms1, ms20)]
	#plot(q, [abs(x) for x in cepstrum[ms1:ms20]])
	#xlabel('Quefrency (s)')
	#ylabel('Amplitude')
	#show()

	for i, j in izip (cepstrum, range(0, num_window*window_length, window_length)):
		max_cepstrum = max([abs(x) for x in i[ms1:ms20]])
		#print 'Frame %d (Sec. %.2f)  F0=%.0fHz' % (j, (float(j) / sample_rate), sample_rate / (ms1 + int(max_cepstrum) - 1))
		(j, (float(j) / sample_rate), sample_rate / (ms1 + int(max_cepstrum) - 1))

if __name__ == "__main__":
#	import psyco
#	psyco.bind(main)

#	from timeit import Timer
#	print Timer('cProfile.run(\'main(argv[1])\')', 'import cProfile').timeit(1)
#	print 'This script takes %.3f seconds to complete' % Timer('main(argv[1])', 'from __main__ import main; from sys import argv').timeit(1)

	main(argv[1])
