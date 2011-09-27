#include <stdlib.h>
#include <string.h>
#include "liane-util.h"

/************************************************************************
 *                                                                      *
 * trata_crlf															*
 *                                                                      *
 ************************************************************************/

void trata_crlf (char *linha)
{
	char *ultimo_ch;
	
	ultimo_ch =  linha + strlen (linha) - 1;
	*ultimo_ch-- = '\0';	// newline
	if (*ultimo_ch == '\r') *ultimo_ch = '\0';
	str_strip (linha);
}

/************************************************************************
 *                                                                      *
 * eh_comentario														*
 *                                                                      *
 ************************************************************************/

bool eh_comentario (char *linha)
{
	return (*linha == '\0') || (*linha == ';');
}

