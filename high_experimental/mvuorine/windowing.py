#!/usr/bin/python
#
def hanning(x_in):
	# Windowing using 512-point Hanning window and coercion to floats
	arg = 2 * pi / 512.0 # This is as in the original, but should this be? This
			# results in a division of 511 parts of one cycle, and
			# wvalue[0] ends up the same as wvalue[511]...
	for i in range(0, 512):
		wvalue = 0.5 - 0.5*cos(arg*i)
		data.append(float(x_in[sample+i-256] * wvalue))
		data.append(0.0)
	return data

