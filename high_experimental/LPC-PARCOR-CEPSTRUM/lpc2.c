/*
*------------------------------------------------------------------------------
*	file:	lpc2.c
*	desct:	finding LPC coefficients with autocorrelation matrix 
*	date:	19 May 1992
*	by:	patrick ko shu pui
*	ver:	v0.1b
*------------------------------------------------------------------------------
*/
#include <stdio.h>
#include <stdlib.h>
#include "matrix.h"
#include "lpc.h"

/*
*------------------------------------------------------------------------------
*	funct:	normeqn_ac	
*	desct:	create a normal equation with autocorrelation matrix 
*	given:	a1 = allocated matrix (M x M) for autocorrelation matrix
*		a2 = allocated matrix (M x 1)
*		s = signal array
*		nn= number of signal
*	retrn:	nothing
*------------------------------------------------------------------------------
*/
int normeqn_ac( A1, A2, s, nn )
MATRIX A1, A2;
double *s;
int nn;
{
	int	i, j, k, n, m;

	m = MatCol(A1);

	/*
	* create autocorrelation matrix
	*/
	for (i=0; i<m; i++)
		{
		A1[0][i] = 0.0;
		for (n=0; n<nn-i; n++)
			{
			A1[0][i] += s[n] * s[n+i];
			}
		}
	for (i=1; i<m; i++)
	for (j=0; j<m; j++)
		{
		A1[i][j] = A1[0][abs(i-j)];
		}

	for (k=1; k<m; k++)
		{
		A2[k-1][0] = A1[0][k];
		}

	A2[m-1][0] = 0.0;
	for (n=0; n<nn-m; n++)
		{
		A2[m-1][0] += s[n] * s[n+m];
		}
}

/*
*------------------------------------------------------------------------------
*	funct:	lpc2 (autocorrelation approach)
*	desct:	lpc on one windows of data
*	given:	A = allocated correlation matrix (M x M) M=order of LPC 
*		B = allocated column vector (M x 1) 
*		P = allocated column vector (M x 1) for PARCOR coefs
*		E = allocated column vector (M+1 x 1) for residue power
*		s = signal array
*		nn = number of signals in s  
*	retrn:	a column matrix of LPC coefficients	
*------------------------------------------------------------------------------
*/ 
MATRIX lpc2( A, B, s, nn, P, E )
MATRIX A, B;
double *s;
int nn;
MATRIX P, E;
{
	MATRIX X;
	int i;

	normeqn_ac( A, B, s, nn );

	X = mat_lsolve_durbin( A, B, P, E );

	for (i=0; i<MatRow(X); i++)
		X[i][0] *= -1.0;

	return (X);
} 
