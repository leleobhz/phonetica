<?php
//
// SIMP
// Descricao: Classe que oferece varias listas uteis
// Autor: Rodrigo Pereira Moreira && Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rpmoreira@tecnolivre.ufla.br
// Versao: 1.0.0.21
// Data: 20/08/2007
// Modificado: 08/06/2009
// Copyright (C) 2007  Rodrigo Pereira Moreira
// License: LICENSE.TXT
//
define('LISTAS_LOCALIDADE',  $CFG->localidade);
define('LISTAS_CHARSET',     $CFG->charset);
define('LISTAS_UTF8',        $CFG->utf8);
define('LISTAS_DIR_MODULOS', $CFG->dirmods);
define('LISTAS_DIR_CLASSES', $CFG->dirclasses);

final class listas {


    //
    //     Construtor privado: utilize os metodos estaticos
    //
    private function __construct() {}


    //
    //     Retorna um vetor com um intervalo de numeros
    //
    static public function numeros($inicio, $fim, $formato = false, $passo = 1) {
    // Int $inicio: inicio do vetor
    // Int $fim: fim do vetor
    // String $formato: formato usado na funcao sprintf para exibicao do numero ao usuario
    // Int $passo: saltar de N em N
    //
        $vetor = array();

        if (!$formato) {
            $formato = '%d';
        }

        $passo = abs($passo);

        setlocale(LC_NUMERIC, 'C');
        if ($inicio < $fim) {
            for ($i = $inicio; $i <= $fim; $i += $passo) {
                $vetor[$i] = sprintf($formato, $i);
            }
        } else {
            for ($i = $fim; $i >= $fim; $i -= $passo) {
                $vetor[$i] = sprintf($formato, $i);
            }
        }
        setlocale(LC_NUMERIC, LISTAS_LOCALIDADE);

        return $vetor;
    }


    //
    //     Retorna um vetor com nomes de fontes
    //
    static public function get_fontes($padrao = true) {
    // Bool $padrao: incluir o padrao no vetor
    //
        $vet = array(
                     'Arial'           => 'Arial',
                     'Courier'         => 'Courier',
                     'Courier New'     => 'Courier New',
                     'cursive'         => 'Cursive',
                     'fantasy'         => 'Fantasy',
                     'Georgia'         => 'Georgia',
                     'Helvetica'       => 'Helvetica',
                     'monospace'       => 'Monospace',
                     'sans-serif'      => 'Sans-serif',
                     'serif'           => 'Serif',
                     'Tahoma'          => 'Tahoma',
                     'Times New Roman' => 'Times New Roman',
                     'Verdana'         => 'Verdana'
                    );

        if ($padrao) {
            $vet['padrao'] = 'Padr&atilde;o';
        }

        return $vet;
    }


    //
    //     Retorna um vetor com nomes de estados brasileiros
    //
    static public function get_estados($nenhum = true) {
    // Bool $nenhum: opcao para selecionar nenhum
    //
        $vet = array('AC' => 'Acre',
                     'AL' => 'Algoas',
                     'AP' => 'Amap&aacute;',
                     'AM' => 'Amazonas',
                     'BA' => 'Bahia',
                     'CE' => 'Cear&aacute;',
                     'DF' => 'Distrito Feredral',
                     'ES' => 'Esp&iacute;rito Santo',
                     'GO' => 'Goi&aacute;s',
                     'MA' => 'Maranh&atilde;o',
                     'MT' => 'Mato Grosso',
                     'MS' => 'Mato Grosso do Sul',
                     'MG' => 'Minas Gerais',
                     'PA' => 'Par&aacute;',
                     'PB' => 'Para&iacute;ba',
                     'PR' => 'Paran&aacute;',
                     'PE' => 'Pernambuco',
                     'PI' => 'Piau&iacute;',
                     'RJ' => 'Rio de Janeiro',
                     'RN' => 'Rio Grande do Norte',
                     'RS' => 'Rio Grande do Sul',
                     'RO' => 'Rond&ocirc;nia',
                     'RR' => 'Roraima',
                     'SP' => 'S&atilde;o Paulo',
                     'SC' => 'Santa Catarina',
                     'SE' => 'Sergipe',
                     'TO' => 'Tocantis');
        if ($nenhum) {
            $vet['--'] = '(Nenhum)';
        }
        return $vet;
    }


    //
    //     Retorna um vetor com as localidades permitidas
    //
    static public function get_locales() {
        static $matriz = array(
            'Padr&atilde;o' => array('C', 'C'),
            'Afrikaans' => array('af_ZA', 'Afrikaans_South Africa.1252'),
            'Albanian' => array('sq_AL', 'Albanian_Albania.1250'),
            'Arabic' => array('ar_SA', 'Arabic_Saudi Arabia.1256'),
            'Basque' => array('eu_ES', 'Basque_Spain.1252'),
            'Belarusian' => array('be_BY', 'Belarusian_Belarus.1251'),
            'Bosnian' => array('bs_BA', 'Serbian (Latin)'),
            'Bulgarian' => array('bg_BG', 'Bulgarian_Bulgaria.1251'),
            'Catalan' => array('ca_ES', 'Catalan_Spain.1252'),
            'Croatian' => array('hr_HR', 'Croatian_Croatia.1250'),
            'Chinese (Simplified)' => array('zh_CN', 'Chinese_China.936'),
            'Chinese (Traditional)' => array('zh_TW', 'Chinese_Taiwan.950'),
            'Czech' => array('cs_CZ', 'Czech_Czech Republic.1250'),
            'Danish' => array('da_DK', 'Danish_Denmark.1252'),
            'Dutch' => array('nl_NL', 'Dutch_Netherlands.1252'),
            'English' => array('en', 'English_Australia.1252'),
            'English (US)' => array('en_US', 'English_Australia.1252'),
            'Estonian' => array('et_EE', 'Estonian_Estonia.1257'),
            'Farsi' => array('fa_IR', 'Farsi_Iran.1256'),
            'Filipino' => array('ph_PH', 'Filipino_Philippines.1252'),
            'Finnish' => array('fi_FI', 'Finnish_Finland.1252'),
            'French (FR)' => array('fr_FR', 'French_France.1252'),
            'French (CH)' => array('fr_CH', 'French_France.1252'),
            'French (BE)' => array('fr_BE', 'French_France.1252'),
            'French (Canada)' => array('fr_CA', 'French_France.1252'),
            'Gaelic' => array('ga', 'Gaelic; Scottish Gaelic'),
            'Gallego' => array('gl_ES', 'Galician_Spain.1252'),
            'Georgian' => array('ka_GE', 'Georgian_Georgia.65001'),
            'German' => array('de_DE', 'German_Germany.1252'),
            'German (Personal)' => array('de_DE', 'German_Germany.1252'),
            'Greek' => array('el_GR', 'Greek_Greece.1253'),
            'Gujarati' => array('gu', 'Gujarati_India.0'),
            'Hebrew' => array('he_IL.utf8', 'Hebrew_Israel.1255'),
            'Hindi' => array('hi_IN', 'Hindi.65001'),
            'Hungarian' => array('hu', 'Hungarian_Hungary.1250'),
            'Icelandic' => array('is_IS', 'Icelandic_Iceland.1252'),
            'Indonesian' => array('id_ID', 'Indonesian_indonesia.1252'),
            'Italian' => array('it_IT', 'Italian_Italy.1252'),
            'Japanese' => array('ja_JP', 'Japanese_Japan.932'),
            'Kannada' => array('kn_IN', 'Kannada.65001'),
            'Khmer' => array('km_KH', 'Khmer.65001'),
            'Korean' => array('ko_KR', 'Korean_Korea.949'),
            'Lao' => array('lo_LA', 'Lao_Laos.1257'),
            'Lithuanian' => array('lt_LT', 'Lithuanian_Lithuania.1257'),
            'Latvian' => array('lat', 'Latvian_Latvia.1257'),
            'Malayalam' => array('ml_IN', 'Malayalam_India.x-iscii-ma'),
            'Malaysian' => array('id_ID', 'Indonesian_indonesia.1252'),
            'Maori (Ngai Tahu)' => array('mi_NZ', 'Maori.1252'),
            'Maori (Waikoto Uni)' => array('mi_NZ', 'Maori.1252'),
            'Mongolian' => array('mn', 'Cyrillic_Mongolian.1251'),
            'Norwegian' => array('no_NO', 'Norwegian_Norway.1252'),
            'Norwegian (Primary)' => array('no_NO', 'Norwegian_Norway.1252'),
            'Nynorsk' => array('nn_NO', 'Norwegian-Nynorsk_Norway.1252'),
            'Polish' => array('pl', 'Polish_Poland.1250'),
            'Portuguese' => array('pt_PT', 'Portuguese_Portugal.1252'),
            'Portuguese (Brazil)' => array('pt_BR', 'Portuguese_Brazil.1252'),
            'Romanian' => array('ro_RO', 'Romanian_Romania.1250'),
            'Russian' => array('ru_RU', 'Russian_Russia.1251'),
            'Samoan' => array('mi_NZ', 'Maori.1252'),
            'Serbian' => array('sr_CS', 'Serbian (Cyrillic)_Serbia and Montenegro.1251'),
            'Slovak' => array('sk_SK', 'Slovak_Slovakia.1250'),
            'Slovenian' => array('sl_SI', 'Slovenian_Slovenia.1250'),
            'Somali' => array('so_SO', false),
            'Spanish (International)' => array('es_ES', 'Spanish_Spain.1252'),
            'Swedish' => array('sv_SE', 'Swedish_Sweden.1252'),
            'Tagalog' => array('tl', false),
            'Tamil' => array('ta_IN', 'English_Australia.1252'),
            'Thai' => array('th_TH', 'Thai_Thailand.874'),
            'Tongan' => array('mi_NZ', 'Maori.1252'),
            'Turkish' => array('tr_TR', 'Turkish_Turkey.1254'),
            'Ukrainian' => array('uk_UA', 'Ukrainian_Ukraine.1251'),
            'Vietnamese' => array('vi_VN', 'Vietnamese_Viet Nam.1258')
        );

        $locale = setlocale(LC_ALL, 0);

        static $vetor = array();
        if (!count($vetor)) {
            if (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN') {
                foreach ($matriz as $nome => $codigos) {
                    $codigo = $codigos[1];
                    if ($codigo && setlocale(LC_ALL, $codigo)) {
                        $vetor[$nome] = $codigo;
                    }
                }
            } else {
                foreach ($matriz as $nome => $codigos) {
                    $codigo = $codigos[0].(LISTAS_CHARSET ? '.'.strtoupper(LISTAS_CHARSET) : '');
                    if ($codigos[0] && setlocale(LC_ALL, $codigo)) {
                        $vetor[$nome] = $codigo;
                    }
                }
            }
        }

        setlocale(LC_ALL, $locale);

        return $vetor;
    }


    //
    //     Retorna vetor com codigos de linguas
    //
    static public function get_linguas() {
        $vet = array(
        'af'    => 'Afrikaans',
        'sq'    => 'Albanian',
        'ar'    => 'Arabic',
        'ar-dz' => 'Arabic (Algeria)',
        'ar-bh' => 'Arabic (Bahrain)',
        'ar-eg' => 'Arabic (Egypt)',
        'ar-jo' => 'Arabic (Jordan)',
        'ar-kw' => 'Arabic (Kuwait)',
        'ar-lb' => 'Arabic (Lebanon)',
        'ar-ma' => 'Arabic (Morocco)',
        'ar-om' => 'Arabic (Oman)',
        'ar-qa' => 'Arabic (Qatar)',
        'ar-sa' => 'Arabic (Saudi Arabia)',
        'ar-tn' => 'Arabic (Tunisia)',
        'ar-ae' => 'Arabic (U.A.E.)',
        'ar-ye' => 'Arabic (Yemen)',
        'hy'    => 'Armenian',
        'as'    => 'Assamese',
        'az'    => 'Azeri',
        'eu'    => 'Basque',
        'be'    => 'Belarusian',
        'bn'    => 'Bengali',
        'bg'    => 'Bulgarian',
        'ca'    => 'Catalan',
        'zh'    => 'Chinese',
        'zh-cn' => 'Chinese (China)',
        'zh-hk' => 'Chinese (Hong Kong SAR)',
        'zh-mo' => 'Chinese (Macau SAR)',
        'zh-sg' => 'Chinese (Singapore)',
        'zh-tw' => 'Chinese (Taiwan)',
        'hr'    => 'Croatian',
        'cs'    => 'Czech',
        'da'    => 'Danish',
        'nl-be' => 'Dutch (Belgium)',
        'nl'    => 'Dutch (Netherlands)',
        'en'    => 'English',
        'en-au' => 'English (Australia)',
        'en-bz' => 'English (Belize)',
        'en-ca' => 'English (Canada)',
        'en-ie' => 'English (Ireland)',
        'en-jm' => 'English (Jamaica)',
        'en-nz' => 'English (New Zealand)',
        'en-ph' => 'English (Philippines)',
        'en-za' => 'English (South Africa)',
        'en-tt' => 'English (Trinidad)',
        'en-gb' => 'English (United Kingdom)',
        'en-us' => 'English (United States)',
        'en-zw' => 'English (Zimbabwe)',
        'et'    => 'Estonian',
        'fo'    => 'Faeroese',
        'fa'    => 'Farsi',
        'fi'    => 'Finnish',
        'fr-be' => 'French (Belgium)',
        'fr-ca' => 'French (Canada)',
        'fr'    => 'French (France)',
        'fr-lu' => 'French (Luxembourg)',
        'fr-mc' => 'French (Monaco)',
        'fr-ch' => 'French (Switzerland)',
        'mk'    => 'FYRO Macedonian',
        'ka'    => 'Georgian',
        'de-at' => 'German (Austria)',
        'de'    => 'German (Germany)',
        'de-li' => 'German (Liechtenstein)',
        'de-lu' => 'German (Luxembourg)',
        'de-ch' => 'German (Switzerland)',
        'el'    => 'Greek',
        'gu'    => 'Gujarati',
        'he'    => 'Hebrew',
        'hi'    => 'Hindi',
        'hu'    => 'Hungarian',
        'is'    => 'Icelandic',
        'id'    => 'Indonesian',
        'it'    => 'Italian (Italy)',
        'it-ch' => 'Italian (Switzerland)',
        'ja'    => 'Japanese',
        'kn'    => 'Kannada',
        'kk'    => 'Kazakh',
        'ko'    => 'Korean',
        'lv'    => 'Latvian',
        'lt'    => 'Lithuanian',
        'ms'    => 'Malay',
        'ml'    => 'Malayalam',
        'mr'    => 'Marathi',
        'ne'    => 'Nepali (India)',
        'no'    => 'Norwegian',
        'nb-no' => 'Norwegian (Bokmal)',
        'nn-no' => 'Norweigan (Nynorsk)',
        'or'    => 'Oriya',
        'pl'    => 'Polish',
        'pt-br' => 'Portuguese (Brazil)',
        'pt'    => 'Portuguese (Portugal)',
        'pa'    => 'Punjabi',
        'ro'    => 'Romanian',
        'ru'    => 'Russian',
        'sa'    => 'Sanskrit',
        'sr'    => 'Serbian',
        'sk'    => 'Slovak',
        'sl'    => 'Slovenian',
        'es'    => 'Spanish',
        'es-ar' => 'Spanish (Argentina)',
        'es-bo' => 'Spanish (Bolivia)',
        'es-cl' => 'Spanish (Chile)',
        'es-co' => 'Spanish (Colombia)',
        'es-cr' => 'Spanish (Costa Rica)',
        'es-do' => 'Spanish (Dominican Republic)',
        'es-ec' => 'Spanish (Ecuador)',
        'es-sv' => 'Spanish (El Salvador)',
        'es-gt' => 'Spanish (Guatemala)',
        'es-hn' => 'Spanish (Honduras)',
        'es-mx' => 'Spanish (Mexico)',
        'es-ni' => 'Spanish (Nicaragua)',
        'es-pa' => 'Spanish (Panama)',
        'es-py' => 'Spanish (Paraguay)',
        'es-pe' => 'Spanish (Peru)',
        'es-pr' => 'Spanish (Puerto Rico)',
        'es-uy' => 'Spanish (Uruguay)',
        'es-ve' => 'Spanish (Venezuela)',
        'sw'    => 'Swahili',
        'sv'    => 'Swedish',
        'sv-fi' => 'Swedish (Finland)',
        'ta'    => 'Tamil',
        'tt'    => 'Tatar',
        'te'    => 'Telugu',
        'th'    => 'Thai',
        'tr'    => 'Turkish',
        'uk'    => 'Ukrainian',
        'ur'    => 'Urdu',
        'uz'    => 'Uzbek',
        'vi'    => 'Vietnamese'
        );
        return $vet;
    }


    //
    //     Obtem as faixas de caracteres Unicode
    //
    public static function get_faixas_unicode() {
        return array(
            'Latino B&aacute;sico' => array(0x0000, 0x007F),
            'Latino-1 Suplementar' => array(0x0080, 0x00FF),
            'Latino Estendido-A' => array(0x0100, 0x017F),
            'Latino Estendido-B' => array(0x0180, 0x024F),
            'Extens&otilde;es IPA' => array(0x0250, 0x02AF),
            'Letras de modifica&ccedil;&atilde;o de espa&ccedil;o' => array(0x02B0, 0x02FF),
            'Combina&ccedil;&atilde;o de marcas diacr&iacute;ticas' => array(0x0300, 0x036F),
            'Grego e Copto' => array(0x0370, 0x03FF),
            'Cir&iacute;lico' => array(0x0400, 0x04FF),
            'Cir&iacute;lico Suplementar' => array(0x0500, 0x052F),
            'Arm&ecirc;nio' => array(0x0530, 0x058F),
            'Hebraico' => array(0x0590, 0x05FF),
            '&Aacute;rabe' => array(0x0600, 0x06FF),
            'Sir&iacute;aco' => array(0x0700, 0x074F),
            '&Aacute;rabe Suplementar' => array(0x0750, 0x077F),
            'Thaana' => array(0x0780, 0x07BF),
            'NKo' => array(0x07C0, 0x07FF),
            'Devanagari' => array(0x0900, 0x097F),
            'Bengali' => array(0x0980, 0x09FF),
            'Gurmukhi' => array(0x0A00, 0x0A7F),
            'Gujarati' => array(0x0A80, 0x0AFF),
            'Oriya' => array(0x0B00, 0x0B7F),
            'T&acirc;mil' => array(0x0B80, 0x0BFF),
            'T&eacute;lugo' => array(0x0C00, 0x0C7F),
            'Kannada' => array(0x0C80, 0x0CFF),
            'Malaiala' => array(0x0D00, 0x0D7F),
            'Cingal&ecirc;s' => array(0x0D80, 0x0DFF),
            'Tailand&ecirc;s' => array(0x0E00, 0x0E7F),
            'Laociano' => array(0x0E80, 0x0EFF),
            'Tibetano' => array(0x0F00, 0x0FFF),
            'Myanmar' => array(0x1000, 0x109F),
            'Georgiano' => array(0x10A0, 0x10FF),
            'Hangul J&auml;mO' => array(0x1100, 0x11FF),
            'Et&iacute;ope' => array(0x1200, 0x137F),
            'Et&iacute;ope Suplementar' => array(0x1380, 0x139F),
            'Cherokee' => array(0x13A0, 0x13FF),
            'S&iacute;labas Abor&iacute;genes Unificadas Canadenses' => array(0x1400, 0x167F),
            'Ogham' => array(0x1680, 0x169F),
            'R&uacute;nico' => array(0x16A0, 0x16FF),
            'Tagalo' => array(0x1700, 0x171F),
            'Hanun&oacute;o' => array(0x1720, 0x173F),
            'BUHID' => array(0x1740, 0x175F),
            'Tagbanwa' => array(0x1760, 0x177F),
            'Khmer' => array(0x1780, 0x17FF),
            'Mongol' => array(0x1800, 0x18AF),
            'LIMBU' => array(0x1900, 0x194F),
            'Le Tai' => array(0x1950, 0x197F),
            'Nova Tai Lue' => array(0x1980, 0x19DF),
            'S&iacute;mbolos Khmer' => array(0x19E0, 0x19FF),
            'Bugin&ecirc;s' => array(0x1A00, 0x1A1F),
            'Balinese' => array(0x1B00, 0x1B7F),
            'Sundan&ecirc;s' => array(0x1B80, 0x1BBF),
            'LEPCHA' => array(0x1C00, 0x1C4F),
            'Ol Chiki' => array(0x1C50, 0x1C7F),
            'Extens&otilde;es Fon&eacute;ticas' => array(0x1D00, 0x1D7F),
            'Extens&otilde;es Fon&eacute;ticas Suplementares' => array(0x1D80, 0x1DBF),
            'Combina&ccedil;&atilde;o de marcas diacr&iacute;ticas Suplementares' => array(0x1DC0, 0x1DFF),
            'Estens&atilde;o Latina Adicional' => array(0x1E00, 0x1EFF),
            'Grego Estendida' => array(0x1F00, 0x1FFF),
            'Pontua&ccedil;&otilde;es' => array(0x2000, 0x206F),
            'Superescritos e Subescritos' => array(0x2070, 0x209F),
            'S&iacute;mbolos Monet&aacute;rios' => array(0x20A0, 0x20CF),
            'Combina&ccedil;&atilde;o de marcas diacr&iacute;ticas para s&iacute;mbolos' => array(0x20D0, 0x20FF),
            'S&iacute;mbolos semelhantes a letras' => array(0x2100, 0x214F),
            'Formas de N&uacute;meros' => array(0x2150, 0x218F),
            'Setas' => array(0x2190, 0x21FF),
            'Operadores Matem&aacute;ticos' => array(0x2200, 0x22FF),
            'Operadores T&eacute;cnicos Diversos' => array(0x2300, 0x23FF),
            'Controle de Imagens' => array(0x2400, 0x243F),
            'Reconhecimento de Caracteres &Oacute;pticos' => array(0x2440, 0x245F),
            'Alfanum&eacute;ricos Delimitados' => array(0x2460, 0x24FF),
            'Desenho de Caixas' => array(0x2500, 0x257F),
            'Elementos de Bloco' => array(0x2580, 0x259F),
            'Formas Geom&eacute;tricas' => array(0x25A0, 0x25FF),
            'S&iacute;mbolos em Geral' => array(0x2600, 0x26FF),
            'Dingbats' => array(0x2700, 0x27BF),
            'S&iacute;mbolos Matem&aacute;ticos em Geral-A' => array(0x27C0, 0x27EF),
            'Setas Suplementares-A' => array(0x27F0, 0x27FF),
            'Braille' => array(0x2800, 0x28FF),
            'Setas Suplementares-B' => array(0x2900, 0x297F),
            'S&iacute;mbolos Matem&aacute;ticos em Geral-B' => array(0x2980, 0x29FF),
            'Operadores Matem&aacute;ticos Suplementares' => array(0x2A00, 0x2AFF),
            'Setas e S&iacute;mbolos em Geral' => array(0x2B00, 0x2BFF),
            'Glagol&iacute;tico' => array(0x2C00, 0x2C5F),
            'Latino Estendido-C' => array(0x2C60, 0x2C7F),
            'Copto' => array(0x2C80, 0x2CFF),
            'Georgiano Suplementar' => array(0x2D00, 0x2D2F),
            'Tifinagh' => array(0x2D30, 0x2D7F),
            'Et&iacute;ope Estendido' => array(0x2D80, 0x2DDF),
            'Cir&iacute;lico Estendido-A' => array(0x2DE0, 0x2DFF),
            'Pontua&ccedil;&atilde;o Suplementar' => array(0x2E00, 0x2E7F),
            'Radicais CJK Suplementar' => array(0x2E80, 0x2EFF),
            'Radicais Kanji (Japon&ecirc;s)' => array(0x2F00, 0x2FDF),
            'Ideogramas de Descri&ccedil;&atilde;o em Caracteres' => array(0x2FF0, 0x2FFF),
            'S&iacute;mbolos e Pontua&ccedil;&otilde;es CJK' => array(0x3000, 0x303F),
            'Hiragana (Japon&ecirc;s)' => array(0x3040, 0x309F),
            'Katakana (Japon&ecirc;s)' => array(0x30A0, 0x30FF),
            'Bopomofo' => array(0x3100, 0x312F),
            'Hangul Compat&iacute;vel Jamo' => array(0x3130, 0x318F),
            'Kanbun' => array(0x3190, 0x319F),
            'Bopomofo Estendido' => array(0x31A0, 0x31BF),
            'CJK Strokes' => array(0x31C0, 0x31EF),
            'Katakana Estens&atilde;o Fon&eacute;tica' => array(0x31F0, 0x31FF),
            'Letras CJK Delimitadas e M&ecirc;ses' => array(0x3200, 0x32FF),
            'CJK Compat&iacute;vel' => array(0x3300, 0x33FF),
            'CJK Estens&atilde;o de Ideogramas Unificados-A' => array(0x3400, 0x4DBF),
            'S&iacute;mbolos Yijing Hexagram' => array(0x4DC0, 0x4DFF),
            'CJK Ideogramas Unificados' => array(0x4E00, 0x9FFF),
            'S&iacute;labas Yi' => array(0xA000, 0xA48F),
            'Radicais Yi' => array(0xA490, 0xA4CF),
            'Vai' => array(0xA500, 0xA63F),
            'Cir&iacute;lico Estendido-B' => array(0xA640, 0xA69F),
            'Letras de Modifica&ccedil;&atilde;o de Tom' => array(0xA700, 0xA71F),
            'Latino Estendido-D' => array(0xA720, 0xA7FF),
            'Syloti Nagri' => array(0xA800, 0xA82F),
            'Phags-pa' => array(0xA840, 0xA87F),
            'Saurashtra' => array(0xA880, 0xA8DF),
            'Kayah Li' => array(0xA900, 0xA92F),
            'Rejang' => array(0xA930, 0xA95F),
            'Cham' => array(0xAA00, 0xAA5F),
            'S&iacute;labas Hangul' => array(0xAC00, 0xD7AF),
            'Substitutos Fortes' => array(0xD800, 0xDB7F),
            'Substitutos Fortes de Uso Privado' => array(0xDB80, 0xDBFF),
            'Substitutos Fracos' => array(0xDC00, 0xDFFF),
            '&Aacute;rea de Uso Privado' => array(0xE000, 0xF8FF),
            'CJK Compat&iacute;vel com Ideogramas' => array(0xF900, 0xFAFF),
            'Formas de Apresenta&ccedil;&atilde;o Alfab&eacute;tica' => array(0xFB00, 0xFB4F),
            'Formas de Apresenta&ccedil;&atilde;o &Aacute;rabe-A' => array(0xFB50, 0xFDFF),
            'Seletores Variantes' => array(0xFE00, 0xFE0F),
            'Formas Verticais' => array(0xFE10, 0xFE1F),
            'Combina&ccedil;&atilde;o de Marcas Divididas' => array(0xFE20, 0xFE2F),
            'CJK Compat&iacute;veis com Formas' => array(0xFE30, 0xFE4F),
            'Formas Variantes Pequenas' => array(0xFE50, 0xFE6F),
            'Formas de Apresenta&ccedil;&atilde;o &Aacute;rabe-B' => array(0xFE70, 0xFEFF),
            'Formas de Largura Quebrada ou Completa' => array(0xFF00, 0xFFEF),
            'Especiais' => array(0xFFF0, 0xFFFF),
            'Linear B Syllabary' => array(0x10000, 0x1007F),
            'Linear B Ideograms' => array(0x10080, 0x100FF),
            'N&uacute;meros Aegeanos' => array(0x10100, 0x1013F),
            'N&uacute;meros Gregos' => array(0x10140, 0x1018F),
            'S&iacute;mbolos Antigos' => array(0x10190, 0x101CF),
            'Discos Festos' => array(0x101D0, 0x101FF),
            'Liciano' => array(0x10280, 0x1029F),
            'Cariano' => array(0x102A0, 0x102DF),
            'Italiano Antigo' => array(0x10300, 0x1032F),
            'G&oacute;tico' => array(0x10330, 0x1034F),
            'Ugar&iacute;tico' => array(0x10380, 0x1039F),
            'Persa Antigo' => array(0x103A0, 0x103DF),
            'Deseret' => array(0x10400, 0x1044F),
            'Shaviano' => array(0x10450, 0x1047F),
            'Osmanya' => array(0x10480, 0x104AF),
            'S&iacute;labas Cypriot' => array(0x10800, 0x1083F),
            'Fen&iacute;cio' => array(0x10900, 0x1091F),
            'Lidiano' => array(0x10920, 0x1093F),
            'Kharoshthi' => array(0x10A00, 0x10A5F),
            'Cuneiforme' => array(0x12000, 0x123FF),
            'N&uacute;meros e Pontua&ccedil;&otilde;es Cuneiformes' => array(0x12400, 0x1247F),
            'S&iacute;mbolos Musicais Bizantinos' => array(0x1D000, 0x1D0FF),
            'S&iacute;mbolos Musicais' => array(0x1D100, 0x1D1FF),
            'Nota&ccedil;&atilde;o Musical Grega Antiga' => array(0x1D200, 0x1D24F),
            'S&iacute;mbolos Tai Xuan Jing' => array(0x1D300, 0x1D35F),
            'Numerais Rod' => array(0x1D360, 0x1D37F),
            'S&iacute;mbolos Alfanum&eacute;ricos Matem&aacute;ticos' => array(0x1D400, 0x1D7FF),
            'Pe&ccedil;as Mahjong' => array(0x1F000, 0x1F02F),
            'Pe&ccedil;as Domin&oacute;' => array(0x1F030, 0x1F09F),
            'CJK Ideogramas Estens&atilde;o-B' => array(0x20000, 0x2A6DF),
            'CJK Compatibilidade de Ideogramas Suplementar' => array(0x2F800, 0x2FA1F),
            'Tags' => array(0xE0000, 0xE007F),
            'Seletores Variantes Suplementares' => array(0xE0100, 0xE01EF),
            '&Aacute;rea de Uso Privado Suplementar-A' => array(0xF0000, 0xFFFFF),
            '&Aacute;rea de Uso Privado Suplementar-B' => array(0x100000, 0x10FFFF)
        );
    }


    //
    //     Retorna vetor com os meses
    //
    static public function get_meses($nenhum = false) {
    // Bool $nenhum: indica se deve incluir a opcao "Nenhum"
    //
        $vt_meses = array();
        if ($nenhum) {
            $vt_meses[0] = '--';
        }
        if (function_exists('nl_langinfo')) {
            for ($i = 1; $i <= 12; $i++) {
                $vt_meses[$i] = ucfirst(nl_langinfo(constant('MON_'.$i)));
            }
        } else {
            $ano = (int)strftime('%Y');
            for ($mes = 1; $mes <= 12; $mes++) {
                $time = mktime(0, 0, 0, $mes, 1, $ano);
                $vt_meses[$mes] = ucfirst(strftime('%B', $time));
            }
        }
        if (LISTAS_UTF8) {
            $vt_meses = array_map('utf8_decode', $vt_meses);
            $vt_meses = array_map('utf8_encode', $vt_meses);
        }
        return $vt_meses;
    }


    //
    //     Retorna um vetor com os nomes dos dias da semana (0 = Domingo, 6 = Sabado)
    //
    static public function get_semanas($abreviado = false) {
    // Bool $abreviado: retorna a lista com os nomes abreviados
    //
        setlocale(LC_ALL, LISTAS_LOCALIDADE);
        $vt_semana = array();
        if (function_exists('nl_langinfo')) {
            if ($abreviado) {
                for ($i = 1; $i <= 7; $i++) {
                    $vt_semana[] = nl_langinfo(constant('ABDAY_'.$i));
                }
            } else {
                for ($i = 1; $i <= 7; $i++) {
                    $vt_semana[] = nl_langinfo(constant('DAY_'.$i));
                }
            }
        } else {
            list($dia, $mes, $ano) = util::get_data_completa();
            $time = mktime(0, 0, 0, $mes, $dia, $ano);
            if (strftime('%u') === false) {
                $dados_data = getdate($time);
                $dia_semana = $dados_data['wday'];
            } else {
                $dia_semana = (int)strftime('%u', $time);
            }
            $dia += 7 - ($dia_semana % 7);
            for ($i = 0; $i < 7; $i++, $dia++) {
                $time = mktime(0, 0, 0, $mes, $dia, $ano);
                $nome = $abreviado ? strftime('%a', $time) : strftime('%A', $time);
                $vt_semana[] = ucfirst($nome);
            }
        }
        if (LISTAS_UTF8) {
            $vt_semana = array_map('utf8_decode', $vt_semana);
            $vt_semana = array_map('utf8_encode', $vt_semana);
        }
        return $vt_semana;
    }


    //
    //     Retorna um vetor com os dias do mes
    //
    static public function get_dias($nenhum = false) {
    // Bool $nenhum: indica se deve incluir a opcao "Nenhum"
    //
        if ($nenhum) {
            return array_merge(array(0 => '--'), self::numeros(1, 31));
        }
        return self::numeros(1, 31);
    }


    //
    //     Retorna vetor com os anos
    //
    static public function get_anos($passado = 20, $futuro = 5, $nenhum = false) {
    // Int $passado: numero de anos anteriores ao atual
    // Int $futuro: numero de anos posteriores ao atual
    // Bool $nenhum: indica se deve incluir a opcao "Nenhum"
    //
        $anos = array();
        if ($nenhum) {
            $anos[0] = '----';
        }
        $atual = (int)strftime('%Y');
        for ($i = $atual - $passado; $i <= $atual + $futuro; $i++) {
            $anos[$i] = $i;
        }
        return $anos;
    }


    //
    //     Retorna um vetor com os semestres
    //
    static public function get_semestres() {
        return array(1 => '1&ordm; Semestre',
                     2 => '2&ordm; Semestre');
    }


    //
    //    Obtem as classes de um diretorio recursivamente e, opcionalmente, que extendem determinada classe
    //
    static public function get_classes($diretorio, $classe_base = false, $apenas_instanciaveis = true) {
    // String $diretorio: caminho para o diretorio a ser percorrido
    // String $classe_base: filtra apenas as classes filhas da classe base indicada
    // Bool $apenas_instanciaveis: filtra apenas as classes instanciaveis
    //
        $classes = array();
        if (!is_dir($diretorio)) {
            trigger_error('O diretorio "'.$diretorio.'" nao existe', E_USER_WARNING);
            return $classes;
        }

        $reflexao_base = false;
        if ($classe_base) {
            try {
                simp_autoload($classe_base);
                $reflexao_base = new ReflectionClass($classe_base);
            } catch (Exception $e) {
                trigger_error('A classe base "'.$classe_base.'" nao existe ou possui erros', E_USER_WARNING);
                return $classes;
            }
            if ($reflexao_base->isFinal()) {
                trigger_error('A classe base "'.$classe_base.'" eh final e nao pode ter filhas', E_USER_WARNING);
                return $classes;
            }
        }
        self::get_classes_recursivo($diretorio, $classes, $reflexao_base, $apenas_instanciaveis);
        return $classes;
    }


    //
    //     Recursao do metodo get_classes
    //
    static private function get_classes_recursivo($diretorio, &$classes, $reflexao_base = false, $apenas_instanciaveis = true) {
    // String $diretorio: caminho para o diretorio
    // Array[String => String] $classes: classes encontradas no diretorio
    // Bool || ReflectionClass $reflexao_base: filtra apenas as classes filhas da classe base indicada por sua reflexao
    // Bool $apenas_instanciaveis: filtra apenas as classes instanciaveis
    //
        $dir = opendir($diretorio);
        if (!$dir) {
            return false;
        }
        while (($item = readdir($dir)) !== false) {
            if ($item[0] == '.') { continue; }
            if (is_dir($diretorio.$item)) {
                self::get_classes_recursivo($diretorio.$item.'/', $classes, $reflexao_base, $apenas_instanciaveis);
            } elseif (preg_match('/^([A-z0-9-_\.]+).class.php$/', $item, $match)) {
                $classe = $match[1];
                if (isset($classes[$classe])) {
                    continue;
                }
                if ($reflexao_base) {
                    try {
                        simp_autoload($classe);
                        $reflexao_filha = new ReflectionClass($classe);
                    } catch (Exception $e) {
                        trigger_error('A classe "'.$classe.'" possui erros', E_USER_WARNING);
                        continue;
                    }
                    if ($reflexao_filha->isSubclassOf($reflexao_base)) {
                        if (!$apenas_instanciaveis || $reflexao_filha->isInstantiable()) {
                            $classes[$classe] = $classe;
                        }
                    }
                    unset($reflexao_filha);
                } else {
                    $classes[$classe] = $classe;
                }
            }
        }
    }


    //
    //     Retorna um vetor indexado pelo nome das entidades do sistema e que aponta para a descricao da entidade
    //
    static public function get_entidades() {
        $classes_nomes = array();

        $classes = self::get_classes(LISTAS_DIR_CLASSES.'/extensao/', 'objeto', true);
        $i = 0;
        foreach ($classes as $classe) {
            $classes_nomes[$classe] = objeto::get_objeto($classe)->get_entidade();
            $i++;

            // A cada 10 classes percorridas: limpar as definicoes para guardar memoria
            if ($i % 10 == 0) {
                objeto::limpar_definicoes_classes();
            }
        }

        asort($classes_nomes);
        return $classes_nomes;
    }


    //
    //     Retorna um vetor de modulos
    //
    static public function get_modulos($dir_modulos = false, $prefixo = '') {
    // String $dir_modulos: diretorio de modulos
    // String $prefixo: prefixo a ser adicionado
    //
        $vet = array();
        $dir_modulos = $dir_modulos ? $dir_modulos : LISTAS_DIR_MODULOS;
        $dir = opendir($dir_modulos);
        if ($dir) {
            while (($item = readdir($dir)) !== false) {
                if ($item == '.' || $item == '..' || $item == '.svn') { continue; }
                if (is_dir($dir_modulos.'/'.$item)) {
                    $vet[$prefixo.$item] = $prefixo.$item;
                    $vet2 = self::get_modulos($dir_modulos.'/'.$item.'/', $prefixo.$item.'/');

                    $vet = $vet + $vet2;
                }
            }
            asort($vet);
        }
        return $vet;
    }

}//class
