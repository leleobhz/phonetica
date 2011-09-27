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

#include "ttsinic.h"
#include "ttsexcessoes.h"
#include "ttstonica.h"

/************************************************************************
 *                                                                      *
 * Variaveis globais							*
 *                                                                      *
 ************************************************************************/

REGRA	   *pt_aux;

char		*pos_i;
char		*pos_f;
char		*pt_palavra;

/************************************************************************
 *                                                                      *
 * fora_limite_palavra							*
 *                                                                      *
 ************************************************************************/

bool fora_limite_palavra (char *palavra)
{
	return (pt_palavra < palavra) || (pt_palavra > pos_f);
}

/************************************************************************
 *                                                                      *
 * eh_vogal								*
 *                                                                      *
 ************************************************************************/

bool eh_vogal (char *palavra)
{
	if ((pt_palavra < palavra) || (pt_palavra > pos_f)) {
		return FALSE;
	}

	return EH_VOGAL (*pt_palavra);
}

/************************************************************************
 *                                                                      *
 * eh_a_ou_o								*
 *                                                                      *
 ************************************************************************/

bool eh_a_ou_o (char *palavra)
{
	if ((pt_palavra < palavra) || (pt_palavra > pos_f)) {
		return FALSE;
	}

	return EH_A_ou_O (*pt_palavra);
}

/************************************************************************
 *                                                                      *
 * eh_vogal_ou_inic_palavra						*
 *                                                                      *
 ************************************************************************/

bool eh_vogal_ou_inic_palavra (char *palavra)
{
	if ((pt_palavra < palavra) || (pt_palavra > pos_f)) {
		return FALSE;
	}

	if (pt_palavra == palavra) return TRUE;
	
	return EH_VOGAL (*pt_palavra);
}

/************************************************************************
 *                                                                      *
 * eh_antecessor_l							*
 *                                                                      *
 ************************************************************************/

bool eh_antecessor_l (char *palavra)
{
	if ((pt_palavra <= palavra) || (pt_palavra > pos_f)) {
		return FALSE;
	}
	
	return EH_N_R_ou_S (*pt_palavra);
}

/************************************************************************
 *                                                                      *
 * em_outra_silaba							*
 *                                                                      *
 ************************************************************************/

bool em_outra_silaba (char *palavra)
{
	if ((pt_palavra <= pos_f) &&
		(!EH_CONSOANTE (*pt_palavra) || EH_H (*pt_palavra))) {
			 return FALSE;
	}

	return TRUE;
}

/************************************************************************
 *                                                                      *
 * eh_consoante_muda							*
 *                                                                      *
 ************************************************************************/

bool eh_consoante_muda (char *palavra)
{
	if ((pt_palavra < palavra) || (pt_palavra > pos_f))
	{
		return FALSE;
	}

	return EH_CONSOANTE (*pt_palavra) && (!EH_R_ou_L (*pt_palavra));
}

/************************************************************************
 *                                                                      *
 * eh_e_ou_i								*
 *                                                                      *
 ************************************************************************/

bool eh_e_ou_i (char *palavra)
{
	if ((pt_palavra < palavra) || (pt_palavra > pos_f))
	{
		return FALSE;
	}

	return EH_E_ou_I (*pt_palavra);
}

/************************************************************************
 *                                                                      *
 * eh_s									*
 *                                                                      *
 ************************************************************************/

bool eh_s (char *palavra)
{
	if ((pt_palavra < palavra) || (pt_palavra > pos_f))
	{
		return FALSE;
	}

	return EH_S (*pt_palavra);
}

/************************************************************************
 *                                                                      *
 * testa_lmnrz								*
 *                                                                      *
 ************************************************************************/

bool eh_lmnrz (char *palavra)
{
	if ((pt_palavra < palavra) || (pt_palavra > pos_f))
	{
		return FALSE;
	}

	return EH_L_M_N_R_ou_Z (*pt_palavra);
}

/************************************************************************
 *                                                                      *
 * contexto_a_esquerda_satisfaz						*
 *                                                                      *
 ************************************************************************/

bool contexto_a_esquerda_satisfaz (char *palavra)
{
	char		*marca_anterior;
	char		*pt_contexto;
	bool	aceito;
	
	pt_palavra = pos_i - 1;
	marca_anterior = pt_aux->contexto_a_esquerda - 1;
	pt_contexto = pt_aux->contexto_a_esquerda +
			strlen (pt_aux->contexto_a_esquerda) - 1;
	
	aceito = TRUE;
	while (aceito && (pt_contexto > marca_anterior)) {
		switch (*pt_contexto) {
			case '%': 
				aceito = fora_limite_palavra (palavra);
				break;
			case '#': 
				aceito = eh_vogal (palavra);
				break;
			case ']':
				aceito = eh_a_ou_o (palavra);
				break;
			case '_':
				aceito = eh_vogal_ou_inic_palavra (palavra);
				break;
			case '|':
				aceito = eh_antecessor_l (palavra);
				break;
			default:
				if (pt_palavra < palavra) return FALSE;
				if (*pt_contexto != *pt_palavra) return FALSE;
		}
		
		if (aceito) pt_palavra--;
		pt_contexto--;
	}
	
	return aceito;
}

/************************************************************************
 *                                                                      *
 * contexto_a_direita_satisfaz						*
 *                                                                      *
 ************************************************************************/

bool contexto_a_direita_satisfaz (char *palavra)
{
	char		*pt_contexto;
	bool	aceito;

	pt_palavra = pos_i + strlen (pt_aux->contexto);
	pt_contexto = pt_aux->contexto_a_direita;

	aceito = TRUE;	
	while (aceito && (*pt_contexto != '\0')) {
		switch (*pt_contexto) {
			case '[': 
				aceito = em_outra_silaba (palavra);
				break;
			case '*': 
				aceito = eh_consoante_muda (palavra);
				break;
			case '+':
				aceito = eh_e_ou_i (palavra);
				break;
			case '%':
				aceito = fora_limite_palavra (palavra);
				break;
			case '#':
				aceito = eh_vogal (palavra);
				break;
			case '\\':
				aceito = eh_s (palavra);
				if (!aceito) {
					pt_contexto++;
					aceito = TRUE;
					continue;
				}
				break;
			case '&':
				aceito = eh_lmnrz (palavra);
				break;
			default:
				if (pt_palavra > pos_f) return FALSE;
				if (*pt_contexto != *pt_palavra) return FALSE;
		}
		if (aceito) pt_palavra++;
		pt_contexto++;
	}
	
	return aceito;
}

/************************************************************************
 *                                                                      *
 * contexto_satisfaz							*
 *                                                                      *
 ************************************************************************/

bool contexto_satisfaz (char *palavra)
{
	char	*pt_contexto;
	char	*pt_palavra;
	
	pt_contexto = pt_aux->contexto;
	pt_palavra = pos_i;
	
	while (*pt_contexto != '\0') {
		if ((pt_palavra > pos_f) || (*pt_contexto != *pt_palavra)) {
			return FALSE;
		}
		else {
			pt_contexto++;
			pt_palavra++;
		}
	}
		   
	return TRUE;
}

/************************************************************************
 *                                                                      *
 * traduz								*
 *                                                                      *
 ************************************************************************/

void traduz (char *palavra, char *tonica, DList **fonemas)
{
	unsigned char	*pont;
	char			seq_fonemas[12];
	char			*pt_fon;
	int				ind_regra;
	char			f[TAM_PALAVRA];
	bool			satisfeito;
	
	pos_i = &palavra[0];
	pos_f = &palavra[strlen (palavra) - 1];
	f[0] = '\0';
	if (tonica == NULL) tonica = palavra + strlen (palavra);

	while (pos_i <= pos_f) {
		pont = (unsigned char *)pos_i;
		ind_regra = *pont - PRIMEIRO_CHAR;
		pt_aux = ((ind_regra < 0) || (ind_regra > 235)) ? NULL :
			regras[ind_regra];
		satisfeito = FALSE;
		while ((!satisfeito) && (pt_aux != NULL)) {
			satisfeito = contexto_satisfaz (palavra);
			satisfeito &= contexto_a_esquerda_satisfaz (palavra);
			satisfeito &= contexto_a_direita_satisfaz (palavra);
			if (!satisfeito) {
				pt_aux = pt_aux->prox;
			}
		}

		if (satisfeito) {
			strcpy (seq_fonemas, pt_aux->fonemas);
			pt_fon = &seq_fonemas[0];


			while (*pt_fon != '\0') {
				if (pos_i >= tonica) {
					f[strlen (f) + 1] = '\0';
					f[strlen (f)] = '>';
					tonica = palavra + strlen (palavra);
				}
				
				if (*pt_fon == '&') {
					*fonemas = dlist_prepend (*fonemas, strdup (f));
					f[0] = '\0';
				}
				else {
					f[strlen (f) + 1] = '\0';
					f[strlen (f)] = *pt_fon;
				}
				pt_fon++;
			}
			
			if (strlen (seq_fonemas) > 0) {
				*fonemas = dlist_prepend (*fonemas, strdup (f));
				f[0] = '\0';
			}
		}
		
		pos_i += (pt_aux != NULL) ? strlen (pt_aux->contexto) : 1;
	}
	
	*fonemas = dlist_prepend (*fonemas, strdup (f));
}

/************************************************************************
 *                                                                      *
 * pausa_pontuacao							*
 *                                                                      *
 ************************************************************************/

void pausa_pontuacao (char ch, DList **fonemas)
{
	switch (ch) {
		case ',':
			*fonemas = dlist_prepend (*fonemas, strdup ("_,"));
			break;
		case ':':
			*fonemas = dlist_prepend (*fonemas, strdup ("_:"));
			break;
		case ';':
			*fonemas = dlist_prepend (*fonemas, strdup ("_;"));
			break;
		case '.':
			*fonemas = dlist_prepend (*fonemas, strdup ("_."));
			break;
		case '(':
			*fonemas = dlist_prepend (*fonemas, strdup ("_("));
			break;
		case ')':
			*fonemas = dlist_prepend (*fonemas, strdup ("_)"));
			break;
		case '!':
			*fonemas = dlist_prepend (*fonemas, strdup ("_!"));
			break;
		case '?':
			*fonemas = dlist_prepend (*fonemas, strdup ("_?"));
			break;
	}
}

/************************************************************************
 *                                                                      *
 * destonifica								*
 *                                                                      *
 ************************************************************************/

bool destonifica (char *palavra)
{
	return
		(strlen (palavra) < 3) ||
		(strcmp (palavra, "por") == 0) ||
		(strcmp (palavra, "das") == 0) ||
		(strcmp (palavra, "dos") == 0) ||
		(strcmp (palavra, "com") == 0) ||
		(strcmp (palavra, "não") == 0) ||
		(strcmp (palavra, "sim") == 0);
}

/************************************************************************
 *                                                                      *
 * coarticula								*
 *                                                                      *
 ************************************************************************/

void coarticula (DList *fonemas)
{
	char	*s;
	char	*s2;
	DList  *anterior;
	DList  *atual;
	
	atual = dlist_nth (fonemas, 3);
	while (atual != NULL) {
		s = (char *)atual->data;
		if (*s == ';') {
			atual = dlist_next (atual);
			s = (char *)atual->data;
			if (EH_VOGAL_W_ou_Y (*s)) {
				anterior = dlist_nth_prev (atual, 3);
				s2 = (char *)anterior->data;
				if (strcmp (s2, "r2") == 0) strcpy (s2, "r");
				else if (strcmp (s2, "s2") == 0) strcpy (s2, "z");
			}
		}
		atual = dlist_next (atual);
	}
}

/************************************************************************
 *                                                                      *
 * inic_tradutor							*
 *                                                                      *
 ************************************************************************/

bool inic_tradutor (char *nome_arq_regras, char *nome_arq_excessoes)
{
	bool ok;
	
	ok = inic_vars_tradutor (nome_arq_regras);
	if (ok) ok = carrega_excessoes (nome_arq_excessoes);

	return ok;
}

/************************************************************************
 *                                                                      *
 * fim_tradutor								*
 *                                                                      *
 ************************************************************************/

void fim_tradutor ()
{
	lib_mem_regras ();
	lib_mem_excessoes ();
}

/************************************************************************
 *                                                                      *
 * compila_fonemas							*
 *                                                                      *
 ************************************************************************/

DList *compila_fonemas (SList *texto_marcado)
{
	char	*tonica;
	char	palavra[TAM_PALAVRA];
	DList   *fonemas;
	
	fonemas = NULL;
	
	while (texto_marcado != NULL) {
		fonemas = dlist_prepend (fonemas,
						str_concat (";", (char *)texto_marcado->data, NULL));

		if ((strlen ((char *)texto_marcado->data)) == 4) {
			fonemas = dlist_prepend (fonemas, strdup ("__"));
			fonemas = dlist_prepend (fonemas, strdup (""));
			texto_marcado = slist_next (texto_marcado);
			continue;
		}
		strcpy (palavra,  ((char *)texto_marcado->data) + 4);
		str_tolower (palavra);
		if (EH_DELIMITADOR (*palavra)) {
			pausa_pontuacao (*palavra, &fonemas);
		}
		else {
			trata_excessoes (palavra);
			if (destonifica (palavra)) {
				tonica = palavra - 1;
			}
			else {
				tonica = descobre_tonica (palavra);
				if ((tonica > palavra) && EH_CONSOANTE (*tonica)) {
					tonica--;
				}
			}
			traduz (palavra, tonica, &fonemas);
		}
		
		texto_marcado = slist_next (texto_marcado);
	}
	fonemas = dlist_prepend (fonemas, strdup ("__"));
	fonemas = dlist_reverse (fonemas);
	coarticula (fonemas);
	
	return fonemas;
}

