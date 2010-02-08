<?php
//
// SIMP
// Descricao: Exemplo de utilizacao do player de som
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.com.br
// Versao: 1.0.0.0
// Data: 03/03/2007
// Modificado: 03/03/2007
// Copyright (C) 2007  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//
require_once('../../config.php');

$titulo = 'Player';
$nav = array('#index.php');
$estilos = false;

$pagina = new pagina();
$pagina->cabecalho($titulo, $nav, $estilos);
$pagina->inicio_conteudo($titulo);
echo <<<XHTML
<p>Bot&atilde;o simples:</p>
<object type="application/x-shockwave-flash" width="18" height="18" data="{$CFG->wwwroot}webservice/player_button/musicplayer.swf?playlist_url={$CFG->wwwroot}/teste/player/playlist.xml">
<param name="movie" value="{$CFG->wwwroot}/webservice/player_button/musicplayer.swf?playlist_url={$CFG->wwwroot}/teste/player/playlist.xml" />
</object>
<hr />
<p>Player pequeno:</p>
<object type="application/x-shockwave-flash" width="110" height="25" data="{$CFG->wwwroot}/webservice/player_slim/xspf_player_slim.swf?playlist_url={$CFG->wwwroot}/teste/player/playlist.xml">
<param name="movie" value="{$CFG->wwwroot}/webservice/player_slim/xspf_player_slim.swf?playlist_url={$CFG->wwwroot}/teste/player/playlist.xml" />
</object>
<hr />
<p>Player Fino:</p>
<object type="application/x-shockwave-flash" width="400" height="25" data="{$CFG->wwwroot}/webservice/player_slim/xspf_player_slim.swf?playlist_url={$CFG->wwwroot}/teste/player/playlist.xml">
<param name="movie" value="{$CFG->wwwroot}/webservice/player_slim/xspf_player_slim.swf?playlist_url={$CFG->wwwroot}/teste/player/playlist.xml" />
</object>
<hr />
<p>Player completo:</p>
<object type="application/x-shockwave-flash" width="400" height="170" data="{$CFG->wwwroot}/webservice/player/xspf_player.swf?playlist_url={$CFG->wwwroot}/teste/player/playlist.xml">
<param name="movie" value="{$CFG->wwwroot}/webservice/player/xspf_player.swf?playlist_url={$CFG->wwwroot}/teste/player/playlist.xml" />
</object>
XHTML;
$pagina->fim_conteudo();
$pagina->rodape();
exit(0);
