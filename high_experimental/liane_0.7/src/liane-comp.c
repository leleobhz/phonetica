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
#include <stdio.h>
#include <string.h>
#include "liane-util.h"

#include "ttsportug.h"
#include "ttspreproc.h"
#include "ttsprosodia.h"

/************************************************************************
 *                                                                      *
 *  Variáveis globais                                                   *
 *                                                                      *
 ************************************************************************/

static char		*voz_liane;
static char		nome_arquivo_difones[256];

/************************************************************************
 *                                                                      *
 *  Função: liane_inicia_compilador                                     *
 *                                                                      *
 *  Descrição: Inicia a biblioteca para receber falas                   *
 *                                                                      *
 ************************************************************************/

bool liane_inicia_compilador (char *voice, char *parm_nome_arquivo)
{
	char	*voz;
	char	*arq_abrevs;
	char	*arq_difones;
	char	*arq_excessoes;
	char	*arq_regras;
	char	*arq_prosodia;

	voz = str_lower (voice);
	voz_liane = str_concat (LIANE_DIR, "/", voz, NULL);
	free (voz);

	log_write_string ("voz_liane", voz_liane);
	
	strcpy (nome_arquivo_difones, parm_nome_arquivo);
	log_write_string ("nome_arquivo_difones", nome_arquivo_difones);

	arq_abrevs	= str_concat (voz_liane, "/", "lianetts.abr", NULL);
	arq_difones	= str_concat (voz_liane, "/", "lianetts.dfn", NULL);
	arq_excessoes   = str_concat (voz_liane, "/", "lianetts.exc", NULL);
	arq_regras	= str_concat (voz_liane, "/", "lianetts.nrl", NULL);
	arq_prosodia	= str_concat (voz_liane, "/", "lianetts.pro", NULL);

	if (!inic_tradutor (arq_regras, arq_excessoes)) {
		fprintf (stderr, "Erro na base de dados do compilador\n");
		return FALSE;
	}

	if (!inic_abrev (arq_abrevs)) {
		fprintf (stderr, "Erro no arquivo de abreviaturas\n");
		return FALSE;
	}

	if (!inic_prosodia (arq_prosodia)) {
		fprintf (stderr, "Erro no arquivo de prosódia\n");
		return FALSE;
	}

	if (!inic_lista_difones (arq_difones)) {
		fprintf (stderr, "Erro na lista de difones\n");
		return FALSE;
	}

	free (arq_abrevs);
	free (arq_difones);
	free (arq_excessoes);
	free (arq_regras);
	free (arq_prosodia);

	return TRUE;
}

/************************************************************************
 *                                                                      *
 *  Função: liane_compila                                               *
 *                                                                      *
 *  Descrição: Gera arquivo de difones para um texto                    *
 *                                                                      *
 ************************************************************************/

void imprime_slist (SList *lista, char *cabecalho)
{
	puts (cabecalho);
	while (lista) {
		puts (lista->data);
		lista = lista->next;
	}
}

void imprime_dlist (DList *lista, char *cabecalho)
{
	puts (cabecalho);
	while (lista) {
		puts (lista->data);
		lista = lista->next;
	}
}

bool liane_compila (char *texto)
{
	FILE	*arq_difones;
	DList	*fonemas;
	SList	*palavras_com_codigos;
	SList	*palavras_com_prosodia;
	SList	*mbrola_pho;
	SList	*saux;
	char	*texto_expandido;
	

//printf ("texto_original = %s\n", texto);

	log_write_string ("compila", "pre_processa");
	texto_expandido = pre_processa (texto);

//printf ("texto_expandido = %s\n", texto_expandido);
	
	log_write_string ("compila", "pre_prosodia");
	palavras_com_codigos = pre_prosodia (texto_expandido);
	free (texto_expandido);

//imprime_slist (palavras_com_codigos, "palavras_com_codigos");
	
	log_write_string ("compila", "calcula_curva_prosodia");
	palavras_com_prosodia = calcula_curva_prosodia (palavras_com_codigos, TRUE);
	slist_foreach (palavras_com_codigos, free, NULL);
	slist_free (palavras_com_codigos);

//imprime_slist (palavras_com_prosodia, "palavras_com_prosodia");
	
	log_write_string ("compila", "compila_fonemas");
	fonemas = compila_fonemas (palavras_com_prosodia);
	slist_foreach (palavras_com_prosodia, free, NULL);
	slist_free (palavras_com_prosodia);

//imprime_dlist (fonemas, "fonemas");
	
	log_write_string ("compila", "aplica_prosodia");
	mbrola_pho = aplica_prosodia (fonemas, 1.0, 1.0);
	dlist_foreach (fonemas, free, NULL);
	dlist_free (fonemas);

//imprime_slist (mbrola_pho, "mbrola_pho");

	if ((arq_difones = fopen (nome_arquivo_difones, "w")) == NULL) {
		fprintf (stderr, "Nao consegui abrir %s\n", nome_arquivo_difones);
		return FALSE;
	}
	saux = mbrola_pho;
	while (saux != NULL) {
		fprintf (arq_difones, "%s\n", (char *)saux->data);
		saux = slist_next (saux);
	}
	fclose (arq_difones);

	slist_foreach (mbrola_pho, free, NULL);
	slist_free (mbrola_pho);

	return TRUE;
}

/************************************************************************
 *                                                                      *
 *  Função: liane_termina_compilador                                    *
 *                                                                      *
 *  Descrição: Libera recursos alocados para a compilacao               *
 *                                                                      *
 ************************************************************************/

bool liane_termina_compilador ()
{
	fim_tradutor ();
	fim_abrev ();
	fim_prosodia ();
	fim_lista_difones ();
	
	return TRUE;
}

