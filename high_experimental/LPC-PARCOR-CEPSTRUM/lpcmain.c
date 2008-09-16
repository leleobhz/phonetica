#include <stdio.h>

#include "matrix.h"
#include "lpc.h"
#include "cparser.h"
#include "version.h"
#include "gio.h"


/*
*-------------------------------------------------------------------------
*	lpc parameters:
*	lpc_nsize = window size (no. of points)
*	lpc_order = order of LPC 
*	lpc_itype = input raw data type
*	lpc_cepstrum = cepstrum flag
*-------------------------------------------------------------------------
*/
int	lpc_wsize = 256;
int	lpc_wover = 0;
int	lpc_order = 8;
int	lpc_dtype = AUDIOPCM16;
int	usestdout = 0;
int	lpc_method = LPCAUTOCOR;
int	acfile = 1;
char	*ext = ".lpc";
GIO *	gin;
GIO *	gout;
GIO *	gacout;

int	usage( )
{
	printf( "\n\n\n" );
	printf( "%s %s - by %s\n", PROGNAME, VERSION, AUTHOR );
	printf( "Copyright (c) 1992 All Rights Reserved. %s\n", DATE );
	printf( "Synopsis: a LPC, PARCOR, LPC Cepstrum encoding program\n");
	printf( "Usage: %s <switches> <files>\n", PROGNAME);
	printf( "<switches> : \n" );
	printf( "\t-order=#         \t;LPC order (def=%d)\n", lpc_order );
	printf( "\t-wsize=#         \t;LPC window size (def=%d)\n", lpc_wsize );
	printf( "\t-wover=#         \t;LPC window overlap points (def=0)\n" );
	printf( "\t-dtype=ulaw8     \t;8-bit ulaw input audio data type\n" );
	printf( "\t-dtype=pcm16     \t;16-bit PCM input audio data type\n" );

	/*
	* not implemented yet
	*/
/*
	printf( "\t-msgqin=<key>   \t;input from message queue\n" );
	printf( "\t-msgqout=<key>  \t;output to message queue\n" );
*/
	printf( "\t-stdout          \t;use standard output only\n" );
	printf( "\t-noacfile        \t;do not generate .ac file\n" );
	printf( "\t                 \t;\n" );
	printf( "\t-autocor         \t;generate LPC autocorr coefficients (def)\n" );
	printf( "\t-covar           \t;generate LPC covariance coefficients\n" );
	printf( "\t-cepstrum        \t;generate LPC cepstrum coefficients\n" );
	printf( "\t-parcor          \t;generate PARCOR coefficients\n" );
	printf( "\t                 \t;(default = autocorrelation method)\n" );
	printf( "\n" );
	printf( "<files> = input audio file name(s)\n" );
	printf( "\n" );
	printf( "Note: response file @ feature is supported\n" );
	printf( "\te.g. %s @myfile.rsp\n", PROGNAME );
	exit (0);
}

int	parse( )
{
	int	cmd;
	char	rest[128];
	char	temp[128];
	int	resti, i;
	long	restl;

	while ((cmd = cmdget( rest ))!= -1)
		{
		resti = atoi(rest);
		restl = atol(rest);
		switch (cmd)
		{
		case CMD_LPCORDER:
			lpc_order = resti;
			break;
		case CMD_LPCWSIZE:
			lpc_wsize = resti;
			break;
		case CMD_LPCWOVER:
			lpc_wover = resti;
			break;
		case CMD_DTYPEU8:
			lpc_dtype = AUDIOULAW8;
			break;
		case CMD_DTYPEP16:
			lpc_dtype = AUDIOPCM16;
			break;
		case CMD_MSGQIN:
			break;
		case CMD_MSGQOUT:
			break;
		case CMD_STDOUT:
			usestdout = 1;
			break;
		case CMD_NOACFILE:
			acfile = 0;
			break;
		case CMD_AUTOCOR:
			ext = ".lpc";
			lpc_method = LPCAUTOCOR;
			break;
		case CMD_COVAR:
			ext = ".lpc";
			lpc_method = LPCCOVAR;
			break;
		case CMD_CEPSTRUM:
			ext = ".cep";
			lpc_method = LPCCEPSTRUM;
			break;
		case CMD_PARCOR:
			ext = ".par";
			lpc_method = PARCOR;
			break;
		case CMD_COMMENT:
			break;
		case CMD_NULL:
			if ((gin = gopen(GIO_FILE, rest, "rb")) == NULL)
				{
				fprintf( stderr, "file <%s> open fails\n", rest );
				break;
				}
			if (!usestdout)
				{
				i = strlen(rest);
				strcpy( temp, rest );
				strcpy( temp+i, ext );
				if ((gout= gopen(GIO_FILE, temp, "w")) == NULL)
				{
				fprintf(stderr, "file <%s> open fails\n", temp );
				break;
				}
				}
			else 
				gout = gopen(GIO_STDOUT, 0, 0);  

			if ((lpc_method == LPCAUTOCOR) && acfile)
				{
				i = strlen(rest);
				strcpy( temp, rest );
				strcpy( temp+i, ".ac" );
				if ((gacout = gopen(GIO_FILE, temp, "w")) == NULL)
				{
				fprintf(stderr, "file <%s> open fails\n", temp );
				break;
				}
				}
			else
				gacout = NULL;

			lpc(gin, lpc_dtype, gout, gacout, lpc_order, lpc_wsize, lpc_wover, lpc_method );

			gclose(gin);
			gclose(gout);
			gclose(gacout);
			break;
			}
		}
}

int	main( argc, argv )
int	argc;
char	** argv;
{

	if (argc < 2)
		{
		usage();
		}
	else
		{
		cmdinit( argc, argv );
		parse();
		}

}


