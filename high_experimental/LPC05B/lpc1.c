/*
*------------------------------------------------------------------------------
*	file:	lpc1.c
*	desct:	finding LPC coefficients with covariance method
*	date:	18 May 1992
*	by:	patrick ko shu pui
*	ver:	v0.1b
*------------------------------------------------------------------------------
*/
#include <stdio.h>
#include "matrix.h"
#include "lpc.h"


/*
*------------------------------------------------------------------------------
*	funct:	normeqn_cv	
*	desct:	create a normal equation for LPC eqn solving
*	given:	a1 = allocated matrix (M x M)
*		a2 = allocated matrix (M x 1)
*		s = signal array
*		nn= number of signal
*	retrn:	nothing
*------------------------------------------------------------------------------
*/
int normeqn_cv( A1, A2, s, nn )
MATRIX A1, A2;
double *s;
int nn;
{
	int	j, k, n, m;
	double	temp;

	m = MatCol(A1);

	for (k=1; k<=m; k++)
		{
		for (j=0; j<=k; j++)
		{
		temp = 0.0;
		for (n=0; n<nn; n++)
			{
			if (n-j < 0)
				continue;	
			temp += s[n-j]*s[n-k];
			}
		if (j==0)
			A2[k-1][0] = temp * -1.0;
		else
			A1[j-1][k-1] = A1[k-1][j-1] = temp;	
		}
		}
}

/*
*------------------------------------------------------------------------------
*	funct:	lpc1
*	desct:	lpc on one windows of data
*	given:	A = allocated correlation matrix (M x M) M=order of LPC 
*		B = allocated column vector (M x 1) 
*		s = signal array
*		nn = number of signals in s  
*	retrn:	a column matrix of LPC coefficients	
*------------------------------------------------------------------------------
*/ 
MATRIX lpc1( A, B, s, nn )
MATRIX A, B;
double *s;
int nn;
{
	normeqn_cv( A, B, s, nn );
	return (mat_lsolve( A, B ));
} 
