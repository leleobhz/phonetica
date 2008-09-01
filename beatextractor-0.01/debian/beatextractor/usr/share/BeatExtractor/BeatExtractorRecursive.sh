#!/bin/bash

if [[ -d ~/.bbep/ ]]; then
	if [[ -e ~/.bbep/target.btex ]]; then echo "" &> /dev/null
	else
	touch ~/.bbep/target.btex
	kdialog --title "Arquivo Vazio" --error "Provavelmente voce nao tinha o diretorio\n $HOME/.bbep/target.btex.\n Eu o criei para voce, porem sera necessario popular este arquivo.\n Leia a Documentacao para maiores informacoes"
	fi
else
mkdir ~/.bbep
touch ~/.bbep/target.btex
kdialog --title "Arquivo Vazio" --error "Provavelmente voce nao tinha o diretorio\n $HOME/.bbep/target.btex.\n Eu o criei para voce, porem sera necessario popular este arquivo.\n Leia a Documentacao para maiores informacoes"
fi

for file in `cat ~/.bbep/target.btex`; do
clear
/usr/share/BeatExtractor/BeatExtractor.sh "$1" "$2" "$3" "$4" "$5" "$6" "$7" "$8" "$9" "$file" "${10}"
done
