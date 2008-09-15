/*
*-----------------------------------------------------------------------------
*	file:	cparser.c
*	desc:	simple command parser
*	by:	patrick ko
*	date:	22 aug 91
*	revi:	26 feb 92 ; response file feature
*-----------------------------------------------------------------------------
*/
#include <stdio.h>
#include <stdlib.h>

#ifdef __TURBOC__
#include <mem.h>
#include <ctype.h>
#endif

#include "cparser.h"

CMDTBL	cmdtbl[] =
	{
	{CMD_LPCORDER,	"-order="},
	{CMD_LPCWSIZE,	"-wsize="},
	{CMD_LPCWOVER,	"-wover="},
	{CMD_DTYPEU8,	"-dtype=ulaw8"},
	{CMD_DTYPEP16,	"-dtype=pcm16"},
	{CMD_MSGQIN,	"-msgqin="},
	{CMD_MSGQOUT,	"-msgqout="},
	{CMD_STDOUT,	"-stdout"},
	{CMD_NOACFILE,	"-noacfile"},
	{CMD_AUTOCOR,	"-autocor"},
	{CMD_COVAR,	"-covar"},
	{CMD_CEPSTRUM,	"-cepstrum"},
	{CMD_PARCOR,	"-parcor"},
	{CMD_COMMENT,	"//"}
	};

static int	cmdtblsize = 0;
static int	cmdargc = 0;
static int	cmdcnt = 0;
static char	**cmdargv;
static FILE 	*frsp = NULL;
int cmdsearch( str, rest )
char	*str;
char	*rest;
{
	int	i, l;
	
	for (i=0; i<cmdtblsize; i++)
		{
		l = strlen( cmdtbl[i].cmdstr );
		if (!memcmp(str, cmdtbl[i].cmdstr, l))
			{
			strcpy( rest, str + l );
			return (cmdtbl[i].cmdno);
			}
		}
	strcpy( rest, str );
	return (CMD_NULL);
}

int cmdinit( argc, argv )
int	argc;
char	**argv;
{
	cmdtblsize = sizeof(cmdtbl) / sizeof(cmdtbl[0]);
	cmdargc = argc;
	cmdargv = argv;
}

int cmdget( rest )
char 	*rest;
{
	int	i, j;
	char	nstr[129];
	char	*rspname;

	if ((cmdcnt >= cmdargc - 1) && (frsp == NULL))
		{
		return (-1);
		}
	else
		{
		/* test for response file */
		if (frsp == NULL)
			{
			rspname = *(cmdargv + cmdcnt + 1);
			if (*rspname == '@')
				{
				rspname++;
				cmdcnt++;
				if ((frsp = fopen(rspname, "r")) == NULL)
				{
				fprintf(stderr, "rsp file open fails\n");
				exit (1);
				}
				}
			}
		if (frsp != NULL)
			{
			*nstr = 0;
			for (;!strlen(nstr)&&!feof(frsp);)
				{
				fgets(nstr, 128, frsp);
				for (i=j=0; i<strlen(nstr); i++)
					{
					if (!isspace(nstr[i]))
						{
						nstr[j++] = nstr[i];
						}
					}
				nstr[j] = 0;
				}
			if (strlen(nstr))
				return (cmdsearch( nstr, rest ));
			fclose(frsp);
		 	frsp = NULL;
			if (cmdcnt >= cmdargc - 1) return (-1);
			}	
		return (cmdsearch( *(cmdargv + ++cmdcnt), rest ));
		}
}
