#This script writes the labels into a TextGrid previously segmented from 
# a txt file where there is one label for line. Both textGrid and txt
# files must hav the same name.
# This is a GPL script. Author: Ana Matte ana@semiofon.org

form Arquivo
 sentence Arquivo nomfr_000
 integer CamadaVV 1
endform


#CONTA O NUMERO DE INTERVALOS NO ARQUIVO COM O TEXTO
Read Strings from raw text file... 'arquivo$'.txt
nStrings = Get number of strings


#ABRE ARQUIVO DE TEXTGRID e CONTA O NUMERO DE INTERVALOS DO TEXTGRID
Read from file... 'arquivo$'.TextGrid
nIntervalos = Get number of intervals... 1



#ACHA O INTERVALO INICIAL PRESUMINDO QUE, SE HOUVER UM A MAIS, 
# O PRIMEIRO EH SILENCIO INICIAL E, COM DOIS A MAIS, O ULTIMO SILENCIO FINAL
nDiff = nIntervalos - nStrings
if nDiff = 0
 i = 1
 end = 'nIntervalos'
elsif nDiff = 1
 i = 2
 end = 'nIntervalos'
else
 i = 2
 end = 'nIntervalos' - 1
endif

string = 1

for i from 'i' to 'end'
 select Strings 'arquivo$'
 string$ = Get string... 'string'
 select TextGrid 'arquivo$'
 Set interval text... camadaVV i 'string$'
 string = string + 1
endfor

Write to text file... 'arquivo$'.TextGrid

select all
Remove


 
 



#