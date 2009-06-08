<?php
//
// SIMP
// Descricao: Funcionalidades para gerar e testar uma senha
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.ufla.br
// Versao: 1.0.0.2
// Data: 15/05/2008
// Modificado: 06/11/2008
// Copyright (C) 2008  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
final class senha {


    //
    //     Construtor privado: utilize os metodos estaticos
    //
    private function __construct() {}


    //
    //     Gera uma senha aleatoriamente com algumas caracteristicas definidas
    //
    public static function gerar($tamanho = 8, $pronunciavel = false) {
    // Int $tamanho: numero de caracteres da senha gerada
    // Bool $pronunciavel: flag que indica se a senha deve ser pronunciavel ou nao
    //
        // Gerar senha
        $senha = '';

        // Se deseja uma senha pronunciavel
        if ($pronunciavel) {

            // Caracteres validos para gerar senha
            $vogais     = 'aeiou';
            $consoantes = 'bcdfgjmnprstvxz'; // Retirado o l para nao confundir com 1
            $numeros    = '023456789';       // Retirado o 1 para nao confundir com l

            $num_vogais     = strlen($vogais) - 1;
            $num_consoantes = strlen($consoantes) - 1;
            $num_numeros    = strlen($numeros) - 1;

            $parte1 = round($tamanho * 0.7);   // 70% letras
            $parte2 = $tamanho - $parte1;      // 30% numeros

            for ($i = 0; $i < $parte1; $i++) {
                if ($i % 2) {
                    $pos = mt_rand(0, $num_vogais);
                    $senha .= $vogais[$pos];
                } else {
                    $pos = mt_rand(0, $num_consoantes);
                    $senha .= $consoantes[$pos];
                }
            }
            for ($i = 0; $i < $parte2; $i++) {
                $pos = mt_rand(0, $num_numeros);
                $senha .= $numeros[$pos];
            }

        // Se deseja uma senha totalmente aleatoria
        } else {
            $simbolos = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!?:;@#$%&*()-+=[]{}';
            $num_simbolos = strlen($simbolos) - 1;
            for ($i = 0; $i < $tamanho; $i++) {
                $pos = mt_rand(0, $num_simbolos);
                $senha .= $simbolos[$pos];
            }
        }
        return $senha;
    }


    //
    //     Retorna o significado da nota
    //
    public static function get_resultado_qualidade($nota) {
    // Int $nota: valor da qualidade da senha
    //
        if (!is_numeric($nota)) {
            return 'desconhecida';
        }
        if ($nota < 25) {
            return 'muito fraca';
        } elseif ($nota < 50) {
            return 'fraca';
        } elseif ($nota < 75) {
            return 'boa';
        } elseif ($nota < 90) {
            return 'muito boa';
        } else {
            return 'excelente';
        }
    }


    //
    //     Testa a qualidade de uma senha e retorna uma nota entre 0 e 100
    //
    public static function get_qualidade($senha, $dados = null, &$qualidade = '', &$detalhes = '') {
    // String $senha: senha a ser testada
    // Array[String => String] $dados: alguns dados pessoais do dono da senha
    // String $qualidade: qualidade da nota na forma textual
    // String $detalhes: detalhes que podem ajudar a melhorar a qualidade
    //
        $tamanho = strlen(utf8_decode($senha));

        // Checar caracteres utilizados
        $vogais = 'aeiou';
        $consoantes = 'bcdfghjklmnpqrstvwxyz';

        $num_minusculas   = 0;
        $num_maiusculas   = 0;
        $num_vogais       = 0;
        $num_consoantes   = 0;
        $num_numeros      = 0;
        $num_simbolos     = 0;
        $num_repetidos    = 0;
        $num_distribuicao = 0;

        $buf = '';
        $tipo = false;
        for ($i = 0; $i < $tamanho; $i++) {
            $char = texto::substr($senha, $i, 1);

            // Checar caracteres repetidos
            if (strpos($buf, $char) !== false) {
                $num_repetidos += 1;
            } else {
                $buf .= $char;
            }

            if (is_numeric($char)) {
                if ($tipo != 'numero') {
                    $tipo = 'numero';
                    $num_distribuicao += 1;
                }
                $num_numeros += 1;
            } elseif (strpos($vogais, texto::strtolower($char)) !== false) {
                if ($tipo != 'letra') {
                    $tipo = 'letra';
                    $num_distribuicao += 1;
                }
                $num_vogais += 1;
                if ($char == texto::strtolower($char)) {
                    $num_minusculas += 1;
                } else {
                    $num_maiusculas += 1;
                }
            } elseif (strpos($consoantes, texto::strtolower($char)) !== false) {
                if ($tipo != 'letra') {
                    $tipo = 'letra';
                    $num_distribuicao += 1;
                }
                $num_consoantes += 1;
                if ($char == texto::strtolower($char)) {
                    $num_minusculas += 1;
                } else {
                    $num_maiusculas += 1;
                }
            } else {
                if ($tipo != 'simbolo') {
                    $tipo = 'simbolo';
                    $num_distribuicao += 1;
                }
                $num_simbolos += 1;
            }
        }

        $peso_minusculas   = ($num_minusculas ? 1 : 0);                            //   0 -  1
        $peso_maiusculas   = ($num_maiusculas ? 1 : 0);                            //   0 -  1
        $peso_vogais       = self::peso_simbolo($num_vogais);                      //  -1 -  2
        $peso_consoantes   = self::peso_simbolo($num_consoantes);                  //  -1 -  2
        $peso_numeros      = self::peso_simbolo($num_numeros);                     //  -1 -  2
        $peso_simbolos     = self::peso_simbolo($num_simbolos);                    //  -1 -  2
        $peso_repetidos    = self::peso_repetido($num_repetidos, $tamanho);        //  -1 -  1
        $peso_distribuicao = self::peso_distribuicao($num_distribuicao, $tamanho); //  -1 -  1
        $peso_tamanho      = self::peso_tamanho($tamanho);                         //   1 -  8
                                                                                   //  -5 - 20
        $nota = $peso_minusculas + $peso_maiusculas + $peso_vogais +
                $peso_consoantes + $peso_numeros + $peso_simbolos +
                $peso_repetidos + $peso_distribuicao + $peso_tamanho;

        $detalhes = "Min&uacute;sculas: {$peso_minusculas} (0 a 1)\n".
                    "Mai&uacute;sculas: {$peso_maiusculas} (0 a 1)\n".
                    "Vogais: {$peso_vogais} (-1 a 2)\n".
                    "Consoantes: {$peso_consoantes} (-1 a 2)\n".
                    "N&uacute;meros: {$peso_numeros} (-1 a 2)\n".
                    "S&iacute;mbolos: {$peso_simbolos} (-1 a 2)\n".
                    "Repetidos: {$peso_repetidos} (-1 a 1)\n".
                    "Distribui&ccedil;&atilde;o: ".round($peso_distribuicao, 2)." (-1 a 1)\n".
                    "Tamanho: {$peso_tamanho} (1 a 8)";

        // Retirar pontos caso usou algum dado pessoal
        if (is_array($dados) && ($total_dados = count($dados))) {
            foreach ($dados as $dado => $valor) {
                $p = self::peso_dado($senha, $valor);
                if ($p != 0) {
                    $detalhes .= "\nSemelhan&ccedil;a com {$dado}: {$p}";
                    $nota += $p;
                    break;
                }
            }
        }

        // Forcar que o valor fique entre 0 e 20
        $nota = max(0, min(20, $nota));

        // Retornar valor entre 0 e 100
        $nota = round((double)$nota * 5.0, 2);

        $qualidade = self::get_resultado_qualidade($nota);

        return $nota;
    }


    //
    //     Da um peso a qualidade de acordo com o numero de ocorrencias de um tipo de simbolo
    //
    private static function peso_simbolo($quantidade) {
    // Int $quantidade: quantidade de simbolos de um tipo
    //
        switch ($quantidade) {
        case 0:
            return -1;
        case 1:
            return 0;
        case 2:
            return 1;
        default:
            return 2;
        }
    }


    //
    //     Da um peso para ocorrencia de simbolos repetidos (entre -1 e 1)
    //
    private static function peso_repetido($quantidade, $tamanho) {
    // Int $quantidade: quantidade de simbolos repetidos
    // Int $tamanho: tamanho da senha
    //
        if (!$tamanho) {
            return 0;
        }
        $percentagem = (double)$quantidade / (double)$tamanho * 100.0;
        $peso = -2.0 * (double)$percentagem / 100.0 + 1.0;
        $peso = max(-1, min(1, $peso));
        return $peso;
    }


    //
    //     Da um peso para o quao distribuidos estao os simbolos na senha (entre -2 e 2)
    //
    private static function peso_distribuicao($quantidade, $tamanho) {
    // Int $quantidade: quantidade de mudancas de tipo ao longo da senha
    // Int $tamanho: tamanho da senha
    //
        if (!$tamanho) {
            return 0;
        }
        $percentagem = (double)$quantidade / (double)$tamanho * 100.0;
        $peso = 2.0 * (double)$percentagem / 100.0 - 1.0;
        $peso = max(-1, min(1, $peso));
        return $peso;
    }


    //
    //     Da um peso para o tamanho da senha (entre 1 e 8)
    //
    private static function peso_tamanho($tamanho) {
    // Int $tamanho: tamanho da senha
    //
        // Tamanho ideal: 14 ou mais
        $peso = (double)$tamanho / 2.0 + 1.0;
        $peso = max(0, min(8, round($peso)));
        return $peso;
    }


    //
    //     Da um peso negativo caso algum dado pessoal foi usado como parte da senha
    //
    private static function peso_dado($senha, $valor) {
    // String $senha: senha a ser testada
    // String $valor: valor a ser testado
    //
        if (strpos($valor, ' ') !== false) {
            $peso = 0;
            $vt_valor = explode(' ', $valor);
            foreach ($vt_valor as $i => $v) {
                $peso += self::peso_dado($senha, $v);
            }
            return $peso;
        }
        $senha = texto::strtolower(texto::strip_acentos(texto::strip_espacos($senha)));
        $valor = texto::strtolower(texto::strip_acentos($valor));

        // Se encontrou o dado pessoal na senha
        if (strpos($senha, $valor) !== false) {
            $percentagem = (double)strlen($valor) / (double)strlen($senha);
            return -10.0 * $percentagem;
        }

        // Se o dado pessoal e' razoavelmente parecido com a senha
        similar_text($senha, $valor, $percentagem);
        if ($percentagem > 40) {
            $peso = -10.0 * (double)strlen($valor) / (double)strlen($senha);
            return $peso * (double)$percentagem / 100.0;
        }

        // Nao encontrou o dado pessoal na senha
        return 0;
    }

}//class
