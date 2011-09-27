#ifndef TTSPORTUG_H
#define TTSPORTUG_H

#include "liane-util.h"

extern bool inic_tradutor (char *nome_arq_regras, char *nome_arq_excessoes);
extern DList *compila_fonemas (SList *texto_marcado);
extern void fim_tradutor ();

#endif
