/*
*-----------------------------------------------------------------------------
*	file:	cparser.h
*	desc:	simple command parser
*	by:	patrick ko
*	date:	22 aug 91
*-----------------------------------------------------------------------------
*/

#define	CMD_NULL	0

typedef	struct	{
	int	cmdno;
	char	* cmdstr;
	}       CMDTBL;

/*
*	#define your own commands here starting from 1
*	-* modifiable *-
*/
#define	CMD_LPCORDER	1
#define	CMD_LPCWSIZE	2
#define	CMD_LPCWOVER	3

#define	CMD_DTYPEU8	4
#define	CMD_DTYPEP16	5

#define	CMD_MSGQIN	6
#define	CMD_MSGQOUT	7

#define	CMD_STDOUT	8

#define	CMD_NOACFILE	9

#define CMD_AUTOCOR	10
#define	CMD_COVAR	11
#define	CMD_CEPSTRUM	12
#define	CMD_PARCOR	13 

#define	CMD_COMMENT	128

#ifdef	__TURBOC__

int	cmdsearch		(char *, char *);
int	cmdinit			(int, char **);
int 	cmdget			(char *);

#else

int	cmdsearch		( );
int	cmdinit			( );
int 	cmdget			( );

#endif
