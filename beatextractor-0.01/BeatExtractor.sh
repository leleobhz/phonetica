#!/bin/bash -x

# Start to get sysarg and convert into variables readable in Praat

case "$1" in
"male")
	speaker_sex="\"1\"";
	;;
"female")
	speaker_sex="\"2\"";
	;;
*) 
	echo "Invalid speaker_sex" >&2 
	exit 1;
esac

case "$2" in
"butterworth")
	filter="1";
	;;
"hanning")
	filter="2";
	;;
*)
	echo "Invalid filter" >&2 
	exit 1;
esac

case "$3" in
"order_filter_1")
	filter_order="1";
	;;
"order_filter_2")
	filter_order="2";
	;;
*)
	echo "Invalid orfer_filter" >&2 
	exit 1;
esac

left_Cut_off_frequency="$4"
right_Cut_off_frequency="$5"
smoothing_cut_freq="$6"

case "$7" in
"derivative")
	technique="1"
	technique_str="\"Derivative\""
	;;
"amplitude")
	technique="2"
	technique_str="\"Amplitude\""
	;;
*)
	echo "Invalid technique" >&2 
	exit 1;
esac

threshold1=$8
threshold2=$9

file="\"$(basename ${10})\""
dir="\"$(dirname ${10})\""

## All relative to optional progressbar. Compatible with kommander scripts
if [ -n "${11}" ]; then
	PrBarObj=${11}
else
	PrBarObj=ProgressBar1
fi

## Setting commander to 0%
`pwd`/Progress.sh "$PrBarObj" "0"

# TODO01
# rm -rf *.beat.wav *filt.wav *.orig-beat-mix.wav

rm -rf xxxxxxx 2>/dev/null
TMPFILE=`mktemp xxxxxxx`

echo "Starting the procedure for the file $file."

cat <<EOF > $TMPFILE
# Codigo do praat aqui em baixo

#!/usr/bin/praat

# BeatExtractor.sh
# Script implemented by Plinio A. Barbosa (plinio@iel.unicamp.br), LAFAPE/IEL/Unicamp,Brazil,
# based originally on Fred Cummins' beat extractor with some modifications of the default
# parameters and some additions (an additional filter, and another technique for searching for beats.
# Please, DO NOT DISTRIBUTE WITHOUT THE README FILE BEATEXTRACTOR.RDM
# Credits:	Fred Cummins, for tips about his own beatextractor, and suggestions
#	Sophie Scott, for support on her p-centre predictor model
#	Paul Boersma, for crucial tips/suggestions on programming in Praat
#	Pablo Arantes, Jussara Vieira, Alexsandro Meireles, and Ana C. Matte, for comments during a debugging phase

# These are a enhanced version of BeatExtractor, with a lot of bugfixes on source and insertation into a shell script, making the process easily and praat graphic interface independent. Can be used with CGIs and graphics interfaces, being a thousand times more easy to use and debug.

# Changelog:
#  * Qui Fev 22 2007 Leonardo Amaral <leleobhz@leleobhz.org>, Ana Matte <ana@underlinux.com.br>, Christian Tosta <ch_tosta@terra.com.br>
#    - Fixed the "Time domain is not match" issue on saving mixed beat and orig file
#    - Added a bash interface
#    - Inserted progressbar for kommander - no error if isnt from kommander
#    - Adequated for batch execution and outside scripting

# ToDo:
#    - TODO01: Make a new cleaning code based on files input.

# Parameters' input
# Variables Declaration: 
#Sex: 1=Male 2=Feamale
speaker_sex$ = $speaker_sex

#Filter: 1=Butterworth 2=Hanning
filter = $filter
filter_order = $filter_order

# If using a form, set-it to real
# auto=0

left_Cut_off_frequency = $left_Cut_off_frequency
right_Cut_off_frequency = $right_Cut_off_frequency
smoothing_cut_freq = $smoothing_cut_freq

#Technique: 1=Amplitude 2=Derivative
technique$ = $technique_str
technique = $technique

#   positive Threshold1_(0.05..0.50) 0.15
threshold1 = $threshold1
#  positive Threshold2_(0.05..0.15) 0.12
threshold2 = $threshold2

path$ = $dir
file$ = $file
fil$ = path$ + "/" + file$

progressbar$ = environment$ ("PWD") + "/Progress.sh"

##
# mindur is the minimum duration allowed between two consecutive boundaries
# fcut is the cut-off frequency of the low-pass filters used here
# fe/male default are the default cut-off frequencies according to speaker sex
mindur = 0.040
male_default_left = 1000
male_default_right = 1800
female_default_left = 1150
female_default_right = 2100
if left_Cut_off_frequency = 0  ; automatic
   left_Cut_off_frequency = if speaker_sex$ = "Male" then 'male_default_left' else 'female_default_left' fi
endif
if right_Cut_off_frequency = 0  ; automatic
   right_Cut_off_frequency = if speaker_sex$ = "Male" then 'male_default_right' else 'female_default_right' fi
endif
if filter_order = 0  ; automatic
   filter_order = if filter = 1 then 2 else 0 fi
endif
if smoothing_cut_freq = 0  ; automatic
   smoothing_cut_freq = if technique$ = "Amplitude" then 40 else 20 fi
endif
fcut = smoothing_cut_freq
##

### Kommander ProgressBar1
system_nocheck 'progressbar$' "$PrBarObj" "1"

Read from file... 'fil$'
filename$ = selected$ ("Sound")
centerf = ('right_Cut_off_frequency'  + 'left_Cut_off_frequency')/2
w = ('right_Cut_off_frequency'  - 'left_Cut_off_frequency')/2
select Sound 'filename$'
# The sound file is filtered according to the preceding choices
if filter = 1
 Filter (formula)... sqrt(1.0/(1.0 + ((x-centerf)/w)^(2*'filter_order')))*self; butterworth filter
elif filter = 2
 Filter (pass Hann band)... 'left_Cut_off_frequency' 'right_Cut_off_frequency' 100
endif
Copy... temp

tmp$ = path$ + "/" + filename$ + "_filt"
tmpext$ = tmp$ + ".wav"
Write to WAV file... 'tmpext$'

## Kommander ProgressBar1
system_nocheck 'progressbar$' "$PrBarObj" "2"


# Filtered sound file's rectification
Formula... abs(self)
w2 =  'smoothing_cut_freq'/10
# Rectified file is low-pass-band filtered producing the beat wave file
Filter (pass Hann band)... 0 'smoothing_cut_freq' w2
max = Get maximum... 0.0 0.0 None
# Beat wave is normalised
Formula... self/max
beatwave$ = filename$ + "_beatwave"
Rename... 'beatwave$'
select Sound 'beatwave$'
derivbeatwave$ = filename$ + "_drvbeatwave"
Copy... temp3

### Kommander ProgressBar1
system_nocheck 'progressbar$' "$PrBarObj" "3"

# The derivative of beat wave file is computed and low-pass filtered
Formula... (self[col+1] - self[col])/dx
Filter (pass Hann band)... 0 fcut fcut/10
Rename... 'derivbeatwave$'
max = Get maximum... 0.0 0.0 None
Formula... self/max
select Sound temp3
Remove

### Kommander ProgressBar1
system_nocheck 'progressbar$' "$PrBarObj" "4"

select Sound 'beatwave$'

begin = Get starting time
end = Get finishing time
beginindex = Get index from time... 'begin'
beginindex = round(beginindex)
endindex = Get index from time... 'end'
endindex = round(endindex)
fileout$ = path$ + "/" + filename$ + ".TextGrid"
# Start writing of the TextGrid file
filedelete 'fileout$'
fileappend 'fileout$' File type = "ooTextFile short" 'newline$'
fileappend 'fileout$' "TextGrid" 'newline$'
fileappend 'fileout$' 'newline$'
fileappend 'fileout$' 'begin' 'newline$'
fileappend 'fileout$' 'end' 'newline$'
fileappend 'fileout$' <exists> 'newline$'
fileappend 'fileout$' 1 'newline$'
fileappend 'fileout$' "IntervalTier" 'newline$'
fileappend 'fileout$' "VowelOnsets" 'newline$'
fileappend 'fileout$' 'begin' 'newline$'
fileappend 'fileout$' 'end' 'newline$'
i = beginindex
t = begin
cpt = 0

### Kommander ProgressBar1
system_nocheck 'progressbar$' "$PrBarObj" "5"

# Choice of technique
### Technique = 1
# This technique takes the values of the beatwave around threshold 1, within the rising parts (derivative > 0)
if technique = 1
 epsilon = 'threshold1'/5
 repeat
  select Sound 'beatwave$'
  value = Get value at index... 'i'
  value = round(1000*value)/1000
  select Sound 'derivbeatwave$'
  valuederiv = Get value at index... 'i'
  if (value < ('threshold1' + epsilon) and value > ('threshold1' - epsilon)) and (valuederiv > 0.01)
     time'cpt' = Get time from index... 'i'
     if cpt <> 0 
       delayedcpt = cpt -1
       if (time'cpt' - time'delayedcpt') <= mindur
          cpt = cpt -1
       endif
     endif
     cpt = cpt + 1
 endif
 t = t + 0.001
 i = Get index from time... 't'
 i = round(i)
 until (i >= endindex-1)
 ### Kommander ProgressBar1
 system_nocheck 'progressbar$' "$PrBarObj" "6"

###
# Technique = 2
# This technique takes the values of the maxima of the derivative of the beatwave
# greater than threshold 2, where the amplitude of the beatwave is greater than threshold 1
elif technique = 2
 select Sound 'derivbeatwave$'
 drv2beatwave$ = path$ + "/" + filename$ + "_drv2beatwave"
 Copy... temp2
 Formula... (self[col+1] - self[col])/dx
 Filter (pass Hann band)... 0 fcut fcut/10
 Rename... 'drv2beatwave$'
 max = Get maximum... 0.0 0.0 None
 Formula... self/max
 
 ### Kommander ProgressBar1
 system_nocheck 'progressbar$' "$PrBarObj" "6"
 
 repeat
  select Sound 'drv2beatwave$'
  drvvalue = Get value at index... 'i'
  drvvalue = round(drvvalue)
  select Sound 'derivbeatwave$'
  value = Get value at index... 'i'
  select Sound 'beatwave$'
  valuebeat = Get value at index... 'i'
  if (drvvalue = 0) and (value > 'threshold2') and (valuebeat > 'threshold1') and (valuebeat < 0.3)
     time'cpt' = Get time from index... 'i'
     if cpt <> 0 
       delayedcpt = cpt -1
       if (time'cpt' - time'delayedcpt') <= mindur
          cpt = cpt -1
       endif
     endif
     cpt = cpt + 1
 endif
 t = t + 0.001
 i = Get index from time... 't'
 i = round(i)
 until (i >= endindex-1)
 select Sound  'drv2beatwave$'
 plus Sound  temp2
 Remove
endif 
#####

### Kommander ProgressBar1
system_nocheck 'progressbar$' "$PrBarObj" "7"

# A little Debug:
select Sound 'beatwave$'
beatfile$ = path$ + "/" + filename$ + ".beat.wav"
Write to WAV file... 'beatfile$'

tmp = cpt+1
fileappend 'fileout$' 'tmp' 'newline$'
temp = 0
for i from 0 to cpt-1
 fileappend 'fileout$' 'temp' 'newline$'
 temp = time'i'
 fileappend 'fileout$' 'temp' 'newline$'
 fileappend 'fileout$' "" 'newline$'
endfor
fileappend 'fileout$' 'temp' 'newline$'
fileappend 'fileout$' 'end' 'newline$'
fileappend 'fileout$' "" 'newline$'
#fil$ = path$ + filename$ + "integr"

### Kommander ProgressBar1
system_nocheck 'progressbar$' "$PrBarObj" "8"

# Creates a long sound file containing the original soound and the beat wave 
# Select the created TextGrid file containing the detected boundaries
temp$ = path$ + "/" + filename$ + "integr"
select all
nb = numberOfSelected ("LongSound")
if nb <> 0
 select LongSound 'temp$'
 Remove
endif

# Trying to go arround the "Time domains not match" issue

select all
Remove

Read from file... 'fil$'
Read from file... 'beatfile$'
select all
filstereo$ = path$ + "/" + filename$ + "-orig-beat-mix.wav"
Write to stereo WAV file... 'filstereo$'

select all
Remove

### Kommander ProgressBar1
system_nocheck 'progressbar$' "$PrBarObj" "9"

# Fim do codigo do praat
EOF

praat $TMPFILE && rm -f $TMPFILE && echo "Procedure Finished Successfully!" && exit 0

echo "Procedure Failled!"
exit 1
