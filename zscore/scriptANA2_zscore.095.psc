# scriptANA table so para a camada VV
# para calcular valores de dur na camada com VVs (camada 8)
# SGdetector (by Plinio Barbosa) adapted by Ana Matte for use with Praat
# function value = z (unit, duration)
# use toguether with table.txt and set.txt

## FORMULARIO DE ENTRADA
form Dados
 sentence Caminho /home/acris/testePraat/teste/
 sentence Arquivo Monicalit1_2-2
 choice Tipo 1
   button wav
   button mp3
 integer Camada 1
endform

# ARQUIVOS DE ENTRADA - AQUISICAO DOS ARQUIVOS DE SOM
arq$ = caminho$ + arquivo$
arqwav$ = arq$ + "." + tipo$
arqgrid$ = arq$ + ".TextGrid"
Read from file... 'arqwav$'
Read from file... 'arqgrid$'
 
select all
Extract non-empty intervals... 'camada' yes
nselected = numberOfSelected ("Sound")
select Sound 'arquivo$'
Remove
select TextGrid 'arquivo$'
Remove
  

# ARQUIVOS DE SAIDA 
if 'camada' = 1
  camada$ = "VOpiF"
  table$ = "table.txt"
endif

##ARQUIVO DE SAIDA PROVISORIO 11
   arqout11$ = arquivo$ +  "_11_" + camada$ + ".txt"
   filedelete 'arqout11$'
   fileappend 'arqout11$' numero segmento duracao zscore zsuav posicao 'newline$'
##ARQUIVO DE SAIDA PROVISORIO 12
   arqout12$ = arquivo$ +  "_12_" + camada$ +".txt"
   filedelete 'arqout12$'
   fileappend 'arqout12$' der zsuav 'newline$'
##ARQUIVO DE SAIDA PROVISORIO 13
arqout13$ =  arquivo$ + "_13_" + camada$ + ".txt"
filedelete 'arqout13$'
fileappend 'arqout13$' Segmento duracao zscore 'newline$'
## ARQUIVO DE SAIDA FINAL 14
   arqout14$ = arquivo$ +  "_DurTudo_" + camada$ + ".csv"
   filedelete 'arqout14$'
   fileappend 'arqout14$' arquivo numero segmento duracao zscore zsuav posicao tamanho 'newline$'
##ARQUIVO DE SAIDA PROVISORIO 15
 arqout15$ = arquivo$ + "_15_" + camada$ + ".txt"
filedelete 'arqout15$'
fileappend 'arqout15$' numero dur zscore 'newline$'


###############
# SCRITP 
tam = 0
for i from 1 to nselected
 select all
 soundID = selected ("Sound", 'i')
 select 'soundID'
 nome$ = selected$ ("Sound")
 numero = 'i'

 ##VER  NUMERO MAXIMO PARA N
   labeln$ = selected$ ("Sound")   
   ending = length (labeln$)

 ## DURACAO
 if nome$ <> "_"
  dur = Get duration
  dur = round(dur*1000)
 endif

 ## CALCULANDO Z-SCORE
 if nome$ <> "_"
  smean = 0
  ssd = 0
  svar = 0
  call values
  call z
 fileappend 'arqout15$' 'numero' 'dur' 'zscore' 'newline$'
  select all
 endif
 fileappend 'arqout13$' 'nome$' 'dur' 'zscore' 'newline$'
endfor


## CALCULANDO ZSUAV
#sa = 1
s = 1
for s from 1 to nselected
 pos = 's'
 select all
 max = nselected - 1
 numero = 's'
   if numero < 3
       call zsuav0 zscore numero
   else
       if numero < max 
          call suav zscore numero
       elsif numero >= max
          call zsuav0 zscore numero
       endif
   endif
 Read TableOfReal from headerless spreadsheet file... 'arqout13$'
 arqout132$ = arqout13$ - ".txt"
 select TableOfReal 'arqout132$'
 nome$ = Get row label... 's'
 fileappend 'arqout11$' 'numero' 'nome$' 'dur' 'z:3' 'zsuav:3' 'pos' 'pos' 'newline$' 
 select TableOfReal 'arqout132$'
 Remove
 select all
endfor
call tamanho pos
select all
Remove
filedelete 'arqout15$'
filedelete 'arqout13$'
filedelete 'arqout12$'
filedelete 'arqout11$'
# FIM DO SCRIPT
######################

######################
# VALUES
procedure values
## SEGMENTACAO
 for n from 1 to ending
## LE A TABELA SET
   Read TableOfReal from headerless spreadsheet file... set.txt
   select TableOfReal set
##PROCURA NA TABELA SET O SEGMENTO COM 3 CARACTERES A PARTIR DE N
   seg$ = mid$ (nome$, 'n', 3)
   setseg = Get row index... 'seg$'
#SE EXISTE, GUARDA O SEG$ E AUMENTA N + 2
   if setseg <> 0
      n = n + 2
#SE NAO EXISTE, PROCURA O SEGMENTO COM 2 CARACTERES A PARTIR DE N
   elsif setseg = 0
      seg$ = mid$ (nome$, 'n', 2)
      setseg = Get row index... 'seg$
#SE EXISTE, GUARDA O  SEG$ E AUMENTA N + 1
      if setseg <> 0
         n = n + 1   
#SE NAO EXISTE, PROCURA O SEGMENTO COM 1 CARACTERE A PARTIR DE N (O PROPRIO N)
      elsif setseg = 0
         seg$ = mid$ (nome$, 'n', 1)
         setseg = Get row index... 'seg$'
#SE N NAO EH UMA LETRA, SOMA N + 1 E PROCURA DE NOVO COM 3
         if seg$ = "_"
              n = n + 1
              seg$ = mid$ (nome$, 'n', 3) 
              setseg = Get row index... 'seg$'    
#SE EXISTE, GUARDA O  SEG$ E AUMENTA N + 2
              if setseg <> 0
                  n = n + 2
#SE NAO EXISTE, PROCURA O SEGMENTO COM 2 CARACTERES A PARTIR DE N
              elsif setseg = 0
                 seg$ = mid$ (nome$, 'n', 2)
                 setseg = Get row index... 'seg$'
#SE EXISTE, GUARDA O  SEG$ E AUMENTA N + 1
                 if setseg <> 0
                    n = n + 1
#SE NAO EXISTE, PROCURA O SEGMENTO COM 1 CARACTERE A PARTIR DE N (O PROPRIO N) E AUMENTA N + 1
                 elsif setseg = 0
                     seg$ = mid$ (nome$, 'n', 1)
                     setseg = Get row index... 'seg$'
                     n = n + 1
                  endif
            endif
       endif
  endif
endif
#REMOVE A TABELA SET
   Remove
## LE A TABELA TABLE E PROCURA A LINHA DO SEG$
    Read TableOfReal from headerless spreadsheet file... 'table$'
    tableB$ = table$ - ".txt"
    select TableOfReal 'tableB$'
    label$ = Get row index... 'seg$'
#PEGA A MEDIA E O DESVIO PADRAO NA TABELA E CALCULA A VARIANCA
        if label$ <> "0"
          mean = Get value... 'label$' 1
          sd = Get value... 'label$' 2
          var = sd*sd
          smean = mean + smean
          svar = var + svar
          Remove
       else
          Remove
       endif
 endfor
endproc
#####################

#####################
# FORMULA DO Z-SCORE
  procedure z
     upper = dur - smean
     zscore = upper/sqrt(svar)
  endproc
#####################

#####################
# FORMULA DE SUAVIZACAO
procedure suav z line
    Read TableOfReal from headerless spreadsheet file... 'arqout15$'
    arqout152$ = arqout15$-".txt"
    select TableOfReal 'arqout152$'
## OBTENCAO DAS VARIAVEIS
   lineA = 'line' - 2
   zA = Get value... 'lineA' 2
   lineB = 'line' - 1
   zB = Get value... 'lineB' 2
   z = Get value... 'line' 2
   lineC = 'line' + 1
   zC = Get value... 'lineC' 2
   lineD = 'line' + 2
   zD = Get value... 'lineD' 2
   dur = Get value... 'line' 1

## CALCULO DO ZSUAV
   upper2 = 1*zA + 3 *zB + 5*z + 3*zC + 1*zD
   zsuav = upper2/13
   fileappend 'arqout12$' zsuav 'zsuav' 'newline$'
   select TableOfReal 'arqout152$'
   Remove
endproc
###################

###################
# QUANDO Z SUAVIZADO = 0
procedure zsuav0 z line
   Read TableOfReal from headerless spreadsheet file... 'arqout15$'
   arqout152$ = arqout15$-".txt"
   select TableOfReal 'arqout152$'
   dur = Get value... 'line' 1
   z = Get value... 'line' 2
   zsuav = 0
   fileappend 'arqout12$' zsuav 0 'newline$'
#   select TableOfReal 'arqout152$'  
   Remove
endproc
###################

###################
# PARA ACHAR OS PICOS DE Z-SUAV
procedure acharpico line
   Read TableOfReal from headerless spreadsheet file... 'arqout12$'
   max = Get number of rows
   arqout122$ = arqout12$ - ".txt"
   select TableOfReal 'arqout122$'
   if line <=2
      pico = 0  
      picoA$ = "positivo"
   elsif line > 2
      lineA = 'line' - 1
      lineP = 'line' + 1
      if lineP < max
         suavA = Get value... 'lineA' 1
         suavP = Get value... 'lineP' 1
         suaviz = Get value... 'line' 1
         derA = suaviz - suavA
         derP = suavP - suaviz 
         if derA >= 0 and derP < 0
            pico = 1
            picoA$ = "neg"
         elsif derA >= 0 and derP < 0 and picoA$ = "neg"
            pico = 0
         else
            pico = 0
            picoA$ = "posit"
         endif
      elsif lineP = max
         suavA = Get value... 'lineA' 1
         suaviz = Get value... 'line' 1
         derA = suaviz - suavA
         if derA >=0
            pico = 1
         else
            pico = 0
         endif
      endif
   endif
   select TableOfReal 'arqout122$'
   Remove
endproc
####################


####################
## ACHAR POSICAO E TAMANHO
procedure tamanho pos
posA = 1
for a from 1 to nselected
 pos = 'a'
 select all
 max = nselected - 1
 call acharpico pos
   if pico = 1
      tam = pos - posA +1
      posN = posA - pos
      Read TableOfReal from headerless spreadsheet file... 'arqout13$'
      arqout132$ = arqout13$ - ".txt"
      select TableOfReal 'arqout132$'
      total = Get number of rows
      nome$ = Get row label... 'posA'
      dur = Get value... 'posA' 1
      z = Get value... 'posA' 2
      select TableOfReal 'arqout132$'
      Remove
      Read TableOfReal from headerless spreadsheet file... 'arqout12$'
      arqout122$ = arqout12$ - ".txt"
      select TableOfReal 'arqout122$'
      if posA <= max
      	zsuav = Get value... 'posA' 1
      endif
      select TableOfReal 'arqout122$'
      Remove
      fileappend 'arqout14$' 'arquivo$' 'posA' 'nome$' 'dur' 'z:3' 'zsuav:3' 'posN' 'tam' 'newline$'
      for x from (posA+1) to pos
         Read TableOfReal from headerless spreadsheet file... 'arqout13$'
         arqout132$ = arqout13$ - ".txt"
         select TableOfReal 'arqout132$'
         nome$ = Get row label... 'x'
         dur = Get value... 'x' 1
         z = Get value... 'x' 2
         select TableOfReal 'arqout132$'
         Remove
         Read TableOfReal from headerless spreadsheet file... 'arqout12$'
         arqout122$ = arqout12$ - ".txt"
         select TableOfReal 'arqout122$'
         zsuav = Get value... 'x' 1
         select TableOfReal 'arqout122$'
         Remove
         posN = x - tam - posA + 1
         fileappend 'arqout14$' 'arquivo$' 'x' 'nome$' 'dur' 'z:3' 'zsuav:3' 'posN' 'tam' 'newline$'
      endfor
   posA = 'x'
   endif   
 endif
select all
endfor
endproc
#####################


