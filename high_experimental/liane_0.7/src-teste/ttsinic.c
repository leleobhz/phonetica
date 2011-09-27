#include <stdlib.h>
#include <stdio.h>
#include <string.h>
#include "liane-util.h"

#include "arquivo.h"
#include "ttsinic.h"

/************************************************************************
 *                                                                      *
 * Vetor de regras														*
 *                                                                      *
 ************************************************************************/

REGRA *regras[NUM_CHARS];

/************************************************************************
 *                                                                      *
 * inic_vars_tradutor													*
 *                                                                      *
 ************************************************************************/

REGRA *extrai_campos (char *linha)
{
	char	string[TAM_PALAVRA];
	char	*pt_linha;
	char	*pt_fim;
	char	*pt_string;	
	REGRA	*nova_regra = malloc (sizeof (REGRA));

	pt_linha = linha;
	pt_fim = linha + strlen (linha) - 1;

	pt_string = &string[0];
	while (pt_linha < pt_fim) {
		if (*pt_linha == '(') {
			pt_linha++;
			break;
		}

		*pt_string++ = *pt_linha++;
	}
	*pt_string = '\0';
	nova_regra->contexto_a_esquerda = strdup (string);

	pt_string = &string[0];
	while (pt_linha < pt_fim) {
		if (*pt_linha == ')') {
			pt_linha++;
			break;
		}
		*pt_string++ = *pt_linha++;
	}
	*pt_string = '\0';
	nova_regra->contexto = strdup (string);

	pt_string = &string[0];
	while (pt_linha < pt_fim) {
		if (*pt_linha == '=') {
			pt_linha++;
			break;
		}
		*pt_string++ = *pt_linha++;
	}
	*pt_string = '\0';
	nova_regra->contexto_a_direita = strdup (string);

	pt_string = &string[0];
	while (pt_linha < pt_fim) {
		if (*pt_linha == '|') {
			pt_linha++;
			break;
		}
		*pt_string++ = *pt_linha++;
	}
	*pt_string = '\0';
	nova_regra->fonemas = strdup (string);

	nova_regra->prox = NULL;

	return nova_regra;
}

/************************************************************************
 *                                                                      *
 * inic_vars_tradutor													*
 *                                                                      *
 ************************************************************************/

bool inic_vars_tradutor (char *nome_arq)
{
	int		ind_regra;
	REGRA   *pt_regra;
	REGRA   *pt_aux;
	FILE	*arq_regras;
	char	linha[TAM_LINHA];
	
	for (ind_regra = PRIMEIRO_CHAR; ind_regra <= ULTIMO_CHAR; ind_regra++) {
		regras[ind_regra] = NULL;
	}

	if ((arq_regras = fopen (nome_arq, "r")) == NULL) {
		return FALSE;
	}
	
	while ((fgets (linha, TAM_LINHA, arq_regras)) != NULL) {
		trata_crlf (linha);
		if (eh_comentario (linha)) continue;

		str_tolower (linha);
		pt_regra = extrai_campos (linha);
		ind_regra = (unsigned char)pt_regra->contexto[0] - PRIMEIRO_CHAR;

		if (regras[ind_regra] == NULL) {
			regras[ind_regra] = pt_regra;
		}
		else {
			pt_aux = regras[ind_regra];
			while (pt_aux->prox != NULL) pt_aux = pt_aux->prox;
			pt_aux->prox = pt_regra;
		}
	}
	
	fclose (arq_regras);
	return TRUE;
}

/************************************************************************
 *                                                                      *
 * lib_mem_regras														*
 *                                                                      *
 ************************************************************************/

void lib_mem_regras ()
{
	REGRA   *preg, *prox;
	int	ind_regra;
	
	for (ind_regra = 0; ind_regra < NUM_CHARS; ind_regra++) {
		preg = regras[ind_regra];
		while (preg != NULL) {
			prox = preg->prox;
			free (preg->contexto_a_esquerda);
			free (preg->contexto);
			free (preg->contexto_a_direita);
			free (preg->fonemas);

			free (preg);
			regras[ind_regra] = NULL;
			preg = prox;
		}
	}
}
