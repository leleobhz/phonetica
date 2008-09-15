/*
*-----------------------------------------------------------------------------
*	file:	matsolve.c
*	desc:	solve linear equations
*	by:	ko shu pui, patrick
*	date:	24 nov 91 v0.1
*	revi:	14 may 92 v0.2
*	ref:
*       [1] Mary L.Boas, "Mathematical Methods in the Physical Sciene,"
*	John Wiley & Sons, 2nd Ed., 1983. Chap 3.
*
*	[2] Kendall E.Atkinson, "An Introduction to Numberical Analysis,"
*	John Wiley & Sons, 1978.
*
*-----------------------------------------------------------------------------
*/
#include <stdio.h>
#include <math.h>

#ifdef	__TURBOC__
#include <alloc.h>
#else
#include <malloc.h>
#endif

#include "matrix.h"

/*
*-----------------------------------------------------------------------------
*	funct:	mat_lu
*	desct:	in-place LU decomposition with partial pivoting
*	given:	!! A = square matrix (attention! see commen)
*		ri = place to put row index (size of n)
*	retrn:	A = successful, NULL = fail
*	comen:	A will be overwritten to be a LU-composite matrix
*	note:	the LU decomposed may NOT be equal to the LU of
*		the orignal matrix a. But equal to the LU of the
*		rows interchanged matrix.
*-----------------------------------------------------------------------------
*/
MATRIX mat_lu( A, ri )
MATRIX A;
int *ri;
{
	int	i, j, k, n;
	int	maxi, tmp;
	double	c, c1;

	n = MatCol(A);

	for (i=0; i<n; i++)
		{
		ri[i] = i;
		}

	for (k=0; k<n; k++)
	{
	/*
	* --- partial pivoting ---
	*/
	for (i=k, maxi=k, c=0.0; i<n; i++)
		{
		c1 = fabs( A[ri[i]][k] );
		if (c1 > c)
			{
			c = c1;
			maxi = i;
			}
		}
	tmp = ri[k];
	ri[k] = ri[maxi];
	ri[maxi] = tmp;

	/*
	*	suspected singular matrix
	*/
	if ( A[ri[k]][k] == 0.0 )
		return ((MATRIX)mat_error(MAT_SINGULAR));

	for (i=k+1; i<n; i++)
		{
		/*
		* --- calculate m(i,j) ---
		*/
		A[ri[i]][k] = A[ri[i]][k] / A[ri[k]][k];

		/*
		* --- elimination ---
		*/
		for (j=k+1; j<n; j++)
			{
			A[ri[i]][j] -= A[ri[i]][k] * A[ri[k]][j];
			}
		}
	}

}

/*
*-----------------------------------------------------------------------------
*	funct:	mat_backsubs1
*	desct:	back substitution
*	given:	A = square matrix A (LU composite)
*		!! B = column matrix B (attention!, see comen)
*		!! X = place to put the result of X
*		xcol = column of x to put the result
*		ri = row index (in case after partial pivoting)
*	retrn:	column matrix X (of AX = B)
*	comen:	B will be overwritten
*-----------------------------------------------------------------------------
*/
MATRIX mat_backsubs1( A, B, X, xcol, ri )
MATRIX A, B, X;
int xcol;
int *ri;
{
	int	i, j, k, n;
	double	sum;

	n = MatCol(A);

	for (k=0; k<n; k++)
		{
		for (i=k+1; i<n; i++)
			B[ri[i]][0] -= A[ri[i]][k] * B[ri[k]][0];
		}

	X[n-1][xcol] = B[ri[n-1]][0] / A[ri[n-1]][n-1];
	for (k=n-2; k>=0; k--)
		{
		sum = 0.0;
		for (j=k+1; j<n; j++)
			{
			sum += A[ri[k]][j] * X[j][xcol];
			}
		X[k][xcol] = (B[ri[k]][0] - sum) / A[ri[k]][k];
		}

	return (X);
}

/*
*-----------------------------------------------------------------------------
*	funct:	mat_lsolve
*	desct:	solve linear equations
*	given:	a = square matrix A
*		b = column matrix B
*	retrn:	column matrix X (of AX = B)
*-----------------------------------------------------------------------------
*/
MATRIX mat_lsolve( a, b )
MATRIX a, b;
{
	MATRIX	A, B, X;
	int	*ri, i, n;
	double	temp;

	n = MatCol(a);
	A = mat_copy(a);
	B = mat_copy(b);
	X = mat_creat(n, 1, ZERO_MATRIX);

	if ((ri = (int *)malloc(sizeof(int) * n)) == NULL)
		return (NULL);

	mat_lu( A, ri );
	mat_backsubs1( A, B, X, 0, ri );

	free(ri);
	mat_free(A);
	mat_free(B);

	return (X);
}
