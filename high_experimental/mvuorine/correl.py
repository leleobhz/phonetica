#!/usr/bin/python
# -*- coding: utf-8 -*-

from four1 import four1
from math import sin

def correl(data1, data2, n):
	ans=(2*n+1)*[0.0]
	correl_r(data1[:], data2[:], n, ans)
	return ans

# Computes the correlation of two real data sets data1[1..n] and data2[1..n]
# (including any user-specified zero-padding). n MUST be an integer power of two.
# The answer is returned as the first n points in ans[1..2*n] stored in wrap-around
# order, i.e. correlations at increasingly negative lags are in ans[n] on down to
# ans[n/2+1], while correlations at increasingly positive lags are in ans[1]
# (zero lag) on up to ans[n/2]. Note that ans must be supplied in the calling
# program with length at least 2*n, since it is also used as working space.
# Sign convention of this routine: if data1 lags data2, i.e. is shifted to the
# right of it, then ans will show a peak at positive lags. */
def correl_r(data1, data2, n, ans):
        fft=(1+n<<1) * [0.0]
        twofft(data1,data2,fft,ans,n)
        no2=n>>1
        for i in range(2, n+2+1, 2):
		dum=ans[i-1]
                ans[i-1]=(fft[i-1]*dum+fft[i]*ans[i])/no2
                ans[i]=(fft[i]*dum-fft[i-1]*ans[i])/no2
        ans[2]=ans[n+1]
        realft(ans,n,-1)

# Given two real input arrays data1[0..n-1] and data2[0..n-1], this routine calls
# four1 and returns a tuple of complex output arrays, fft1[0..n-1] and fft2[0..n-1],
# each of complex length n, which contains the discrete
# Fourier transforms of the respective data arrays.
def twofft(data1, data2, fft1, fft2, n):

	nn2 = n + n + 2
	nn3 = nn2 + 1
        for j in range(1, n+1):
		jj = 2*j
                fft1[jj-1]=data1[j]
                fft1[jj]=data2[j]
        four1(fft1,n,1);        
        fft2[1]=fft1[2];
        fft1[2]=fft2[2]=0.0;
        for j in range(3,n+1+1,2):
                rep=0.5*(fft1[j]+fft1[nn2-j])
                rem=0.5*(fft1[j]-fft1[nn2-j])
                aip=0.5*(fft1[j+1]+fft1[nn3-j])
                aim=0.5*(fft1[j+1]-fft1[nn3-j])
                fft1[j]=rep
                fft1[j+1]=aim
                fft1[nn2-j]=rep
                fft1[nn3-j] = -aim
                fft2[j]=aip
                fft2[j+1] = -rem
                fft2[nn2-j]=aip
                fft2[nn3-j]=rem

# Calculates the Fourier transform of a set of n real-valued data points.
# Replaces this data (which is stored in array data[1..n]) by the positive
# frequency half of its complex Fourier transform. The real-valued first and last
# components of the complex transform are returned as elements data[1] and
# data[2], respectively. n must be a power of 2. This routine also calculates the
# inverse transform of a complex data array if it is the transform of real data.
# (Result in this case must be multiplied by 2/n.)
def realft(data, n, isign):
	c1=0.5
        theta=3.141592653589793/(n>>1)
        if (isign == 1):
                c2 = -0.5;
                four1(data,n>>1,1)
        else:
                c2 = 0.5;
                theta = -theta;
        wtemp=sin(0.5*theta)
        wpr = -2.0*wtemp*wtemp
        wpi=sin(theta)
        wr=1.0+wpr
        wi=wpi
        np3=n+3
        for i in range(2, (n>>2)+1):
		i1=i+i-1
		i2=1+i1
		i3=np3-i2
                i4=1+i3
                h1r=c1*(data[i1]+data[i3])
                h1i=c1*(data[i2]-data[i4])
                h2r = -c2*(data[i2]+data[i4])
                h2i=c2*(data[i1]-data[i3])
                data[i1]=h1r+wr*h2r-wi*h2i
                data[i2]=h1i+wr*h2i+wi*h2r
                data[i3]=h1r-wr*h2r+wi*h2i
                data[i4] = -h1i+wr*h2i+wi*h2r
		wtemp=wr
                wr=wtemp*wpr-wi*wpi+wr
                wi=wi*wpr+wtemp*wpi+wi
        if (isign == 1):
		h1r = data[1]
                data[1] = h1r+data[2]
                data[2] = h1r-data[2]
        else:
		h1r = data[1]
                data[1]=c1*(h1r+data[2])
                data[2]=c1*(h1r-data[2])
                four1(data,n>>1,-1)
