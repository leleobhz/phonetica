/*
*----------------------------------------------------------------------- 
*	file:	lpc.h
*	desct:	lpc.c header
*	by:	patrick ko shu pui
*	date:	23 may 1992
*-----------------------------------------------------------------------
*/
/*
*----------------------------------------------------------------------- 
*	LPC methods \
*
*		LPCCOVAR = LPC using covariance method
*		LPCAUTOCOR = LPC using autocorrelation method
*		LPCCEPSTRUM = LPC CEPSTRUM 
*		PARCOR	= PARtial CORrelation coefficients
*-----------------------------------------------------------------------
*/

#define	LPCCOVAR	128
#define	LPCAUTOCOR	129
#define	LPCCEPSTRUM	130
#define	PARCOR		131


/*
*----------------------------------------------------------------------- 
* input data types
*-----------------------------------------------------------------------
*/
#define	AUDIOULAW8	1
#define	AUDIOPCM16	2

/*
*----------------------------------------------------------------------- 
* data normalization to (-1,+1)
*-----------------------------------------------------------------------
*/
#define norm_s2d(x)	((unsigned short)(x) == 0x8000? -1 : \
			((double)((short)(x))) / 32767.0)

extern short ulaw2linear[];
#define	u2s(x)		(ulaw2linear[(unsigned char)(x)])
/*
*-----------------------------------------------------------------------
*	function declarations
*-----------------------------------------------------------------------
*/
MATRIX cepstrum( );
int normeqn_cv( );
MATRIX lpc1( );
int normeqn_ac( );
MATRIX lpc2( );

