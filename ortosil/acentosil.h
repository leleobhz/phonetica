/*         Programa para tratamento ling"u'istico                 */
/*         Modulo 2 - Acentua,c~ao                                */

#include <stdio.h>
#include <string.h>

#define VOGACENT         "ƒˆ“ ‚¡¢£…"
#define VOGAL            "aeiouÆäƒˆ“ ‚¡¢£…"
#define CONSOANTE        "bc‡dfghjklmnpqrstvwxyz"
#define FIMOXIT          "ar*er*ir*or*ur*is*us*az*ez*iz*oz*uz*al*el*il*ol*ul*am*im*om*um"


#define VOGCHAR(car)     strchr(VOGAL,(car))
#define CONSCHAR(car)    strchr(CONSOANTE,(car))
#define ACENTUADO(car)   strchr(VOGACENT,(car))



/* Modifica "palavra" de modo que ela contenha o acento                */
/* "palavra" deve ser alocado por quem chamou com tamanho suficiente para
    a inclus~ao do acento */

void acentua(char *palavra)
{
     int tamanho, i, pos, vowel = 0;

     tamanho = strlen(palavra);

      
     if ((!strcmp(palavra,"rodrigues"))) //!= tamanho) 
     {
		  pos = 4;
		  for(i = tamanho;i >= pos;i--)palavra[i+1] = palavra[i];
		  palavra[pos+1] = '\'';
     }
	 
     if ((!strcmp(palavra,"alex"))) //!= tamanho) 
     {
		  pos = 2;
		  for(i = tamanho;i >= pos;i--)palavra[i+1] = palavra[i];
		  palavra[pos+1] = '\'';
     }

	 if ((!strcmp(palavra,"marmitex"))) //!= tamanho) 
     {
		  pos = 6;
		  for(i = tamanho;i >= pos;i--)palavra[i+1] = palavra[i];
		  palavra[pos+1] = '\'';
     } 

     
     /* 1a. regra - colocar o acento se ele estiver grafado */
     if ((pos = strcspn(palavra,VOGACENT)) != tamanho)    //coloquei else if 
     {
		  for(i = tamanho;i >= pos;i--) palavra[i+1] = palavra[i];
		  palavra[pos+1] = '\'';
		  return;
     }

     /* 2a. regra - acentuar os monoss'ilabos da forma
	 * V, VC, CV, CVC, CVCC, CCVC, CCV    */
     if (tamanho <= 4)     /* pode ser um dos monoss'ilabos desejado */   //coloquei else if
     {
	      char *classe;

	      classe = (char *) malloc( (tamanho*sizeof(char))+1 );
	      for(i = 0;i < tamanho; i++)
		    if (VOGCHAR(palavra[i]))
			    classe[i] = 'V';
		    else if (CONSCHAR(palavra[i]))
			    classe[i] = 'C';
	      classe[tamanho] = '\0';
	      if ((strstr("V*VC*CV*CVC*CVCC*CCVC*CCV",classe)) && (strcmp(classe,"C")))
	      {
		      pos = strcspn(classe,"V");
		      for(i = tamanho;i >= pos;i--) palavra[i+1] = palavra[i];
		      palavra[pos+1] = '\'';
		      return;
	       }
       }

       /* 3a. regra acentuar as 1as. vogais da direita para a esquerda
	das palavras terminadas em Æ, ar, er, ir, or,ur, i, u, is, us,
	az, ez, iz, oz, uz, al, el, il, ol, ul, am, im, om, um

	- exce,c~oes que devem ser acentuadas na 2a. vogal:
	  * Terminadas em "is" ou "us" precedidas de CV , onde C 'e diferente de "q"
	  * Terminadas em "i" ou "u" precedidas de CV, onde C 'e diferente de  "q"
	  ex:  varais, cais, naus */

       if (strstr(FIMOXIT,&palavra[tamanho-2]))  //coloquei else if
       {
	    if ( (strstr("is*us",&palavra[tamanho-2])) &&
		 (strchr(VOGAL,palavra[tamanho-3])) &&
		 (strchr(CONSOANTE,palavra[tamanho-4])) &&
		 (palavra[tamanho-4] != 'q') ) pos = tamanho - 3;
	    else pos = tamanho - 2;
	    for(i = tamanho;i >= pos;i--) palavra[i+1] = palavra[i];
	    palavra[pos+1] = '\'';
	    return;
       }
       if (strchr("u*i*Æ",palavra[tamanho-1]))
       {
	    if ( (strstr("i*u",&palavra[tamanho-1])) &&
		 (strchr(VOGAL,palavra[tamanho-2])) &&
		 (palavra[tamanho-3] != 'q') ) pos = tamanho - 2;
	    else pos = tamanho - 1;
	    for(i = tamanho;i >= pos;i--) palavra[i+1] = palavra[i];
	    palavra[pos+1] = '\'';
	    return;
       }


       /* 4a. regra - o que chegou at'e este ponto sem acento 'e paro-
	 x'itona, portanto deve ter a 2a. vogal (da direita para esquer-
	 da) acentuada , com exce,c~ao dos ditongos  (3a. vogal) */

       for (i = tamanho-1;i >= 1; i--)
       {
	    if (strchr(VOGAL,palavra[i])) vowel++;
	    if (vowel == 2)
		if (strchr("q*g",palavra[i+1])) //i-1 antigo que da acento atras da vogal
		      vowel--;
		else   break;
       }
       pos = i;
       if ((strchr("i*u",palavra[pos])) && (strchr(VOGAL,palavra[pos-1])) &&
	    (!strchr("q*g",palavra[pos-2])))
	      pos--;
       for(i = tamanho;i >= pos;i--) palavra[i+1] = palavra[i];
       palavra[pos+1] = '\''; //pos+1
       return;
}








