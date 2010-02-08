<?php
//
// SIMP
// Descricao: Bloqueio do modulo de Desenvolvimento
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.com.br
// Versao: 1.0.0.1
// Data: 05/10/2007
// Modificado: 11/08/2008
// License: LICENSE.TXT
// Copyright (C) 2007  Rubens Takiguti Ribeiro
//

// Modifique o valor da constante DEVEL_BLOQUEADO no arquivo constantes.php
if (DEVEL_BLOQUEADO) {
    pagina::erro(false, 'M&oacute;dulo de desenvolvimento bloqueado',
                 'Fechado por quest&otilde;es de Seguran&ccedil;a');
    exit(0);
}
