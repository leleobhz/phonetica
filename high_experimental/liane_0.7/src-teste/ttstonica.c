#include <stdlib.h>
#include <string.h>
#include <stdio.h>
#include "liane-util.h"

/************************************************************************
 *                                                                      *
 * descobre_tonica														*
 *                                                                      *
 ************************************************************************/

char *descobre_tonica (char *palavra)
{
	int		estado;
	char	*ch;
	char	*sentinela;
	char	*pos_acento = NULL;
	char	*retorno = NULL;

	ch = palavra + strlen (palavra) - 1;
	sentinela = palavra - 1;
	while (ch > sentinela) {
		if (EH_ACENTUADA (*ch)) return ch;
		if ((pos_acento == NULL) && (EH_VOGAL (*ch))) pos_acento = ch;
		
		ch--;
	}
	
	if (pos_acento == NULL) return sentinela;
	
	estado = 0;
	ch = palavra + strlen (palavra) - 1;
	while (ch > sentinela) {
		switch (estado) {
			case 0:
				switch (*ch) {
					case 'a':
					case 'e':
					case 'o':
						retorno = ch;
						estado = 1;
						break;
					case 'u':
						retorno = ch;
						estado = 10;
						break;
					case 'i':
						retorno = ch;
						estado = 20;
						break;
					case 's':
						estado = 0;
						break;
					case 'm':
						estado = 40;
						break;
					default:
						estado = 30;
				}
				break;
			
			case 1:
				if (*ch == 'u') {
					estado = 4;
				}
				else if (EH_VOGAL (*ch)) {
					retorno = ch;
					ch = sentinela;
				}
				else {
					estado = 2;
				}
				break;
			
			case 2:
				switch (*ch) {
					case 'i':
					case 'u':
						retorno = ch;
						estado = 3;
						break;
					case 'a':
					case 'e':
					case 'o':
						retorno = ch;
						ch = sentinela;
				}
				break;
			
			case 3:
				if ((*ch == 'a') || (*ch == 'e') || (*ch == 'o')) {
					retorno = ch;
					ch = sentinela;
				}
				else if (*ch == 'u') {
					estado = 5;
				}
				else {
					ch = sentinela;
				}
				break;
			
			case 4:
				if ((*ch == 'g') || (*ch == 'q')) {
					retorno = ch + 2;
					estado = 2;
				}
				else {
					retorno = ch + 1;
					ch = sentinela;
				}
				break;
			
			case 5:
				if ((*ch == 'g') || (*ch == 'q')) {
					retorno = ch + 2;
				}
				else {
					retorno = ch + 1;
				}
				ch = sentinela;
				break;
			
			case 10:
				if (EH_A_E_I_ou_O (*ch)) retorno = ch;
				ch = sentinela;
				break;
			
			case 20:
				if ((*ch == 'a') || (*ch == 'e') || (*ch == 'o')) {
					retorno = ch;
				}
				else if (*ch == 'u') {
					estado = 21;
				}
				else {
					ch = sentinela;
				}
				break;
			
			case 21:
				if ((*ch == 'g') || (*ch == 'q')) {
					retorno = ch + 2;
				}
				else {
					retorno = ch + 1;
				}
				ch = sentinela;
				break;
								
			case 30:
				if (EH_VOGAL (*ch)) {
					retorno = ch;
					ch = sentinela;
				}
				break;
			
			case 40:
				if ((*ch == 'i') || (*ch == 'o') || (*ch == 'u')) {
					retorno = ch;
					ch = sentinela;
				}
				else if ((*ch == 'a') || (*ch == 'e')) {
					retorno = ch;
					estado = 1;
				}
				else {
					estado = 0;
				}
		}
		
		ch--;
	}
		
	return retorno;
}
