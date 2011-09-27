/***************************************************************************

LIANE-TTS COMPILER - C Version
Portuguese Text To Speech Compiler for the MBROLA Synthesizer

Copyright (C) 2010 - Serpro - ServiçFederal de Processamento de Dados

    Author: Anibal Anibal de Souza Teles - NCE/UFRJ
    
    Based on the original LianeTTS synthesizer - Pascal Version
    Programmed by Joséntonio dos Santos Borges - NCE/UFRJ

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.

***************************************************************************/

#include <stdlib.h>
#include <unistd.h>
#include <stdio.h>
#include <string.h>
#include <sys/stat.h>

#include "liane-util.h"

/************************************************************************
 *                                                                      *
 * Variáveis do módulo e declaração "forward"				* 
 *                                                                      *
 ************************************************************************/

static char *filename;
static bool ready = FALSE;
static bool file_exists (char * filename);

/************************************************************************
 *                                                                      *
 * Inicia uma sessao no arquivo de log, com nome igual ao parâmetro	* 
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
 * log_write_string - Escreve um valor do tipo string no log		*
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
 * log_write_int - Escreve um valor do tipo "int" no log		*
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
 * log_write_double - Escreve um valor do tipo "double" no log		*
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
 * file_exists - Indica se o arquivo passado como parâmetro existe	*
 *                                                                      *
 ************************************************************************/

static bool file_exists (char * filename)
{
	struct stat buf;
	return (stat (filename, &buf) == 0);
}

