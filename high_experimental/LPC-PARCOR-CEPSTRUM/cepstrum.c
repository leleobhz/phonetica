#include <stdio.h>
#include <math.h>
#include "matrix.h"
#include "lpc.h"

/*
*-----------------------------------------------------------------------------
*	funct:	cepstrum
*	desct:	derive LPC cepstrum coefficients from LPC coefficients
*	given:	A = LPC vector of coefficients (n x 1)
*		E = residue power 
*	retrn:	cepstrum matrix (dim n+1 x 1)
*-----------------------------------------------------------------------------
*/
MATRIX cepstrum( A, E )
MATRIX A;
double E;
{
	int	i, i1, n, k, k1, ik, kk;
	MATRIX	C;
	double	csum;

	n = MatRow(A);
	C = mat_creat( n+1, 1, UNDEFINED );

	C[0][0] = 0.5 * log(E);
	C[1][0] = -A[0][0];

	for (i=2; i<=n; i++)
		{
		i1 = i + 1;
		kk = i - 1;
		csum = 0.0;
		for (k=1; k<=kk; k++)
			{
			ik = i - k;
			k1 = k + 1;
			csum += k * C[k1-1][0] * A[ik-1][0];
			}
		csum /= i;
		C[i1-1][0] = -(A[i-1][0] + csum);
		}

	return (C);
}
