/*
*-----------------------------------------------------------------------------
*	file:	matrix.h
*	desc:	matrix mathematics header file
*	by:	ko shu pui, patrick
*	date:	24 nov 91	v0.1b
*	revi:
*	ref:
*       [1] Mary L.Boas, "Mathematical Methods in the Physical Sciene,"
*	John Wiley & Sons, 2nd Ed., 1983. Chap 3.
*
*-----------------------------------------------------------------------------
*/

/*
*-----------------------------------------------------------------------------
*	internal matrix structure
*-----------------------------------------------------------------------------
*/
typedef struct {
	int	row;
	int	col;
	}	MATHEAD;

typedef struct {
	MATHEAD	head;
	/*
	* only the starting address of the following will be
	* returned to the C programmer, like malloc() concept
	*/
	double	*matrix;
	}	MATBODY;

typedef	double	**MATRIX;

#define	Mathead(a)	((MATHEAD *)((MATHEAD *)(a) - 1))
#define MatRow(a)	(Mathead(a)->row)
#define	MatCol(a)	(Mathead(a)->col)

/*
*----------------------------------------------------------------------------
*	mat_errors definitions
*----------------------------------------------------------------------------
*/
#define	MAT_MALLOC	1
#define MAT_FNOTOPEN	2
#define MAT_SINGULAR	3
#define	MAT_FNOTGETMAT	4

/*
*----------------------------------------------------------------------------
*	matrice types
*----------------------------------------------------------------------------
*/
#define UNDEFINED	-1
#define ZERO_MATRIX	0
#define	UNIT_MATRIX	1

/* prototypes of matrix package */

MATRIX mat_error	();
MATRIX _mat_creat	();
MATRIX mat_creat	();
MATRIX mat_fill		();
int mat_free		();
MATRIX mat_copy		();
MATRIX mat_colcopy1	();
int fgetmat		();
MATRIX mat_dump		();
MATRIX mat_fdump	();

MATRIX mat_add		();
MATRIX mat_sub		();
MATRIX mat_mul		();
MATRIX mat_tran		();
MATRIX mat_inv		();
MATRIX mat_inv2		();
MATRIX mat_SymToeplz	();

MATRIX mat_lu		();
MATRIX mat_backsubs1	();
MATRIX mat_lsolve	();

double mat_cofact	();
double mat_det		();
double mat_minor	();

MATRIX mat_durbin       ();
MATRIX mat_lsolve_durbin();

