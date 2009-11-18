#extract F1, F2, F3, F0, duration intensity and speech rate
# Script implemented by Ana Matte (FALE/UFMG) for obtaining
# data of non-empty, non-? previously segmented intervals (TextGrid)
# Works in one-sentence files
# Licence: GPL. Distribute mainteining the authory.

## FORMULARIO DE ENTRADA
form Dados
 sentence Caminho /home/acris/testePraat/SetFon-Scripts/
 sentence Arquivo nomfr_000
 choice Tipo 1
   button wav
   button mp3
 integer Camada 1
 integer CamadaTE 2
endform

# ARQUIVO DE SAIDA 
arqout1$ = arquivo$ + "_dados.csv"
filedelete 'arqout1$'
fileappend 'arqout1$' arquivo numero segmento TE duracaoVV intensMedia intensDP intensMediana mediaF0 medianaF0 desvPadF0 mediaF1 medianaF1 desvPadF1 mediaF2 medianaF2 desvPadF2 mediaF3 medianaF3 desvPadF3 ini end'newline$'

# ARQUIVOS DE ENTRADA 
arq$ = caminho$ + arquivo$
arqwav$ = arq$ + "." + tipo$
arqgrid$ = arq$ + ".TextGrid"

# ABRE OS ARQUIVOS DE ENTRADA
Read from file... 'arqwav$'
Read from file... 'arqgrid$'

#EXTRAI SEGMENTOS CAMADA TE E CALCULA
select all
Extract non-empty intervals... 'camadaTE' yes
select Sound 'arquivo$'
Remove
select TextGrid 'arquivo$'
Remove
select all
call calcTE
Remove

# EXTRAI OS SEGMENTOS
Read from file... 'arqwav$'
Read from file... 'arqgrid$'
select all
Extract non-empty intervals... 'camada' yes
nselected = numberOfSelected ("Sound")
select Sound 'arquivo$'
Remove
select TextGrid 'arquivo$'
Remove
select all

# ABRE OS OBJETOS INTENSIDADE, PITCH E FORMANTES PARA ANALISE E...

for i from 1 to nselected

   
# CALCULA OS FORMANTES
call formantes

# CALCULA O PITCH
call pitch

# CALCULA A INTENSIDADE
call intensidade

# SALVA CADA LINHA
   fileappend 'arqout1$' 'arquivo$' 'i' 'segm$' 'te' 'dur' 'intM:0' 'intDP:0' 'intMe:0' 'f0m:0' 'f0dp:0' 'f0me:0' 'f1m:0' 'f1me:0' 'f1dp:0' 'f2m:0' 'f2me:0' 'f2dp:0' 'f3m:0' 'f3me:0' 'f3dp:0' 'ini:3' 'end:3' 'newline$'

   select Pitch 'segm$'
   plus Intensity 'segm$'
   plus Formant 'segm$'
   Remove
   select all
endfor


select all
Remove

############################
procedure calcTE
durTE = Get total duration
durTE = round(durTE*10)
nSegm$ = selected$ ("Sound")
nSegm = 'nSegm$'
te = durTE/nSegm
endproc

###########################
procedure formantes
   segm$ = selected$ ("Sound", 'i')
   select Sound 'segm$'
   ini = Get start time
   end = Get end time
   dur = Get total duration
   dur = round(dur*1000)
   To Formant (burg)... 0 5 5500 0.025 50
   f1m = Get mean... 1 0 0 Hertz
   f2m = Get mean... 2 0 0 Hertz
   f3m = Get mean... 3 0 0 Hertz
   f1me = Get quantile... 1 0 0 Hertz 0.5
   f2me = Get quantile... 2 0 0 Hertz 0.5
   f3me = Get quantile... 3 0 0 Hertz 0.5
   f1dp = Get standard deviation... 1 0 0 Hertz
   f2dp = Get standard deviation... 2 0 0 Hertz
   f3dp = Get standard deviation... 2 0 0 Hertz
endproc

###########################
procedure pitch
   select Sound 'segm$'
   To Pitch... 0 75 600
   f0m = Get mean... 0 0 Hertz
   f0dp = Get standard deviation... 0 0 Hertz
   f0me = Get quantile... 0 0 0.5 Hertz
endproc

###########################
procedure intensidade
   select Sound 'segm$'
   To Intensity... 100 0 yes
   intM = Get mean... 0 0 energy
   intDP = Get standard deviation... 0 0
   intMe = Get quantile... 0 0 0.5
endproc

###########################

