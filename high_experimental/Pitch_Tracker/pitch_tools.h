#ifndef _TOOLS_
#define _TOOLS_
/******************************************************************************

Copyright(C) 1992,1993 Tony Robinson

Permission is granted to use this software for non-commercial, non-military
purposes.  It may be copied and distributed freely, provided that this notice
is copied and distributed with it.  Modified versions may be distributed with
the same permissions and restrictions, provided that clear notice of the
alterations is given.  This software carries NO WARRANTY, expressed or
implied.  The user assumes all risks, known or unknown, direct or indirect,
which involve this software in any way.

Acknowledgement is requested if this software contributes significantly
toward any research publication.

Dr Tony Robinson
Cambridge University Engineering Department
Trumpington Street, Cambridge, CB2 1PZ, England.

email: ajr@eng.cam.ac.uk
voice: +44-223-332754

******************************************************************************/

/* include anything that might be useful */
# include <math.h>
# include <stdio.h>
# include <ctype.h>
# include <string.h>
# include <stdlib.h>

#ifdef sun
# include <alloca.h>
#endif

#ifndef TC
# include <unistd.h>
# include <signal.h>
# include <varargs.h>
# include <sys/types.h>
# include <utmp.h>
# include <sys/stat.h>
#ifndef sgi
# include <sys/timeb.h>
#endif
# include <sys/param.h>
# define B011_NODEID 0
#else
# include <conc.h>
# include <float.h>
# include <stdarg.h>
# include <sys/boot.h>
# include <sys/t-rack.h>
# include <sys/node_mmap.h>
# include <sys/ctrl_proto.h>
#endif

#if defined(sun) && ! defined(__svr4__)
/* where are these defined? */
void	bcopy(char*, char*, int);
void	bzero(char*, int);
int	printf();
int	fclose(FILE*);
int	fflush(FILE*);
int	fgetc(FILE*);
int	fprintf();
int	fscanf();
int	fseek(FILE*, long, int);
void	rewind(FILE*);
int	scanf();
int	setlinebuf(FILE*);
int	sscanf();
int	vfprintf();
#endif

#ifdef TC
# define  schar  signed   char
# define  sshort signed   short
# define  sint   signed   int
# define  uchar  unsigned char
# define  ushort unsigned short
# define  uint   unsigned int
#else
# define  schar           char
# define  sshort          short
# define  sint            int
# define  uchar  unsigned char
# define  ushort unsigned short
# define  uint   unsigned int
#endif

# define  MAX_SCHAR     127
# define  MAX_UCHAR     255

/*
** definitions of logical values
*/
# define FALSE 0
# define TRUE ~FALSE

char   *Char_array(int);

short   *Panic_short_array(int);
short   *Short_array(int);
void	Panic_fseek(FILE*, long, int);
char*   Scan_string();
int	Scan_flag(int, char**, char*);
int	Scan_int(int, char**, char*, int);
FILE*	Std_fopen(char*, char*);
void	Panic_fread(void*, int, int, FILE*);

#endif /* _TOOLS_ */
