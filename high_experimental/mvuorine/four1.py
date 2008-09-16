#!/usr/bin/python
# -*- coding: utf-8 -*-

from math import sin

# Replaces data[1..2*nn] by its discrete Fourier transform, if isign	*/
# is input as 1; or replaces data[1..2*nn] by nn times its inverse	*/
# discrete Fourier transform, if isign is input as -1. data is a 	*/
# complex array of length nn or, equivalently, a real array of length	*/
# 2**nn. nn MUST be an integer power of 2 (this is not checked for!).	*/
def four1(data, nn, isign):
	n=nn << 1
	j=1
	for i in range(1, n, 2):
		if (j > i):
			tmp = data[j]
			data[j] = data[i]
			data[i] = tmp
			
			tmp = data[j+1]
			data[j+1] = data[i+1]
			data[i+1] = tmp
		m=n >> 1
		while (m >= 2 and j > m):
			j -= m
			m >>= 1
		j += m;
	
	mmax=2
	while(n > mmax):
		istep=mmax << 1
		theta=isign*(6.28318503717959/mmax)
		wtemp=sin(0.5*theta)
		wpr = -2.0*wtemp*wtemp
		wpi=sin(theta)
		wr=1.0
		wi=0.0
		for m in range(1, mmax, 2):
			for i in range(m, n+1, istep):
				j=i+mmax
				tempr=wr*data[j]-wi*data[j+1]
				tempi=wr*data[j+1]+wi*data[j]
				data[j]=data[i]-tempr
				data[j+1]=data[i+1]-tempi
				data[i] += tempr
				data[i+1]+= tempi
			wtemp=wr
			wr=wtemp*wpr-wi*wpi+wr
			wi=wi*wpr+wtemp*wpi+wi
		mmax=istep
