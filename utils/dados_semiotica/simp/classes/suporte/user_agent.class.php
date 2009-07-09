<?php
//
// SIMP
// Descricao: Classe que identifica o Navegador e o SO
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.ufla.br
// Versao: 1.0.0.6
// Data: 04/06/2007
// Modificado: 24/06/2009
// Copyright (C) 2007  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
final class user_agent {
    private $user_agent       = '';  // String user agent recebido
    private $navegador        = '';  // Stirng Nome do Navegador
    private $versao_navegador = '';  // String Versao do Navegador
    private $so               = '';  // String Nome do SO
    private $versao_so        = '';  // String Versao do SO
    private $texto            = 0;   // Int Navegador modo texto
    private $movel            = 0;   // Int Navegador de dispositivo movel
    private $mozfamily        = 0;   // Int Navevador da familia do mozilla
    private $ie               = 0;   // Int Versao do IE ou false
    private $css              = 0;   // Int Suporte a CSS
    private $javascript       = 0;   // Int Suporte a JavaScript


    //
    //     Construtor
    //
    public function __construct($user_agent) {
    // String $user_agent: user agent informado por $_SERVER['HTTP_USER_AGENT']
    //
        $this->user_agent = $user_agent;
        $this->consultar();
    }


    //
    //     Retorna um dos atributos do objeto
    //
    public function __get($chave) {
    // String $chave: chave desejada
    //
        if (isset($this->$chave)) {
            return $this->$chave;
        }
    }


    //
    //     Retorna um objeto com os dados consultados
    //
    static public function get_dados($user_agent) {
    // String $user_agent: user agent obtido pela requisicao HTTP ao servidor
    //
        $classe = __CLASS__;
        $ua = new $classe($user_agent);

        $obj = new stdClass();
        foreach (get_class_vars($classe) as $atributo => $valor) {
            $obj->$atributo = $ua->__get($atributo);
        }
        return $obj;
    }


    //
    //     Consulta os dados do User Agent
    //
    public function consultar() {
        $this->consultar_navegador();
        $this->consultar_so();

        switch (strtolower($this->navegador)) {
        case 'mozilla':
        case 'firefox':
        case 'seamonkey':
        case 'iceweasel':
        case 'netscape':
            $this->mozfamily = 1;
            $this->texto = 0;
            break;
        case 'internet explorer':
            $this->ie = $this->versao_navegador;
            $this->texto = 0;
            break;
        case 'links':
        case 'elinks':
        case 'lynx':
        case 'w3m':
            $this->texto = 1;
            break;
        }

        // Tenta obter dados do browser (suporte a CSS, JavaScript e se e' de dispositivo movel)
        if (function_exists('get_browser') && ini_get('browscap')) {
            $obj = get_browser($this->user_agent);

            // Se e' um buscador
            if (isset($obj->crawler) && $obj->crawler) {
                $agent->css        = '0';
                $agent->javascript = '0';
                $agent->movel      = '0';

            // Se e' um navegador
            } else {

                if (isset($obj->suportscss)) {
                    $agent->css = $obj->suportscss ? '1' : '0';
                } else {
                    $agent->css = '1';
                }
                if (isset($obj->javascript)) {
                    $agent->javascript = $obj->javascript ? '1' : '0';
                } else {
                    $agent->javascript = '1';
                }
                if (isset($obj->ismobiledevice)) {
                    $agent->movel = $obj->ismobiledevice ? '1' : '0';
                } else {
                    $agent->movel = '0';
                }
            }

        // Assumir que da suporte a CSS e JavaScript
        } else {
            $this->css        = '1';
            $this->javascript = '1';
            $this->movel      = '0';
        }
    }


    //
    //     Consulta os dados do Navegador
    //
    private function consultar_navegador() {

        // IE
        if (preg_match('/msie/i', $this->user_agent)) {
            $this->navegador = 'Internet Explorer';
            $this->versao_navegador = $this->entre('MSIE', ';');

        // Derivados Netscape
        } elseif (preg_match('/mozilla\/5.0/i', $this->user_agent) &&
                  preg_match('/rv:/i', $this->user_agent) &&
                  preg_match('/gecko\//i', $this->user_agent)) {

            // Netscape
            if (preg_match('/navigator/i', $this->user_agent)) {
                $this->navegador = 'Netscape';
                $this->versao_navegador = $this->entre('Navigator/');

            // Iceweasel
            } elseif (preg_match('/iceweasel/i', $this->user_agent)) {
                $this->navegador = 'Iceweasel';
                $this->versal_navegador = $this->entre('Iceweasel/', ' ');

            // SeaMonkey
            } elseif (preg_match('/seamonkey/i', $this->user_agent)) {
                $this->navegador = 'SeaMonkey';
                $this->versao_navegador = $this->entre('SeaMonkey/', ' ');

            // Firefox
            } elseif (preg_match('/firefox/i', $this->user_agent)) {
                $this->navegador = 'Firefox';
                $this->versao_navegador = $this->entre('Firefox/', ' ');

            // Mozilla
            } elseif (preg_match('/ rv:/i', $this->user_agent)) {
                $this->navegador = 'Mozilla';
                $this->versao_navegador = $this->entre(' rv:', ')');
            }
            return;
        }

        // Netscape
        if (preg_match('/netscape/i', $this->user_agent)) {
            $this->navegador = 'Netscape';
            $this->versao_navegador = $this->entre('Netscape', ' ');

        // Opera
        } elseif (preg_match('/opera/i', $this->user_agent)) {
            $this->navegador = 'Opera';
            $this->versao_navegador = $this->entre('Opera/', ' ');

        // Chrome
        } elseif (preg_match('/chrome/i', $this->user_agent)) {
            $this->navegador = 'Chrome';
            $this->versao_navegador = $this->entre('Chrome/', ' ');

        // Safari
        } elseif (preg_match('/safari/i', $this->user_agent)) {
            $this->navegador = 'Safari';

        // Galeon
        } elseif (preg_match('/galeon/i', $this->user_agent)) {
            $this->navegador = 'Galeon';

        // Konqueror
        } elseif (preg_match('/konqueror/i', $this->user_agent)) {
            $this->navegador = 'Konqueror';
            $this->versao_navegador = $this->entre('Konqueror/', ';');

        // Links
        } elseif (preg_match('/links/i', $this->user_agent)) {
            $this->navegador = 'Links';

        // Lynx
        } elseif (preg_match('/lynx/i', $this->user_agent)) {
            $this->navegador = 'Lynx';
            $this->versao_navegador = $this->entre('Lynx/', ' ');

        // W3M
        } elseif (preg_match('/w3m/i', $this->user_agent)) {
            $this->navegador = 'W3M';
            $this->versao_navegador = $this->entre('w3m/');

        // Navegadores Diversos
        } elseif (preg_match('/amaya/i', $this->user_agent)) {
            $this->navegador = 'amaya';

        } elseif (preg_match('/aol/i', $this->user_agent)) {
            $this->navegador = 'AOL';

        } elseif (preg_match('/aweb/i', $this->user_agent)) {
            $this->navegador = 'aweb';

        } elseif (preg_match('/beonex/i', $this->user_agent)) {
            $this->navegador = 'Beonex';

        } elseif (preg_match('/camino/i', $this->user_agent)) {
            $this->navegador = 'Camino';

        } elseif (preg_match('/cyberdog/i', $this->user_agent)) {
            $this->navegador = 'Cyberdog';

        } elseif (preg_match('/dillo/i', $this->user_agent)) {
            $this->navegador = 'Dillo';

        } elseif (preg_match('/doris/i', $this->user_agent)) {
            $this->navegador = 'Doris';

        } elseif (preg_match('/emacs/i', $this->user_agent)) {
            $this->navegador = 'Emacs';

        } elseif (preg_match('/firebird/i', $this->user_agent)) {
            $this->navegador = 'Firebird';

        } elseif (preg_match('/frontpage/i', $this->user_agent)) {
            $this->navegador = 'FrontPage';

        } elseif (preg_match('/chimera/i', $this->user_agent)) {
            $this->navegador = 'Chimera';

        } elseif (preg_match('/icab/i', $this->user_agent)) {
            $this->navegador = 'iCab';

        } elseif (preg_match('/liberate/i', $this->user_agent)) {
            $this->navegador = 'Liberate';

        } elseif (preg_match('/netcaptor/i', $this->user_agent)) {
            $this->navegador = 'Netcaptor';

        } elseif (preg_match('/netpliance/i', $this->user_agent)) {
            $this->navegador = 'Netpliance';

        } elseif (preg_match('/offbyone/i', $this->user_agent)) {
            $this->navegador = 'OffByOne';

        } elseif (preg_match('/omniweb/i', $this->user_agent)) {
            $this->navegador = 'OmniWeb';

        } elseif (preg_match('/oracle/i', $this->user_agent)) {
            $this->navegador = 'Oracle';

        } elseif (preg_match('/phoenix/i', $this->user_agent)) {
            $this->navegador = 'Phoenix';

        } elseif (preg_match('/planetweb/i', $this->user_agent)) {
            $this->navegador = 'PlanetWeb';

        } elseif (preg_match('/powertv/i', $this->user_agent)) {
            $this->navegador = 'PowerTV';

        } elseif (preg_match('/prodigy/i', $this->user_agent)) {
            $this->navegador = 'Prodigy';

        } elseif (preg_match('/voyager/i', $this->user_agent)) {
            $this->navegador = 'Voyager';

        } elseif (preg_match('/quicktime/i', $this->user_agent)) {
            $this->navegador = 'QuickTime';

        } elseif (preg_match('/sextatnt/i', $this->user_agent)) {
            $this->navegador = 'Tango';

        } elseif (preg_match('/elinks/i', $this->user_agent)) {
            $this->navegador = 'ELinks';

        } elseif (preg_match('/webexplorer/i', $this->user_agent)) {
            $this->navegador = 'WebExplorer';

        } elseif (preg_match('/webtv/i', $this->user_agent)) {
            $this->navegador = 'webtv';

        } elseif (preg_match('/yandex/i', $this->user_agent)) {
            $this->navegador = 'Yandex';

        } elseif (preg_match('/mspie/i', $this->user_agent)) {
            $this->navegador = 'Pocket Internet Explorer';
        }
    }


    //
    //     Consulta os dados do SO
    //
    private function consultar_so() {

        // Windows
        if (preg_match('/win/i', $this->user_agent)) {
            $this->so = 'Windows';
            $versoes = array(
                             'Windows CE'     => 'CE',
                             'Win3.11'        => '3.11',
                             'Win3.1'         => '3.1',
                             'Windows 95'     => '95',
                             'Win95'          => '95',
                             'Windows ME'     => 'ME',
                             'Win 9x 4.90'    => 'ME',
                             'Windows 98'     => '98',
                             'Win98'          => '98',
                             'Windows NT 5.0' => '2000',
                             'WinNT5.0'       => '2000',
                             'Windows 2000'   => '2000',
                             'Win2000'        => '2000',
                             'Windows NT 5.1' => 'XP',
                             'WinNT5.1'       => 'XP',
                             'Windows XP'     => 'XP',
                             'Windows NT 5.2' => '.NET 2003',
                             'WinNT5.2'       => '.NET 2003',
                             'Windows NT 6.0' => 'Vista'
                            );
            $this->versao_so = $this->versao($versoes);

        // Linux
        } elseif (preg_match('/linux/i', $this->user_agent)) {
            $this->so = 'Linux';

            $versoes = array('i686' => 'i686',
                             'i586' => 'i586',
                             'i486' => 'i486',
                             'i386' => 'i386'
                             );
            $this->versao_so = $this->versao($versoes);

        // FreeBSD
        } elseif (preg_match('/freebsd/i', $this->user_agent)) {
            $this->so = 'FreeBSD';

            $versoes = array('i686' => 'i686',
                             'i586' => 'i586',
                             'i486' => 'i486',
                             'i386' => 'i386'
                             );
            $this->versao_so = $this->versao($versoes);

        // NetBSD
        } elseif (preg_match('/netbsd/i', $this->user_agent)) {
            $this->so = 'NetBSD';

            $versoes = array('i686' => 'i686',
                             'i586' => 'i586',
                             'i486' => 'i486',
                             'i386' => 'i386'
                             );
            $this->versao_so = $this->versao($versoes);

        // MAC
        } elseif (preg_match('/mac/i', $this->user_agent)) {
            $this->so = 'MacIntoch';

        // Outros SOs
        } elseif (preg_match('/sunos/i', $this->user_agent)) {
            $this->so = 'SunOS';
        } elseif (preg_match('/hp-ux/i', $this->user_agent)) {
            $this->so = 'HP-UX';
        } elseif (preg_match('/irix/i', $this->user_agent)) {
            $this->so = 'Irix';
        } elseif (preg_match('/os\/2/i', $this->user_agent)) {
            $this->so = 'OS/2';
        } elseif (preg_match('/amiga/i', $this->user_agent)) {
            $this->so = 'Amiga';
        } elseif (preg_match('/qnx/i', $this->user_agent)) {
            $this->so = 'QNX';
        } elseif (preg_match('/dreamcast/i', $this->user_agent)) {
            $this->so = 'Sega Dreamcast';
        } elseif (preg_match('/palm/i', $this->user_agent)) {
            $this->so = 'Palm';
        } elseif (preg_match('/powertv/i', $this->user_agent)) {
            $this->so = 'PowerTV';
        } elseif (preg_match('/prodigy/i', $this->user_agent)) {
            $this->so = 'Prodigy';
        } elseif (preg_match('/symbian/i', $this->user_agent)) {
            $this->so = 'Symbian';
        } elseif (preg_match('/unix/i', $this->user_agent)) {
            $this->so = 'Unix';
        } elseif (preg_match('/webtv/i', $this->user_agent)) {
            $this->so = 'WebTV';
        }
    }


    //
    //     Retorna a versao de acordo com o vetor passado
    //
    private function versao($vetor) {
    // Array[String => String] $vetor: vetor associativo com chave e versao
    //
        foreach ($vetor as $chave => $versao) {
            if (preg_match('/'.$chave.'/i', $this->user_agent)) {
                return $versao;
            }
        }
    }


    //
    //     Retorna o valor entre as substrings informadas
    //
    private function entre($a, $b = false) {
    // String $a: inicio
    // String $b: fim
    //
        $vt = explode($a, $this->user_agent);
        if ($b) {
            if (count($vt) < 2) {
                return '';
            }
            if ($pos = strpos($vt[1], $b)) {
                return trim(substr($vt[1], 0, $pos));
            }
        }
        return trim($vt[1]);
    }

}//class
