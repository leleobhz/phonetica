<?php
//
// SIMP
// Descricao: Classe que identifica o Navegador e o SO
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.ufla.br
// Versao: 1.0.0.4
// Data: 04/06/2007
// Modificado: 19/02/2009
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

            $agent->css        = isset($obj->supportscss)    ? (bool)$obj->supportscss    : 1;
            $agent->javascript = isset($obj->javascript)     ? (bool)$obj->javascript     : 1;
            $agent->movel      = isset($obj->ismobiledevice) ? (bool)$obj->ismobiledevice : 0;

        // Assumir que da suporte a CSS e JavaScript
        } else {
            $this->css        = 1;
            $this->javascript = 1;
            $this->movel      = 0;
        }
    }


    //
    //     Consulta os dados do Navegador
    //
    private function consultar_navegador() {

        // IE
        if (eregi('msie', $this->user_agent)) {
            $this->navegador = 'Internet Explorer';
            $this->versao_navegador = $this->entre('MSIE', ';');

        // Derivados Netscape
        } elseif (eregi('mozilla/5.0', $this->user_agent) &&
                  eregi('rv:', $this->user_agent) &&
                  eregi('gecko/', $this->user_agent)) {

            // Netscape
            if (eregi('navigator', $this->user_agent)) {
                $this->navegador = 'Netscape';
                $this->versao_navegador = $this->entre('Navigator/');

            // Iceweasel
            } elseif (eregi('iceweasel', $this->user_agent)) {
                $this->navegador = 'Iceweasel';
                $this->versal_navegador = $this->entre('Iceweasel/', ' ');

            // SeaMonkey
            } elseif (eregi('seamonkey', $this->user_agent)) {
                $this->navegador = 'SeaMonkey';
                $this->versao_navegador = $this->entre('SeaMonkey/', ' ');

            // Firefox
            } elseif (eregi('firefox', $this->user_agent)) {
                $this->navegador = 'Firefox';
                $this->versao_navegador = $this->entre('Firefox/', ' ');

            // Mozilla
            } elseif (eregi(' rv:', $this->user_agent)) {
                $this->navegador = 'Mozilla';
                $this->versao_navegador = $this->entre(' rv:', ')');
            }
            return;
        }

        // Netscape
        if (eregi('netscape', $this->user_agent)) {
            $this->navegador = 'Netscape';
            $this->versao_navegador = $this->entre('Netscape', ' ');

        // Opera
        } elseif (eregi('opera', $this->user_agent)) {
            $this->navegador = 'Opera';
            $this->versao_navegador = $this->entre('Opera/', ' ');

        // Chrome
        } elseif (eregi('chrome', $this->user_agent)) {
            $this->navegador = 'Chrome';
            $this->versao_navegador = $this->entre('Chrome/', ' ');

        // Safari
        } elseif (eregi('safari', $this->user_agent)) {
            $this->navegador = 'Safari';

        // Galeon
        } elseif (eregi('galeon', $this->user_agent)) {
            $this->navegador = 'Galeon';

        // Konqueror
        } elseif (eregi('konqueror', $this->user_agent)) {
            $this->navegador = 'Konqueror';
            $this->versao_navegador = $this->entre('Konqueror/', ';');

        // Links
        } elseif (eregi('links', $this->user_agent)) {
            $this->navegador = 'Links';

        // Lynx
        } elseif (eregi('lynx', $this->user_agent)) {
            $this->navegador = 'Lynx';
            $this->versao_navegador = $this->entre('Lynx/', ' ');

        // W3M
        } elseif (eregi('w3m', $this->user_agent)) {
            $this->navegador = 'W3M';
            $this->versao_navegador = $this->entre('w3m/');

        // Navegadores Diversos
        } elseif (eregi('amaya', $this->user_agent)) {
            $this->navegador = 'amaya';

        } elseif (eregi('aol', $this->user_agent)) {
            $this->navegador = 'AOL';

        } elseif (eregi('aweb', $this->user_agent)) {
            $this->navegador = 'aweb';

        } elseif (eregi('beonex', $this->user_agent)) {
            $this->navegador = 'Beonex';

        } elseif (eregi('camino', $this->user_agent)) {
            $this->navegador = 'Camino';

        } elseif (eregi('cyberdog', $this->user_agent)) {
            $this->navegador = 'Cyberdog';

        } elseif (eregi('dillo', $this->user_agent)) {
            $this->navegador = 'Dillo';

        } elseif (eregi('doris', $this->user_agent)) {
            $this->navegador = 'Doris';

        } elseif (eregi('emacs', $this->user_agent)) {
            $this->navegador = 'Emacs';

        } elseif (eregi('firebird', $this->user_agent)) {
            $this->navegador = 'Firebird';

        } elseif (eregi('frontpage', $this->user_agent)) {
            $this->navegador = 'FrontPage';

        } elseif (eregi('chimera', $this->user_agent)) {
            $this->navegador = 'Chimera';

        } elseif (eregi('icab', $this->user_agent)) {
            $this->navegador = 'iCab';

        } elseif (eregi('liberate', $this->user_agent)) {
            $this->navegador = 'Liberate';

        } elseif (eregi('netcaptor', $this->user_agent)) {
            $this->navegador = 'Netcaptor';

        } elseif (eregi('netpliance', $this->user_agent)) {
            $this->navegador = 'Netpliance';

        } elseif (eregi('offbyone', $this->user_agent)) {
            $this->navegador = 'OffByOne';

        } elseif (eregi('omniweb', $this->user_agent)) {
            $this->navegador = 'OmniWeb';

        } elseif (eregi('oracle', $this->user_agent)) {
            $this->navegador = 'Oracle';

        } elseif (eregi('phoenix', $this->user_agent)) {
            $this->navegador = 'Phoenix';

        } elseif (eregi('planetweb', $this->user_agent)) {
            $this->navegador = 'PlanetWeb';

        } elseif (eregi('powertv', $this->user_agent)) {
            $this->navegador = 'PowerTV';

        } elseif (eregi('prodigy', $this->user_agent)) {
            $this->navegador = 'Prodigy';

        } elseif (eregi('voyager', $this->user_agent)) {
            $this->navegador = 'Voyager';

        } elseif (eregi('quicktime', $this->user_agent)) {
            $this->navegador = 'QuickTime';

        } elseif (eregi('sextatnt', $this->user_agent)) {
            $this->navegador = 'Tango';

        } elseif (eregi('elinks', $this->user_agent)) {
            $this->navegador = 'ELinks';

        } elseif (eregi('webexplorer', $this->user_agent)) {
            $this->navegador = 'WebExplorer';

        } elseif (eregi('webtv', $this->user_agent)) {
            $this->navegador = 'webtv';

        } elseif (eregi('yandex', $this->user_agent)) {
            $this->navegador = 'Yandex';

        } elseif (eregi('mspie', $this->user_agent)) {
            $this->navegador = 'Pocket Internet Explorer';
        }
    }


    //
    //     Consulta os dados do SO
    //
    private function consultar_so() {

        // Windows
        if (eregi('win', $this->user_agent)) {
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
        } elseif (eregi('linux', $this->user_agent)) {
            $this->so = 'Linux';

            $versoes = array('i686' => 'i686',
                             'i586' => 'i586',
                             'i486' => 'i486',
                             'i386' => 'i386'
                             );
            $this->versao_so = $this->versao($versoes);

        // FreeBSD
        } elseif (eregi('freebsd', $this->user_agent)) {
            $this->so = 'FreeBSD';

            $versoes = array('i686' => 'i686',
                             'i586' => 'i586',
                             'i486' => 'i486',
                             'i386' => 'i386'
                             );
            $this->versao_so = $this->versao($versoes);

        // NetBSD
        } elseif (eregi('netbsd', $this->user_agent)) {
            $this->so = 'NetBSD';

            $versoes = array('i686' => 'i686',
                             'i586' => 'i586',
                             'i486' => 'i486',
                             'i386' => 'i386'
                             );
            $this->versao_so = $this->versao($versoes);

        // MAC
        } elseif (eregi('mac', $this->user_agent)) {
            $this->so = 'MacIntoch';

        // Outros SOs
        } elseif (eregi('sunos', $this->user_agent)) {
            $this->so = 'SunOS';
        } elseif (eregi('hp-ux', $this->user_agent)) {
            $this->so = 'HP-UX';
        } elseif (eregi('irix', $this->user_agent)) {
            $this->so = 'Irix';
        } elseif (eregi('os/2', $this->user_agent)) {
            $this->so = 'OS/2';
        } elseif (eregi('amiga', $this->user_agent)) {
            $this->so = 'Amiga';
        } elseif (eregi('qnx', $this->user_agent)) {
            $this->so = 'QNX';
        } elseif (eregi('dreamcast', $this->user_agent)) {
            $this->so = 'Sega Dreamcast';
        } elseif (eregi('palm', $this->user_agent)) {
            $this->so = 'Palm';
        } elseif (eregi('powertv', $this->user_agent)) {
            $this->so = 'PowerTV';
        } elseif (eregi('prodigy', $this->user_agent)) {
            $this->so = 'Prodigy';
        } elseif (eregi('symbian', $this->user_agent)) {
            $this->so = 'Symbian';
        } elseif (eregi('unix', $this->user_agent)) {
            $this->so = 'Unix';
        } elseif (eregi('webtv', $this->user_agent)) {
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
            if (eregi($chave, $this->user_agent)) {
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
