#include <stdlib.h>
#include <string.h>
#include <stdio.h>
#include "liane-util.h"

#include "ttsexcessoes.h"
#include "arquivo.h"

/************************************************************************
 *                                                                      *
 * Constantes, tipos e variáveis										*
 *                                                                      *
 ************************************************************************/

int n_excessoes = 0;

PALAVRA_ALIAS tab_excessoes[MAX_EXCESSOES];

/************************************************************************
 *                                                                      *
 * compara																*
 *                                                                      *
 ************************************************************************/

static int compara (const void *exc1, const void *exc2)
{
	PALAVRA_ALIAS *pa1 = (PALAVRA_ALIAS *)exc1;
	PALAVRA_ALIAS *pa2 = (PALAVRA_ALIAS *)exc2;
	
	return (strcmp (pa1->palav, pa2->palav));
}

/************************************************************************
 *                                                                      *
 * carrega_excessoes													*
 *                                                                      *
 ************************************************************************/

bool carrega_excessoes (char *nome_arq)
{
	FILE	*arq_exc;
	char	linha[TAM_LINHA];
	char	palavra[TAM_PALAVRA];
	char	alias[TAM_PALAVRA];
	char	*pos_igual;

	if ((arq_exc = fopen (nome_arq, "r")) == NULL) return FALSE;
	
	n_excessoes = 0;
	
	while ((fgets (linha, TAM_LINHA, arq_exc)) != NULL) {
		trata_crlf (linha);
		if (eh_comentario (linha)) continue;
		
		palavra[0] = '\0';
		alias[0] = '\0';

		str_tolower (linha);

		pos_igual = strchr (linha, '=');
		if (pos_igual != NULL) {
			*pos_igual = '\0';
			strcpy (palavra, linha);
			strcpy (alias, pos_igual + 1);
		}

		tab_excessoes[n_excessoes].palav = strdup (palavra);
		tab_excessoes[n_excessoes].alias = strdup (alias);
		n_excessoes++;
	}
	
	fclose (arq_exc);
	
	qsort (tab_excessoes, n_excessoes, sizeof (PALAVRA_ALIAS), compara);

	return TRUE;
}

/************************************************************************
 *                                                                      *
 * lib_mem_excessoes													*
 *                                                                      *
 ************************************************************************/

void lib_mem_excessoes ()
{
	int ind_exc;
	
	for (ind_exc = 0; ind_exc < n_excessoes; ind_exc++) {
		free (tab_excessoes[ind_exc].palav);
		free (tab_excessoes[ind_exc].alias);
	}
	n_excessoes = 0;
}

/************************************************************************
 *                                                                      *
 * trata_excessoes														*
 *                                                                      *
 ************************************************************************/

void trata_excessoes (char *palavra)
{
	char			*letra_final;
	bool		procura;
	PALAVRA_ALIAS   elemento, *ret;
	char			removidos[TAM_SILABA];
	char			aux[TAM_SILABA];
	
	removidos[0] = '\0';
	elemento.palav = strdup (palavra);
	letra_final = elemento.palav + strlen (elemento.palav) - 1;
	procura = TRUE;
	while (procura) {
		procura = FALSE;
		ret = bsearch (&elemento, tab_excessoes, n_excessoes,
					   sizeof (PALAVRA_ALIAS), compara);

		if (ret != NULL) {
			strcpy (palavra, ret->alias);
			strcat (palavra, removidos); 
			break;
		}
		
		if (letra_final < elemento.palav) break;
		
		if ((*letra_final == 's') || (*letra_final == 'm')) {
			aux[0] = *letra_final;
			strcpy (&aux[1], removidos);
			strcpy (removidos, aux);
			*letra_final-- = '\0';
			procura = TRUE;
		}
	}
	free (elemento.palav);
}

