<?php
//
// SIMP
// Descricao: Exemplo de como obter uma lista de atributos de uma classe atravez de uma linguagem simples
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.com.br
// Versao: 1.0.0.0
// Data: 03/03/2007
// Modificado: 03/03/2007
// Copyright (C) 2007  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
require_once('../config.php');

/*******************************************************************
Visao geral:
A classe filtro_atributo possui dois metodos estaticos que retornam
vetores com nomes de atributos atraves do processamento de uma
solicitacao, montada com uma linguagem de solicitacao de atirbutos.

O metodo get_atributos_classe obtem os atributos de uma classe
derivada da classe objeto.
O metodo get_atributos_objeto obtem os atributos de um objeto
derivado da classe objeto.

Descricao da linguagem de solicitacao de atributos:
Espacos e quebras de linhas sao ignorados.
O codigo e' dividido em partes separadas por ponto e virgula.
Cada parte possui um conjunto de instrucoes, que adicionam ou removem elementos da parte.
Cada parte representa um vetor, que e' construido com as instrucoes da parte.
Ao final, junta-se o resultado de cada parte para retornar um resultado final.

Cada instrucao e' formada por um tipo de instrucao seguido de um atributo e, opcionalmente,
seguido de um seletor especial.

Os tipos de instrucoes sao:
+ para adicionar um ou mais atributos na parte.
- para remover um ou mais atributos na parte.
caso o tipo seja omitido, considera-se a operacao de adicao.

Os seletores especiais sao delimitados por colchetes e sao utilizados no
final do atributo para obter grupos de atributos.

Os seletores especiais sao:
[PK] = obtem a chave primaria
[FK] = obtem as chaves estrangeiras
[S] = obtem todos os atributos simples (inclusive chaves)
[B] = obtem os atributos simples basicos (que nao sao chaves)
[I] = obtem os atributos implicitos
[OBJ] = obtem os nomes dos objetos de relacionamentos 1:1
[VET] = obtem os nomes dos vetores de relacionamentos 1:N
[*] = obtem todos os atributos

Exemplos abaixo.

*******************************************************************/

// Parte 1: Obter os atributos basicos do objeto usuario menos o atributo senha
// Parte 2: Obter a chave primaria do objeto grupo mais o nome do grupo
// Parte 3: Obter os atributos basicos e chaves estrangeiras da classe usuarios_grupos,
//          exceto a chave estrangeira "cod_grupo"
// Parte 4: Obter o atributo senha do objeto usuario
$vetor = filtro_atributo::get_atributos_classe('usuarios_grupos',
    'usuario:[B] , -usuario:senha;'.
    'grupo:[PK] , +grupo:nome;'.
    '[B] , +[FK] , -cod_grupo;'.
    'usuario:senha;'
);
util::dump($vetor);

// Observe que, mesmo removendo o atributo senha do objeto usuario na Parte 1,
// este mesmo atributo foi adicionado na Parte 4