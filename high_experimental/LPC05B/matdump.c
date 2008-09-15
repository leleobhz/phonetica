/*
*-----------------------------------------------------------------------------
*	file:	matdump.c
*	desc:	matrix mathematics - object dump
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
#include "matrix.h"

/*
*-----------------------------------------------------------------------------
*	funct:	mat_dump
*	desct:	dump a matrix
*	given:	A = matrice to dumped
*	retrn:	nothing
*	comen:	matrix a dumped to standard output
*-----------------------------------------------------------------------------
*/
MATRIX mat_dump( A )
MATRIX A;
{
	int	i, j;

	for (i=0; i<MatRow(A); i++)
		{
		for (j=0; j<MatCol(A); j++)
			{
			fprintf( stdout, "%f ", A[i][j] );
			}
		fprintf( stdout, "\n" );
		}

	return (A);
}

MATRIX mat_fdump( A, fp )
MATRIX A;
FILE *fp;
{
	int 	i, j;

	if (fp==NULL)
		return ((MATRIX)mat_error( MAT_FNOTOPEN ));

	for (i=0; i<MatRow(A); i++)
		{
		for (j=0; j<MatCol(A); j++)
			{
			fprintf( fp, "%f ", A[i][j]);
			}
		fprintf( fp, "\n" );
		}

	return (A);
}
