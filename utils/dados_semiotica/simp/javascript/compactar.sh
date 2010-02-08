#!/bin/sh
# SIMP
# Descricao: script para compactar os arquivos JavaScript
# Autor: Rubens Takiguti Ribeiro
# Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
# E-mail: rubens@tecnolivre.com.br
# Versao: 1.0.0.5
# Data: 16/01/2008
# Modificado: 12/01/2010
# Utilizacao: ./compactar.sh [arquivo]
# Observacaoes: requer o programa JavaScript-Squish-0.05 no path
# Copyright (C) 2008  Rubens Takiguti Ribeiro
# License: LICENSE.TXT
#

EXIT_STATUS=0

# buscar o js_compactor
programa=`which js_compactor 2> /dev/null`
if (($? == 0))
then
    
    if [ -f "/usr/local/bin/js_compactor" ]
    then
        programa="/usr/local/bin/js_compactor"

    elif [ -f "/usr/bin/js_compactor" ]
    then
        programa="/usr/bin/js_compactor"

    elif [ -f "/bin/js_compactor" ]
    then
        programa="/bin/js_compactor"

    else
        echo "O programa JavaScript-Squish-0.05 (js_compactor) nao foi encontrado"
        echo "Consulte: http://search.cpan.org/~unrtst/JavaScript-Squish-0.05/"
        exit 1
    fi
fi

cd `dirname $0` 2&> /dev/null

# Compactar todos arquivos
if (($# == 0))
then
    for arq in ./original/*.js
    do
        # Compactar
        dest=`basename ${arq}`
        rm -f ${dest}
        $programa --src=${arq} --dest=${dest} --opt --force
        let EXIT_STATUS=$EXIT_STATUS+$?

        # Remover espacos antes e depois
        php -r "file_put_contents('${dest}', trim(file_get_contents('${dest}')));" 2&> /dev/null
    done

# Compactar um arquivo especifico
else
    if [ -f ./original/${1} ]
    then
        # Compactar
        $programa --src=./original/${1} --dest=./${1} --opt --force
        let EXIT_STATUS=$EXIT_STATUS+$?

        # Remover espacos antes e depois
        php -r "file_put_contents('${dest}', trim(file_get_contents('${dest}')));" 2&> /dev/null

    else
        echo "Erro: arquivo inexistente (./original/${1})"
        let EXIT_STATUS=1
    fi
fi

cd - 2&> /dev/null

if (($EXIT_STATUS == 0))
then
    echo "OK"
else
    echo "Erro";
fi

exit $EXIT_STATUS
