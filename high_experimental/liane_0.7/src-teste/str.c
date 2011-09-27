#include <stdlib.h>
#include <stdarg.h>
#include <stdio.h>
#include <string.h>

#include "liane-util.h"

static char buffer[TAM_FRASE];

/************************************************************************
 *                                                                      *
 * Conjuntos de caracteres												*
 *                                                                      *
 ************************************************************************/

const char maiusculas[] = "AEIOU��������������BCDFGHJKLMNPQRSTVWXYZ��";

const char minusculas[] = "aeiou��������������bcdfghjklmnpqrstvwxyz��";

const char digitos[] = "0123456789";

const char alfabeto[] = "AEIOU��������������aeiou��������������"
						"bcdfghjklmnpqrstvwxyz��BCDFGHJKLMNPQRSTVWXYZ��";

const char delimitadores[] = " ,:;.!?()";

const char vogais[] = "AEIOU��������������aeiou��������������";

const char consoantes[] = "bcdfghjklmnpqrstvwxyz��BCDFGHJKLMNPQRSTVWXYZ��";

const char acentos[] = "����������������������������";

const char incombinantes[] = "bcdfgjkmnpqstvxz";

const char QG[] = "qg";

const char AO[] = "AOao��������";

const char EI[] = "EIei����";

const char RL[] = "RLrl";

const char S[] = "Ss";

const char H[] = "Hh";

const char LMNRZ[] = "LMNRZlmnrz";

const char NRS[] = "NRSnrs";

const char AEIO[] = "AEIOaeio";

const char AEIOUWY[] = "AEIOUWYaeiouwy";

const char pontuacoes[] = ".,?!;:() ";

/************************************************************************
 *                                                                      *
 * str_contains															*
 *                                                                      *
 ************************************************************************/

bool str_contains (const char *string, char ch)
{
	while (*string != '\0') {
		if (*string == ch) return TRUE;
		
		string++;
	}
	
	return FALSE;
}

/************************************************************************
 *                                                                      *
 * chr_islower															*
 *                                                                      *
 ************************************************************************/

bool chr_islower (char ch)
{
	return str_contains (minusculas, ch);
}

/************************************************************************
 *                                                                      *
 * chr_isupper															*
 *                                                                      *
 ************************************************************************/

bool chr_isupper (char ch)
{
	return str_contains (maiusculas, ch);
}

/************************************************************************
 *                                                                      *
 * chr_lower															*
 *                                                                      *
 ************************************************************************/

char chr_lower (char letra)
{
	int i;
	
	for (i = 0; i < strlen ((char *)maiusculas); i++) {
		if (letra == maiusculas[i]) return minusculas[i];
	}
	
	return letra;
}

/************************************************************************
 *                                                                      *
 * chr_upper															*
 *                                                                      *
 ************************************************************************/

char chr_upper (char letra)
{
	int i;
	
	for (i = 0; i < strlen ((char *)minusculas); i++) {
		if (letra == minusculas[i]) return maiusculas[i];
	}
	
	return letra;
}

/************************************************************************
 *                                                                      *
 * str_lower															*
 *                                                                      *
 ************************************************************************/

char *str_lower (const char *str)
{
	char *str_low = strdup (str);
	char *aux = str_low;

	while (*str) *aux++ = chr_lower (*str++);
	
	return str_low;
}

/************************************************************************
 *                                                                      *
 * str_tolower															*
 *                                                                      *
 ************************************************************************/

void str_tolower (char *str)
{
	while (*str) {
		*str = chr_lower (*str);
		str++;
	}
}

/************************************************************************
 *                                                                      *
 * str_upper															*
 *                                                                      *
 ************************************************************************/

char *str_upper (const char *str)
{
	char *str_up = strdup (str);
	char *aux = str_up;

	while (*str) *aux++ = chr_upper (*str++);
	
	return str_up;
}

/************************************************************************
 *                                                                      *
 * str_toupper															*
 *                                                                      *
 ************************************************************************/

void str_toupper (char *str)
{
	while (*str) {
		*str = chr_upper (*str);
		str++;
	}
}

/************************************************************************
 *                                                                      *
 * str_append															*
 *                                                                      *
 ************************************************************************/

char *str_append (char *dest, char *src)
{
	while (*dest != '\0') dest++;
	while (*src != '\0') *dest++ = *src++;
	*dest = '\0';
	
	return dest;
}

/************************************************************************
 *                                                                      *
 * str_fill																*
 *                                                                      *
 ************************************************************************/

void str_fill (char *str, char val, int num)
{
	char *pt = str;
	
	while (num-- > 0) *pt++ = val;
}

/************************************************************************
 *                                                                      *
 * str_strip															*
 *                                                                      *
 ************************************************************************/

void str_strip (char *str)
{
	char *aux = str;
	char *dest = &buffer[0];
	while (*aux == ' ') aux++;
	while (*aux != '\0') *dest++ = *aux++;
	while (*--dest == ' ') ;
	*++dest = '\0';
	
	strcpy (str, buffer);
}

/************************************************************************
 *                                                                      *
 * str_concat															*
 *                                                                      *
 ************************************************************************/

char *str_concat (char *str, ...)
{
	char *dest;
	char *arg;
	
	va_list args;
	va_start (args, str);
	
	dest = &buffer[0];
	while (*str != '\0') *dest++ = *str++;  
	while ((arg = va_arg (args, char *)) != NULL) {
		while (*arg != '\0') *dest++ = *arg++;  
	}
	va_end (args);
	*dest = '\0';
	return strdup (buffer);
}

/************************************************************************
 *                                                                      *
 * str_has_prefix														*
 *                                                                      *
 ************************************************************************/

bool str_has_prefix (char *str, char *prefix)
{
	while (*prefix != '\0') {
		if (*str++ != *prefix++) return FALSE;
	}
	
	return TRUE;
}

/************************************************************************
 *                                                                      *
 * str_has_suffix														*
 *                                                                      *
 ************************************************************************/

bool str_has_suffix (char *str, char *suffix)
{
	int inicio = strlen (str) - strlen (suffix);
	
	if (inicio < 0) return FALSE;
	
	str = str + inicio;
	while (*str != '\0') {
		if (*str++ != *suffix++) return FALSE;
	}
	
	return TRUE;
}

/************************************************************************
 *                                                                      *
 * str_int																*
 *                                                                      *
 ************************************************************************/

extern char *str_int (char *fmt, int valor)
{
	sprintf (buffer, fmt, valor);
	return strdup (buffer);
} 

/************************************************************************
 *                                                                      *
 * str_float															*
 *                                                                      *
 ************************************************************************/

extern char *str_float (char *fmt, float valor)
{
	sprintf (buffer, fmt, valor);
	return strdup (buffer);
} 

