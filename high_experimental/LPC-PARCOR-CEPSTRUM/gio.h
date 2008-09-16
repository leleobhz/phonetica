#define GIO_STDIN	0
#define	GIO_STDOUT	1
#define	GIO_STDERR	2
#define	GIO_FILE	3
#define GIO_MSGQ	4


typedef	struct {
	int	iotype;
	int	stdio;
	FILE	*fp;
	} GIO;


GIO * greopen( );
GIO * gopen( );
int gclose( );
int gputs( );
int gread( );
