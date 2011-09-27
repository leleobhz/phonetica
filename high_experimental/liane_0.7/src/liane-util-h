#ifndef LIANE_UTIL_H
#define LIANE_UTIL_H

#include <stdlib.h>

/************************************************************************
 *                                                                      *
 * FALSE e TRUE															*
 *                                                                      *
 ************************************************************************/

#define FALSE 0
#define TRUE  1

/************************************************************************
 *                                                                      *
 * Diretorios lianetts e mbrola.										*
 *                                                                      *
 ************************************************************************/

#define LIANE_DIR 		"/usr/share/lianetts"
#define MBROLA_DIR		"/usr/share/mbrola"

#define VOZ_DEFAULT 	"Liane"

/************************************************************************
 *                                                                      *
 * Padronizacao de tamanhos de strings							        *
 *                                                                      *
 ************************************************************************/

#define TAM_SILABA	16
#define TAM_PALAVRA 64
#define TAM_LINHA   256
#define TAM_FRASE   1024	// 1Kb
#define TAM_TEXTO   4096	// 4Kb

/************************************************************************
 *                                                                      *
 * Defines para os conjuntos de caracteres 						        *
 *                                                                      *
 ************************************************************************/

#define EH_MAIUSCULA(x) ((x != '\0') && (strchr (maiusculas, x) != NULL))
#define EH_MINUSCULA(x) ((x != '\0') && (strchr (minusculas, x) != NULL))
#define EH_DIGITO(x) ((x != '\0') && (strchr (digitos, x) != NULL))
#define EH_LETRA(x) ((x != '\0') && (strchr (alfabeto, x) != NULL))
#define EH_DELIMITADOR(x) ((x != '\0') && (strchr (delimitadores, x) != NULL))
#define EH_VOGAL(x) ((x != '\0') && (strchr (vogais, x) != NULL))
#define EH_CONSOANTE(x) ((x != '\0') && (strchr (consoantes, x) != NULL))
#define EH_ACENTUADA(x) ((x != '\0') && (strchr (acentos, x) != NULL))
#define EH_Q_ou_G(x) ((x != '\0') && (strchr (QG, x) != NULL))
#define EH_A_ou_O(x) ((x != '\0') && (strchr (AO, x) != NULL))
#define EH_E_ou_I(x) ((x != '\0') && (strchr (EI, x) != NULL))
#define EH_R_ou_L(x) ((x != '\0') && (strchr (RL, x) != NULL))
#define EH_S(x) ((x != '\0') && (strchr (S, x) != NULL))
#define EH_H(x) ((x != '\0') && (strchr (H, x) != NULL))
#define EH_L_M_N_R_ou_Z(x) ((x != '\0') && (strchr (LMNRZ, x) != NULL))
#define EH_N_R_ou_S(x) ((x != '\0') && (strchr (NRS, x) != NULL))
#define EH_A_E_I_ou_O(x) ((x != '\0') && (strchr (AEIO, x) != NULL))
#define EH_VOGAL_W_ou_Y(x) ((x != '\0') && (strchr (AEIOUWY, x) != NULL))
#define EH_PONTUACAO(x) ((x != '\0') && (strchr (pontuacoes, x) != NULL))

/************************************************************************
 *                                                                      *
 * Tipos bool e pointer													*
 *                                                                      *
 ************************************************************************/

typedef int bool;
typedef void* pointer;

/************************************************************************
 *                                                                      *
 * Tipos SList e DList													*
 *                                                                      *
 ************************************************************************/

typedef struct SLIST SList;
struct SLIST {
	pointer	data;
	SList	*next;
};

typedef struct DLIST DList;
struct DLIST {
	DList	*previous;
	pointer	data;
	DList	*next;
};

/************************************************************************
 *                                                                      *
 *	Tipo VoiceInfo														* 
 *                                                                      *
 ************************************************************************/

struct voice_info{
	char	*name;
	char	*locale;
	char	*gender;
	char	*mbrola_db;
	char	*sapi_number;
	char	*voice_number;
};
typedef struct voice_info VoiceInfo;

/************************************************************************
 *                                                                      *
 * Conjuntos de caracteres usados no compilador					        *
 *                                                                      *
 ************************************************************************/

extern const char maiusculas[];
extern const char minusculas[];
extern const char digitos[];
extern const char alfabeto[];
extern const char delimitadores[];
extern const char vogais[];
extern const char consoantes[];
extern const char acentos[];
extern const char incombinantes[];
extern const char QG[];
extern const char AO[];
extern const char EI[];
extern const char RL[];
extern const char S[];
extern const char H[];
extern const char LMNRZ[];
extern const char NRS[];
extern const char AEIO[];
extern const char AEIOUWY[];
extern const char pontuacoes[];

/************************************************************************
 *                                                                      *
 * Cadeias de caracteres acentuados										*
 *                                                                      *
 ************************************************************************/

extern bool chr_islower (char ch);
extern bool chr_isupper (char ch);
extern char chr_lower (char letra);
extern char chr_upper (char letra);

extern char *str_lower (const char *str);
extern void str_tolower (char *str);
extern char *str_upper (const char *str);
extern void str_toupper (char *str);

extern char *str_append (char *dest, char *src);
extern void str_fill (char *str, char val, int num);

extern void str_strip (char *str);

extern char *str_concat (char *str, ...);

extern bool str_has_prefix (char *str, char *prefix);
extern bool str_has_suffix (char *str, char *suffix);

extern char *str_int (char *fmt, int valor); 
extern char *str_float (char *fmt, float valor); 

/************************************************************************
 *                                                                      *
 * Listas simples														*
 *                                                                      *
 ************************************************************************/

extern int slist_length (SList *lista);
extern SList *slist_append (SList *lista, pointer data);
extern SList *slist_prepend (SList *lista, pointer data);
extern SList *slist_reverse (SList *lista);
extern SList *slist_remove (SList *lista, pointer data);
extern void slist_foreach (SList *lista, void (funcao)(), pointer data);
extern SList *slist_find (SList *lista, bool (funcao)(), pointer data);
extern SList *slist_next (SList *lista);
extern SList *slist_nth (SList *lista, int n);
extern void slist_free (SList *lista);

/************************************************************************
 *                                                                      *
 * Listas duplamente encadeadas											*
 *                                                                      *
 ************************************************************************/

extern int dlist_length (DList *lista);
extern DList *dlist_append (DList *lista, pointer data);
extern DList *dlist_prepend (DList *lista, pointer data);
extern DList *dlist_reverse (DList *lista);
extern DList *dlist_remove (DList *lista, pointer data);
extern void dlist_foreach (DList *lista, void (funcao)(), pointer data);
extern DList *dlist_find (DList *lista, bool (funcao)(), pointer data);
extern DList *dlist_previous (DList *lista);
extern DList *dlist_next (DList *lista);
extern DList *dlist_last (DList *lista);
extern DList *dlist_nth (DList *lista, int n);
extern DList *dlist_nth_prev (DList *lista, int n);
extern void dlist_free (DList *lista);

/************************************************************************
 *                                                                      *
 *	Log																	* 
 *                                                                      *
 ************************************************************************/

extern void log_init(char *parm_filename);
extern void log_write_string (const char *text, const char *string);
extern void log_write_int (const char *text, const int num);
extern void log_write_double (const char *text, const double num);

/************************************************************************
 *                                                                      *
 *	Procura de arquivos de configuração									* 
 *                                                                      *
 ************************************************************************/

extern SList *conf_get_files (char *nomeDir);

/************************************************************************
 *                                                                      *
 *	Vozes (VoiceInfo)													* 
 *                                                                      *
 ************************************************************************/

extern void vi_load_voices ();
extern SList *vi_get_names ();
extern VoiceInfo *vi_get_info (char *name);
extern void vi_free (VoiceInfo *vi);
extern void vi_free_all ();

#endif
