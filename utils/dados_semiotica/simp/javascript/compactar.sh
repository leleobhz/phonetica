#!/bin/sh
# SIMP
# Descricao: script para compactar os arquivos JavaScript
# Autor: Rubens Takiguti Ribeiro
# Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
# E-mail: rubens@tecnolivre.ufla.br
# Versao: 1.0.0.4
# Data: 16/01/2008
# Modificado: 29/09/2008
# Utilizacao: ./compactar.sh [arquivo]
# Observacaoes: requer o programa JavaScript-Squish-0.05 no path
# Copyright (C) 2008  Rubens Takiguti Ribeiro
# License: LICENSE.TXT
#

EXIT_STATUS=0

which js_compactor > /dev/null 2> /dev/null
if (($? != 0))
then
    echo "O programa JavaScript-Squish-0.05 (js_compactor) nao foi encontrado"
    echo "Consulte: http://search.cpan.org/~unrtst/JavaScript-Squish-0.05/"
    exit 1
fi

cd `dirname $0` > /dev/null 2> /dev/null

# Compactar todos arquivos
if (($# == 0))
then
    for arq in ./original/*.js
    do
        dest=`basename ${arq}`
        rm -f ${dest}
        js_compactor --src=${arq} --dest=${dest} --opt --force
        let EXIT_STATUS=$EXIT_STATUS+$?
    done

# Compactar um arquivo especifico
else
    if [ -f ./original/${1} ]
    then
        js_compactor --src=./original/${1} --dest=./${1} --opt --force
        let EXIT_STATUS=$EXIT_STATUS+$?
    else
        echo "Erro: arquivo inexistente (./original/${1})"
        let EXIT_STATUS=1
    fi
fi

cd - > /dev/null 2> /dev/null

if (($EXIT_STATUS == 0))
then
    echo "OK"
else
    echo "Erro";
fi

exit $EXIT_STATUS;
