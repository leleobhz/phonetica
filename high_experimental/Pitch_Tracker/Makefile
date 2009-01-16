LIBS= -lm
HTK_LIBS=/tools/htk/HTK_V1.4C/HTKLib/HTKLib.${CPU}.a -lm
CFLAG= -g -O2 -Wall
HTK_CFLAG= -g -O2 -Wall -D HTKCOMPILE -I /tools/htk/HTK_V1.4C/Cincludes 
.c.o:	; gcc ${CFLAG} -c $*.c 
default:
	make CC=${CC} ${HOSTTYPE} "CCOBJECTS=`ls *.c|sed "s/\.c$$/\.o/"|tr '\012' ' '`"  
htk:	
	make CC=${CC} CFLAG="${HTK_CFLAG}" LIBS="${HTK_LIBS}" ${HOSTTYPE} "CCOBJECTS=`ls *.c|sed "s/\.c$$/\.o/"|tr '\012' ' '`"  

sparc hpux: $(CCOBJECTS)
	gcc ${CFLAG} -o sparc_pitch_track $(CCOBJECTS) ${LIBS}
sgi       : $(CCOBJECTS)
	gcc ${CFLAG} -D SGICOMPILE -o sgi_pitch_track $(CCOBJECTS) ${LIBS}
hp9000s700:$(CCOBJECTS)
	gcc ${CFLAG} -o hp_pitch_track $(CCOBJECTS) ${LIBS}
sgi_pitch_track: pitch_tracker.o 
	gcc ${CFLAG} -D SGICOMPILE -o pitch_track pitch_tracker.o ${LIBS}
sparc_pitch_track: pitch_tracker.o 
	gcc ${CFLAG} -o pitch_track pitch_tracker.o ${LIBS}
hp_pitch_track: pitch_tracker.o 
	gcc ${CFLAG} -o pitch_track pitch_tracker.o ${LIBS}
pitch_tracker.o: pitch_tracker.c
	gcc -c ${CFLAG} pitch_tracker.c 
clean:
	rm -f *.o *.trl *~

