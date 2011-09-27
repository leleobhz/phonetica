#ifndef TTSPROSODIA_H
#define TTSPROSODIA_H

#include "liane-util.h"

extern bool inic_lista_difones (char *arq_difones);
extern void fim_lista_difones ();
extern bool inic_prosodia (char *arq_config_prosodia);
extern void fim_prosodia ();
extern SList *pre_prosodia (char *texto);
extern SList *calcula_curva_prosodia (SList *palavras_com_codigos,
									  bool com_prosodia);
extern SList *aplica_prosodia (DList *fonemas,
                               float perc_duracao, float perc_pitch);

#endif
