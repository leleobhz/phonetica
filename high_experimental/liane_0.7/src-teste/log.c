#include <stdlib.h>
#include <unistd.h>
#include <stdio.h>
#include <string.h>
#include <sys/stat.h>

#include "liane-util.h"

/************************************************************************
 *                                                                      *
 * Variáveis do módulo e declaração "forward"							* 
 *                                                                      *
 ************************************************************************/

static char *filename;
static bool ready = FALSE;
static bool file_exists (char * filename);

/************************************************************************
 *                                                                      *
 * Inicia uma sessao no arquivo de log, com nome igual ao parâmetro		* 
 *                                                                      *
 ************************************************************************/

void log_init (char *parm_filename)
{
	filename = str_concat (getenv ("HOME"), "/", parm_filename, NULL);
	if (file_exists (filename)) unlink (filename);
	ready = TRUE;
}

/************************************************************************
 *                                                                      *
 * log_write_string - Escreve um valor do tipo string no log			*
 *                                                                      *
 ************************************************************************/

void log_write_string (const char *text, const char *string)
{
	FILE    *file;
	
	if (!ready) return;
	file = fopen (filename, "a");
	fprintf (file, "%s -> %s\n", text, string); 
	fclose (file);
}

/************************************************************************
 *                                                                      *
 * log_write_int - Escreve um valor do tipo "int" no log				*
 *                                                                      *
 ************************************************************************/

void log_write_int (const char *text, const int num)
{
	FILE    *file;
	
	if (!ready) return;
	file = fopen (filename, "a");
	fprintf (file, "%s -> %d\n", text, num); 
	fclose (file);
}

/************************************************************************
 *                                                                      *
 * log_write_double - Escreve um valor do tipo "double" no log			*
 *                                                                      *
 ************************************************************************/

void log_write_double (const char *text, const double num)
{
	FILE    *file;
	
	if (!ready) return;
	file = fopen (filename, "a");
	fprintf (file, "%s -> %f\n", text, num); 
	fclose (file);
}

/************************************************************************
 *                                                                      *
 * file_exists - Indica se o arquivo passado como parâmetro existe		*
 *                                                                      *
 ************************************************************************/

static bool file_exists (char * filename)
{
	struct stat buf;
	return (stat (filename, &buf) == 0);
}

