#include <stdlib.h>
#include <stdio.h>
#include <string.h>
#include <math.h>
#include "liane-util.h"

#include "arquivo.h"

/************************************************************************
 *                                                                      *
 * Tipos								*
 *                                                                      *
 ************************************************************************/

struct duracao {
	
	char	*dif;
	int		dur;
};
typedef struct duracao DURACAO;

struct chave {
	char	*palavra;
	char	*funcao;
};
typedef struct chave CHAVE;

struct prosodia {
	char	*cod_ant;
	char	*cod_atual;
	char	*cod_prox;
	char	*codificacao;
};
typedef struct prosodia PROSODIA;

/************************************************************************
 *                                                                      *
 * Variaveis globais							*
 *                                                                      *
 ************************************************************************/

static int		max_fon;
static DURACAO	tab_dur[100];

static int	max_chaves;
static CHAVE	tab_chaves[1000];

static int	max_prosodia;
static PROSODIA	tab_prosodia[300];

static SList	*lista_difones;
static char	ult_dif[TAM_PALAVRA];
static char	dif_atual[TAM_PALAVRA];
static int	hertz;
static int	tab_hertz[10];

static const char *fonemas_validos = "abcdefghijklmnopqrstuvwxyz2@_";

#define EH_FONEMA_VALIDO(x) ((x != '\0') && (strchr (fonemas_validos, x) != NULL))

static const char *fim_de_frase = ".!?";

#define EH_FIM_DE_FRASE(x) ((x != '\0') && (strchr (fim_de_frase, x) != NULL))

/************************************************************************
 *                                                                      *
 * comp_chaves								*
 *                                                                      *
 ************************************************************************/

int comp_chaves (const void *p1, const void *p2)
{
	CHAVE *ch1 = (CHAVE *)p1;
	CHAVE *ch2 = (CHAVE *)p2;

	return (strcmp (ch1->palavra, ch2->palavra));
}

/************************************************************************
 *                                                                      *
 * ordena_chaves							*
 *                                                                      *
 ************************************************************************/

void ordena_chaves ()
{
	qsort (tab_chaves, max_chaves, sizeof (CHAVE), comp_chaves);
}

/************************************************************************
 *                                                                      *
 * busca_chave								*
 *                                                                      *
 ************************************************************************/

const char *busca_chave (char *qual)
{
	CHAVE   elem;
	CHAVE   *ret;
	
	elem.palavra = strdup (qual);
	str_tolower (elem.palavra);
	ret = bsearch (&elem, tab_chaves, max_chaves, sizeof (CHAVE), comp_chaves);
	free (elem.palavra);
	if (ret != NULL) return ret->funcao;

	return NULL;
}

/************************************************************************
 *                                                                      *
 * inic_lista_difones							*
 *                                                                      *
 ************************************************************************/

bool inic_lista_difones (char *nome_arq)
{
	FILE	*arq_difones;
	
	char	linha[TAM_LINHA];
	char	*pt_lin;
	char	*pt_inicio;
	int		tam;

	char	d1[TAM_PALAVRA];
	char	d2[TAM_PALAVRA];

	if ((arq_difones = fopen (nome_arq, "r")) == NULL) return FALSE;
	
	lista_difones = NULL;
	
	while ((fgets (linha, TAM_LINHA, arq_difones)) != NULL) {
		trata_crlf (linha);
		if (eh_comentario (linha)) continue;

		str_tolower (linha);

		pt_lin = &linha[0];
		pt_inicio = pt_lin;
		while (*pt_lin != '\0') {
			if (!EH_FONEMA_VALIDO (*pt_lin)) break;
			pt_lin++;
		}
		tam = pt_lin - pt_inicio;
		strncpy (d1, pt_inicio, tam);
		d1[tam] = '\0';
		while (*pt_lin != '\0') {
			if (EH_FONEMA_VALIDO (*pt_lin)) break;
			pt_lin++;
		}
		
		pt_inicio = pt_lin;
		while (*pt_lin != '\0') {
			if (!EH_FONEMA_VALIDO (*pt_lin)) break;
			pt_lin++;
		}
		tam = pt_lin - pt_inicio;
		strncpy (d2, pt_inicio, tam);
		d2[tam] = '\0';

		lista_difones = slist_prepend (lista_difones,
					str_concat (d1, "-", d2, NULL));
	}

	fclose (arq_difones);
	lista_difones = slist_reverse (lista_difones);

	return TRUE;
}

/************************************************************************
 *                                                                      *
 * fim_lista_difones							*
 *                                                                      *
 ************************************************************************/

void fim_lista_difones ()
{
	slist_foreach (lista_difones, free, NULL);
	slist_free (lista_difones);
}

/************************************************************************
 *                                                                      *
 * carrega_duracao							*
 *                                                                      *
 ************************************************************************/

static bool carrega_duracao (char *nome_arq)
{
	FILE	*arq_prosodia;
	char	linha[TAM_LINHA];
	char	*pos;
	bool	achou;

	if ((arq_prosodia = fopen (nome_arq, "r")) == NULL) return FALSE;
	
	max_fon = 0;
	achou = FALSE;

	while ((fgets (linha, TAM_LINHA, arq_prosodia)) != NULL) {
		trata_crlf (linha);
		if (strcmp (linha, "[DURAÇÃO]") == 0) {
			achou = TRUE;
			break;
		}
	}
	if (!achou) {
		fclose (arq_prosodia);
		return FALSE;
	}
	
	while ((fgets (linha, TAM_LINHA, arq_prosodia)) != NULL) {
		trata_crlf (linha);
		if (eh_comentario (linha)) continue;
		
		if (linha[0] == '[') break;
		
		pos = strchr (linha, '=');
		if (pos == NULL) {
			fclose (arq_prosodia);
			return FALSE;
		}
		*pos = '\0';
		tab_dur[max_fon].dif = strdup (linha);
		if (sscanf (++pos, "%d", &tab_dur[max_fon].dur) != 1) {
			tab_dur[max_fon].dur = 100;
		}
		max_fon++;
	}
	fclose (arq_prosodia);
	
	return TRUE;
}

/************************************************************************
 *                                                                      *
 * lib_mem_duracao							*
 *                                                                      *
 ************************************************************************/

void lib_mem_duracao ()
{
	int	i;
	
	for (i = 0; i < max_fon; i++) free (tab_dur[i].dif);
	max_fon = 0;
}

/************************************************************************
 *                                                                      *
 * carrega_chaves							*
 *                                                                      *
 ************************************************************************/

static bool carrega_chaves (char *nome_arq)
{
	FILE	*arq_prosodia;
	char	linha[TAM_LINHA];
	char	*pos;
	bool	achou;

	if ((arq_prosodia = fopen (nome_arq, "r")) == NULL) return FALSE;

	achou = FALSE;

	while ((fgets (linha, TAM_LINHA, arq_prosodia)) != NULL) {
		trata_crlf (linha);
		if (strcmp (linha, "[CHAVES]") == 0) {
			achou = TRUE;
			break;
		}
	}
	if (!achou) {
		fclose (arq_prosodia);
		return FALSE;
	}
	
	while ((fgets (linha, TAM_LINHA, arq_prosodia)) != NULL) {
		trata_crlf (linha);
		if (eh_comentario (linha)) continue;
		
		if (linha[0] == '[') break;
		
		str_tolower (linha);
		pos = strchr (linha, '=');
		if (pos == NULL) {
			fclose (arq_prosodia);
			return FALSE;
		}

		*pos = '\0';
		tab_chaves[max_chaves].palavra = strdup (linha);
		tab_chaves[max_chaves].funcao = strdup (++pos);
		max_chaves++;
	}
	fclose (arq_prosodia);

	ordena_chaves ();
	return TRUE;
}

/************************************************************************
 *                                                                      *
 * lib_mem_chaves							*
 *                                                                      *
 ************************************************************************/

void lib_mem_chaves ()
{
	int i;
	
	for (i = 0; i < max_chaves; i++) {
		free (tab_chaves[i].palavra);
		free (tab_chaves[i].funcao);
	}
	max_chaves = 0;
}

/************************************************************************
 *                                                                      *
 * carrega_prosodia							*
 *                                                                      *
 ************************************************************************/

static bool carrega_prosodia (char *nome_arq)
{
	FILE	*arq_prosodia;
	char	linha[TAM_LINHA];
	char	*sequencia;
	bool	achou;
	int	c;
	char	*inicio;
	char	*fim;

	if ((arq_prosodia = fopen (nome_arq, "r")) == NULL) return FALSE;

	achou = FALSE;

	while ((fgets (linha, TAM_LINHA, arq_prosodia)) != NULL) {
		trata_crlf (linha);
		if (strcmp (linha, "[PROSÓDIA]") == 0) {
			achou = TRUE;
			break;
		}
	}
	if (!achou) {
		fclose (arq_prosodia);
		return FALSE;
	}
	
	while ((fgets (linha, TAM_LINHA, arq_prosodia)) != NULL) {
		trata_crlf (linha);
		if (eh_comentario (linha)) continue;
		
		if (linha[0] == '[') break;
		
		if (strncmp (linha, "hertz=", 6) == 0) {
			sscanf (&linha[6], "%d", &hertz);
			continue;
		}
		
		if (strncmp (linha, "tabhertz=", 9) == 0) {
			linha[strlen (linha) + 1] = '\0';
			linha[strlen (linha)] = ',';
			inicio = &linha[9];
			for (c = 0; c < 10; c++) {
				fim = strchr (inicio, ',');
				*fim = '\0';
				sscanf (inicio, "%d", &tab_hertz[c]);
				inicio = fim + 1;
			}
			continue;
		}
		
		str_tolower (linha);
		fim = strchr (linha, '=');
		tab_prosodia[max_prosodia].codificacao = strdup (fim + 1);
		
		*fim = '\0';
		sequencia = malloc (strlen (linha) + 3 + 1);
		strcpy (sequencia, linha);
		strcat (sequencia, "|||");
		
		inicio = sequencia;
		fim = strchr (inicio, '|');
		*fim = '\0';
		tab_prosodia[max_prosodia].cod_ant = strdup (inicio);
		
		inicio = fim + 1;
		fim = strchr (inicio, '|');
		*fim = '\0';
		tab_prosodia[max_prosodia].cod_atual = strdup (inicio);
		
		inicio = fim + 1;
		fim = strchr (inicio, '|');
		*fim = '\0';
		tab_prosodia[max_prosodia].cod_prox = strdup (inicio);

		free (sequencia);
		max_prosodia++;
	}
	
	fclose (arq_prosodia);
	return TRUE;
}

/************************************************************************
 *                                                                      *
 * lib_mem_prosodia							*
 *                                                                      *
 ************************************************************************/

void lib_mem_prosodia ()
{
	int i;
	
	for (i = 0; i < max_prosodia; i++) {
		free (tab_prosodia[i].cod_ant);
		free (tab_prosodia[i].cod_atual);
		free (tab_prosodia[i].cod_prox);
		free (tab_prosodia[i].codificacao);
	}
	max_prosodia = 0;
}

/************************************************************************
 *                                                                      *
 * inic_prosodia							*
 *                                                                      *
 ************************************************************************/

bool inic_prosodia (char *nome_arq)
{
	return (carrega_duracao (nome_arq) &&
			carrega_chaves (nome_arq) &&
			carrega_prosodia (nome_arq));
}

/************************************************************************
 *                                                                      *
 * fim_prosodia								*
 *                                                                      *
 ************************************************************************/

void fim_prosodia ()
{
	lib_mem_duracao ();
	lib_mem_chaves ();
	lib_mem_prosodia ();
}

/************************************************************************
 *                                                                      *
 * pega_palavra								*
 *                                                                      *
 ************************************************************************/

static char *pega_palavra (char *pt, char *palavra, bool *especial)
{
	char	*aux;
	
	*especial = TRUE;
	while (*pt == ' ') pt++;

	if (strncmp (pt, "..", 2) == 0) {
		strcpy (palavra, ".-");
		while (*pt == '.') pt++;
		return pt;
	}
	
	if ((*pt != '\0') && (!EH_LETRA (*pt))) {
		palavra[0] = *pt;
		palavra[1] = *pt;
		palavra[2] = '\0';
		pt++;
		return pt;
	}
	
	aux = palavra;
	while (EH_LETRA (*pt)) *aux++ = *pt++;
	*aux = '\0';
	*especial = FALSE;
	return pt;
}

/************************************************************************
 *                                                                      *
 * pre_prosodia								*
 *                                                                      *
 ************************************************************************/

SList *pre_prosodia (char *texto)
{
	char		*pt;
	SList		*saida;
	char		pal[TAM_PALAVRA];
	bool		e_especial;
	char		ch[3];
	const char	*aux;
	
	saida = NULL;
	saida = slist_prepend (saida, strdup ("[--]"));
	pt = texto;
	while (*pt != '\0') {
		pt = pega_palavra (pt, pal, &e_especial);
		if (pal[0] == '\0') break;

		if (e_especial) {
			strcpy (ch, pal);
			pal[1] = '\0';
		}
		else {
			aux = busca_chave (pal);
			strcpy (ch, aux != NULL ? aux : "xx");
		}
		
		saida = slist_prepend (saida, str_concat ("[", ch, "]", pal, NULL));
		if (EH_FIM_DE_FRASE (pal[0])) {
			saida = slist_prepend (saida, strdup ("[--]"));
		}
	}
	saida = slist_reverse (saida);
	
	return saida;
}

/************************************************************************
 *                                                                      *
 * calcula_curva_prosodia						*
 *                                                                      *
 ************************************************************************/

SList *calcula_curva_prosodia (SList *palavras_com_codigos,
				bool com_prosodia)
{
	char	*s0 = "";
	char	*s = "";
	char	*sp = "";

	char	c_ant[TAM_SILABA];
	char	c_atual[TAM_SILABA];
	char	c_prox[TAM_SILABA];

	char	ult_valor = '5';
	SList	*lista_com_curva = NULL;
	SList	*pt;
	bool	varrendo;

	char	cod[TAM_PALAVRA];
	int	ind_p;
	PROSODIA  *pr;
	bool	ok;
	
	pt = palavras_com_codigos;
	varrendo = TRUE;
	while (varrendo) {
		if (pt != NULL) {
			if (*((char *)(pt->data)) == '\0') {
				pt = pt->next;
				continue;
			}
		}
		
		s0 = s;
		s = sp;
		sp = (pt == NULL) ? "" : (char *)pt->data;
		
		if (*s == '\0') {
			pt = pt->next;
			continue;
		}

		if (*s0 != '\0') {
			strncpy (c_ant, s0 + 1, 2);
			c_ant[2] = '\0';
		}
		else c_ant[0] = '\0';

		strncpy (c_atual, s + 1, 2);
		c_atual[2] = '\0';

		if (*sp != '\0') {
			strncpy (c_prox, sp + 1, 2);
			c_prox[2] = '\0';
		}
		else c_prox[0] = '\0';
		
		strcpy (cod, "555");
		if (com_prosodia) {
			for (ind_p = 0; ind_p < max_prosodia; ind_p++) {
				pr = &tab_prosodia[ind_p];

				ok = (pr->cod_ant[0] == '\0') ||
					(strcmp (pr->cod_ant, c_ant) == 0);

				ok &= (pr->cod_atual[0] == '\0') ||
					(strcmp (pr->cod_atual, c_atual) == 0);
				
				ok &= (pr->cod_prox[0] == '\0') ||
					(strcmp (pr->cod_prox, c_prox) == 0);
				
				if (ok) {
					strcpy (cod, pr->codificacao);
					break;
				}
			}
		}
		
		if (*cod == '?') *cod = ult_valor;
		ult_valor = *(cod + 2);

		lista_com_curva = slist_prepend (lista_com_curva,
				  str_concat (cod, "|", &s[4], NULL));
		
		if (pt != NULL) pt = pt->next;
		else varrendo = FALSE;
	}
	lista_com_curva = slist_reverse (lista_com_curva);
	
	return lista_com_curva;
}

/************************************************************************
 *                                                                      *
 * calcula_prosodia							*
 *                                                                      *
 ************************************************************************/

#define INICIO  0
#define ESTAVEL 1
#define TONICA  2
#define FIM	3

static char *calcula_prosodia (int *estado, bool ult_letra,
				   float perc_pitch, char *cod)
{
	int	pitch;
	int	h;
	int	posic;
	char	aux[TAM_PALAVRA];
	
	h = 0;
	posic = 0;
	
	switch (*estado) {
		case INICIO:
			h = tab_hertz[*cod - '0'];
			posic = 50;
			*estado = ESTAVEL;
			break;
			
		case ESTAVEL:
			break;
			
		case TONICA:
			h = tab_hertz[*(cod + 1) - '0'];
			posic = 80;
			*estado = FIM;
			break;
			
		case FIM:
			if (ult_letra) {
				h = tab_hertz[*(cod + 2) - '0'];
				posic = 50;
			}
			break;
	}
	
	if (h != 0) {
		pitch = ((int)(perc_pitch * h * hertz)) / 100;
		sprintf (aux, "%d %d", posic, pitch);
		return strdup (aux);
	}

	return NULL;
}

/************************************************************************
 *                                                                      *
 * checa_difones_errados						*
 *									*
 * Obs: eventualmente tem que colocar um y depois de   lh m nh r rr z	*
 *                                                                      *
 ************************************************************************/

static void checa_difones_errados (SList **mbrola_cmd, char *s)
{
	char	aux[TAM_LINHA];

	if (dif_atual[0] != '\0') {
		strcpy (aux, ult_dif);
		strcat (aux, "-");
		strcat (aux, dif_atual);
		if (slist_find (lista_difones, (void *)strcmp, aux) == NULL) {
			strcpy (aux, ult_dif);
			strcat (aux, "-_");
			if ((strcmp (ult_dif, "r2") == 0) &&
				(slist_find (lista_difones, (void *)strcmp, aux) != NULL)) {
					*mbrola_cmd = slist_prepend (*mbrola_cmd, strdup ("_ 50")); 
			}
			else {
				strcpy (aux, ult_dif);
				strcat (aux, "-y");
				if (slist_find (lista_difones, (void *)strcmp, aux) != NULL) {
					*mbrola_cmd = slist_prepend (*mbrola_cmd, strdup ("y 50"));
				}
				else {
					*mbrola_cmd = slist_prepend (*mbrola_cmd, strdup ("_ 50"));
				}
			}
		}
		
		if (*s != '\0') strcpy (ult_dif, dif_atual);
	}
}

/************************************************************************
 *                                                                      *
 * busca_duracao							*
 *                                                                      *
 ************************************************************************/

static int busca_duracao (char *s)
{
	int	f;
	
	for (f = 0; f < max_fon; f++) {
		if (strcmp (tab_dur[f].dif, s) == 0) {
			return tab_dur[f].dur;
		}
	}

	return 100;
}

/************************************************************************
 *                                                                      *
 * aplica_prosodia							*
 *                                                                      *
 ************************************************************************/

SList *aplica_prosodia (DList *fonemas, float perc_duracao, float perc_pitch)
{
	char		str_aux[TAM_PALAVRA];
	char		*prosodia;
	int		duracao;
	char		cod[4];
	bool		ult_letra;
	DList		*pf;
	char		s[TAM_PALAVRA];
	
	int		amplif = 100;
	int		estado = INICIO;
	int		tamanho = 0;
	SList		*mbrola_cmd = NULL;
	
	strcpy (cod, "555");
	strcpy (ult_dif, "_");
	
	pf = fonemas;
	while (pf != NULL) {
		prosodia = "";
		strcpy (s, (char *)pf->data);

		ult_letra = (pf->next == NULL);
		if (!ult_letra) ult_letra = *((char *)pf->next->data) == '\0';
		
		if (s[0] == '\0') {
			amplif = 100;
			dif_atual[0] = '\0';
			goto guarda;
		}
		
		if ((strlen (s) > 4) && (s[0] == ';') && (s[4] == '|')) {
			strncpy (cod, &s[1], 3);
			cod[3] = '\0';
			strcpy (&s[1], &s[5]);
			tamanho = strlen (s);
			estado = INICIO;
			goto guarda;
		}
		
		if (s[0] == '>') {
			amplif = 145;
			strcpy (s, &s[1]);
			if (s[0] == '¨') strcpy (s, &s[1]);
			estado = TONICA;
		}
		else if (s[0] == '¨') {
			strcpy (s, &s[1]);
			amplif = 100;
		}
		
		if (s[0] == '_') {
			if (strlen (s) == 1) strcpy (s, "_ 100");
			if (s[1] == ' ') {
				strcpy (str_aux, &s[2]);
				str_strip (str_aux);
				duracao = atoi (str_aux);
			}
			else
				duracao = busca_duracao (s);
			strcpy (s, "_");
		}
		else {
			duracao = busca_duracao (s);
			if (estado < TONICA) {
				if (tamanho > 13)
					duracao = duracao * 80 / 100;
				else if (tamanho > 9)
					duracao = duracao * 90 / 100;
			}
		}

		strcpy (dif_atual, s);
		duracao = (int)((duracao * amplif * perc_duracao) / 100);
		sprintf (str_aux, "%d", duracao);
		strcat (s, " ");
		strcat (s, str_aux);

		prosodia = calcula_prosodia (&estado, ult_letra, perc_pitch, cod);
		if (prosodia != NULL) {
			strcat (s, " ");
			strcat (s, prosodia);
		}

guarda:

		checa_difones_errados (&mbrola_cmd, s);
		mbrola_cmd = slist_prepend (mbrola_cmd, strdup (s));
		pf = dlist_next (pf);
	}

	return slist_reverse (mbrola_cmd);
}
