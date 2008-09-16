#include <stdio.h>
#include "gio.h"


GIO * greopen( type, name, arg, gp )
int type;
char *name;
char *arg;
GIO *gp;
{
	gclose(gp);
	gp = gopen( type, name, arg );
}

GIO * gopen( type, name, arg )
int type;
char *name;
char *arg;
{
	GIO	*gp;

	if ((gp = (GIO *)malloc(sizeof(GIO))) == NULL)
		return (NULL);

	switch (type)
		{
		case GIO_STDIN:
		gp->iotype = GIO_FILE;
		gp->fp = stdin;
		gp->stdio = 1;
		break;

		case GIO_STDOUT:
		gp->iotype = GIO_FILE;
		gp->fp = stdout;
		gp->stdio = 1;
		break;

		case GIO_STDERR:
		gp->iotype = GIO_FILE;
		gp->fp = stderr;
		gp->stdio = 1;
		break;
	
		case GIO_FILE:
		gp->iotype = GIO_FILE;
		gp->stdio = 0;
		if ((gp->fp = fopen(name, arg)) == NULL)
			return (NULL);
		break;

		case GIO_MSGQ:
		return (NULL);
		break;

		default:
		return (NULL);
		break;
		}
	
	return (gp);	
}

int gclose( gp )
GIO *gp;
{
	if (gp == NULL) return;
	switch (gp->iotype)
		{
		case GIO_FILE:
		if (!(gp->stdio))
			fclose(gp->fp);
		break;

		case GIO_MSGQ:
		break;

		default:
		return (NULL);
		}

	free(gp);
	return (1);
}

int geof( gp )
GIO *gp;
{
	switch (gp->iotype)
		{
		case GIO_FILE:
		return (feof(gp->fp));

		case GIO_MSGQ:
		return (1);
		break;

		default:
		return (1);
		}
}

int gputs( s, gp )
char *s;
GIO *gp;
{
	if (gp == NULL) return;
	switch (gp->iotype)
		{
		case GIO_FILE:
		return (fputs( s, gp->fp ));

		case GIO_MSGQ:
		break;

		default:
		return (-1);
		}
}

int gread( buf, size, n, gp )
char *buf;
int size;
int n;
GIO *gp;
{
	switch (gp->iotype)
		{
		case GIO_FILE:
		return (fread(buf, size, n, gp->fp));

		case GIO_MSGQ:
		break;

		default:
		return (0);
		}
}

