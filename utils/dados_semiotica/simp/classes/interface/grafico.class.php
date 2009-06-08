<?php
//
// SIMP
// Descricao: Classe de geracao de Graficos com a biblioteca GD ou em HTML
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.ufla.br
// Versao: 1.0.0.21
// Data: 20/06/2007
// Modificado: 04/03/2009
// Copyright (C) 2007  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
global $CFG;

// Tipos de Graficos
define('GRAFICO_LINHA', 1);
define('GRAFICO_BARRA', 2);
define('GRAFICO_PIZZA', 3);

// Posicoes
define('DIREITA',  1);
define('ESQUERDA', 2);
define('CIMA',     3);
define('BAIXO',    4);

// Tipos de Cores
define('COR_CLARA',  1);
define('COR_ESCURA', 2);
define('COR_NORMAL', 3);

// Tipos de Bordas
define('BORDA_SOLIDA', 1);
define('BORDA_3D',     2);

// Tipos de Pontos sobre graficos de Linha
define('PONTO_NENHUM',   0);
define('PONTO_BOLA',     1);
define('PONTO_QUADRADO', 2);

// Formatos de Arquivos
define('TIPO_PNG',  1);
define('TIPO_JPG',  2);
define('TIPO_GIF',  3);
define('TIPO_BMP',  4);
define('TIPO_HTML', 5);

// Constantes
define('GRAFICO_LOCALIDADE', $CFG->localidade);
define('GRAFICO_GD',         $CFG->gd);
define('GRAFICO_GMT',        $CFG->gmt);
define('MARGEM',        10);  // Margem entre os elementos
define('ALTURA_TITULO', 15);  // Altura da fonte do Titulo
define('ALTURA_TEXTO',  14);  // Altura da fonte do Texto
define('QUADRADO',      12);  // Tamanho dos lados do quadrado da legenda
define('PRECISAO',      2);   // Precisao da porcentagem exibixa
define('CLAREAR',       1.4); // Porcentagem do clareamento das cores
define('ESCURECER',     0.7); // Porcentagem do escurecimento das cores
define('FONTE_TITULO',  $CFG->dirclasses.'interface/fontes/AppleGaramond-Bold.ttf'); // Fonte do Titulo
define('FONTE',         $CFG->dirclasses.'interface/fontes/AppleGaramond.ttf');      // Fonte do Texto


//
//     Classe
//
class grafico {

    // Geral/Controle
    private $gd            = false;        // Possui biblioteca GD instalada

    // Atributos principais
    private $titulo         = false;        // Titulo do Grafico
    private $tipo_grafico   = false;        // Tipo de Grafico (GRAFICO_BARRA, GRAFICO_LINHA ou GRAFICO_PIZZA)
    private $altura         = 200;          // Altura em pixels
    private $largura        = 300;          // Largura em pixels
    private $valor_topo     = false;        // Maior valor da escala vertical (graficos de barra ou de linha)
    private $valores        = false;        // Vetor ou Matriz de valores
    private $legenda        = false;        // Vetor de itens da legenda
    private $escala         = false;        // Vetor de itens da escala horizontal
    private $linhas         = false;        // Vetor de linhas que cortam os graficos de linha ou barra (pares de valores indicando inicio e fim da linha)
    private $legenda_linhas = false;        // Vetor de itens da legenda das linhas que cortam o grafico

    // Opcoes adicionais
    private $angulo        = 45;           // Angulo do texto da escala horizontal (entre 30 e 90)
    private $pos_legenda   = DIREITA;      // Posicao da legenda (DIREITA, ESQUERDA, CIMA ou BAIXO)
    private $tipo_cor      = COR_NORMAL;   // Tom das cores (COR_NORMAL, COR_CLARA ou COR_ESCURA)
    private $borda         = BORDA_3D;     // Tipo de borda (BORDA_SOLIDA ou BORDA_3D)
    private $ponto         = PONTO_NENHUM; // Tipo de ponto (PONTO_NENHUM, PONTO_BOLA, PONTO_QUADRADO)
    private $cores         = false;        // Vetor com as cores a serem utilizadas
    private $formato       = TIPO_PNG;     // Tipo de arquivo (TIPO_PNG, TIPO_JPG, TIPO_GIF ou TIPO_BMP)
    private $qualidade     = 100;          // Qualidade da imagem para arquivos jpg (entre 0 e 100)
    private $nome_arquivo  = 'grafico';    // Nome do arquivo gerado
    private $salvar        = false;        // Opcao para fazer download da imagem
    private $cache         = false;        // Tempo da imagem em cache


    //
    //     Construtor
    //
    public function __construct($titulo = '', $tipo_grafico = GRAFICO_BARRA, $altura = 200, $largura = 300) {
    // String $titulo: titulo do grafico
    // Int $tipo_grafico: tipo de grafico
    // Int $altura: altura em pixels
    // Int $largura: largura em pixels
    //
        $this->gd           = extension_loaded('gd');
        $this->titulo       = $titulo;
        $this->tipo_grafico = $tipo_grafico;
        $this->altura       = $altura;
        $this->largura      = $largura;
    }


    //
    //     Obtem um atributo
    //
    public function __get($campo) {
    // String $campo: nome do campo
    //
        if (isset($this->$campo)) {
            return $this->$campo;
        }
        return null;
    }


    //
    //     Define os atributos
    //
    public function __set($campo, $valor) {
    // String $campo: nome do campo
    // Mixed $valor: valor a ser atribuido
    //
        if (isset($this->$campo)) {
            switch ($campo) {

            // STRING
            case 'titulo':
            case 'nome_arquivo':
                $this->$campo = (string)$valor;
                break;

            // FLOAT
            case 'valor_topo':
                $this->$campo = (float)$valor;
                break;

            // INTEGER
            case 'tipo_grafico':
            case 'altura':
            case 'largura':
            case 'pos_legenda':
            case 'tipo_cor':
            case 'borda':
            case 'ponto':
            case 'qualidade':
            case 'formato':
                $this->$campo = (int)$valor;
                break;
            case 'angulo': // entre 30 e 90
                $this->$campo = max(min((int)$valor, 90), 30);
                break;

            // ARRAY
            case 'valores':
            case 'legenda':
            case 'escala':
            case 'cores':
            case 'linhas':
            case 'legenda_linhas':
                $this->$campo = (array)$valor;
                break;

            // BOOLEAN
            case 'salvar':
                $this->$campo = (bool)$valor;
                break;

            // OUTROS
            default:
                $this->$campo = $valor;
            }
        }
    }


    //
    //     Retorna um grafico com imagem ou montado com estilos CSS
    //
    static public function exibir_grafico($nome, $link, $arquivo, $dados = null, $html = false) {
    // String $nome: nome do grafico
    // String $link: link onde e' processada a imagem do grafico
    // String $arquivo: arquivo onde e' processada a imagem do grafico
    // Object $dados: objeto com os parametros de filtragem
    // Bool $html: forcar que seja um grafico em HTML
    //
        static $i = 1;
        if (GRAFICO_GD && (!$html)) {
            $parametros = array();
            $src = $link;
            if (is_array($dados) || is_object($dados)) {
                foreach ($dados as $chave => $valor) {
                    $src = link::adicionar_atributo($src, $chave, $valor);
                }
            }
            $longdesc = link::adicionar_atributo($src, 'longdesc', 1);
            $mapa = 'mapa'.($i++);
            echo "<div class=\"area_grafico\">\n";
            echo "<img class=\"imagem\" src=\"{$src}\" longdesc=\"{$longdesc}\" usemap=\"#{$mapa}\" alt=\"{$nome}\" title=\"{$nome}\" />\n";
            echo "<p><a rel=\"blank\" href=\"{$longdesc}\" class=\"acessivel\">Resultado Textual</a></p>\n";
            echo "</div>\n";

            $link_mapa = link::adicionar_atributo($src, 'mapa', $mapa);
            $f = fopen(texto::decodificar($link_mapa), 'r');
            while (!feof($f)) {
                echo fgets($f, 1024);
            }
            fclose($f);
        } else {
            if (is_array($dados) || is_object($dados)) {
                foreach ($dados as $chave => $valor) { $_GET[$chave] = $valor; }
            }
            require_once($arquivo);
        }
    }


    //
    //     Imprime o grafico (conteudo do arquivo)
    //
    public function imprimir() {

        // Se pediu para descrever o grafico, exibir um HTML textual
        if (isset($_GET['longdesc'])) {
            $this->descrever_grafico();
            return;

        // Se pediu para exibir o mapa da imagem, exibir um HTML
        } elseif (isset($_GET['mapa'])) {
            $this->imprimir_mapa($_GET['mapa']);
            return;
        }

        // [0] Checar se pode usar a GD
        if (($this->formato == TIPO_HTML) || (!$this->gd)) {
            $this->imprimir_grafico_html();
            return;
        }

        // [1] Recuperar dados gerais
        list($nome, $mime, $funcao) = $this->get_dados();

        // [2] Calcular Tamanhos
        $this->tamanhos($vt_total, $vt_grafico, $vt_legenda);

        // Largura e altura total
        list($largura, $altura) = $vt_total;

        // Limites da area do grafico
        list($inicio_x, $inicio_y, $fim_x, $fim_y) = $vt_grafico;

        // Limites da area da legenda
        list($inicio_l_x, $inicio_l_y, $fim_l_x, $fim_l_y) = $vt_legenda;

        // [3] Criar a imagem
        $img = imagecreatetruecolor($largura, $altura);

        // [4] Alocar as cores padrao
        if (!$this->cores) {
            $this->alocar_cores($img);
        }

        // [5] Pintar Fundo
        imagefill($img, 0, 0, $this->cores['fundo']);
        $this->borda($img, 0, 0, $largura - 1, $altura - 1, 'borda', 1);

        // [6] Imprimir a legenda
        if ($this->legenda || $this->legenda_linhas) {
            $this->imprimir_legenda($img, $inicio_l_x, $inicio_l_y, $fim_l_x, $fim_l_y);
        }

        // [7] Imprimir Grafico
        $this->imprimir_grafico($img, $inicio_x, $inicio_y, $fim_x, $fim_y);

        // [8] Gerar Titulo
        imagettftext($img, ALTURA_TITULO, 0, MARGEM, ALTURA_TITULO + MARGEM, $this->cores['texto'], FONTE_TITULO, $this->titulo);

        // [9] Gerar o cabecalho HTTP
        $this->cabecalho($nome, $mime);

        // [10] Exibir a imagem e desalocar da memoria
        if ($this->formato == TIPO_JPG) {
            $funcao($img, '', $this->qualidade);
        } else {
            $funcao($img);
        }
        imagedestroy($img);
    }


    //
    //     Retorna o nome do arquivo, o mime-type e a funcao para criar a imagem
    //
    private function get_dados() {
        switch ($this->formato) {
        case TIPO_JPG:
            $nome   = $this->nome_arquivo.'.jpg';
            $mime   = 'image/jpeg';
            $funcao = 'imagejpeg';
            break;
        case TIPO_PNG:
            $nome   = $this->nome_arquivo.'.png';
            $mime   = 'image/png';
            $funcao = 'imagepng';
            break;
        case TIPO_GIF:
            $nome   = $this->nome_arquivo.'.gif';
            $mime   = 'image/gif';
            $funcao = 'imagegif';
            break;
        case TIPO_BMP:
            $nome   = $this->nome_arquivo.'.bmp';
            $mime = 'image/vnd.wap.wbmp';
            $funcao = 'imagewbmp';
            break;
        }
        return array($nome, $mime, $funcao);
    }


    //
    //     Calcula o tamanho da imagem, do grafico e da legenda
    //
    private function tamanhos(&$total, &$grafico, &$legenda) {
    // Array[Int] $total: vetor com a largura e altura da imagem
    // Array[Int] $grafico: vetor com os limites do grafico
    // Array[Int] $legenda: vetor com os limites da legenda
    //
        // Calcular tamanho da legenda
        $count_legenda = is_array($this->legenda) ? count($this->legenda) : 0;
        $count_legenda_linhas = is_array($this->legenda_linhas) ? count($this->legenda_linhas) : 0;
        if ($count_legenda || $count_legenda_linhas) {

            // Achar texto mais largo da legenda ($max)
            $vt_legenda = array('Legenda:');
            if ($count_legenda) {
                $vt_legenda = array_merge($vt_legenda, $this->legenda);
            }
            if ($count_legenda_linhas) {
                $vt_legenda = array_merge($vt_legenda, $this->legenda_linhas);
            }

            $max = 0;
            foreach ($vt_legenda as $l) {
                $tam = $this->largura_texto($l);
                $max = ($max > $tam) ? $max : $tam;
            }
            $largura_legenda = MARGEM + QUADRADO + MARGEM + $max + MARGEM;
            $altura_legenda = MARGEM + ((ALTURA_TEXTO + 5) * count($vt_legenda)) + MARGEM;

            // Calcular maior altura e largura
            $max_largura = ($largura_legenda > $this->largura) ? $largura_legenda
                                                               : $this->largura;
            $max_altura = ($altura_legenda > $this->altura) ? $altura_legenda
                                                            : $this->altura;

            // Calcular tamanho da imagem de acordo com a posicao da legenda
            switch ($this->pos_legenda) {

            case DIREITA:
                $largura    = MARGEM + $this->largura + MARGEM + $largura_legenda + MARGEM;
                $altura     = MARGEM + ALTURA_TITULO + MARGEM + $max_altura + MARGEM;
                $inicio_x   = MARGEM;
                $inicio_y   = MARGEM + ALTURA_TITULO + MARGEM;
                $inicio_l_x = MARGEM + $this->largura + MARGEM;
                $inicio_l_y = MARGEM + ALTURA_TITULO + MARGEM;
                break;

            case ESQUERDA:
                $largura    = MARGEM + $largura_legenda + MARGEM + $this->largura + MARGEM;
                $altura     = MARGEM + ALTURA_TITULO + MARGEM + $max_altura + MARGEM;
                $inicio_x   = MARGEM + $largura_legenda + MARGEM;
                $inicio_y   = MARGEM + ALTURA_TITULO + MARGEM;
                $inicio_l_x = MARGEM;
                $inicio_l_y = MARGEM + ALTURA_TITULO + MARGEM;
                break;

            case CIMA:
                $largura    = MARGEM + $max_largura + MARGEM;
                $altura     = MARGEM + ALTURA_TITULO + MARGEM + $altura_legenda + MARGEM + $this->altura + MARGEM;
                $inicio_x   = MARGEM;
                $inicio_y   = MARGEM + ALTURA_TITULO + MARGEM + $altura_legenda + MARGEM;
                $inicio_l_x = MARGEM;
                $inicio_l_y = MARGEM + ALTURA_TITULO + MARGEM;
                break;

            case BAIXO:
                $largura    = MARGEM + $max_largura + MARGEM;
                $altura     = MARGEM + ALTURA_TITULO + MARGEM + $this->altura + MARGEM + $altura_legenda + MARGEM;
                $inicio_x   = MARGEM;
                $inicio_y   = MARGEM + ALTURA_TITULO + MARGEM;
                $inicio_l_x = MARGEM;
                $inicio_l_y = MARGEM + ALTURA_TITULO + MARGEM + $this->altura + MARGEM;
                break;
            }
            $fim_x = $inicio_x + $this->largura;
            $fim_y = $inicio_y + $this->altura;
            $fim_l_x = $inicio_l_x + $largura_legenda;
            $fim_l_y = $inicio_l_y + $altura_legenda;

        // Se nao tem legenda
        } else {
            $largura  = MARGEM + $this->largura + MARGEM;
            $altura   = MARGEM + ALTURA_TITULO + MARGEM + $this->altura + MARGEM;
            $inicio_x = MARGEM;
            $inicio_y = MARGEM + ALTURA_TITULO + MARGEM;
            $fim_x    = $inicio_x + $this->largura;
            $fim_y    = $inicio_y + $this->altura;
        }

        // Checar largura do titulo
        $largura_titulo = MARGEM + $this->largura_texto($this->titulo, ALTURA_TITULO, FONTE_TITULO) + MARGEM;
        if ($largura_titulo > $largura) {
            $largura = $largura_titulo;
        }

        // Armazenar resultados nos vetores passados por parametro
        $total   = array($largura, $altura);
        $grafico = array($inicio_x, $inicio_y, $fim_x, $fim_y);
        if ($this->legenda || $this->legenda_linhas) {
            $legenda = array($inicio_l_x, $inicio_l_y, $fim_l_x, $fim_l_y);
        }
    }


    //
    //     Retorna o cabecalho HTTP da imagem
    //
    private function cabecalho($nome, $mime) {
    // String $nome: nome do arquivo
    // String $mime: mime-type do arquivo
    //
        $disposition  = ($this->salvar) ? 'attachment' : 'inline';

        $t = time();

        // Header
        setlocale(LC_TIME, 'C');
        header("Content-Type: {$mime}");
        header("Content-Disposition: {$disposition}; filename={$nome}");
        header('Date: '.gmstrftime(GRAFICO_GMT, $t));
        header('Last-Modified: '.gmstrftime(GRAFICO_GMT, $t));
        if ($this->cache) {
            $c = $t + $this->cache;
            header('Expires: '.gmstrftime(GRAFICO_GMT, $c));
            header('Cache-Control: public');
            header('Pragma: ');
        } else {
            header('Expires: '.gmstrftime(GRAFICO_GMT, $t));
            header('Cache-Control: no-cache, no-store, must-revalidate');
            header('Pragma: no-cache');
        }
        setlocale(LC_TIME, GRAFICO_LOCALIDADE);
    }


    //
    //     Imprime a legenda em uma posicao
    //
    private function imprimir_legenda(&$img, $ix, $iy, $fx, $fy) {
    // Resource $img: imagem usada para imprimir a legenda
    // Int $ix: coordenada x inicial (superior esquerdo)
    // Int $iy: coordenada y inicial
    // Int $fx: coordenada x final (inferior direito)
    // Int $fy: coordenada y final
    //

        // Checar se existe legenda
        if (!$this->legenda && !$this->legenda_linhas) {
            return;
        }

        // Area da Legenda
        imagefilledrectangle($img, $ix, $iy, $fx, $fy, $this->cores['fundo_grafico']);
        $this->borda($img, $ix, $iy, $fx, $fy, 'borda', 1);

        // Imprimir titulo da legenda
        $y = $iy + MARGEM;
        imagettftext($img, ALTURA_TEXTO, 0, $ix + MARGEM, $y + ALTURA_TEXTO, $this->cores['texto'], FONTE_TITULO, 'Legenda:');
        $y += 5 + ALTURA_TEXTO;

        // Imprimir cada item
        $i = 0;
        if (is_array($this->legenda)) {
            foreach ($this->legenda as $l) {

                switch ($this->tipo_grafico) {

                // Imprimir quadrado com cor
                case GRAFICO_BARRA:
                    // Calcular valor para alinhar os quadrados da legenda no centro verticalmente
                    $c = (int)((ALTURA_TEXTO - QUADRADO) / 2);

                    imagefilledrectangle($img, $ix + MARGEM, $y + $c, $ix + MARGEM + QUADRADO, $y + $c + QUADRADO, $this->cores[$i]);
                    $this->borda($img, $ix + MARGEM, $y + $c, $ix + MARGEM + QUADRADO, $y + $c + QUADRADO, $i);
                    break;

                // Imprimir quadrado com cor
                case GRAFICO_PIZZA:

                    // Calcular valor para alinhar os quadrados da legenda no centro verticalmente
                    $c = (int)((ALTURA_TEXTO - QUADRADO) / 2);

                    imagefilledrectangle($img, $ix + MARGEM, $y + $c, $ix + MARGEM + QUADRADO, $y + $c +QUADRADO, $this->cores[$i]);
                    $this->borda($img, $ix + MARGEM, $y + $c, $ix + MARGEM + QUADRADO, $y + $c + QUADRADO, $i);
                    break;

                // Imprimir Linha
                case GRAFICO_LINHA:

                    // Calcular valor para alinhar as linhas da legenda no centro verticalmente
                    $c = (int)(QUADRADO / 2);

                    imagesetthickness($img, 2);
                    imageline($img, $ix + MARGEM, $y + $c, $ix + MARGEM + QUADRADO, $y + $c, $this->cores[$i]);
                    imagesetthickness($img, 1);
                    break;
                }

                // Imprimir texto
                imagettftext($img, ALTURA_TEXTO, 0, $ix + MARGEM + QUADRADO + MARGEM, $y + ALTURA_TEXTO, $this->cores['texto'], FONTE, $l);

                // Atualizar eixo Y
                $y += 5 + ALTURA_TEXTO;
                $i++;
            }

        // Atualizar o $i, pois graficos de linha simples podem nao ter legenda
        } else {
            $i = 1;
        }

        // Imprimir itens das linhas
        if (is_array($this->legenda_linhas)) {
            foreach ($this->legenda_linhas as $l) {
                switch ($this->tipo_grafico) {
                case GRAFICO_BARRA:
                case GRAFICO_LINHA:

                    // Calcular valor para alinhar as linhas da legenda no centro verticalmente
                    $c = (int)(QUADRADO / 2);

                    imageline($img, $ix + MARGEM, $y + $c, $ix + MARGEM + QUADRADO, $y + $c, $this->cores[$i]);
                    break;
                }

                // Imprimir texto
                imagettftext($img, ALTURA_TEXTO, 0, $ix + MARGEM + QUADRADO + MARGEM, $y + ALTURA_TEXTO, $this->cores['texto'], FONTE, $l);

                // Atualizar eixo Y
                $y += 5 + ALTURA_TEXTO;
                $i++;
            }
        }
    }


    //
    //     Imprime o grafico
    //
    private function imprimir_grafico(&$img, $ix, $iy, $fx, $fy, $mapa = false) {
    // Resource $img: imagem usada para imprimir a legenda
    // Int $ix: coordenada x inicial (superior esquerdo)
    // Int $iy: coordenada y inicial
    // Int $fx: coordenada x final (inferior direito)
    // Int $fy: coordenada y final
    // Bool $mapa: indica se deve exibir o mapa da imagem (true) ou o grafico (false)
    //
        // Verificar se existem valores para desenhar o grafico
        if (!is_array($this->valores)) {
            return;
        }

        // Desenhar Area do Grafico
        if (!$mapa) {
            imagefilledrectangle($img, $ix, $iy, $fx, $fy, $this->cores['fundo_grafico']);
            $this->borda($img, $ix, $iy, $fx, $fy, 'borda', 1);
        }

        // Colocar margem para area onde o grafico sera impresso
        $ix += MARGEM;
        $iy += MARGEM;
        $fx -= MARGEM;
        $fy -= MARGEM;

        // Desenhar o grafico de acordo com o tipo
        switch ($this->tipo_grafico) {
        case GRAFICO_BARRA:
            $this->imprimir_grafico_barra($img, $ix, $iy, $fx, $fy, $mapa);
            break;
        case GRAFICO_LINHA:
            $this->imprimir_grafico_linha($img, $ix, $iy, $fx, $fy, $mapa);
            break;
        case GRAFICO_PIZZA:
            $this->imprimir_grafico_pizza($img, $ix, $iy, $fx, $fy, $mapa);
            break;
        }
    }


    //
    //     Imprime um grafico de barra
    //
    private function imprimir_grafico_barra(&$img, $ix, $iy, $fx, $fy, $mapa = false) {
    // Resource $img: imagem usada para imprimir a legenda
    // Int $ix: coordenada x inicial (superior esquerdo)
    // Int $iy: coordenada y inicial
    // Int $fx: coordenada x final (inferior direito)
    // Int $fy: coordenada y final
    // Bool $mapa: indica se deve exibir o mapa da imagem (true) ou o grafico (false)
    //
        $iy += MARGEM;
        $valores = array_values($this->valores);

        // Obter maior valor da escala vertical
        if ($this->valor_topo === false) {
            if (is_array($valores[0])) {
                $maior = 0;
                foreach ($valores as $vet) {
                    $vet = array_values($vet);
                    $maior_vet = max($vet);
                    $maior = ($maior_vet > $maior) ? $maior_vet : $maior;
                }
            } else {
                $maior = max($valores);
            }
        } else {
            $maior = $this->valor_topo;
        }

        // Imprimir moldura
        $v = $this->imprimir_moldura($img, $maior, $ix, $iy, $fx, $fy, $mapa);
        list($div_h, $ix, $iy, $fx, $fy) = $v;

        // Imprimir conteudo do grafico
        $this->imprimir_conteudo_barra($img, $maior, $div_h, $ix, $iy + 1, $fx, $fy - 1, $mapa);

        // Imprimir linhas extras
        $this->imprimir_linhas_extras($img, $maior, $ix + 1 - $div_h / 2, $iy, $fx - 1 + $div_h / 2, $fy);
    }


    //
    //     Imprime o conteudo do grafico de barras
    //
    private function imprimir_conteudo_barra(&$img, $maior, $div_h, $ix, $iy, $fx, $fy, $mapa = false) {
    // Resource $img: imagem usada para imprimir a legenda
    // Int $maior: maior largura do grafico
    // Int $div_h: distancia entre as barras
    // Int $ix: coordenada x inicial (superior esquerdo)
    // Int $iy: coordenada y inicial
    // Int $fx: coordenada x final (inferior direito)
    // Int $fy: coordenada y final
    // Bool $mapa: indica se deve exibir o mapa da imagem (true) ou o grafico (false)
    //
        $px = $ix;
        $c = (QUADRADO / 2);
        $altura = $fy - $iy;
        $valores = array_values($this->valores);

        // Varias Barras (varias cores)
        if (is_array($valores[0])) {
            $count_valores = count($valores);
            $c = ($div_h - MARGEM) / (2 * $count_valores);
            foreach ($valores as $i => $v) {
                $v = array_values($v);
                $px = $ix - (($div_h - MARGEM) / 2);
                $px = $px + (2 * $c * $i) + $c;
                foreach ($v as $j => $valor) {
                    $py = $fy - ($valor * $altura / max($maior, 1));
                    $px1 = $px - $c;
                    $px2 = $px + $c - 1;
                    if (!$mapa) {
                        imagefilledrectangle($img, $px1, $py, $px2, $fy, $this->cores[$i]);
                        $this->borda($img, $px1, $py, $px2, $fy, $i);
                    } else {
                        $ponto_x = round($px1);
                        $ponto_y = round($py);
                        $ponto_x2 = round($px2);
                        $ponto_y2 = round($fy);
                        $title = ($this->legenda ? $this->legenda[$i].'/' : '').$this->escala[$j].': '.$valor;
                        echo "<area shape=\"rect\" coords=\"{$ponto_x},{$ponto_y},{$ponto_x2},{$ponto_y2}\" nohref=\"nohref\" title=\"{$title}\" />\n";
                    }
                    $px += $div_h;
                }
            }

        // Uma Barra (uma cor)
        } else {
            foreach ($valores as $i => $valor) {
                $py = $fy - ($valor * $altura / max($maior, 1));
                $px1 = $px - $c;
                $px2 = $px + $c - 1;
                if (!$mapa) {
                    imagefilledrectangle($img, $px1, $py, $px2, $fy, $this->cores[0]);
                    $this->borda($img, $px1, $py, $px2, $fy, 0);
                } else {
                    $ponto_x = round($px1);
                    $ponto_y = round($py);
                    $ponto_x2 = round($px2);
                    $ponto_y2 = round($fy);
                    $title = $this->escala[$i].': '.$valor;
                    echo "<area shape=\"rect\" coords=\"{$ponto_x},{$ponto_y},{$ponto_x2},{$ponto_y2}\" nohref=\"nohref\" title=\"{$title}\" />\n";
                }
                $px += $div_h;
            }
        }
    }


    //
    //     Imprime um grafico de linha
    //
    private function imprimir_grafico_linha(&$img, $ix, $iy, $fx, $fy, $mapa = false) {
    // Resource $img: imagem usada para imprimir a legenda
    // Int $ix: coordenada x inicial (superior esquerdo)
    // Int $iy: coordenada y inicial
    // Int $fx: coordenada x final (inferior direito)
    // Int $fy: coordenada y final
    // Bool $mapa: indica se deve exibir o mapa da imagem (true) ou o grafico (false)
    //
        $iy += MARGEM;
        $valores = array_values($this->valores);

        // Obter maior valor da escala vertical
        if ($this->valor_topo === false) {
            if (is_array($valores[0])) {
                $maior = 0;
                foreach ($valores as $vet) {
                    $vet = array_values($vet);
                    $maior_vet = max($vet);
                    $maior = ($maior_vet > $maior) ? $maior_vet : $maior;
                }
            } else {
                $maior = max($valores);
            }
        } else {
            $maior = $this->valor_topo;
        }

        // Imprimir moldura
        $v = $this->imprimir_moldura($img, $maior, $ix, $iy, $fx, $fy, $mapa);
        list($div_h, $ix, $iy, $fx, $fy) = $v;

        // Imprimir conteudo do grafico
        $this->imprimir_conteudo_linha($img, $maior, $div_h, $ix, $iy, $fx, $fy, $mapa);

        // Imprimir linhas extras
        $this->imprimir_linhas_extras($img, $maior, $ix + 1 - $div_h / 2, $iy, $fx - 1 + $div_h / 2, $fy);
    }


    //
    //     Imprime o conteudo do grafico de linhas
    //
    private function imprimir_conteudo_linha(&$img, $maior, $div_h, $ix, $iy, $fx, $fy, $mapa = false) {
    // Resource $img: imagem usada para imprimir a legenda
    // Int $maior: maior valor do grafico
    // Int $div_h: distancia entre as linhas verticais
    // Int $ix: coordenada x inicial (superior esquerdo)
    // Int $iy: coordenada y inicial
    // Int $fx: coordenada x final (inferior direito)
    // Int $fy: coordenada y final
    // Bool $mapa: indica se deve exibir o mapa da imagem (true) ou o grafico (false)
    //
        $altura = $fy - $iy;

        $valores = array_values($this->valores);

        // Varias Linhas
        if (is_array($valores[0])) {

            $count_valores = count($valores);
            for ($i = 0; $i < $count_valores; $i++) {
                $v = array_values($valores[$i]);
                $px = $ix;
                $py = $fy - ($v[0] * $altura / max($maior ,1));
                $primeiro = array_shift($v);

                // Pintar primeiro ponto
                if (!$mapa) {
                    switch ($this->ponto) {
                    case PONTO_BOLA:
                        imagefilledellipse($img, $px, $py, 7, 7, $this->cores[$i]);
                        break;
                    case PONTO_QUADRADO:
                        imagefilledrectangle($img, $px - 3, $py - 3, $px + 3, $py + 3, $this->cores[$i]);
                        break;
                    }

                // Mapa do primeiro ponto
                } else {
                    $ponto_x  = round($px - 5);
                    $ponto_x2 = round($px + 5);
                    $ponto_y  = round($py - 5);
                    $ponto_y2 = round($py + 5);
                    $title = ($this->legenda ? $this->legenda[$i].'/' : '').$this->escala[0].': '.$primeiro;
                    echo "<area shape=\"rect\" coords=\"{$ponto_x},{$ponto_y},{$ponto_x2},{$ponto_y2}\" title=\"{$title}\" nohref=\"nohref\" />\n";
                }

                foreach ($v as $j => $valor) {
                    $py2 = $fy - ($valor * $altura / max($maior, 1));

                    // Linha e Ponto
                    if (!$mapa) {
                        imagesetthickness($img, 2);
                        imageline($img, $px, $py, $px + $div_h, $py2, $this->cores[$i]);
                        imagesetthickness($img, 1);

                        switch ($this->ponto) {
                        case PONTO_BOLA:
                            imagefilledellipse($img, $px + $div_h, $py2, 7, 7, $this->cores[$i]);
                            break;
                        case PONTO_QUADRADO:
                            imagefilledrectangle($img, $px + $div_h - 3, $py2 - 3, $px + $div_h + 3, $py2 + 3, $this->cores[$i]);
                            break;
                        }

                    // Mapa do ponto
                    } else {
                        $ponto_x  = round($px + $div_h - 5);
                        $ponto_x2 = round($px + $div_h + 5);
                        $ponto_y  = round($py2 - 5);
                        $ponto_y2 = round($py2 + 5);
                        $title = ($this->legenda ? $this->legenda[$i].'/' : '').$this->escala[$j + 1].': '.$valor;
                        echo "<area shape=\"rect\" coords=\"{$ponto_x},{$ponto_y},{$ponto_x2},{$ponto_y2}\" title=\"{$title}\" nohref=\"nohref\" />\n";
                    }
                    $py = $py2;
                    $px += $div_h;
                }
            }

        // Uma linha
        } else {
            $px = $ix;
            $py = $fy - ($valores[0] * $altura / max($maior, 1));
            $primeiro = array_shift($valores);

            // Primeiro ponto
            if (!$mapa) {
                switch ($this->ponto) {
                case PONTO_BOLA:
                    imagefilledellipse($img, $px, $py, 7, 7, $this->cores[0]);
                    break;
                case PONTO_QUADRADO:
                    imagefilledrectangle($img, $px - 3, $py - 3, $px + 3, $py + 3, $this->cores[0]);
                    break;
                }

            // Mapa do primeiro ponto
            } else {
                $ponto_x  = round($px - 5);
                $ponto_x2 = round($px + 5);
                $ponto_y  = round($py - 5);
                $ponto_y2 = round($py + 5);
                $title = $this->escala[0].': '.$primeiro;
                echo "<area shape=\"rect\" coords=\"{$ponto_x},{$ponto_y},{$ponto_x2},{$ponto_y2}\" title=\"{$title}\" nohref=\"nohref\" />\n";
            }

            foreach ($valores as $i => $valor) {
                $py2 = $fy - ($valor * $altura / max($maior, 1));

                // Linha e ponto
                if (!$mapa) {
                    imagesetthickness($img, 2);
                    imageline($img, $px, $py, $px + $div_h, $py2, $this->cores[0]);
                    imagesetthickness($img, 1);

                    switch ($this->ponto) {
                    case PONTO_BOLA:
                        imagefilledellipse($img, $px + $div_h, $py2, 7, 7, $this->cores[0]);
                        break;
                    case PONTO_QUADRADO:
                        imagefilledrectangle($img, $px + $div_h - 3, $py2 - 3, $px + $div_h + 3, $py2 + 3, $this->cores[0]);
                        break;
                    }

                // Mapa do ponto
                } else {
                    $ponto_x  = round($px + $div_h - 5);
                    $ponto_x2 = round($px + $div_h + 5);
                    $ponto_y  = round($py2 - 5);
                    $ponto_y2 = round($py2 + 5);
                    $title = $this->escala[$i + 1].': '.$valor;
                    echo "<area shape=\"rect\" coords=\"{$ponto_x},{$ponto_y},{$ponto_x2},{$ponto_y2}\" title=\"{$title}\" nohref=\"nohref\" />\n";
                }
                $py = $py2;
                $px += $div_h;
            }
        }
    }


    //
    //     Imprime um grafico de pizza
    //
    private function imprimir_grafico_pizza(&$img, $ix, $iy, $fx, $fy, $mapa) {
    // Resource $img: imagem usada para imprimir a legenda
    // Int $ix: coordenada x inicial (superior esquerdo)
    // Int $iy: coordenada y inicial
    // Int $fx: coordenada x final (inferior direito)
    // Int $fy: coordenada y final
    // Bool $mapa: indica se deve exibir o mapa da imagem (true) ou o grafico (false)
    //
        // Calcular a soma dos valores
        $soma = array_sum($this->valores);

        // Calcular raio X e Y da elipse
        $RX = (int)(($fx - $ix) / 2) - 10;
        $RY = (int)(($fy - $iy) / 2) - 10;

        // Calcular posicao do centro da elipse
        $CX = $ix + (int)(($fx - $ix) / 2);
        $CY = $iy + (int)(($fy - $iy) / 2);

        // Desenhar a Pizza!
        $inicio_angulo = 0;
        $max = count($this->valores);
        $textos = array();
        for ($i = 0; $i < $max; $i++) {
            $angulo = ceil($this->valores[$i] * 360 / $soma);
            $porcentagem = round($this->valores[$i] * 100 / $soma, 2).'%';
            $fim_angulo = $inicio_angulo + $angulo;

            // Imprimir Arco
            if (!$mapa) {
                if ($angulo) {
                    imagefilledarc($img, $CX, $CY, (2 * $RX), (2 * $RY), $inicio_angulo, $fim_angulo, $this->cores[$i], IMG_ARC_EDGED);
                } else {
                    imageline($img, $CX, $CY, $CX + $RX * cos(deg2rad($inicio_angulo)), $CY + $RY * sin(deg2rad($inicio_angulo)), $this->cores[$i]);
                }
            }

            // Calcular porcentagem e posicao onde ela fica no grafico
            $meio_angulo = (int)(($inicio_angulo + $fim_angulo) / PRECISAO);
            $x = $CX + ($RX * 0.55 * cos(deg2rad($meio_angulo)));
            $y = $CY + ($RY * 0.55 * sin(deg2rad($meio_angulo)));

            $largura = $this->largura_texto($porcentagem);
            $x -= $largura / 2;
            $y += ALTURA_TEXTO / 2;

            // Texto com sombra
            $obj = new stdClass();
            $obj->texto = $porcentagem;
            $obj->x = $x;
            $obj->y = $y;
            $obj->title = $this->legenda[$i].': '.$this->valores[$i].' ('.$porcentagem.')';
            $textos[] = $obj;

            // Reiniciar angulo
            $inicio_angulo = $fim_angulo;
        }

        // Imprimir porcentagens
        foreach ($textos as $obj) {
            if (!$mapa) {
                $this->texto_sombra($img, ALTURA_TEXTO, 0, $obj->x, $obj->y, $this->cores['fundo'], $this->cores['texto'], $obj->texto);
            } else {
                $ponto_x  = round($obj->x);
                $ponto_y  = round($obj->y);
                $ponto_x2 = round($obj->x + 50);
                $ponto_y2 = round($obj->y - 20);
                $title = $obj->title;
                echo "<area shape=\"rect\" coords=\"{$ponto_x},{$ponto_y},{$ponto_x2},{$ponto_y2}\" title=\"{$title}\" />\n";
            }
        }

        // Borda da Pizza
        if (!$mapa) {
            imagearc($img, $CX, $CY, (2 * $RX), (2 * $RY), 0, 360, $this->cores['borda']);
        }
    }


    //
    //     Imprime as linhas extras nos graficos de barra ou de linha
    //
    private function imprimir_linhas_extras(&$img, $maior, $ix, $iy, $fx, $fy) {
    // Resource $img: imagem usada para imprimir as linhas
    // Float $maior: maior valor do grafico
    // Int $ix: coordenada x inicial (superior esquerdo)
    // Int $iy: coordenada y inicial
    // Int $fx: coordenada x final (inferior direito)
    // Int $fy: coordenada y final
    //
        if (!$this->linhas) {
            return;
        }
        $max = $fy - $iy;
        if ($this->legenda) {
            $cor = count($this->legenda);
        } else {
            $cor = 1;
        }

        $tam = count($this->linhas);
        for ($i = 0; $i < $tam; $i += 2) {
            $p1  = $this->linhas[$i];
            $px1 = $ix;
            $py1 = $fy - ($p1 * $max / $maior);

            $p2  = $this->linhas[$i + 1];
            $px2 = $fx;
            $py2 = $fy - ($p2 * $max / $maior);

            imageline($img, $px1, $py1, $px2, $py2, $this->cores[$cor]);
            $cor++;
        }
    }


    //
    //     Imprime a moldura do grafico de linhas e de barras
    //
    private function imprimir_moldura(&$img, $maior, $ix, $iy, $fx, $fy, $mapa = false) {
    // Resource $img: imagem usada para imprimir a legenda
    // Float $maior: maior valor do grafico
    // Int $ix: coordenada x inicial (superior esquerdo)
    // Int $iy: coordenada y inicial
    // Int $fx: coordenada x final (inferior direito)
    // Int $fy: coordenada y final
    // Bool $mapa: indica se deve exibir o mapa da imagem (true) ou o grafico (false)
    //
        // Calcular tamanho do texto da maior escala horizontal
        $maior_altura_escala_h = 0;
        $vt_largura_escala_h = array();
        if ($this->escala) {
            foreach ($this->escala as $e) {
                $altura_e  = $this->altura_texto($e, ALTURA_TEXTO, FONTE, $this->angulo);
                $largura_e = $this->largura_texto($e, ALTURA_TEXTO, FONTE, $this->angulo);
                $vt_largura_escala_h[] = $largura_e;
                if ($altura_e > $maior_altura_escala_h) {
                    $maior_altura_escala_h = $altura_e;
                }
            }
            $maior_altura_escala_h  += MARGEM;
        }

        // Resolver problema com graficos com numeros pequenos
        // Obter largura do texto da maior escala vertical
        if ($maior < 10) {
            $maior_largura_escala_v = $this->largura_texto($maior.',0');
            $usar_virgula = true;
        } else {
            $maior_largura_escala_v = $this->largura_texto($maior);
            $usar_virgula   = false;
        }

        // Em casos de escalas horizontais com angulo:
        // a largura ocupada pelos textos dos itens (especialmente os da esquerda)
        // podem ser superiores ao da largura da maior escala vertical
        $div = 35;
        if ($this->angulo !== false && $this->angulo !== 90) {
            $sub = MARGEM + MARGEM;
            foreach ($vt_largura_escala_h as $largura_escala_h) {
                $diferenca = $largura_escala_h - ($sub + $div);
                if ($diferenca < 0) { break; }
                if ($maior_largura_escala_v < $diferenca) {
                    $maior_largura_escala_v = MARGEM + $diferenca + MARGEM;
                }
                $div += 35;
            }
        }

        $igx = $ix + $maior_largura_escala_v + MARGEM + MARGEM;
        $igy = $iy;
        $fgx = $fx;
        $fgy = $fy - $maior_altura_escala_h;

        $altura     = $fgy - $igy;
        $num_escala = $altura / (MARGEM + ALTURA_TEXTO + MARGEM);
        while ($altura % (MARGEM + ALTURA_TEXTO + MARGEM)) {
            $altura--;
        }
        $num_escala = round($altura / (MARGEM + ALTURA_TEXTO + MARGEM));
        $fgy = $igy + $altura;

        $div_escala = $maior / $num_escala;
        $div        = $altura / $num_escala;

        // Imprimir escala vertical
        $px = $ix + $maior_largura_escala_v - $this->largura_texto('0');

        // Imprimir o ponto zero
        if (!$mapa) {
            imagettftext($img, ALTURA_TEXTO, 0, $px, $fgy + (ALTURA_TEXTO / 2), $this->cores['texto'], FONTE, '0');
        }
        $py = $fgy;
        $escala = 0;
        if (!$mapa && $div_escala) {
            for ($i = $num_escala; $i > 0; $i--) {
                $escala += $div_escala;
                $texto_escala = ($usar_virgula) ? round($escala, 1) : round($escala);
                $py -= $div;
                $px = $ix + $maior_largura_escala_v - $this->largura_texto($texto_escala);
                imagettftext($img, ALTURA_TEXTO, 0, $px, $py + (ALTURA_TEXTO / 2), $this->cores['texto'], FONTE, $texto_escala);
                imageline($img, $igx - MARGEM, $py, $fgx, $py, $this->cores['pontilhado']);
            }
        }

        $valores = array_values($this->valores);
        if (is_array($valores[0])) {
            $count_valores = count($valores[0]);
        } else {
            $count_valores = count($valores);
        }

        // Imprimir escala horizontal
        $largura = $fgx - $igx;
        if ($count_valores > 0) {
            $div_h = $largura / $count_valores;
        } else {
            $div_h = 0;
        }
        $px = $igx + ($div_h / 2);
        $c = ALTURA_TEXTO / 2;
        if (!$mapa) {
            for ($i = 0; $i < $count_valores; $i++) {
                if ($this->escala) {
                    $e = $this->escala[$i];
                    $altura_escala  = $this->altura_texto($e, ALTURA_TEXTO, FONTE, $this->angulo);
                    $largura_escala = $this->largura_texto($e, ALTURA_TEXTO, FONTE, $this->angulo);
                    $pey = $fgy + $altura_escala  + MARGEM;
                    $pex = $px  - $largura_escala + MARGEM + (10 * $this->angulo / 90);
                    imagettftext($img, ALTURA_TEXTO, $this->angulo, $pex, $pey, $this->cores['texto'], FONTE, $e);
                    imageline($img, $px, $igy, $px, $fgy + MARGEM, $this->cores['pontilhado']);
                } else {
                    imageline($img, $px, $igy, $px, $fgy, $this->cores['pontilhado']);
                }
                $px += $div_h;
            }

            // Imprimir moldura
            imagerectangle($img, $igx, $igy, $fgx, $fgy, $this->cores['borda']);
        }

        $igx += ($div_h / 2);
        if ($count_valores == 1) {
            $fgx = $igx;
        } else {
            $fgx = $igx + ($div_h * ($count_valores - 1));
        }

        return array($div_h, $igx, $igy, $fgx, $fgy);
    }


    //
    //     Retorna a largura da imagem de um texto
    //
    public function largura_texto($texto, $altura = ALTURA_TEXTO, $fonte = FONTE, $angulo = 0) {
    // String $texto: texto a ser conferido
    // Int $altura: altura da fonte
    // String $fonte: fonte utilizada
    // Int $angulo: angulo do texto
    //
        $tamanho = $this->tamanho_texto($texto, $altura, $fonte, $angulo);
        return $tamanho[0];
    }


    //
    //     Retorna a altura da imagem de um texto
    //
    public function altura_texto($texto, $altura = ALTURA_TEXTO, $fonte = FONTE, $angulo = 0) {
    // String $texto: texto a ser conferido
    // Int $altura: altura da fonte
    // String $fonte: fonte utilizada
    // Int $angulo: angulo do texto
    //
        $tamanho = $this->tamanho_texto($texto, $altura, $fonte, $angulo);
        return $tamanho[1];
    }


    //
    //     Retorna a largura e altura da imagem de um texto (atraves de um array)
    //
    public function tamanho_texto($texto, $altura = ALTURA_TEXTO, $fonte = FONTE, $angulo = 0) {
    // String $texto: texto a ser conferido
    // Int $altura: altura da fonte
    // String $fonte: fonte utilizada
    // Int $angulo: angulo do texto
    //
        $aux = imagecreate(1000, 1000);
        $v = imagettftext($aux, $altura, 0, 10, 10, 0, $fonte, $texto);
        imagedestroy($aux);

        $altura  = $v[1] - $v[7];
        $largura = $v[4] - $v[0];

        if ($angulo) {
            $complemento = abs(180 - ($angulo + 90));
            while ($complemento >= 180) {
                $complemento -= 180;
            }

            $rad   = deg2rad($angulo);
            $rad_c = deg2rad($complemento);

            $sen_rad   = round(sin($rad), 2);
            $cos_rad   = round(cos($rad), 2);
            $sen_rad_c = round(sin($rad_c), 2);
            $cos_rad_c = round(cos($rad_c), 2);

            $altura_angulo  = $sen_rad * $largura + $sen_rad_c * $altura;
            $largura_angulo = $cos_rad * $largura + $cos_rad_c * $altura;
        }

        if ($angulo) {
            return array(abs($largura_angulo), abs($altura_angulo));
        }
        return array(abs($largura), abs($altura));
    }


    //
    //     Imprime um texto com sombra
    //
    private function texto_sombra(&$img, $altura, $angulo, $x, $y, $cor, $sombra, $texto) {
    // Resource $img: imagem usada para imprimir a legenda
    // Int $altura: altura do texto
    // Int $angulo: angulo do texto
    // Int $x: coordenada x
    // Int $y: coordenada y
    // Int $cor: cor do texto
    // Int $sombra: cor da sombra
    // String $texto: texto a ser impresso
    //
        $v1 = array($x - 1, $x, $x + 1);
        $v2 = array($y - 1, $y, $y + 1);
        foreach ($v1 as $xs) {
            foreach ($v2 as $ys) {
                imagettftext($img, $altura, $angulo, $xs, $ys, $sombra, FONTE, $texto);
            }
        }
        imagettftext($img, $altura, $angulo, $x, $y, $cor, FONTE, $texto);
    }


    //
    //     Imprime uma borda
    //
    private function borda(&$img, $ix, $iy, $fx, $fy, $cor = 'borda', $solida = false) {
    // Resource $img: imagem usada para imprimir a borda
    // Int $ix: coordenada x inicial (superior esquerdo)
    // Int $iy: coordenada y inicial
    // Int $fx: coordenada x final (inferior direito)
    // Int $fy: coordenada y final
    // String $cor: cor utilizada para fazer a borda
    // Bool $solida: forcar para que a borda seja solida
    //
        if ($solida || ($this->borda == BORDA_SOLIDA)) {
            imagerectangle($img, $ix, $iy, $fx, $fy, $this->cores[$cor]);
        } elseif ($this->borda == BORDA_3D) {

            // Horizontal Clara
            imageline($img, $ix + 1, $iy + 1, $fx - 1, $iy + 1, $this->cores['claro'.$cor]);

            // Vertical Clara
            imageline($img, $ix + 1, $iy + 1, $ix + 1, $fy, $this->cores['claro'.$cor]);

            // Vertical Escura
            imageline($img, $fx, $iy + 1, $fx, $fy, $this->cores['escuro'.$cor]);

            // Horizontal Escura
            imageline($img, $ix + 1, $fy, $fx, $fy, $this->cores['escuro'.$cor]);
        }
    }


    //
    //     Aloca as cores necessarias
    //
    private function alocar_cores(&$img) {
    // Resource $img: imagem usada para imprimir a legenda
    //
        $this->cores['fundo']         = imagecolorallocate($img, 255, 255, 240);
        $this->cores['fundo_grafico'] = imagecolorallocate($img, 255, 255, 255);
        $this->cores['texto']         = imagecolorallocate($img,   0,   0,   0);
        $this->cores['borda']         = imagecolorallocate($img,  20,  20,  20);
        $this->cores['linha']         = imagecolorallocate($img, 150, 150, 150);

        $pontilhado = array($this->cores['linha'], $this->cores['linha'], $this->cores['fundo_grafico'], $this->cores['fundo_grafico']);
        imagesetstyle($img, $pontilhado);
        $this->cores['pontilhado']    = IMG_COLOR_STYLED;

        // Criar as cores extras
        $cores = $this->cores();
        foreach ($cores as $i => $c) {
            switch ($this->tipo_cor) {
            case COR_NORMAL:
                list($r, $g, $b) = $c;
                break;
            case COR_CLARA:
                $r = min($c[0] * CLAREAR, 255);
                $g = min($c[1] * CLAREAR, 255);
                $b = min($c[2] * CLAREAR, 255);
                break;
            case COR_ESCURA:
                $r = max($c[0] * ESCURECER, 0);
                $g = max($c[1] * ESCURECER, 0);
                $b = max($c[2] * ESCURECER, 0);
                break;
            }

            // Cores clareadas
            $rc = min($r * CLAREAR, 255);
            $gc = min($g * CLAREAR, 255);
            $bc = min($b * CLAREAR, 255);

            // Cores escurecidas
            $re = max($r * ESCURECER, 0);
            $ge = max($g * ESCURECER, 0);
            $be = max($b * ESCURECER, 0);

            $this->cores[$i]          = imagecolorallocate($img, $r, $g, $b);
            $this->cores['claro'.$i]  = imagecolorallocate($img, $rc, $gc, $bc);
            $this->cores['escuro'.$i] = imagecolorallocate($img, $re, $ge, $be);
        }
    }


    //
    //     Retorna um vetor de cores
    //
    private function cores() {
        $cores = array();
        $cores[] = array(200, 100, 100); // Vermelho
        $cores[] = array(100, 100, 200); // Azul
        $cores[] = array(200, 200,  50); // Amarelo
        $cores[] = array( 50, 200, 200); // Ciano
        $cores[] = array(180, 180, 180); // Cinza
        $cores[] = array(100, 200, 100); // Verde
        $cores[] = array(200, 100, 200); // Pink
        $cores[] = array(200, 100,   0); // Laranja
        $cores[] = array(170, 170,  70); // Marrom
        $cores[] = array(230, 230, 230); // Cinza Claro
        $cores[] = array( 51,   0, 102); // Roxo
        $cores[] = array(187,   0,   0); // Vermelho escuro
        $cores[] = array(  0, 187,   0); // Verde escuro
        $cores[] = array(  0,   0,  85); // Azul escuro
        $cores[] = array(100, 255, 204); // Aqua
        $cores[] = array(136,   0,   0); // Marrom escuro
        $cores[] = array(204,  51, 153); // Rosa
        $cores[] = array(255, 102,   0); // Laranja claro
        $cores[] = array(  0, 153, 153); // Verde Azulado
        $cores[] = array( 60,  60,  60); // Cinza Escuro

        // Checar se precisa de mais cores
        $count_cores = count($cores);
        if ($this->legenda) {
            $necessario = count($this->legenda);
        } elseif (is_array($this->valores)) {
            $necessario = count($this->valores);
        } else {
            $necessario = 0;
        }
        if (is_array($this->legenda_linhas)) {
            $necessario += count($this->legenda_linhas);
        }

        // Criar cores aleatorias
        while ($count_cores < $necessario) {
            $r = mt_rand(1, 255);
            $g = mt_rand(1, 255);
            $b = mt_rand(1, 255);
            $cores[]  = array($r, $g, $b);
            $count_cores++;
        }
        return $cores;
    }


    //
    //     Imprime um grafico com HTML
    //
    public function imprimir_grafico_html() {
        setlocale(LC_ALL, 'C');

        $vt_cores = $this->cores();
        $vt_cores['fundo']         = array(255, 255, 240);
        $vt_cores['fundo_grafico'] = array(255, 255, 255);
        $vt_cores['texto']         = array(  0,   0,   0);
        $vt_cores['borda']         = array( 20,  20,  20);
        $vt_cores['linha']         = array(150, 150, 150);

        foreach ($vt_cores as $i => $c) {
            $this->cores[$i] = sprintf('#%02X%02X%02X', $c[0], $c[1], $c[2]);
        }

        $largura = $this->largura;

        if ($this->legenda) {
            switch ($this->pos_legenda) {
            case DIREITA:
                $antes = true;
                $lado  = 'position: relative; float: left;';
                $ladol = 'position: relative; float: right;';
                $largura += 200 + MARGEM;
                break;
            case ESQUERDA:
                $antes = true;
                $lado  = 'position: relative; float: right;';
                $ladol = 'position: relative; float: left;';
                $largura += 200 + MARGEM;
                break;
            case CIMA:
                $antes = false;
                $lado = 'margin-bottom: '.MARGEM.'px;';
                $ladol = '';
                break;
            case BAIXO:
                $antes = true;
                $lado = 'margin-top: '.MARGEM.'px;';
                $ladol = '';
                break;
            }
        }

        echo "<div style=\"border: 1px solid ".$this->cores['borda']."; ".
             "background-color: ".$this->cores['fundo']."; padding: ".MARGEM."px; width: {$largura}px;\">\n";
        echo "<strong style=\"color: ".$this->cores['borda']."; clear: both;\">{$this->titulo}</strong>\n";
        if ($this->legenda) {
            if ($antes) {
                $this->imprimir_legenda_html($ladol);
                $this->imprimir_grafico_barra_html($lado);
            } else {
                $this->imprimir_grafico_barra_html($lado);
                $this->imprimir_legenda_html($ladol);
            }
        } else {
            $this->imprimir_grafico_barra_html();
        }
        echo "<br style=\"font-size: 0px; clear: both;\" />\n";
        echo "</div>\n";

        setlocale(LC_ALL, GRAFICO_LOCALIDADE);
    }


    //
    //     Imprime uma legenda em HTML
    //
    private function imprimir_legenda_html($lado = '') {
    // String $lado: estilo indicando o lado
    //
        $largura = 200 - (2 * MARGEM);

        echo "<div id=\"legenda\" style=\"border: 1px solid ".$this->cores['borda']."; ".
             "background-color: ".$this->cores['fundo_grafico']."; width: {$largura}px; ".
             "padding: ".MARGEM."px; {$lado}\">\n";
        echo "<strong style=\"display: block;\">Legenda:</strong>\n";
        echo "<ul style=\"margin: 0px; padding: 0px; list-style-type: none; list-style-image: none;\">\n";
        foreach ($this->legenda as $i => $l) {
            if ($this->borda == BORDA_SOLIDA) {
                $borda = "border: 1px solid ".$this->cores['borda']."; ";
            } else {
                $borda = "border: 1px outset ".$this->cores[$i]."; ";
            }

            $q = "<span style=\"display: block; width: ".QUADRADO."px; height: ".QUADRADO."px; ".
                 $borda."float: left; margin: 5px 3px; ".
                 "background-color: ".$this->cores[$i]."; line-height: 1px;\">&nbsp;</span>\n";

            echo "  <li style=\"clear: both;\">{$q} <span>{$l}</span></li>\n";
        }
        echo "</ul>\n";
        echo "</div>\n";
    }


    //
    //     Imprime um grafico de barra em HTML
    //
    private function imprimir_grafico_barra_html($lado = '') {
    // String $lado: codigo CSS que define o lado do grafico
    //
        $largura = $this->largura - (2 * MARGEM);
        echo "<div id=\"grafico\" style=\"border: 1px solid ".$this->cores['borda'].";".
             "background-color: ".$this->cores['fundo_grafico']."; ".
             "width: {$largura}px; min-height: {$this->altura}px; ".
             "padding: ".MARGEM."px; {$lado}\">\n";

        echo "<div>\n";

        // Barras multiplas (varias cores)
        $valores = array_values($this->valores);
        if (is_array($valores[0])) {
            $maior = 0;
            foreach ($valores as $v) {
                $v = array_values($v);
                $maior_v = max($v);
                $maior = ($maior > $maior_v) ? $maior : $maior_v;
            }

            $w = $this->largura - (80 + (3 * MARGEM) + 10);
            $h = (($this->altura - (2 * MARGEM)) / count($this->escala) / count($this->legenda)) - 5;
            foreach ($this->escala as $i => $escala) {
                echo '<div style="display: table-row;">';
                echo '<p style="display: table-cell; vertical-align: middle; text-align: right;">';
                echo '<strong>'.$escala.':</strong>';
                echo '</p>';
                echo '<div style="display: table-cell;">';
                foreach ($valores as $j => $v) {
                    $valor = $v[$i];
                    $porcentagem = round($valor * 100 / max($maior, 1), 0);
                    $texto = '<div style="display: none;">'.$this->legenda[$j].': '.$valor.'</div>';
                    $p = "<div style=\"width: {$porcentagem}%; border: 1px outset ".$this->cores[$j]."; ".
                         "background-color: ".$this->cores[$j]."; ".
                         "height: {$h}px; font-size: {$h}px;\" >{$texto}</div>";

                    echo "<div style=\"border: 1px inset #F5F5F5; margin: 1px; ".
                         "background-color: #F5F5F5; width: {$w}px; height: {$h}px; line-height: 1px;".
                         "font-size: {$h}px; margin-top: 5px;\">{$p}</div>\n";
                }
                echo '</div>';
                echo '</div>';
            }


        // Uma barra (uma cor)
        } else {
            $maior = max($valores);
            $w_escala = 80;
            $w_valor  = 70;
            $w = $this->largura - ($w_escala + 3 * MARGEM + $w_valor);
            $h = QUADRADO;
            $h2 = $h - 2;
            foreach ($valores as $i => $valor) {
                $porcentagem = round($valor * 100 / ($maior ? $maior : 1), 0);

                echo "<div style=\"clear: both;\">\n";

                if ($this->escala) {
                    $e = $this->escala[$i];
                    echo "<div style=\"text-align: right; padding-right: 5px; width: {$w_escala}px; float: left;\" >{$e}:&nbsp;</div>";
                }

                $p = "<div style=\"width: {$porcentagem}%; border: 1px outset ".$this->cores[0]."; ".
                     "background-color: ".$this->cores[0]."; ".
                     "height: {$h2}px; font-size: {$h2}px;\" ></div>";

                echo "<div style=\"position: relative; top: 5px; float: left; border: 1px inset #F5F5F5; margin: 1px; ".
                     "background-color: #F5F5F5; width: {$w}px; height: {$h}px; line-height: 1px;".
                     "font-size: {$h}px;\">{$p}</div>\n";

                echo "<div style=\"text-align: right; width: {$w_valor}px; float: left;\" >{$valor} ({$porcentagem}%)</div>";

                echo "</div>\n";
            }
        }

        echo "<div style=\"clear: both; height: 1px;\"></div>\n";
        echo "</div>\n";

        echo "</div>\n";
    }


    //
    //     Imprime a descricao do grafico em HTML
    //
    private function descrever_grafico() {
        $link = $_SERVER['REQUEST_URI'];
        $pos = strpos($link, '?');
        if ($pos !== false) {
            $link = substr($link, 0, $pos);
        }

        echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01//EN\" \"http://www.w3.org/TR/html4/strict.dtd\">\n";
        echo '<html>';
        echo '<head>';
        echo '  <title>'.texto::codificar($this->titulo).'</title>';
        echo '  <link rev="help" href="'.$link.'" />';
        echo '</head>';
        echo '<body>';
        echo '<h1>'.texto::codificar($this->titulo).'</h1>';

        // Bidimensional
        $valores = array_values($this->valores);
        if (is_array($valores[0])) {
            $maior = 0;
            $subtotal = array();
            foreach ($valores as $i => $v) {
                $v = array_values($v);
                foreach ($v as $j => $v2) {
                    $subtotal[$j] += $v2;
                }
                $maior_v = max($v);
                $maior = ($maior > $maior_v) ? $maior : $maior_v;
            }
            foreach ($this->escala as $i => $escala) {
                echo '<p>';
                echo '<strong>'.texto::codificar($escala).'</strong>';
                echo '<ul>';
                foreach ($valores as $j => $v) {
                    $valor = $v[$i];
                    $porcentagem = round($valor * 100 / $subtotal[$i], 0);
                    echo '<li><em>'.texto::codificar($this->legenda[$j]).':</em> '.texto::numero($valor).' ('.$porcentagem.'%)</li>';
                }
                echo '</ul>';
                echo '</p>';
            }

        // Unidimensional
        } else {
            $total = array_sum($valores);
            foreach ($valores as $i => $valor) {
                $porcentagem = round($valor * 100 / $total, 0);

                echo '<p>';
                if ($this->escala) {
                    echo '<strong>'.texto::codificar($this->escala[$i]).':</strong> ';
                }
                echo texto::numero($valor).' ('.$porcentagem.'%)';
                echo '</p>';
            }
            echo '<p><strong>Total:</strong> '.texto::numero($total).'</p>';
        }

        if ($this->legenda_linhas) {
            echo '<hr />';
            $count_linhas = count($this->linhas);
            $j = 0;
            for ($i = 0; $i < $count_linhas; $i += 2) {
                $legenda = $this->legenda_linhas[$j++];
                $valor = $this->linhas[$i];
                if ($this->linhas[$i] != $this->linhas[$i + 1]) {
                    $valor .= '..'.$this->linhas[$i + 1];
                }
                echo '<p><strong>'.texto::codificar($legenda).':</strong> '.texto::codificar($valor).'</p>';
            }
        }

        echo '<body>';
        echo '</html>';
    }


    //
    //     Exibe o mapa da imagem
    //
    private function imprimir_mapa($id) {
    // String $id: identificador do mapa
    //

        // Calcular Tamanhos
        $this->tamanhos($vt_total, $vt_grafico, $vt_legenda);

        // Largura e altura total
        list($largura, $altura) = $vt_total;

        // Limites da area do grafico
        list($inicio_x, $inicio_y, $fim_x, $fim_y) = $vt_grafico;

        // Imprimir o Mapa
        $img = null;
        $img = imagecreatetruecolor($largura, $altura);

        echo "<map name=\"{$id}\" id=\"{$id}\">\n";
        $this->imprimir_grafico($img, $inicio_x, $inicio_y, $fim_x, $fim_y, true);
        echo "</map>\n";

        imagedestroy($img);
    }

}//class

