# include "acentosil.h"    //faz a acentuação das palavras
# include <stdio.h>
# include <math.h>
# include <ctype.h>
# include <string.h>
# include <fcntl.h>
# define ConsoanteSemMudanca "bdfkmptvz"
# define ConsoanteComMudanca "c‡ghjlnqrsxw‡"  //alt+135=ç exceto o "h"
# define Consoante "bdfkmptvzc‡gjlnqrsxw‡"   // TIREI O R V SE DA PAU
# define ConsoanteR "bdfkmptvzc‡gjlnqsxw‡"  //para o r de coda não pode ter r
# define ConsoanteP "bdfkmptvzc‡gjnqsxw‡"
# define ConsoanteS "bdfkmptvzgjlnqrxw" //para o s de coda não pode ter s c e ç
# define ConsoanteX "bcdfkmptvzgjlnqrxw"  //tirei c para excitado   Coloquei c mas ver c+i ou c+e
# define VogalSemMudanca "aeiou"
# define VogalComMudanca "Æä ‚¡¢£ƒˆ“"  //ãõáéíóúâêô VER ASCII TABLE
# define VogalS "Æä ¢£ƒ“aou"
# define VogalNasal "Æä"
# define Vogal "aeiouÆä ‚¡¢£ƒˆ“"    // VER MEIRELES, JOYCE, ALEXSANDRO
# define DigrafoS "sci*sce"
# include <ctype.h>

main()
{
char sil[16]="", word[56], phonword[56]="", tonicidade[25]="",*silaba, syl,sile;  //syl e so p marcar tonica
int sizeword,ini=0,Snumberofsil=0,Snumberofpre=0, tamanho,
    nsil=1,fim=0,V=0,i, controle = ' '; //ctl=0;
while (controle != 's')
{
int Snumberofsil = 0, Snumberofpre = 0, ini = 0, nsil = 1, fim = 0, V = 0;
char phonword[56]="", tonicidade[25]="", sil[]="", syl=' ', sile = ' ';
/*silaba[0]= ' ';
silaba[1]= ' ';
silaba[2]= ' ';
silaba[3]= ' ';
silaba[4]= ' ';
silaba[5]= ' ';
silaba[6]= ' ';  */

printf("Ortosil 0.13\nDesenvolvido por Alexsandro Meireles\nDigite");
printf(" a palavra: ");
gets(word);
for (i=0; word[i];i++){  //essa parte é importante para evitar problemas
   word[i]=(tolower(word[i])); //com letras maiusculas
   }
acentua(word);  //função que coloca o acento na forma ortográfica (Listas)
printf("%s",word);
sizeword = strlen(word);
//phonword = (char *) malloc( (sizeword*sizeof(char))+1);
silaba = (char *) malloc( (7*sizeof(char)));  //para tRa'NS + nulo    TAVA 7

while(ini<=(sizeword-1))  //verificar se eh sizeword ou sizeword -1
{
int ctl = 0;

/******************TRABALHA ONSET SILABICO SIMPLES**********************/

if(strchr(ConsoanteComMudanca, word[ini])) //trabalha ataque silábico
{
    if(word[ini] == 'c')   //resolve c
    {
      if(strchr("ei‚¡ˆ", word[ini+1]))
      {
      silaba[ctl] = 's';
      fim++;
      ctl++;
      }
      else if(word[ini+1] == 'h')
      {
      silaba[ctl] = 's';
      silaba[ctl+1] = 'h';
      fim+=2;
      ctl+=2;
      }
      else
      {
      silaba[ctl] = 'k';
      fim++;              //ver se tem que colocar em todos
      ctl++;
      }
    }  // fim c

    if(word[ini] == '‡') //resolve ç
    {
      silaba[ctl] = 's';
      fim++;
      ctl++;
    } // fim ç

    if(word[ini] == 'g')   //resolve g
    {
      if(strchr("ei‚¡ˆ", word[ini+1]))
      {
      silaba[ctl] = 'z';
      silaba[ctl+1] = 'h';
      fim++;
      ctl+=2;
      }
      else
      {
      silaba[ctl] = 'g';
      fim++;
      ctl++;
      }
      if(word[fim] == 'u')// implementa guerra
        fim++;
    } // fim g

    if(word[ini] == 'j')   //resolve g
    {
      silaba[ctl] = 'z';
      silaba[ctl+1] = 'h';
      fim++;
      ctl+=2;
    }

    if(word[ini] == 'h') //resolve h mudo
    {
      silaba[ctl] = ' ';
      fim++;
      ctl++;
    } // fim h mudo

    if(word[ini] == 'l')   //resolve l no inicio de silaba
    {
      if(word[ini+1] == 'h')
      {
      silaba[ctl] = 'l';
      silaba[ctl+1] = 'h';
      fim+=2;
      ctl+=2;
      }
      else if(word[ini+1] != 'h'){
      silaba[ctl] = 'l';
      fim++;              //ver se tem que colocar em todos
      ctl++;
      }
    }  // fim l no inicio de silaba

    if(word[ini] == 'n')   //resolve n no inicio de silaba
    {
      if(word[ini+1] == 'h')
      {
      silaba[ctl] = 'n';
      silaba[ctl+1] = 'h';
      fim+=2;
      ctl+=2;
      }
      else
      {
      silaba[ctl] = 'n';
      fim++;              //ver se tem que colocar em todos
      ctl++;
      }
    }  // fim n no inicio de silaba

    if(word[ini] == 'q')   //resolve q
    {
      if((toascii(word[ini+1])) == 1)//'ü')
      {
      silaba[ctl] = 'k';
      silaba[ctl+1] = 'U';
      fim+=2;
      ctl+=2;
      }
      else if(strchr("ei‚¡ˆ", word[ini+2]))
      {
      silaba[ctl] = 'k';
      fim += 2;
      ctl += 1;
      }
      else  //ACHO QUE PODE TIRAR ISSO
      {
      silaba[ctl] = 'k';
      silaba[ctl+1] = 'U';
      fim+=2;
      ctl+=2;
      }
    } // fim q

    if(word[ini] == 'w') //resolve w
    {
      silaba[ctl] = 'v';
      fim++;
      ctl++;
    } // fim w

    if(word[ini] == 'r')   //LIDA COM R E RR
    {
      if((ini>= 1) && ((strchr(Vogal, word[ini-1]))
      || (strchr(Vogal, word[ini-2]))) && (strchr(Vogal, word[ini+1]))
        && (!(strchr(Consoante, word[ini-1]))))
      {    // AQUI ACEITA ABROGAR > INCLUIR ACENTO PARA RESOLVER O PROBLEMA
        silaba[ctl] = 'R';
        fim++;
        ctl++;
      }
      else
      {
        silaba[ctl] = 'r';
        fim++;
        ctl++;
      }
      if(word[ini+1] == 'r')
        fim++;
    }

    if(word[ini] == 's')    //resolve s
    {
      if ((ini>= 1) && ((strchr(Vogal, word[ini-1]))
      || (strchr(Vogal, word[ini-2]))) && (strchr(Vogal, word[ini+1]))  //ver se precisa word ini-1 == '\''
        && (!(strchr(Consoante, word[ini-1])))
      || ((word[ini-1] == 'n') && (!strchr(Vogal, word[ini+2]))) ) //para ansiedade
      {  // PROBLEMA AQUI
        silaba[ctl] = 'z';
        fim++;
        ctl++;
      }
      else
      {
         silaba[ctl] = 's';
         fim++;
         ctl++;
      }
      if((word[ini+1] == 'c') || (word[ini+1] == '‡') || (word[ini+1] == 's'))
      {   //c ç
         fim++;
      }
    }  // fim de s

/********************inicio do XXXXXXXXXXX **************************/
    if(word[ini] == 'x')    //resolve x
    {
       silaba[ctl] = 's';
       silaba[ctl+1] = 'h';
       fim++;
       ctl+=2;                  
    }
/*********************fim do XXXXXXXXXXXXXX *************************/

} //fecha if de consoante com mudança
if(strchr(ConsoanteSemMudanca, word[ini]))
{  //aplica regras aqui  COLOCAR REGRA
//ESPECIAL PARA N, L, Z, C POR CAUSA DE NH, LH, ZH, CH, ETC
silaba[ctl] = word[ini];
fim++;
ctl++;

}   //  fim if consoante sem mudanca

/***************** FIM DO ATAQUE SILÁBICO SIMPLES *************************/

/******************TRABALHA ONSET SILABICO COMPLEXO**********************/

if(word[ini+1] == 'r')
{
  if((word[ini] != 'r') && (!(strchr(Vogal, word[ini]))))  //resolve r
  {
    silaba[ctl] = 'R';
    fim++;
    ctl++;
  }
}

if((word[ini+1] == 'l') && (strchr(Consoante, word[ini])))  //conferir transatlantico
{
  silaba[ctl] = 'l';
  fim++;
  ctl++;
}


/***************** FIM DO ATAQUE SILÁBICO COMPLEXO *************************/

//aplica regras aqui  COLOCAR REGRA
//ESPECIAL PARA N, L, Z, C POR CAUSA DE NH, LH, ZH, CH, ETC


/***************************** A PARTIR DA VOGAL ***************************/
if(strchr(Vogal, word[fim]))
{
int V = fim;

/*********************** SE DITONGO CRESCENTE OU TRITONGO********************/
if((V+2) <= sizeword)
{
   if((word[V] == 'i') && (strchr(Vogal, word[V+1])) && (syl == 'T'))
   {
   silaba[ctl] = 'I';
   silaba[ctl+1] = word[V+1];
   fim++;
   ctl++;
   sile = 'P';
   strcat(sil, "P");
   silaba[ctl] = 'i';
   printf("\nTONICIDADE = %c\n", sile);
   }
   else if((word[V]== 'i') && (strchr(Vogal, word[V+1])) && (!(word[V+2] == '\'')))
   {
   silaba[ctl] = 'I';
   silaba[ctl+1] = word[V+1];
   fim+=2;
   ctl+=2;
   sile = 'A';
   strcat(sil, "A");
   silaba[ctl] = 'i';
   printf("\nTONICIDADE = %c\n", sile);
   } // VERIFICAR FIM = V NO U

   if((word[V] == 'u') && (strchr(Vogal, word[V+1])) && (word[V+2] != '\''))
   {
   silaba[ctl] = 'U';
   silaba[ctl+1] = word[V+1];  // VERIFICAR TONICIDADE AQUI
   fim+=2;
   ctl+=2;
   }
}

/*********************** FIM DITONGO CRESCENTE OU TRITONGO********************/

/************************** CONVERTENDO OS TIPOS DE As************************/
if((word[V] == 'a') || (word[V] == ' ') || (word[V] == 'ƒ'))  //mudei p V
{
   if(word[V+1] == '\'')
   {
   syl = 'T';  //isso é só para marcar se já ocorreu uma silaba tonica
   sile = 'T';
   strcat(sil,"T");      //CONCATENAR NO FINAL
   silaba[ctl] = 'a';
   silaba[ctl+1] = '\'';
   fim+=2;
   ctl+=2;
   printf("\n");
   printf("\nTONICIDADE = %c\n", sile);
   }
   else if(syl != 'T')
   {
   sile = 'A';
   strcat(sil, "A");
   silaba[ctl] = 'a';
   fim++;
   ctl++;
   printf("\nTONICIDADE = %c\n", sile);
   }
   else
   {
   sile = 'P';
   strcat(sil,"P");
   silaba[ctl] = 'A';
   fim++;
   ctl++;
   printf("\nTONICIDADE = %c\n", sile);
   }
}

/************************ FIM CONVERTENDO OS TIPOS DE As**********************/

/************************** CONVERTENDO OS TIPOS DE Es************************/
if((word[V] == 'e') || (word[V] == 'ˆ'))  //mudei para V
{
   if(word[V+1] == '\'')
   {
   syl = 'T';  //isso é só para marcar se já ocorreu uma silaba tonica
   strcat(sil,"T");      //CONCATENAR NO FINAL
   sile = 'T';
   silaba[ctl] = 'e';    //PRRRRRRRRRRROBLEMA AQUI ABERTA OU FECHADA
   silaba[ctl+1] = '\'';
   fim+=2;
   ctl+=2;
   printf("\nTONICIDADE = %c\n", sile);
   }
   else if(syl != 'T')
   {
   sile = 'A';
   strcat(sil, "A");
   silaba[ctl] = 'e';
   fim++;
   ctl++;
   printf("\nTONICIDADE = %c\n", sile);
   }
   else
   {
   sile = 'P';
   strcat(sil,"P");
   silaba[ctl] = 'E';
   fim++;
   ctl++;
   printf("\nTONICIDADE = %c\n", sile);
   }
}

if((word[V] == '‚') && ((word[V+2] != 'm') && (word[V+2] != 'n')))  // PARA VOGAL ABERTA  VER SE PRECISA COLOCAR V+1 == '\''
{
   if(word[V+1] == '\'')          // CONFERIR TODOS OS FINS E CTLS
   {
   sile = 'T';
   syl = 'T';
   strcat(sil,"T");
   silaba[ctl] = 'e';
   silaba[ctl+1] = 'h';
   silaba[ctl+2] = '\'';
   fim+=2;
   ctl+=3;
   printf("\nTONICIDADE = %c\n", sile);
   }
}
else if(word[V] == '‚')    // é    experiênte entra aqui
{
   if(word[V+1] == '\'')
   {
   sile = 'T';
   syl = 'T';
   strcat(sil,"T");
   silaba[ctl] = 'e';
   silaba[ctl+1] = '\'';
   fim+=2;
   ctl+=2;
   printf("\nTONICIDADE = %c\n", sile);
   }
}

/************************ FIM CONVERTENDO OS TIPOS DE Es**********************/

/************************** CONVERTENDO OS TIPOS DE Is************************/
if(((word[V] == 'i') || (word[V] == '¡')) && (strchr(Consoante, word[V+1]))) //TROQUEI FIM POR V   VER ANSIEDADE
{                                 // ACHO QUE RESOLVI COLOCAR I, U para ditongo decrescente
   if(word[V+1] == '\'')
   {
   sile = 'T';
   syl = 'T';  //isso é só para marcar se já ocorreu uma silaba tonica
   strcat(sil,"T");      //CONCATENAR NO FINAL
   silaba[ctl] = 'i';    //PRRRRRRRRRRROBLEMA AQUI ABERTA OU FECHADA
   silaba[ctl+1] = '\'';
   fim+=2;
   ctl+=2;
   printf("\nTONICIDADE = %c\n", sile);
   }
   else if(syl != 'T')
   {
   sile = 'A';
   strcat(sil, "A");
   silaba[ctl] = 'i';
   fim++;
   ctl++;
   printf("\nTONICIDADE = %c\n", sile);
   }
   else
   {
   sile = 'P';
   strcat(sil,"P");
   silaba[ctl] = 'I';
   fim++;
   ctl++;
   printf("\nTONICIDADE = %c\n", sile);
   }
}
else if(((word[V] == 'i') || (word[V] == '¡')) && (strchr(Vogal, word[V+1]))
  && (word[V+2] == '\''))   // PARA RESOLVER EXPERIÊNCIA
  {
   sile = 'A';
   strcat(sil, "A");
   silaba[ctl] = 'i';
   fim++;
   ctl++;
   printf("\nTONICIDADE = %c\n", sile);
   }

if((word[V] == 'i') || (word[V] == '¡'))
{
   if((strchr(Vogal, word[V])) && (word[V+1] == '\''))  //RESOLVENDO PAÍS
   {
      sile = 'T';
      syl = 'T';
      strcat(sil, "T");
      silaba[ctl] = 'i';
      silaba[ctl+1] = '\'';
      fim+=2;
      ctl+=2;
      printf("\nTONICIDADE = %c\n", sile);
   }
}

/************************ FIM CONVERTENDO OS TIPOS DE Is**********************/

/************************** CONVERTENDO OS TIPOS DE Os************************/
if((word[V] == 'o') || (word[V] == '“'))
{
   if(word[V+1] == '\'')
   {
   sile = 'T';
   syl = 'T';  //isso é só para marcar se já ocorreu uma silaba tonica
   strcat(sil,"T");      //CONCATENAR NO FINAL
   silaba[ctl] = 'o';    //PRRRRRRRRRRROBLEMA AQUI ABERTA OU FECHADA
   silaba[ctl+1] = '\'';
   fim+=2;
   ctl+=2;
   printf("\nTONICIDADE = %c\n", sile);
   }
   else if(syl != 'T')
   {
   sile = 'A';
   strcat(sil, "A");
   silaba[ctl] = 'o';
   fim++;
   ctl++;
   printf("\nTONICIDADE = %c\n", sile);
   }
   else
   {
   sile = 'P';
   strcat(sil,"P");
   silaba[ctl] = 'O';
   fim++;
   ctl++;
   printf("\nTONICIDADE = %c\n", sile);
   }
}

if((word[fim] == '¢'))  // PARA VOGAL ABERTA
{
   if(word[fim+1] == '\'')
   {
   sile = 'T';
   syl = 'T';
   strcat(sil,"T");
   silaba[ctl] = 'o';
   silaba[ctl+1] = 'h';
   silaba[ctl+2] = '\'';
   fim+=2;
   ctl+=3;
   printf("\nTONICIDADE = %c\n", sile);
   }
}

/************************ FIM CONVERTENDO OS TIPOS DE Os**********************/
//////VER SEEEEEEXUAL

/************************** CONVERTENDO OS TIPOS DE Us************************/
if((word[V] == 'u') || (word[V] == '£'))  //troquei para V
{
   if(word[V+1] == '\'')
   {
   sile = 'T';
   syl = 'T';  //isso é só para marcar se já ocorreu uma silaba tonica
   strcat(sil,"T");      //CONCATENAR NO FINAL
   silaba[ctl] = 'u';    //PRRRRRRRRRRROBLEMA AQUI ABERTA OU FECHADA
   silaba[ctl+1] = '\'';
   fim+=2;
   ctl+=2;
   printf("\nTONICIDADE = %c\n", sile);
   }
   else if(syl != 'T')
   {
   sile = 'A';
   strcat(sil, "A");
   silaba[ctl] = 'u';
   fim++;
   ctl++;
   printf("\nTONICIDADE = %c\n", sile);
   }
   else
   {
   sile = 'P';
   strcat(sil,"P");
   silaba[ctl] = 'U';
   fim++;
   ctl++;
   printf("\nTONICIDADE = %c\n", sile);
   }
}
/************************ FIM CONVERTENDO OS TIPOS DE us**********************/

/*********************** ANALISE DE VOGAIS NASAIS ***************************/
// trabalha com ã e õ  Æä    (strchr(Vogal, word[fim]))
// analisa tonicidade

if(word[V] == 'Æ')    //mude para V  ã
{
   if(word[V+1] == '\'')    // PENSAR EM PENSAR EM COLOCAR O DITONGO NASAL
   {
     sile = 'T';
     syl = 'T';  //isso é só para marcar se já ocorreu uma silaba tonica
     strcat(sil,"T");
     printf("\nTONICIDADE = %c\n", sile);
   }
   else if(syl != 'T')       //  PAAAAAAAAAAAAAAOOOOOOOOOOOLA  > V nasal ão
   {
     sile = 'A';
     strcat(sil, "A");
     printf("\nTONICIDADE = %c\n", sile);
   }
   else
   {
     sile = 'P';
     strcat(sil,"P");
     printf("\nTONICIDADE = %c\n", sile);
   }

   silaba[ctl] = 'a';
   ctl++;
   fim++;

   if(word[fim] == '\'')
     {
        silaba[ctl] = '\'';
        silaba[ctl+1] = 'N';
        ctl+=2;
        fim++;
     }
        //fim++;
  //else if(strchr(Vogal, word[fim])   Acho que o problema de pãozinho n e aki
  else
  {
     silaba[ctl] = 'N';
     ctl++;
     //fim++;
  }
}

if(word[V] == 'ä')  //õ
{
   if(word[V+1] == '\'')
   {
     sile = 'T';
     syl = 'T';  //isso é só para marcar se já ocorreu uma silaba tonica
     strcat(sil,"T");
     printf("\nTONICIDADE = %c\n", sile);
   }
   else if(syl != 'T')
   {
     sile = 'A';
     strcat(sil, "A");
     printf("\nTONICIDADE = %c\n", sile);
   }
   else
   {
     sile = 'P';
     strcat(sil,"P");
     printf("\nTONICIDADE = %c\n", sile);
   }

   silaba[ctl] = 'o';
   ctl++;
   fim++;

   if(word[fim] == '\'')
   {
      silaba[ctl] = '\'';
      silaba[ctl+1] = 'N';
      ctl+= 2;
      fim++;
   }
   else
   {
     silaba[ctl] = 'N';
     ctl++;
        //fim++;
   }
   if((word[fim] == 'o') && (syl == 'T'))   //acho que n prcz syl == 'T'
   {
   silaba[ctl] = 'U';    //PRRRRRRRRRRROBLEMA AQUI ABERTA OU FECHADA
   fim++;
   ctl++;
   }
   else if(word[fim] == 'o') //BIZARRO
   {
   silaba[ctl] = 'U';    //PRRRRRRRRRRROBLEMA AQUI ABERTA OU FECHADA
   fim++;
   ctl++;
   }
}

/********************* FIM ANALISE DE VOGAIS NASAIS*************************/

/*********************COMECO SEMIVOGAL NASAL********************************/
if((fim <= sizeword) && (strchr(VogalNasal,word[V])))    // resolve semivogal u
{
   if((word[fim] == 'o') && (syl == 'T'))   //acho que n prcz syl == 'T'
   {
   silaba[ctl] = 'U';    //PRRRRRRRRRRROBLEMA AQUI ABERTA OU FECHADA
   fim++;
   ctl++;
   }
   else if(word[fim] == 'o') //BIZARRO
   {
   silaba[ctl] = 'U';    //PRRRRRRRRRRROBLEMA AQUI ABERTA OU FECHADA
   fim++;
   ctl++;
   }
}

if(fim <= sizeword)    // resolve semivogal i
{
   if((word[fim] == 'e') && (syl == 'T'))
   {
   silaba[ctl] = 'I';    //PRRRRRRRRRRROBLEMA AQUI ABERTA OU FECHADA
   fim++;
   ctl++;
   }
  /* else if(word[fim] == 'e') //BIZARRO
   {
   silaba[ctl] = 'I';    //PRRRRRRRRRRROBLEMA AQUI ABERTA OU FECHADA
   fim++;
   ctl++;              // tava dando pau em experiente
   }  */
}
/***********************fim semivogal nasal********************************/

/****************** COMEÇA ANALISA DA CONSOANTE DE CODA *******************/
if(fim < sizeword)
{
if(((word[fim] == 'm') || (word[fim] == 'n')) && (strchr(Consoante, word[fim+1])))
    {
     silaba[ctl] = 'N';
     fim++;
     ctl++;
    }
}

if(fim <= (sizeword-1))       // X DE CODA
{
 if (word[fim] == 'x')
 //printf("\n\n\n\nqual é o fim %d, sizeword, %d\n\n\n", fim, sizeword);

   if (fim == (sizeword-1))
   {
      silaba[ctl] = 'K';
      silaba[ctl+1] = 'S';
      fim++;
      ctl += 2;
  }
  else if(word[fim+1] == 's')   // IMPLEMENTAÇÃO PARA ALEXSANDRO
  {
      silaba[ctl] = 'K';
      fim++;
      ctl++;
  }
  else if((strchr(ConsoanteX, word[fim+1])) && (strchr(VogalS,word[fim+2])))      // coloquei && (strchr(VogalS, word[fim+2])))  para excomungado
  {
      silaba[ctl] = 'S';
      fim++;
      ctl++;
  }
  else if(word[fim+1] == 'p')
  {
      silaba[ctl] = 'S';
      fim++;
      ctl++;
  }
}


if((fim < sizeword) && ((!(word[fim+1] == 'l')) || (!(word[fim+1] == 'r'))))
{
if((word[fim] == 'b') && (strchr(ConsoanteP, word[fim+1])))
{
// b de coda  COLOCAR RESTRIÇÃO COM L E R   ABROGAR PODE DAR PAU  > EXCEÇÃO
//acho que vai ter que colocar lista de exceção em algumas palavras
  silaba[ctl] = 'B';
  fim++;
  ctl++;
}
}

if((word[fim] == 'r') && (strchr(ConsoanteR, word[fim+1]))){   // b de coda  COLOCAR RESTRIÇÃO COM L E R
     silaba[ctl] = 'R';
     fim++;
     ctl++;
    }

if(fim < sizeword)
{
    if((word[fim] == 'l') && (strchr(Consoante, word[fim+1])))
    {  // l de coda
     silaba[ctl] = 'L';
     fim++;
     ctl++;
    }
}

if(fim < sizeword)
{
   if((word[fim] == 's') && ((strchr(ConsoanteS, word[fim+1]))))  // s de coda
   {
     silaba[ctl] = 'S';
     fim++;
     ctl++;
   }
   if((word[fim] == 's') && (word[fim+1] == 'c') && (strchr(VogalS, word[fim+2])))
   {
     silaba[ctl] = 'S';
     fim++;
     ctl++;
   }
}

if(fim == (sizeword-1))
{
if((word[fim] == 'z') || (word[fim] == 's'))
    {  // r de coda
     silaba[ctl] = 'S';
     fim++;
     ctl++;
    }
}

if(fim < sizeword)
{
if((word[fim] == 'c') && (strchr(ConsoanteP, word[fim+1]))) //tava Consoante R
    {  // r de coda
     silaba[ctl] = 'K';
     fim++;
     ctl++;
    }
}

if(fim < sizeword)
{
if((word[fim] == 'd') && (strchr(ConsoanteR, word[fim+1]))){  // r de coda
     silaba[ctl] = 'D';
     fim++;
     ctl++;
    }
}

if(fim < sizeword)
{
if((word[fim] == 'g') && (strchr(ConsoanteR, word[fim+1]))){  // r de coda
     silaba[ctl] = 'G';
     fim++;
     ctl++;
    }
}

if(fim < sizeword)
{
if((word[fim] == 'p') && (strchr(ConsoanteP, word[fim+1]))){  // r de coda
     silaba[ctl] = 'P';
     fim++;
     ctl++;
    }
}

/**************** FIM DA ANALISE DA CONSOANTE DE CODA **********************/


/***************** ANALISE DE DITONGO DECRESCENTE ***************************/
if(fim < sizeword)    // resolve semivogal i
{
   if((word[fim] == 'i') || (word[fim] == 'y') && (syl == 'T'))
   {
   silaba[ctl] = 'I';    //PRRRRRRRRRRROBLEMA AQUI ABERTA OU FECHADA
   fim++;
   ctl++;
   }
   else if((word[fim] == '\'') && (word[fim+1] == 'i') && (syl == 'T'))
   {
   silaba[ctl] = 'I';    //PRRRRRRRRRRROBLEMA AQUI ABERTA OU FECHADA
   fim++;
   ctl++;
   }
}

if(fim < sizeword)    // resolve semivogal IA
{
   if((word[fim] == 'a') && (word[fim-1] != '\'') && (word[fim+1] != '\''))//para experiencia
   {
   silaba[ctl] = 'A';
   fim++;
   ctl++;
   //sile = 'P';
   //strcat(sil, "P");
   //printf("\nTONICIDADE = %c\n", sile);
   }
}

if(fim < sizeword)    // resolve semivogal IO
{
   if((word[fim] == 'o') && (word[fim-1] != '\'') && (word[fim+1] != '\''))//para sábio
   {
   silaba[ctl] = 'O';
   fim++;
   ctl++;
   //sile = 'P';
   //strcat(sil, "P");
   //printf("\nTONICIDADE = %c\n", sile);
   }
}

// OBBBBBBBBBStinha semivogal /e/ de ditongo nasal aqui

if(fim < sizeword)    // resolve semivogal u
{
   if((word[fim] == 'u') && (syl == 'T'))
   {
   silaba[ctl] = 'U';    //PRRRRRRRRRRROBLEMA AQUI ABERTA OU FECHADA
   fim++;
   ctl++;
   }
   else if((strchr(Vogal,word[0])) && (word[fim] == 'u'))
   {
   silaba[ctl] = 'U';    //PRRRRRRRRRRROBLEMA AQUI ABERTA OU FECHADA
   fim++;
   ctl++;
   }
}

// OBBBBBBBBBStinha semivogal /o/ de ditongo nasal aqui

if(fim == (sizeword-1))     // lida com o plural >>> Sil Bug
{
if((word[fim] == 'z') || (word[fim] == 's'))
    {  // r de coda
     silaba[ctl] = 'S';
     fim++;
     ctl++;
    }
}


/****************** FIM DA ANALISE DE DITONGO DECRESCENTE ******************/

}//fecha if vogal

silaba[ctl] = '\0';

strncat(phonword, silaba,ctl);  //para dar a palavra inteira convertida
printf("\nSilaba(%d) = %s\n\n", nsil, silaba);

//for (i=0; (i <= ctl) ;i++){  //Tornando genérica o zeramento da silaba
   //silaba[i]=' ';  //deve ser ini ou fim ou ctl  >> BURRICE
//}

silaba[0]=' ';     //ESSA PARTE AQUI DÁ PROBLEMA NO PHONWORD PQ ANEXA PARTES EM
silaba[1]=' ';     // BRANCO À PALAVRA
silaba[2]=' ';
silaba[3]=' ';
silaba[4]=' ';
silaba[5]=' ';
silaba[6]=' ';
//silaba[7]='\0';

ini = fim;
//fim++;
nsil++;
}  //fecha while
free(silaba);
printf("\nA palavra fonol¢gica = /%s/\n\n", phonword);
printf("A tonicidade da palavra = %s\n\n", sil);

tamanho = strlen(phonword);
//printf("\nQual e o tamanho? %d\n", tamanho);
for(i=0; i <= tamanho; i++)   //zera a palavra fonológica para nova consulta
{
   phonword[i] = ' ';
}

//DANDO PAU >>> VERIFICAR
printf("\nTecle enter para uma nova consulta ou s para sair: ");
controle = getchar();
printf("\n");
printf("\n");

}//fim do controle

} //fecha main



