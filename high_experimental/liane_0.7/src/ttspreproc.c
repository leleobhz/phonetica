/***************************************************************************

LIANE-TTS COMPILER - C Version
Portuguese Text To Speech Compiler for the MBROLA Synthesizer

Copyright (C) 2010 - Serpro - Servi�Federal de Processamento de Dados

    Author: Anibal Anibal de Souza Teles - NCE/UFRJ
    
    Based on the original LianeTTS synthesizer - Pascal Version
    Programmed by Jos�ntonio dos Santos Borges - NCE/UFRJ

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

#include "arquivo.h"

/************************************************************************
 *                                                                      *
 * Macros								*
 *                                                                      *
 ************************************************************************/

#define E_LETRA(x) ((x >= 0x20) && (x <= 0x7e))		  /* ' ' a '~' */
#define E_LETRA_ACENTUADA(x) ((x >= 0xc0) && (x <= 0xdc)) /* '�' a '�' */
#define E_LETRA_ESPECIAL(x) ((x >= 0xa1) && (x <= 0xbf))  /* '�' a '�' */

/************************************************************************
 *                                                                      *
 * Tipos e vari�veis							*
 *                                                                      *
 ************************************************************************/

const char *tab_unid[] = {
	"zero", "um", "dois", "tres", "quatro",
	"cinco", "seis", "sete", "oito", "nove"
};

const char *tab_dez[] = {
	"dez", "onze", "doze", "treze", "quatorze",
	"quinze", "dezeseis", "dezessete", "dezoito", "dezenove"
};

const char *tab_dezena[] = {
	"vinte", "trinta", "quarenta", "cinquenta",
	"sessenta", "setenta", "oitenta", "noventa"
};

const char *tab_centena[] = {
	"cem", "cento", "duzentos", "trezentos", "quatrocentos",
	"quinhentos", "seiscentos", "setecentos", "oitocentos", "novecentos"
};

const char *tab_mil[] = {
	"trilh�o", "bilh�o", "milh�o", "mil", ""
};

const char *tab_mils[] = {
	"trilh�es", "bilh�es", "milh�es", "mil", ""
};

const char *tab_ordinais_1[] = {
	"primeiro", "segundo", "terceiro", "quarto", "quinto",
	"sexto", "s�timo", "oitavo", "nono"
};

const char *tab_ordinais_10[] = {
	"d�cimo", "vig�simo", "trig�simo", "quadrag�simo", "quinquag�simo",
	"sexag�simo", "septuag�simo", "octag�simo", "nonag�simo"
};

const char *tab_ordinais_100[] = {
	"cent�simo", "ducent�simo", "tricent�simo", "quadringent�simo",
	"quingent�simo", "sexcent�simo", "septingent�simo",
	"octingent�simo", "nongent�simo"
};

const char *milesimo = "mil�simo";

const char *e = " e ";

#define PL ' '
const char *nome_letra[] = {
	/* ' ' */   "espa�o",
	/* '!' */   "exclama��o",
	/* '"' */   "aspas",
	/* '#' */   "sustenido",
	/* '$' */   "cifr�o",
	/* '%' */   "porcento",
	/* '&' */   "� comercial",
	/* ''' */   "ap�strofo",
	/* '(' */   "abre par�nteses",
	/* ')' */   "fecha par�nteses",
	/* '*' */   "asterisco",
	/* '+' */   "mais",
	/* ',' */   "v�rgula",
	/* '-' */   "tra�o",
	/* '.' */   "ponto",
	/* '/' */   "barra",
	
	"z�ro", "um", "dois", "tr�s", "quatro",
	"cinco", "seis", "s�te", "oito", "n�ve",
	
	/* ':' */   "dois pontos",
	/* ';' */   "ponto e v�rgula",
	/* '<' */   "menor que",
	/* '=' */   "igual",
	/* '>' */   "maior que",
	/* '?' */   "interroga��o",
	/* '@' */   "arr�ba",
	
	"a", "b�", "s�", "d�", "�", "�fe", "j�", "ag�", "i", "j�ta",
	"c�", "�le", "�me", "�ne", "�", "p�", "qu�", "�rre", "�sse",
	"t�", "u", "v�", "d�bliu", "xis", "ipsilom", "z�",
	
	/* '[' */   "abre colchete",
	/* '\' */   "barra invertida",
	/* ']' */   "fecha colchete",
	/* '^' */   "acento circunflexo",
	/* '_' */   "sublinhado",
	/* '`' */   "acento grave",
	
	"a", "b�", "s�", "d�", "�", "�fe", "j�", "ag�", "i", "j�ta",
	"c�", "�le", "�me", "�ne", "�", "p�", "qu�", "�rre", "�sse",
	"t�", "u", "v�", "d�bliu", "xis", "ipsilom", "z�",

	/* '{' */   "abre chave",
	/* '|' */   "barra vertical",
	/* '}' */   "fecha chave",
	/* '~' */   "til"
};

#define PLA '�'
const char *nome_letra_acentuada[] = {
	/* '�' */   "A grave",
	/* '�' */   "A agudo",
	/* '�' */   "A circunflexo",
	/* '�' */   "A com til",
	/* '�' */   "A com trema",
	/* '�' */   "A bola",
	/* '�' */   "A com �",
	/* '�' */   "C� cedilha",
	/* '�' */   "� grave",
	/* '�' */   "� agudo",
	/* '�' */   "� circunflexo",
	/* '�' */   "� com trema",
	/* '�' */   "I grave",
	/* '�' */   "I agudo",
	/* '�' */   "I circunflexo",
	/* '�' */   "I com trema",
	/* '�' */   "D� cortado",
	/* '�' */   "�ne com til",
	/* '�' */   "� grave",
	/* '�' */   "� agudo",
	/* '�' */   "� circunflexo",
	/* '�' */   "� com til",
	/* '�' */   "� com trema",
	/* '�' */   "vezes",
	/* '�' */   "� cortado",
	/* '�' */   "U grave",
	/* '�' */   "U agudo",
	/* '�' */   "U cicunflexo",
	/* '�' */   "U com trema"
};

#define PLE '�'
const char *nome_letra_especial[] = {
	/* '�' */	"exclama��o reversa",
	/* '�' */	"centavo de d�lar",
	/* '�' */	"libra",
	/* '�' */	"s�mbolo de moedas",
	/* '�' */	"iene",
	/* '�' */	"barra interrompida",
	/* '�' */	"par�grafo",
	/* '�' */	"trema",
	/* '�' */	"copyright",
	/* '�' */	"ordinal feminino",
	/* '�' */	"aspas angulares esquerdas",
	/* '�' */	"nega��o",
	/* ' ' */	"h�fem",
	/* '�' */	"marca registrada",
	/* '�' */	"m�cron",
	/* '�' */	"grau",
	/* '�' */	"mais ou menos",
	/* '�' */	"elevado ao quadrado",
	/* '�' */	"elevado ao cubo",
	/* '`' */	"acento agudo",
	/* '�' */	"mi",
	/* '�' */	"pi",
	/* '�' */	"ponto mediano",
	/* '�' */	"cedilha",
	/* '�' */	"expoente um",
	/* '�' */	"ordinal masculino",
	/* '�' */	"aspas angulares direitas",
	/* '�' */	"um quarto",
	/* '�' */	"um meio",
	/* '�' */	"tr�s quartos",
	/* '�' */	"interroga��o reversa"
};

typedef struct {
	char *abrev;
	char *expandido;
} ABREV;

ABREV   *tab_abrev[2000];
int		nabrevs;

/************************************************************************
 *                                                                      *
 * inic_abrev								*
 *                                                                      *
 ************************************************************************/

bool inic_abrev (const char *nome_arq_abrev)
{
	FILE	*arq_abrevs;
	char	linha[TAM_LINHA];
	char	*p;
	
	nabrevs = -1;

	if ((arq_abrevs = fopen (nome_arq_abrev, "r")) == NULL) {
		return FALSE;
	}

	while ((fgets (linha, TAM_LINHA, arq_abrevs)) != NULL) {
		trata_crlf (linha);
		if ((strlen (linha) == 0) || (linha[0] == ';')) continue;
		
		str_tolower (linha);
		p = strchr (linha, '=');
		*p++ = '\0';
		tab_abrev[++nabrevs] = malloc (sizeof (ABREV));
		tab_abrev[nabrevs]->abrev = strdup (linha);
		tab_abrev[nabrevs]->expandido = strdup (p);
	}

	return TRUE;
}

/************************************************************************
 *                                                                      *
 * fim_abrev								*
 *                                                                      *
 ************************************************************************/

void fim_abrev ()
{
	int	i;
	
	for (i = 0; i < nabrevs; i++) {
		free (tab_abrev[i]->abrev);
		free (tab_abrev[i]->expandido);
		free (tab_abrev[i]);
	}
	nabrevs = 0;
}

/************************************************************************
 *                                                                      *
 * convmil								*
 *									*
 * Obs: chamante tem a responsabilidade de liberar a memoria da string  *
 *                                                                      *
 ************************************************************************/

char *convmil (const char *s, bool *tem_conector)
{
	char *conv;
	
	conv = malloc (TAM_PALAVRA);
	conv[0] = '\0';
	
	*tem_conector = FALSE;
	if (strcmp (s, "000") == 0) return conv;

	if (strcmp (s, "100") == 0) {
		strcpy (conv, tab_centena[0]);
		*tem_conector = TRUE;
		return conv;
	}
	
	if (*s != '0') {
		strcpy (conv, tab_centena[*s - '0']);
		if (strcmp (s+1, "00") == 0) {
			*tem_conector = TRUE;
			return conv;
		}
		strcat (conv, e);
	}
	else {
		*tem_conector = TRUE;
	}
	
	if (*(s+1) == '1') {
		strcat (conv, tab_dez[*(s + 2) - '0']);
		return conv;
	}
	
	if (*(s+1) != '0') {
		strcat (conv, tab_dezena[*(s + 1) - '0' - 2]);
		if (*(s + 2) != '0') strcat (conv, e);
	}
	
	if (*(s + 2) != '0') strcat (conv, tab_unid[*(s + 2) - '0']);
	
	return conv;
}

/************************************************************************
 *                                                                      *
 * conv3								*
 *									*
 * Obs: chamante tem a responsabilidade de liberar a memoria da string  *
 *                                                                      *
 ************************************************************************/

char *conv3 (const char *s, int i, bool *pos_conector)
{
	char *conv;
	char tresdig[4];
	
	strncpy (tresdig, s + (i * 3), 3);
	tresdig[3] = '\0';
	conv = convmil (tresdig, pos_conector);
	if (*tab_mil[i] != '\0') {
		if (strcmp (tresdig, "000") != 0) {
			strcat (conv, " ");
			if (strcmp (tresdig, "001") == 0) {
				strcat (conv, tab_mil[i]);
			}
			else {
				strcat (conv, tab_mils[i]);
			}
		}
	}
	
	return conv;
}

/************************************************************************
 *                                                                      *
 * numero_para_string							*
 *									*
 * Obs: chamante tem a responsabilidade de liberar a memoria da string  *
 *                                                                      *
 ************************************************************************/

char *numero_para_string (char	*v)
{
	int	i;
	int	prim_milhar;
	int	ult_milhar;
	char	*smils[5];
	char	conector[TAM_PALAVRA];
	char	num[TAM_PALAVRA];
	char	s[TAM_FRASE];
	char	*pt_s;
	bool	tem_conector[5];
	
	if ((strlen (v) == 1) && (v[0] == '0')) return strdup (tab_unid[0]);
	
	strcpy (num, "00000000000000");
	strcat (num, v);
	strcpy (num, &num[strlen (num) - 14 - 1]);

	prim_milhar = -1;
	ult_milhar = -1;

	for (i = 0; i < 5; i++) {
		tem_conector[i] = FALSE;
		smils[i] = conv3 (num, i, &tem_conector[i]);

		if (*smils[i] != '\0') {
			ult_milhar = i;
			if (prim_milhar == -1) prim_milhar = i;
		}
	}
	
	s[0]= '\0';
	pt_s = &s[0];
	for (i = 0; i <= ult_milhar; i++) {
		if (smils[i][0] == '\0') continue;
		
		if ((i == ult_milhar) && (i != prim_milhar) && tem_conector[i])
			strcpy (conector, e);
		else if (i != prim_milhar)
			strcpy (conector, " ");
		else
			conector[0] = '\0';
		
		pt_s = str_append (pt_s, conector);
		pt_s = str_append (pt_s, smils[i]);
	}
	
	if ((strncmp (s, "um mil", 6) == 0) && (strncmp (s, "um milh", 7) != 0)) {
		strcpy (s, &s[3]);
	}
		
	return strdup (s);
}

/************************************************************************
 *                                                                      *
 * numero_feminino							*
 *									*
 * Obs: chamante tem a responsabilidade de liberar a memoria da string  *
 *                                                                      *
 ************************************************************************/

char *numero_feminino (char *s)
{
	char *fem = malloc (TAM_LINHA);
	strcpy (fem, s);
	
	if (str_has_suffix (s, "um")) return strcat (fem, "a");

	if (str_has_suffix (s, "dois"))
		return strcpy (&fem[strlen (fem) - 4], "duas");
	
	return fem;
}

/************************************************************************
 *                                                                      *
 * trata_feminino							*
 *									*
 * Obs: chamante tem a responsabilidade de liberar a memoria da string  *
 *                                                                      *
 ************************************************************************/

char *trata_feminino (const char *s, char genero)
{
	char *copia = strdup (s);
	
	if (genero == 'f') copia[strlen (copia) - 1] = 'a';
	return copia;
}

/************************************************************************
 *                                                                      *
 * prependa								*
 *                                                                      *
 ************************************************************************/

void prependa (char *destino, const char *origem, char genero)
{
	char *aux_o;
	char *trab;
	char *aux_d;
	
	aux_o = trata_feminino (origem, genero);
	trab = malloc (strlen (destino) + 1 + strlen (origem) + 1);

	aux_d = trab;
	*aux_d = '\0';
	aux_d = str_append (aux_d, aux_o);
	aux_d = str_append (aux_d, " ");
	aux_d = strcpy (aux_d, destino);
	
	strcpy (destino, trab);
	
	free (aux_o);		// retorno de trata_feminino
	free (trab);		// area provisoria de trabalho
}

/************************************************************************
 *                                                                      *
 * ordinal								*
 *									*
 * Obs: chamante tem a responsabilidade de liberar a memoria da string  *
 *                                                                      *
 ************************************************************************/

char *ordinal (char *n, char genero)
{
	char	trad[TAM_FRASE];
	int		v;
	int		num_alg;
	char	*pt_alg;
	
	num_alg = strlen (n);
	if (num_alg > 4) return numero_para_string (n);

	if ((num_alg == 4) && (n[0] > '1')) return numero_para_string (n);

	if ((num_alg == 1) && (n[0] == '0')) {
		strcpy (trad, tab_unid[0]);
		return strdup (trad);
	}
		
	trad[0] = '\0';

	pt_alg = &n[num_alg - 1];
	v = *pt_alg - '0';
	if (v > 0) prependa (trad, tab_ordinais_1[v - 1], genero);

	pt_alg--;
	v = pt_alg < &n[0] ? 0 : *pt_alg - '0';
	if (v > 0) prependa (trad, tab_ordinais_10[v - 1], genero);

	pt_alg--;
	v = pt_alg < &n[0] ? 0 : *pt_alg - '0';
	if (v > 0) prependa (trad, tab_ordinais_100[v - 1], genero);

	pt_alg--;
	v = pt_alg < &n[0] ? 0 : *pt_alg - '0';
	if (v > 0) prependa (trad, milesimo, genero);

	return strdup (trad);
}

/************************************************************************
 *                                                                      *
 * numero_para_texto							*
 *									*
 * Obs: chamante tem a responsabilidade de liberar a memoria da string  *
 *                                                                      *
 ************************************************************************/

char *numero_para_texto (char **end_texto)
{
	char	*texto;
	char	*aux;
	char	*num;
	char	n[TAM_PALAVRA];
	char	*pt_n = &n[0];
		
	texto = *end_texto;
	while (*texto) {
		if (EH_DIGITO (*texto)) {
			*pt_n++ = *texto;
		}
		else if ((*texto == '.') && (strlen (texto) > 3)) {
			if (EH_DIGITO (*(texto + 1)) &&
				EH_DIGITO (*(texto + 2)) &&
				EH_DIGITO (*(texto + 3))) {
					/* NADA */
			}
			else break;
		}
		else break;
		
		texto++;
	}
	*pt_n = '\0';

	aux = NULL;
	if (*texto == '�') {
		texto++;
		num = ordinal (n, 'm');
	}
	else if (*texto == '�') {
		texto++;
		num = ordinal (n, 'f');
	}
	else if (strncmp (texto, "o.", 2) == 0) {
		texto += 2;
		num = ordinal (n, 'm');
	}
	else if (strncmp (texto, "a.", 2) == 0) {
		texto += 2;
		num =  ordinal (n, 'f');
	}
	else if (strncmp (texto, "os.", 3) == 0) {
		texto += 3;
		aux = ordinal (n, 'm');
		num = str_concat (aux, "s", NULL);
	}
	else if (strncmp (texto, "as.", 3) == 0) {
		texto += 3;
		aux = ordinal (n, 'f');
		num = str_concat (aux, "s", NULL);
	}
	else {
		num = numero_para_string (n);
	}
	
	if (aux != NULL) free (aux);
	*end_texto = texto;
	return num;
}

/************************************************************************
 *                                                                      *
 * soletragem								*
 *									*
 * Obs: chamante tem a responsabilidade de liberar a memoria da string  *
 *                                                                      *
 ************************************************************************/

char *soletragem (char ch)
{
	int  	c = (unsigned char)ch;
	char 	aux[TAM_PALAVRA];
	char	num[TAM_SILABA];
	
	if (E_LETRA (c)) {
		return strdup (nome_letra[c - 0x20]);
	}
	else if (E_LETRA_ACENTUADA (c)) {
		return strdup (nome_letra_acentuada[c - 0xc0]);
	}
	else if (E_LETRA_ESPECIAL (c)) {
		return strdup (nome_letra_especial[c - 0xa1]);
	}
	else if (c > 0xe0) {
		return soletragem (c - 0x20);
	}

	strcpy (aux, "\1");
//	sprintf (num, "%d", c);
//	strcpy (aux, "c�digo ");
//	strcat (aux, numero_para_string (num));
	
	return strdup (aux);
}

/************************************************************************
 *                                                                      *
 * busca_abreviatura							*
 *                                                                      *
 ************************************************************************/

char *busca_abreviatura (char *parm_palavra)
{
	int		i;
	char	*palavra;
	char	*ret = NULL;
	
	palavra = str_lower (parm_palavra);
	for (i = 0; i < nabrevs; i++) {
		if (strcmp (palavra, tab_abrev[i]->abrev) == 0) {
			ret = tab_abrev[i]->expandido;
			break;
		}
	}
	
	free (palavra);
	return ret;
}

/************************************************************************
 *                                                                      *
 * pega_palavra								*
 *									*
 * Obs: chamante tem a responsabilidade de liberar a memoria da string  *
 *                                                                      *
 ************************************************************************/

char *pega_palavra (char **end_texto)
{
	char		*texto = *end_texto;
	char		palavra[TAM_PALAVRA];
	char		*pt_txt;
	char		*aux;
	bool	so_consoantes;
	
	so_consoantes = TRUE;
	while (EH_LETRA (*texto)) {
		if (!EH_CONSOANTE (*texto)) so_consoantes = FALSE;
		texto++;
		if (*texto == '-') texto++;
	}

	strncpy (palavra, *end_texto, texto - *end_texto);
	palavra[texto - *end_texto] = '\0';
	if (so_consoantes &&
		((*texto != '.') || busca_abreviatura (palavra) == NULL)) {
				palavra[0] = '\0';
				for (pt_txt = *end_texto; pt_txt < texto; pt_txt++) {
					aux = soletragem (*pt_txt);
					strcat (palavra, aux);
					free (aux);
				}
	}
	
	*end_texto = texto;
	return strdup (palavra);
}

/************************************************************************
 *                                                                      *
 * pre_processa								*
 *									*
 * Obs: chamante tem a responsabilidade de liberar a memoria da string  *
 *                                                                      *
 ************************************************************************/

char *pre_processa (char *txt)
{
	char	*palavra;
	char	txt_sai[TAM_TEXTO];
	char	*pt_sai;
	char	*abr;
	char	*aux;
	
	if (strlen (txt) == 1) txt = soletragem (*txt);
	
	pt_sai = &txt_sai[0];
	*pt_sai = '\0';
	while (*txt != '\0') {
		if ((strlen (txt) > 3) && (strncmp (txt, " - ", 3) == 0)) {
			pt_sai = str_append (pt_sai, ",");
			txt += 3;
		}

		else if ((*txt == '-') && EH_DIGITO (*(txt + 1))) {
			pt_sai = str_append (pt_sai, "menos ");
			txt++;
		}
		
		else if (EH_DIGITO (*txt)) {
			aux = numero_para_texto (&txt);
			pt_sai = str_append (pt_sai, aux);
			pt_sai = str_append (pt_sai, " ");
			free (aux);
			
			while ((strlen (txt) >= 2) &&
						(*txt == ',') && (EH_DIGITO (*(txt + 1)))) {
				pt_sai = str_append (pt_sai, " v�rgula ");
				txt++;
				while (EH_DIGITO (*txt)) {
					aux = soletragem (*txt);
					pt_sai = str_append (pt_sai, " ");
					pt_sai = str_append (pt_sai, aux);
					pt_sai = str_append (pt_sai, " ");
					free (aux);

					txt++;
				}
			}
		}
		
		else if (EH_LETRA (*txt)) {
			palavra = pega_palavra (&txt);
			abr = NULL;
			if ((*txt == '\0') || (*txt != '.')) {
				pt_sai = str_append (pt_sai, palavra);
			}
			else {
				if (strncmp (txt, ". ", 2) == 0) {
					abr = busca_abreviatura (palavra);
					if (abr == NULL) {
						pt_sai = str_append (pt_sai, palavra);
					}
					else {
						pt_sai = str_append (pt_sai, abr);
						txt++;
					}
					if (abr != NULL) free (abr);
				}
				else {
					pt_sai = str_append (pt_sai, palavra);
				}
			}
			free (palavra);
		}
		
		else if (strncmp (txt, "...", 3) == 0) {
			pt_sai = str_append (pt_sai, "...");
			pt_sai = str_append (pt_sai, " ");
			txt += 3;
		}
		
		else {
			if ((*txt == '.') && (strlen (txt) > 1) && (*(txt + 1) != ' ')) {
					aux = soletragem (*txt);
					pt_sai = str_append (pt_sai, " ");
					pt_sai = str_append (pt_sai, aux);
					pt_sai = str_append (pt_sai, " ");
					free (aux);
			}
			else if (EH_PONTUACAO (*txt)) {
				*pt_sai++ = *txt;
				*pt_sai = '\0';
			}			
			else if ((*txt == '\n') || (*txt == '\r') || (*txt == '\t')) {
				*pt_sai++ = ' ';
				*pt_sai = '\0';
			}
			else {
				aux = soletragem (*txt);
				pt_sai = str_append (pt_sai, " ");
				pt_sai = str_append (pt_sai, aux);
				pt_sai = str_append (pt_sai, " ");
				free (aux);
			}

			txt++;
		}
	}
	
	return strdup (txt_sai);
}
