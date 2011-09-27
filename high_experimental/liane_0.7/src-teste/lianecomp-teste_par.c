#include <stdio.h>
#include <string.h>
#include <unistd.h>
#include <getopt.h>
#include <iconv.h>
#include "liane-util.h"
#define MAX_LEN 256



/**********************************************************************
* aqui entra o dodigo do liane-comp.                                   *
***********************************************************************/
#include <stdlib.h>
#include <string.h>

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
//char str_buf[MAX_LEN+2];
char i;

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
	

	switch(i)
	{
	case '1':
	log_write_string ("compila", "pre_processa");
	texto_expandido = pre_processa (texto);
	printf ("texto_expandido = %s\n", texto_expandido);
	//break;
	exit (0);

	case '2':
	texto_expandido = pre_processa (texto);
	log_write_string ("compila", "pre_prosodia");
	palavras_com_codigos = pre_prosodia (texto_expandido);
	free (texto_expandido);
	imprime_slist (palavras_com_codigos, "palavras_com_codigos");
	//break;
	exit (0);
	
	case '3':
	texto_expandido = pre_processa (texto);
	palavras_com_codigos = pre_prosodia (texto_expandido);
	free (texto_expandido);
	log_write_string ("compila", "calcula_curva_prosodia");
	palavras_com_prosodia = calcula_curva_prosodia (palavras_com_codigos, TRUE);
	slist_foreach (palavras_com_codigos, free, NULL);
	slist_free (palavras_com_codigos);
	imprime_slist (palavras_com_prosodia, "palavras_com_prosodia");
	//break;
	exit (0);

	case '4': 
	texto_expandido = pre_processa (texto);
	palavras_com_codigos = pre_prosodia (texto_expandido);
	free (texto_expandido);
	log_write_string ("compila", "calcula_curva_prosodia");
	palavras_com_prosodia = calcula_curva_prosodia (palavras_com_codigos, TRUE);
	slist_foreach (palavras_com_codigos, free, NULL);
	slist_free (palavras_com_codigos);	
	log_write_string ("compila", "compila_fonemas");
	fonemas = compila_fonemas (palavras_com_prosodia);
	slist_foreach (palavras_com_prosodia, free, NULL);
	slist_free (palavras_com_prosodia);
	imprime_dlist (fonemas, "fonemas");
	//break;
	exit (0);
	
	case '5':
	texto_expandido = pre_processa (texto);
	palavras_com_codigos = pre_prosodia (texto_expandido);
	free (texto_expandido);
	log_write_string ("compila", "calcula_curva_prosodia");
	palavras_com_prosodia = calcula_curva_prosodia (palavras_com_codigos, TRUE);
	slist_foreach (palavras_com_codigos, free, NULL);
	slist_free (palavras_com_codigos);	
	log_write_string ("compila", "compila_fonemas");
	fonemas = compila_fonemas (palavras_com_prosodia);
	slist_foreach (palavras_com_prosodia, free, NULL);
	slist_free (palavras_com_prosodia);
	log_write_string ("compila", "aplica_prosodia");
	mbrola_pho = aplica_prosodia (fonemas, 1.0, 1.0);
	dlist_foreach (fonemas, free, NULL);
	dlist_free (fonemas);
	imprime_slist (mbrola_pho, "mbrola_pho");
	//break;
	exit (0);

	case '6':
	exit (0);
	break;
	}
	//Estou comentando o laço abaixo para a aplicaçao de testes

	if ((arq_difones = fopen (nome_arquivo_difones, "w")) == NULL) {
		fprintf (stderr, "Nao consegui abrir %s\n", nome_arquivo_difones);
		return FALSE;
	}
	saux = mbrola_pho;
	//while (saux != NULL) {
	//	fprintf (arq_difones, "%s\n", (char *)saux->data);
	//	saux = slist_next (saux);
	//}
	//fclose (arq_difones);

	//slist_foreach (mbrola_pho, free, NULL);
	//slist_free (mbrola_pho);

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



/**********************************************************************
* aqui termina o dodigo do liane-comp.                                   *
***********************************************************************/


/************************************************************************
 *  Converte caracteres para ISO-8859-1.                                *
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
 *  Remove fim de linha.                                                *
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
 *  Dá saída no arquivo.                                                *
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
 *  Programa principal.                                                 *
 ************************************************************************/

int main (int argc, char *argv[])
{
	
    char str_buf[MAX_LEN+2];
    char temp[20];
    char pid[6];

/* inicializa o compilador, gerando arquivo temporário */

    sprintf (pid, "%05d", (int)getpid ());
    strcpy (temp, "/tmp/");
    strcat (temp, pid);
    strcat (temp, ".pho");

    liane_inicia_compilador ("liane", temp);

/*processamento de parametros*/

//printf("arg = %d\n",argc);
if(argc == 3 )
{
printf ("digite: echo texto|programa opcao \n");
exit (0);
}
i = argv[1][0];

        

/* le stdin, gerando arquivo temporário com fonemas */
	
 	while(fgets(str_buf, MAX_LEN + 1, stdin) != NULL)  {
	       	estripa (str_buf);
        	utfToISO (str_buf);
		liane_compila (str_buf);
	}
	saiArquivo (temp);    // pois arquivo temporário é apagado a cada linha

    liane_termina_compilador();
    unlink (temp);
    return 0;
}

