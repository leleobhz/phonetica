#include "liane-util.h"

/************************************************************************
 *                                                                      *
 *				SLIST					*
 *                                                                      *
 ************************************************************************/


/************************************************************************
 *                                                                      *
 * slist_length								*
 *                                                                      *
 ************************************************************************/

int slist_length (SList *lista)
{
	int count;
	
	for (count = 0; lista != NULL; count++, lista = lista->next);

	return count;
}

/************************************************************************
 *                                                                      *
 * slist_append								*
 *                                                                      *
 ************************************************************************/

SList *slist_append (SList *lista, pointer data)
{
	SList *aux = NULL;
	SList *novo = malloc (sizeof (SList));
	
	novo->data = data;
	novo->next = NULL;
	
	if (lista == NULL) return novo;

	aux = lista;
	while (aux->next != NULL) aux = aux->next;
	aux->next = novo;
	
	return lista;
}

/************************************************************************
 *                                                                      *
 * slist_prepend							*
 *                                                                      *
 ************************************************************************/

SList *slist_prepend (SList *lista, pointer data)
{
	SList *novo = malloc (sizeof (SList));
	
	novo->data = data;
	novo->next = lista;
	
	return novo;
}

/************************************************************************
 *                                                                      *
 * slist_reverse							*
 *                                                                      *
 ************************************************************************/

SList *slist_reverse (SList *lista)
{
	SList *ant;
	SList *prox;
	
	if (lista == NULL) return lista;
	
	ant = NULL;
	while (lista != NULL) {
		prox = lista->next;
		lista->next = ant;
		ant = lista;
		lista = prox;
	}
	return ant;
}

/************************************************************************
 *                                                                      *
 * slist_remove								*
 *                                                                      *
 ************************************************************************/

SList *slist_remove (SList *lista, pointer data)
{
	SList *inic;
	SList *ant;

	if (lista == NULL) return lista;
	
	inic = lista;
	ant = NULL;
	
	while (lista != NULL) {
		if (lista->data == data) {
			if (ant == NULL) inic = lista->next;
			else ant->next = lista->next;
			
			free (data);
			free (lista);
			return inic;
		}
		
		ant = lista;
		lista = lista->next;
	}
	
	return inic;
}

/************************************************************************
 *                                                                      *
 * slist_foreach							*
 *                                                                      *
 ************************************************************************/

void slist_foreach (SList *lista, void (funcao)(), pointer data)
{
	while (lista != NULL) {
		funcao (lista->data, data);
		lista = lista->next;
	}
}

/************************************************************************
 *                                                                      *
 * slist_find								*
 *                                                                      *
 ************************************************************************/

SList *slist_find (SList *lista, bool (funcao)(), pointer data)
{
	while (lista != NULL) {
		if (funcao (lista->data, data)) return lista;
		lista = lista->next;
	}
	
	return NULL;
}

/************************************************************************
 *                                                                      *
 * slist_next								*
 *                                                                      *
 ************************************************************************/

SList *slist_next (SList *lista)
{
	if (lista == NULL) return NULL;
	
	return lista->next;
}

/************************************************************************
 *                                                                      *
 * slist_nth								*
 *                                                                      *
 ************************************************************************/

SList *slist_nth (SList *lista, int n)
{
	int i;
	
	if (lista == NULL) return NULL;
	
	for (i = 0; i < n; i++) {
		if (lista->next == NULL) return NULL;
		lista = lista->next;
	}
	
	return lista;
}

/************************************************************************
 *                                                                      *
 * slist_free								*
 *                                                                      *
 ************************************************************************/

void slist_free (SList *lista)
{
	SList *elem;
	
	while (lista != NULL) {
		elem = lista;
		lista = lista->next;

		free (elem);
	}
}


/************************************************************************
 *                                                                      *
 *				DLIST					*
 *                                                                      *
 ************************************************************************/


/************************************************************************
 *                                                                      *
 * dlist_length								*
 *                                                                      *
 ************************************************************************/

int dlist_length (DList *lista)
{
	int count;
	
	for (count = 0; lista != NULL; count++, lista = lista->next);

	return count;
}

/************************************************************************
 *                                                                      *
 * dlist_append								*
 *                                                                      *
 ************************************************************************/

DList *dlist_append (DList *lista, pointer data)
{
	DList *aux = NULL;
	DList *novo = malloc (sizeof (DList));
	
	novo->previous = NULL;
	novo->data = data;
	novo->next = NULL;
	
	if (lista == NULL) return novo;

	aux = lista;
	while (aux->next != NULL) aux = aux->next;
	aux->next = novo;
	novo->previous = aux;
	
	return lista;
}

/************************************************************************
 *                                                                      *
 * dlist_prepend							*
 *                                                                      *
 ************************************************************************/

DList *dlist_prepend (DList *lista, pointer data)
{
	DList *novo = malloc (sizeof (DList));

	novo->previous = NULL;
	novo->data = data;
	novo->next = lista;
	
	if (lista != NULL) lista->previous = novo;
	
	return novo;
}

/************************************************************************
 *                                                                      *
 * dlist_reverse							*
 *                                                                      *
 ************************************************************************/

DList *dlist_reverse (DList *lista)
{
	DList *ant;
	
	if (lista == NULL) return lista;
	
	while (lista != NULL) {
		ant = lista->previous;
		lista->previous = lista->next;
		lista->next = ant;

		ant = lista;
		lista = lista->previous;
	}
	return ant;
}

/************************************************************************
 *                                                                      *
 * dlist_remove								*
 *                                                                      *
 ************************************************************************/

DList *dlist_remove (DList *lista, pointer data)
{
	DList *inic;
	DList *ant;
	DList *prox;

	if (lista == NULL) return lista;
	
	inic = lista;
	ant = NULL;
	
	while (lista != NULL) {
		if (lista->data == data) {
			if (ant == NULL) {
				inic = lista->next;
				inic->previous = NULL;
			}
			else {
				prox = lista->next;
				ant->next = prox;
				prox->previous = ant;
			}
			
			free (data);
			free (lista);
			return inic;
		}
		
		ant = lista;
		lista = lista->next;
	}
	
	return inic;
}

/************************************************************************
 *                                                                      *
 * dlist_foreach							*
 *                                                                      *
 ************************************************************************/

void dlist_foreach (DList *lista, void (funcao)(), pointer data)
{
	while (lista != NULL) {
		funcao (lista->data, data);
		lista = lista->next;
	}
}

/************************************************************************
 *                                                                      *
 * dlist_find								*
 *                                                                      *
 ************************************************************************/

DList *dlist_find (DList *lista, bool (funcao)(), pointer data)
{
	while (lista != NULL) {
		if (funcao (lista->data, data)) return lista;
		lista = lista->next;
	}
	
	return NULL;
}

/************************************************************************
 *                                                                      *
 * dlist_previous							*
 *                                                                      *
 ************************************************************************/

DList *dlist_previous (DList *lista)
{
	if (lista == NULL) return NULL;
	
	return lista->previous;
}

/************************************************************************
 *                                                                      *
 * dlist_next								*
 *                                                                      *
 ************************************************************************/

DList *dlist_next (DList *lista)
{
	if (lista == NULL) return NULL;
	
	return lista->next;
}

/************************************************************************
 *                                                                      *
 * dlist_last								*
 *                                                                      *
 ************************************************************************/

DList *dlist_last (DList *lista)
{
	if (lista == NULL) return NULL;

	while (lista->next != NULL) lista = lista->next;
	
	return lista;
}

/************************************************************************
 *                                                                      *
 * dlist_nth								*
 *                                                                      *
 ************************************************************************/

DList *dlist_nth (DList *lista, int n)
{
	int i;
	
	if (lista == NULL) return NULL;
	
	for (i = 0; i < n; i++) {
		if (lista->next == NULL) return NULL;
		lista = lista->next;
	}
	
	return lista;
}

/************************************************************************
 *                                                                      *
 * dlist_nth_prev							*
 *                                                                      *
 ************************************************************************/

DList *dlist_nth_prev (DList *lista, int n)
{
	int i;
	
	if (lista == NULL) return NULL;
	
	for (i = 0; i < n; i++) {
		if (lista->previous == NULL) return NULL;
		lista = lista->previous;
	}
	
	return lista;
}

/************************************************************************
 *                                                                      *
 * dlist_free								*
 *                                                                      *
 ************************************************************************/

void dlist_free (DList *lista)
{
	DList *elem;
	
	while (lista != NULL) {
		elem = lista;
		lista = lista->next;

		free (elem);
	}
}

