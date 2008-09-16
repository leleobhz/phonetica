/*
*-----------------------------------------------------------------------------
*	file:	matdurbn.c
*	desc:	Levinson-Durbin algorithm
*
*		(specially for LPC, PARCOR calculation)
*
*	by:	ko shu pui, patrick
*	date:
*	revi:	21 may 92 v0.3
*	ref:
*
*       [1] "Fundementals of Speech Signal Processing," Shuzo Saito,
*       Kazuo Nakata, Academic Press, New York, 1985.
*
*-----------------------------------------------------------------------------
*/

#include <stdio.h>

#include "matrix.h"

/*
*-----------------------------------------------------------------------------
*	funct:	mat_durbin
*	desct:	Levinson-Durbin algorithm
*
*		This function solve the linear eqns Ax = B:
*
*		|  v0   v1   v2  .. vn-1 | |  a1   |    |  v1   |
*		|  v1   v0   v1  .. vn-2 | |  a2   |    |  v2   |
*		|  v2   v1   v0  .. vn-3 | |  a3   |  = |  ..   |
*		|  ...                   | |  ..   |    |  ..   |
*		|  vn-1 vn-2 ..  .. v0   | |  an   |    |  vn   |
*
*		where A is a symmetric Toeplitz matrix and B
*		in the above format (related to A)
*
*	given:	R = autocorrelated matrix (v0, v1, ... vn) (dim (n+1) x 1)
*	retrn:	x (of Ax = B) - LPC coefficients
*		P = PARCOR coefficients (dim n x 1 )
*		E = residue power (dim (n+1) x 1 )
*-----------------------------------------------------------------------------
*/
MATRIX mat_durbin( R, P, E )
MATRIX R, P, E;
{
	int	i, i1, j, ji, p, n;
	MATRIX	W, A, X;

	p = MatRow(R) - 1;
	W = mat_creat( p, 1, UNDEFINED );
	A = mat_creat( p+1, p+1, UNDEFINED );
	

	W[0][0] = R[1][0];
	E[0][0] = R[0][0];

	for (i=1; i<=p; i++)
		{
		P[i-1][0] = W[i-1][0] / E[i-1][0];

		E[i][0] = E[i-1][0] * (1.0 - P[i-1][0] * P[i-1][0]);
		if (E[i][0] <= 0.0)
			E[i][0] = E[i-1][0];

		A[i-1][i-1] = -P[i-1][0];

		i1 = i-1;
		if (i1 >= 1)
			{
			for (j=1; j<=i1; j++)
			{
			ji = i - j;
			A[j-1][i-1] = A[j-1][i1-1] - P[i-1][0] * A[ji-1][i1-1];
			}
			}

		if (i != p)
			{
			W[i][0] = R[i+1][0];
			for (j=1; j<=i; j++)
				W[i][0] += A[j-1][i-1] * R[i-j+1][0];
			}
		}

	X = mat_creat( p, 1, UNDEFINED );
	for (i=0; i<p; i++)
		{
		X[i][0] = -A[i][p-1];
		}

	mat_free( A );
	mat_free( W );
	return (X);
}

/*
*-----------------------------------------------------------------------------
*	funct:	mat_lsolve_durbin
*	desct:	Solve simultaneous linear eqns using
*		Levinson-Durbin algorithm
*
*		This function solve the linear eqns Ax = B:
*
*		|  v0   v1   v2  .. vn-1 | |  a1   |    |  v1   |
*		|  v1   v0   v1  .. vn-2 | |  a2   |    |  v2   |
*		|  v2   v1   v0  .. vn-3 | |  a3   |  = |  ..   |
*		|  ...                   | |  ..   |    |  ..   |
*		|  vn-1 vn-2 ..  .. v0   | |  an   |    |  vn   |
*
*	domain:	where A is a symmetric Toeplitz matrix and B
*		in the above format (related to A)
*
*	given:	A, B
*	retrn:	x (of Ax = B)
*
*	WARNING: this version of Levinson-Durbin method may cause some lost
*		of accuracy, it is only suitable for applications such as
*		speech processing, etc where you bear this in mind
*-----------------------------------------------------------------------------
*/
MATRIX mat_lsolve_durbin( A, B, P, E )
MATRIX A, B, P, E;
{
	MATRIX	R, X;
	int	i, n;

	n = MatRow(A);
	R = mat_creat(n+1, 1, UNDEFINED);
	for (i=0; i<n; i++)
		{
		R[i][0] = A[i][0];
		}
	R[n][0] = B[n-1][0];

	X = mat_durbin( R, P, E );
	mat_free( R );
	return (X);
}
