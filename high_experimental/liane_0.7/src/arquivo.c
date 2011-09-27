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
#include <string.h>
#include "liane-util.h"

/************************************************************************
 *                                                                      *
 * trata_crlf								*
 *                                                                      *
 ************************************************************************/

void trata_crlf (char *linha)
{
	char *ultimo_ch;
	
	ultimo_ch =  linha + strlen (linha) - 1;
	*ultimo_ch-- = '\0';	// newline
	if (*ultimo_ch == '\r') *ultimo_ch = '\0';
	str_strip (linha);
}

/************************************************************************
 *                                                                      *
 * eh_comentario							*
 *                                                                      *
 ************************************************************************/

bool eh_comentario (char *linha)
{
	return (*linha == '\0') || (*linha == ';');
}

