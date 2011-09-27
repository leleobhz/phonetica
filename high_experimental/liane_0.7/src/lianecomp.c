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

#include <stdio.h>
#include <string.h>
#include <unistd.h>
#include <getopt.h>
#include <iconv.h>

#include "liane-util.h"
#include "liane-comp.h"
#define MAX_LEN 256


/************************************************************************
 *									*
 *  Converte caracteres para ISO-8859-1.                                *
 *									*
 ************************************************************************/

void utfToISO (char *s)
{
    int b, b2;
    char *s2;

    s2 = s;
    while (*s) {
        b = (int)*s++;
        if ((b & 0xe0) != 0xc0)
           *s2++ = (char)b;
        else  {
            b2 = (int)*s++ & 0x3f;
            b = (b & 0x03) << 6;
            *s2++ =  (char)(b | b2);
        }
    }
    *s2 = '\0';
}

/************************************************************************
 *									*
 *  Remove fim de linha.                                                *
 *									*
 ************************************************************************/

void estripa (char *str_buf)
{
	int num_ch;

if (str_buf [0] == ';') str_buf [0] = '\0';

        num_ch = strlen (str_buf);
        if (str_buf [num_ch-1] == '\n') num_ch--;
        if (str_buf [num_ch-1] == '\r') num_ch--;
        str_buf [num_ch] = '\0';
}

/************************************************************************
 *									*
 *  Dá saída no arquivo.                                                *
 *									*
 ************************************************************************/

void saiArquivo (char *nome)
{
    FILE *arq;
    char str_buf[MAX_LEN+2];
    
    arq = fopen (nome, "r");
    while(fgets(str_buf, MAX_LEN + 1, arq) != NULL) {
        estripa(str_buf);
        puts (str_buf);
    }
    fclose (arq);     
}

/************************************************************************
 *									*
 *  Programa principal.                                                 *
 *									*
 ************************************************************************/

int main ()
{
    char str_buf[MAX_LEN+2];
    char temp[20];
    char pid[6];

/* inicializa o compilador, gerando arquivo temporário */

// log_init ("lianetts.log");

    sprintf (pid, "%05d", (int)getpid ());
    strcpy (temp, "/tmp/");
    strcat (temp, pid);
    strcat (temp, ".pho");

    liane_inicia_compilador ("liane", temp);

/* le stdin, gerando arquivo temporário com fonemas */

    while(fgets(str_buf, MAX_LEN + 1, stdin) != NULL)  {
        estripa (str_buf);
        utfToISO (str_buf); 
        liane_compila (str_buf);

        saiArquivo (temp);    // pois arquivo temporário é apagado a cada linha
    }

    liane_termina_compilador();
    unlink (temp);
    return 0;
}

