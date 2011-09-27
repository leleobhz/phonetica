/* -*- Mode: C; indent-tabs-mode: t; c-basic-offset: 4; tab-width: 4 -*- */

#ifndef TTSEXCESSOES_H
#define TTSEXCESSOES_H

/************************************************************************
 *                                                                      *
 * Constantes, tipos e variáveis										*
 *                                                                      *
 ************************************************************************/

#define MAX_EXCESSOES 15000

struct palavra_alias {    
    char *palav;
    char *alias;
};

typedef struct palavra_alias PALAVRA_ALIAS;

extern int n_excessoes;

extern PALAVRA_ALIAS tab_excessoes[];

/************************************************************************
 *                                                                      *
 * Funcoes										                        *
 *                                                                      *
 ************************************************************************/

extern bool carrega_excessoes (char *nome_arq);
extern void trata_excessoes (char *palavra);
extern void lib_mem_excessoes ();

#endif
