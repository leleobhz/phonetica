/*
*------------------------------------------------------------------------------
*	file:	lpc.c
*	desct:	lpc module
*	by:	patrick ko shu pui
*	date:	18 may 1992
*
*	ref:
*
*	Shuzo Saito, Kazuo Nakata, "Fundamentals of Speech Signal Processing,"
*	Academic Press, 1985.
*------------------------------------------------------------------------------
*/

#include <stdio.h>
#include "matrix.h"
#include "lpc.h" 
#include "gio.h"


/*
*------------------------------------------------------------------------------
*	funct:	lpcout
*	desct:	output data from generic input
*	given:
*		gout = generic output
*		gacout = generic output for .ac file
*		A = autocorrelation matrix (if exists)
*		B = coefficients matrix (any type .lpc, .par, .cep)
*		i = from offset
*		n = number of coefficients
*------------------------------------------------------------------------------
*/ 
int lpcout(gout, B, gacout, A, i, n)
GIO *gout;
MATRIX B;
GIO *gacout;
MATRIX A;
int i, n;
{
	int	j;
	char	temp[128];

	for (j=i; j<n; j++)
		{
		sprintf(temp, "%f ", B[j][0]);
		gputs( temp, gout );
		if (gacout != NULL)
			{
			sprintf(temp, "%f ", A[j][0]);
			gputs( temp, gacout );
			}
		}
	gputs( "\n", gout );
	if (gacout != NULL)
		gputs( "\n", gacout );
}

/*
*------------------------------------------------------------------------------
*	funct:	lpc
*	desct:	get data from generic input and LPC it
*	given:
*		gin = generic input
*		gout = generic output
*		gacout = generic output for .ac file
*		order = order of LPC
*		ws = window size
*		os = window overlap size
*		method = (see lpc.h) 
*------------------------------------------------------------------------------
*/ 
int lpc( gin, dtype, gout, gacout, order, ws, os, method )
GIO *gin, *gout, *gacout;
int order, ws, os, dtype;
int method;
{
	MATRIX	A, B, F, P, E, CEP;
	double	*s;
	short	t;
	char	c;
	int	i, j;
	char	temp[128];

	A = mat_creat(order, order, UNDEFINED);
	F = mat_creat(order, 1, UNDEFINED);
	P = mat_creat(order, 1, UNDEFINED);
	E = mat_creat(order+1, 1, UNDEFINED);
	s = (double *)malloc(sizeof(double) * ws);
	i = 0;

	while (!geof(gin))
		{
		switch (dtype)
		{
		case AUDIOULAW8:
		if (gread(&c, sizeof(char), 1, gin))
			{
			t = u2s(c);
			s[i] = norm_s2d(t);
			i++;
			}
		break;

		case AUDIOPCM16:		
		if (gread(&t,sizeof(short),1,gin))
			{
			s[i] = norm_s2d(t);
			i++;
			}
		break;

		default:
		fprintf(stderr, "wrong data type specified\n" );
		exit (1);
		}

		if (i>=ws)
			{
			switch (method)
				{
				case LPCCOVAR:
				B = lpc1(A, F, s, ws);
				lpcout( gout, B, NULL, A, 0, order );
				break;

				case LPCAUTOCOR:
				B = lpc2(A, F, s, ws, P, E);
				lpcout( gout, B, gacout, A, 0, order );
				break;

				case LPCCEPSTRUM:
				B = lpc2(A, F, s, ws, P, E);
				CEP = cepstrum(B, E[order][0]);
				lpcout( gout, CEP, gacout, A, 0, order + 1 );
				mat_free(CEP);
				break;

				case PARCOR:
				B = lpc2(A, F, s, ws, P, E);
				lpcout( gout, P, gacout, A, 0, order );
				break;
				}

			mat_free(B);
			if (os > 0)
				memmove( s, s+ws-os, sizeof(double) * os );
			i = os;
			}
		}
	mat_free(A);
	mat_free(F);
	mat_free(P);
	mat_free(E);
	free(s);
}
