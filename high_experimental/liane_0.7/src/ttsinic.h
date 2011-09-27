#ifndef TTSINIC_H
#define TTSINIC_H

#define PRIMEIRO_CHAR   0x20
#define ULTIMO_CHAR		0xff
#define NUM_CHARS		(ULTIMO_CHAR - PRIMEIRO_CHAR + 1)

/************************************************************************
 *                                                                      *
 * Tipos e variaveis													*
 *                                                                      *
 ************************************************************************/

typedef struct SREGRA REGRA;

struct SREGRA {
	char	*contexto_a_esquerda;
	char	*contexto;
	char	*contexto_a_direita;
	char	*fonemas;
	REGRA   *prox;
};

extern REGRA *regras[];

/************************************************************************
 *                                                                      *
 * Funcoes																*
 *                                                                      *
 ************************************************************************/

extern bool inic_vars_tradutor (char *nome_arq);
extern void lib_mem_regras ();

#endif
