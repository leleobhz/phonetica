all:
	sudo apt-get install mbrola
	sudo apt-get install zenity
	cd src; cc -o lianecomp *.c; mv lianecomp ../bin; cd ..
	cd src-teste; cc -o lianecomp-teste_par *.c; mv lianecomp-teste_par ../bin; cd .. 
	sudo cp bin/* /usr/local/bin
	sudo cp -p 07/* /usr/local/bin 
	sudo cp -R share/* /usr/share
	sudo chmod 666 /usr/share/lianetts/liane/lianetts.abr
	sudo chmod 666 /usr/share/lianetts/liane/lianetts.exc		
	echo "Sintetizador Liane TTS instalado." | liane-say
	sudo mkdir -p /etc/speech-dispatcher/modules
	sudo cp spch_disp/lianetts-generic.conf /etc/speech-dispatcher/modules
	sudo cp spch_disp/speechd.conf /etc/speech-dispatcher	
	clear
	cat spch_disp/00-readme.txt
