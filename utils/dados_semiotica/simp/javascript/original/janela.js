//
// SIMP
// Descricao: JavaScript para exibir janelas dinamicamente
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.com.br
// Versao: 1.0.0.33
// Data: 20/12/2007
// Modificado: 10/12/2009
// TODO: Funcionar no IE(ca)
// License: LICENSE.TXT
// Copyright (C) 2007  Rubens Takiguti Ribeiro
//


// Variaveis globais
{
    var janela_ativa = null;
    var obj_filtro   = null;
    class_seletor.instancias    = new Array();
    class_hierarquia.instancias = new Array();
    class_calendario.instancias = new Array();
    class_popup.instancias      = new Array();
}


//
//     Muda o foco de uma janela montada com Javascript
//
function foco_janela(j) {
// DIV j: janela para dar o foco
//
    if (janela_ativa) {
        janela_ativa.style.zIndex = janela_ativa.zIndex_original;
    }
    janela_ativa = j;
    janela_ativa.zIndex_original = janela_ativa.style.zIndex;
    janela_ativa.style.zIndex = 1000;
}


//
//     Classe janela
//
function class_janela() {
    var that     = this;
    this.caixa   = null;
    this.titulo  = null;
    this.pai     = null;
    this.visivel = false;


    //
    //     Abrir janela dentro de um elemento
    //
    this.abrir = function(pai) {
    // Object pai: algum DIV ou o proprio BODY para armazenar a janela
    //
        if (that.visivel) { return; }
        that.pai = pai;
        that.pai.appendChild(that.caixa);
        that.visivel = true;
        foco_janela(that.caixa);
    };


    //
    //     Fechar janela
    //
    this.fechar = function() {
        if (!that.visivel) { return; }
        that.pai.removeChild(that.caixa);
        that.visivel = false;
    };


    //
    //     Muda o titulo da janela
    //
    this.set_titulo = function(texto_titulo) {
    // String texto_titulo: novo titulo da janela
    //
        var divs = that.titulo.getElementsByTagName("div");
        for (var i = 0; i < divs.length; i++) {
            var div = divs.item(i);
            if (get_classe(div) == "texto") {
                limpar(div);
                div.appendChild(document.createTextNode(texto_titulo));
                return;
            }
        }
    };


    //
    //     Criar uma janela
    //
    this.criar_janela = function(texto_titulo, x, y, w, h) {
    // String texto_titulo: titulo da janela
    // Int x: posicao x da janela na tela (em px)
    // Int y: posicao y da janela na tela (em px)
    // Int w: largura da janela (em px)
    // Int h: altura da janela (em px)
    //
        // Criar container
        that.caixa = document.createElement("div");
        definir_classe(that.caixa, "caixa");
        x = Math.max(0, parseInt(x));
        y = Math.max(0, parseInt(y));
        w = Math.abs(parseInt(w));
        h = Math.abs(parseInt(h));

        if (x) { that.caixa.style.left   = x + "px"; }
        if (y) { that.caixa.style.top    = y + "px"; }
        if (w) { that.caixa.style.width  = w + "px"; }
        if (h) { that.caixa.style.height = h + "px"; }
        {
            // Criar titulo da caixa
            that.titulo = document.createElement("h2");
            definir_classe(that.titulo, "titulo");
            {
                // Texto da caixa
                var div_texto = document.createElement("div");
                definir_classe(div_texto, "texto");
                var texto = document.createTextNode(texto_titulo);
                div_texto.appendChild(texto);

                // Area de botoes
                var div_botoes = document.createElement("div");
                definir_classe(div_botoes, "botoes");
                {
                    // Botao de fechar
                    that.bt_fechar = document.createElement("a");
                    definir_classe(that.bt_fechar, "bt_fechar");
                    var texto_bt_fechar = document.createTextNode("fechar");
                    that.bt_fechar.appendChild(texto_bt_fechar);
                    div_botoes.appendChild(that.bt_fechar);
                }//div_botoes

                that.titulo.appendChild(div_texto);
                that.titulo.appendChild(div_botoes);

                // Incluir div com clear both
                var div_clear = document.createElement("div");
                div_clear.style.clear = "both";
                that.titulo.appendChild(div_clear);
            }//that.titulo
        }//that.caixa
        that.caixa.appendChild(that.titulo);

        // Definir eventos a caixa
        that.caixa.onmousedown = function() {
            foco_janela(that.caixa);
        };

        // Definir eventos ao botao de fechar
        that.bt_fechar.onmousedown = function() {
            that.bt_fechar.style.borderStyle = "inset";
        };
        that.bt_fechar.onmouseup = function() {
            that.bt_fechar.style.borderStyle = "outset";
        };
        that.bt_fechar.onclick = that.fechar;

        // Tornar a caixa movel
        objeto_movel(that.titulo, that.caixa);

        return that.caixa;
    };
}


//
//     Classe seletor de entidades
//
function class_seletor(link) {
// A link: link a ser transformado em seletor
//
    var that = this;
    this.id  = class_seletor.instancias.length;
    class_seletor.instancias[this.id] = this;

    // Atributos gerais
    this.link    = link;
    this.url     = link.getAttribute("href");
    this.input   = link.parentNode.getElementsByTagName("input").item(0);
    this.seletor = null;
    // seletor.janela
    // seletor.itens
    // seletor.total_itens
    // seletor.input_busca
    // seletor.selecionado
    // seletor.flag_mouse

    // Atributos auxiliares
    this.timer_filtro  = null;
    this.ajax = new class_ajax();


    //
    //     Marca um item para selecao
    //
    this.marcar = function(scroll) {
    // Bool scroll: indica se a barra de rolagem deve rolar ate aparecer o item selecionado
    //
        var i = that.seletor.selecionado ? that.seletor.selecionado : 0;
        var item = that.seletor.itens.getElementsByTagName("p").item(i);
        adicionar_classe(item, "selecionado");
        
        // Ajustar a barra de rolagem para tornar o item sempre visivel na lista
        if (scroll) {
            if (item.parentNode.firstChild) {
                var item_topo = item.offsetTop - item.parentNode.firstChild.offsetTop;
                var item_baixo = item_topo + item.offsetHeight;
                var scroll_topo = item.parentNode.scrollTop;
                var scroll_baixo = scroll_topo + item.parentNode.offsetHeight;

                // Se o item esta abaixo da area visivel: alinhar a base
                if (item_baixo > scroll_baixo) {
                    item.parentNode.scrollTop = item_baixo - item.parentNode.offsetHeight;
                    
                // Se o item esta acima da area visivel: alinhar ao topo
                } else if (item_topo < scroll_topo) {
                    item.parentNode.scrollTop = item_topo;
                }
            }
        }
    };


    //
    //     Desmarca o item selecionado
    //
    this.desmarcar = function() {
        var i = that.seletor.selecionado;
        var item = that.seletor.itens.getElementsByTagName("p").item(i);
        remover_classe(item, "selecionado");
    };


    //
    //     Seleciona o item anterior disponivel
    //
    this.selecionar_anterior = function() {
        var vt = that.seletor.itens.getElementsByTagName("p");
        var i = that.seletor.selecionado;
        var j = 0;
        that.desmarcar();
        do {
            if (j >= vt.length) { return; }
            i--;
            j++;
            if (i < 0) {
                i = vt.length - 1;
            }
        } while (possui_classe(vt.item(i), "hide"));
        that.seletor.selecionado = i;
        that.marcar(true);
    };


    //
    //     Seleciona o proximo item disponivel
    //
    this.selecionar_proximo = function() {
        var vt = that.seletor.itens.getElementsByTagName("p");
        var i = that.seletor.selecionado;
        var j = 0;
        that.desmarcar();
        do {
            if (j >= vt.length) { return; }
            i++;
            j++;
            if (i >= vt.length) { i = 0; }
        } while (possui_classe(vt.item(i), "hide"));
        that.seletor.selecionado = i;
        that.marcar(true);
    };

  
    //
    //     Muda um item do seletor de acordo com a tecla digitada
    //
    this.mudar_item = function(e) {
    // Event e: evento ocorrido para chamada do metodo
    //
        var k = (window.event) ? e.keyCode : e.which;
        switch (k) {

        // Enter
        case 13:
            var i = that.seletor.selecionado;
            var item = that.seletor.itens.getElementsByTagName("p").item(i);

            // Se o item esta visivel
            if (get_classe(item) != "hide") {
                that.input.value = item.codigo;
                that.seletor.janela.fechar();
                that.input.focus();
                that.input.select();

            // Se nao ha nenhum item selecionado
            } else {
                var r = window.confirm("Nenhum item selecionado.\nVocê deseja limpar o campo de busca e procurar algum?");
                if (r) {
                    that.seletor.input_busca.value = "";
                    obj_filtro = that;
                    filtrar_seletor();
                    that.marcar(true);
                }
            }
            return false;

        // Seta para cima
        case 38:
            that.seletor.flag_mouse = false;
            that.selecionar_anterior();
            return false;

        // Seta para baixo
        case 40:
            that.seletor.flag_mouse = false;
            that.selecionar_proximo();
            return false;

        // ESC
        case 27:
            that.seletor.janela.fechar();
            that.input.focus();
            that.input.select();
            return false;

        // Outra tecla
        default:
            that.ativar_timer_filtro();
            break;
        }
        return true;
    };


    //
    //     Obtem a lista de entidades consultadas e atualiza os itens do seletor
    //
    this.atualizar_itens = function() {
        limpar(that.seletor.itens);
        var xml = that.ajax.get_retorno("xml");
        var entidades = xml.documentElement.getElementsByTagName("entidade");
        for (var i = 0; i < entidades.length; i++) {
            var entidade = entidades.item(i);
            var codigo = entidade.getElementsByTagName("codigo").item(0).textContent;
            var valor = entidade.getElementsByTagName("valor").item(0).textContent;
            var texto_exibido = codigo + ": " + valor;
            
            // Criar linha
            var linha = document.createElement("p");
            linha.pos = i;
            linha.codigo = codigo;
            linha.valor = valor;
            linha.texto_exibido = texto_exibido.toLowerCase();
            var texto = document.createTextNode(texto_exibido);
            linha.appendChild(texto);

            // Se mover, habilitar checagem de mouse
            linha.onmousemove = function() {
                that.seletor.flag_mouse = true;
            };

            // Marcar o item
            linha.onmouseover = function() {
                if (that.seletor.flag_mouse) {
                    that.desmarcar();
                    that.seletor.selecionado = this.pos;
                    that.marcar(false);
                }
            };

            // Inserir o codigo selecionado no input do formulario
            linha.onclick = function() {
                that.input.value = this.codigo;
                that.seletor.janela.fechar();
                that.input.focus();
                that.input.select();
            };

            // Adicionar linha na caixa de itens
            that.seletor.itens.appendChild(linha);
        }
        that.seletor.input_busca.removeAttribute("disabled");
        that.seletor.input_busca.focus();
        that.seletor.input_busca.select();
        obj_filtro = that;
        filtrar_seletor();
        that.marcar(true);
    };


    //
    //     Ativa o timer para iniciar a filtragem
    //     (Faz a busca apenas apos o cliente ficar 1 segundo sem digitar)
    //
    this.ativar_timer_filtro = function() {
        if (that.timer_filtro) {
            clearTimeout(that.timer_filtro);
        }
        obj_filtro = that;
        that.timer_filtro = setTimeout("filtrar_seletor()", 1000);
    };
  

    //
    //     Cria uma caixa de selecao
    //
    this.criar_caixa = function(pos) {
    // Object pos: posicao onde deve ser criada a caixa (atributos x e y em px)
    //
        // Criar janela
        var janela = new class_janela();
        var caixa = janela.criar_janela("Selecione um item", pos.x - 230, pos.y - 150);
        {
            // Criar espaco para busca entre os itens
            var busca = document.createElement("div");
            definir_classe(busca, "busca");
            busca.link = link;
            {
                // Label
                var id = "busca_seletor_" + that.id;
                var label_busca = document.createElement("label");
                label_busca.setAttribute("for", id);
                var texto_label = document.createTextNode("Busca");
                label_busca.appendChild(texto_label);

                // Input
                var input_busca = document.createElement("input");
                definir_classe(input_busca, "input_busca");
                input_busca.id = id;
                input_busca.setAttribute("type", "text");
                input_busca.setAttribute("maxlength", "40");
                input_busca.setAttribute("disabled", "disabled");
                input_busca.onkeydown = that.mudar_item;

                // Botao Fechar
                var atualizar = document.createElement("img");
                definir_classe(atualizar, "bt_atualizar");
                atualizar.setAttribute("alt", "Atualizar lista");
                atualizar.setAttribute("src", wwwroot + "imgs/icones/atualizar.gif");

                // So sera possivel clicar no atualizar 3 vezes
                // Caso contrario o cliente esta brincando com algo que consome muito recurso em segundo plano
                atualizar.pode = 3;

                // Acao ao clicar no icone de atualizar a lista
                atualizar.onclick = function() {
                    if (that.seletor.input_busca.getAttribute("disabled")) {
                        window.alert("Aguarde o carregamento dos dados.");
                        return false;
                    }

                    this.pode--;
                    if (this.pode > 0)  {
                        that.seletor.input_busca.setAttribute("disabled", "disabled");
                        that.carregar_itens();
                    } else {
                        window.alert("Atenção: você clicou em atualizar 3 vezes.\n" + 
                                     "Esta operação consome recursos e deve ser utilizada com moderação.");
                        this.parentNode.removeChild(this);
                    }
                    return true;
                }
            }//busca
            busca.appendChild(label_busca);
            busca.appendChild(input_busca);
            busca.appendChild(atualizar);

            // Criar espaco para listar os itens
            var itens = document.createElement("div");
            definir_classe(itens, "itens");
        }//caixa

        // Adicionar elementos na caixa
        caixa.appendChild(busca);
        caixa.appendChild(itens);

        // Definir o seletor da classe
        that.seletor = {
            janela:janela,
            itens:itens,
            input_busca:input_busca,
            selecionado:0,
            flag_mouse:false
        };
    };
  
  
    //
    //     Carrega os elementos na caixa
    //
    this.carregar_itens = function() {
        that.ajax.set_funcao(that.atualizar_itens);
        that.ajax.exibir_carregando(that.seletor.itens);
        that.ajax.consultar("GET", that.url, true, null);
    };
  
  
    //
    //     Abre um seletor
    //
    this.link.onclick = function abrir_seletor(e) {
    // Event e: evento ao clicar sobre o link
    //
        e = e ? e : window.event;
        e.returnValue = false;
        if (!that.ajax.xmlhttp) {
            var l = that.url;
            l = l + ((l.indexOf("?") > 0) ? "&" : "?") + "input=" + that.input.id;
            window.open(l, "Busca", "toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=yes, width=500, height=400");
            return false;
        }

        // Criar a caixa proximo a posicao do mouse e adiciona-la no documento
        if (that.seletor == null) {
            var pos = get_posicao_mouse(e);
            that.criar_caixa(pos);
            that.carregar_itens();
        }

        // Adicionar a caixa no documento
        that.seletor.janela.abrir(document.getElementsByTagName("body").item(0));

        // Dar o foco no campo de busca
        that.seletor.input_busca.focus();
        that.seletor.input_busca.select();
        return false;
    };
}


//
//     Filtra os elementos de um seletor
//
function filtrar_seletor() {
    var busca = obj_filtro.seletor.input_busca.value.toLowerCase();
    var itens = obj_filtro.seletor.itens.getElementsByTagName("p");

    // Se o texto esta' vazio: tornar todos elemnetos visiveis
    if (busca.length == 0) {
        var item = obj_filtro.seletor.itens.firstChild;
        while (item != null) {
            var valor = item.textContent;
            limpar(item);
            item.appendChild(document.createTextNode(valor));
            remover_classe(item);
            item = item.nextSibling;
        }
        obj_filtro.marcar();
        return;
    }

    // Buscar itens semelhantes ao valor informado
    for (var i = itens.length - 1; i >= 0; i--) {
        var item = itens.item(i);
        var pos = item.texto_exibido.indexOf(busca);

        // Nao encontrou a busca no item atual
        if (pos < 0) {
            definir_classe(item, "hide");

        // Encontrou a busca no item atual
        } else {
            var valor = item.textContent;
            remover_classe(item);
            limpar(item);

            var inicio = document.createCDATASection(valor.substr(0, pos));

            // Destacar o texto de busca no item com STRONG
            var meio = document.createElement("strong");
            meio.appendChild(document.createCDATASection(valor.substr(pos, busca.length)));

            var fim = document.createCDATASection(valor.substr(pos + busca.length));
        
            item.appendChild(inicio);
            item.appendChild(meio);
            item.appendChild(fim);
        }
    }

    // Se o item que estava selecionado nao faz parte da busca, selecionar o proximo
    if (possui_classe(itens.item(obj_filtro.seletor.selecionado), "hide")) {
        obj_filtro.selecionar_proximo();
    } else {
        obj_filtro.marcar();
    }
}


//
//     Classe seletor de entidades na forma hierarquica
//
function class_hierarquia(link) {
// A link: link a ser transformado em seletor
//
    var that = this;
    this.id  = class_hierarquia.instancias.length;
    class_hierarquia.instancias[this.id] = this;

    // Atributos gerais
    this.nivel   = 0;
    that.ul      = null;
    that.pai     = null;
    this.link    = link;
    this.url     = link.getAttribute("href");
    this.ws      = wwwroot + "webservice/hierarquia.xml.php";
    this.input   = link.parentNode.getElementsByTagName("input").item(0);
    this.seletor = null;
    // seletor.janela
    // seletor.itens
    // seletor.status

    // Atributos auxiliares
    this.ajax = new class_ajax();


    //
    //     Alterna o texto da barra de status
    //
    this.set_status = function(texto) {
        var t = document.createTextNode(texto);
        limpar(that.seletor.status);
        that.seletor.status.appendChild(t);
    };


    //
    //     Alterna o botao de expandir/agrupar
    //
    this.set_botao = function(botao, tipo) {
    // IMG botao: botao a ser alterado
    // String tipo: tipo de botao ("+" para expandir ou "-" para agrupar)
    //
        switch (tipo) {
        case "+":
            botao.setAttribute("src", wwwroot + "imgs/icones/mais.gif");
            botao.setAttribute("alt", "+");
            botao.setAttribute("title", "Abrir Grupo");
            break;
        case "-":
            botao.setAttribute("src", wwwroot + "imgs/icones/menos.gif");
            botao.setAttribute("alt", "-");
            botao.setAttribute("title", "Fechar Grupo");
            break;
        }
    };


    //
    //     Obtem a lista de entidades consultadas e atualiza os itens do seletor
    //
    this.atualizar_itens = function() {
        var xml = that.ajax.get_retorno("xml");
        var itens = xml.documentElement.getElementsByTagName("item");

        var ul = document.createElement("ul");
        definir_classe(ul, "hierarquia");
        for (var i = 0; i < itens.length; i++) {
            var item     = itens.item(i);
            var nome     = item.getAttribute("nome");
            var valor    = item.getAttribute("valor");
            var eh_grupo = parseInt(item.getAttribute("eh_grupo"));

            // Criar linha
            var linha     = document.createElement("li");
            linha.nome    = nome;
            linha.valor   = valor;
            linha.nivel   = that.nivel;
            linha.posicao = i;

            var linha_lb = document.createElement("span");
            definir_classe(linha_lb, "lb");
            linha_lb.appendChild(document.createTextNode(" "));

            var linha_l = document.createElement("span");
            if (i < (itens.length - 1)) {
                definir_classe(linha_l, "l");
            }

            var linha_valor = document.createElement("span");
            definir_classe(linha_valor, "valor");
            linha_l.appendChild(linha_valor);

            linha.appendChild(linha_lb);
            linha.appendChild(linha_l);

            {
                // Se e' um grupo
                if (eh_grupo == 1) {

                    // Criar botao de abrir/fechar
                    var botao = document.createElement("img");
                    definir_classe(botao, "bt_expandir");
                    botao.linha = linha;
                    botao.linha_valor = linha_valor;
                    that.set_botao(botao, "+");

                    // Eventos ao passar o mouse sobre o botao
                    botao.onmouseover = function() {
                        if (this.getAttribute("alt") == "+") {
                            that.set_status("Clique para abrir o Grupo");
                        } else {
                            that.set_status("Clique para fechar o Grupo");
                        }
                    };
                    botao.onmouseout = function() {
                        that.set_status("");
                    };

                    // Eventos ao clicar no botao
                    botao.onclick = function() {

                        // Buscar a lista do item a ser aberto
                        var ul = this.nextSibling;
                        while (ul && ul.nodeName.toLowerCase() != "ul") {
                            ul = ul.nextSibling;
                        }

                        // Se achou: mudar status (visivel/invisivel)
                        if (ul) {
                            ul.style.display = (ul.style.display == "none") ? "inherit" : "none";
                            that.set_botao(this, (ul.style.display == "none") ? "+" : "-");

                        // Se nao achou: consultar no web-service
                        } else {
                            that.pai = this.linha_valor;
                            that.nivel += 1;
                            var url = that.ws + "?link=" + that.url;
                            var l = this.linha;
                            while (l.nivel >= 0) {
                                url = url + "&a[" + l.nivel + "]=" + l.posicao;
                                l = l.parentNode.parentNode.parentNode.parentNode;
                            }

                            // Requisicao AJAX
                            that.set_status("Carregando...");
                            that.ajax.set_funcao(that.atualizar_itens);
                            that.ajax.consultar("GET", url, true, null);
                            that.set_botao(this, "-");
                        }
                        this.onmouseover();
                    };

                    // Adicionar botao
                    linha_valor.appendChild(botao);
                }

                // Se e' um item selecionavel, criar link
                if (valor) {
                    var link = document.createElement("a");
                    link.setAttribute("title", "Selecionar");
                    link.linha = linha;
                    var texto = document.createTextNode(valor + ": " + nome);

                    // Eventos ao passar o mouse sobre o link
                    link.onmouseover = function() {
                        that.set_status("Clique para selecionar o item");
                    };
                    link.onmouseout = function() {
                        that.set_status("");
                    };

                    // Evento ao clicar no link
                    link.onclick = function() {
                        that.input.value = this.linha.valor;
                        that.seletor.janela.fechar();
                        that.input.focus();
                        that.input.select();
                    };

                    // Adicionar link na linha
                    link.appendChild(texto);
                    linha_valor.appendChild(link);

                // Se e' apenas um grupo
                } else {
                    var strong = document.createElement("strong");
                    strong.linha = linha;
                    var texto = document.createTextNode(nome);
                    strong.appendChild(texto);
                    linha_valor.appendChild(strong);
                }
            }

            // Adicionar linha na lista
            ul.appendChild(linha);
        }

        // Adicionar lista na caixa de itens
        if (itens.length) {
            that.pai.appendChild(ul);
        }
        that.set_status("Itens carregados");
    };


    //
    //     Carrega os elementos na caixa
    //
    this.carregar_itens = function() {
        that.pai = that.seletor.itens;
        that.nivel = 0;
        that.ajax.set_funcao(that.atualizar_itens);
        that.ajax.exibir_carregando(that.seletor.itens);
        that.ajax.consultar("GET", that.ws + "?link=" + that.url, true, null);
    };


    //
    //     Cria uma caixa de selecao
    //
    this.criar_caixa = function(pos) {
    // Object pos: posicao onde deve ser criada a caixa (atributos x e y em px)
    //
        // Criar janela
        var janela = new class_janela();
        var caixa = janela.criar_janela("Selecione um item", pos.x - 200, pos.y - 100, 400);
        {
            // Criar espaco para listar os itens
            var itens = document.createElement("div");
            definir_classe(itens, "itens");

            // Criar espaco para o status
            var status = document.createElement("div");
            definir_classe(status, "status");
        }//caixa

        // Adicionar elementos na caixa
        caixa.appendChild(itens);
        caixa.appendChild(status);

        // Definir o seletor da classe
        that.seletor = {
            janela:janela,
            itens:itens,
            status:status
        };
    };


    //
    //     Abre um seletor
    //
    this.link.onclick = function abrir_seletor(e) {
    // Event e: evento ao clicar sobre o link
    //
        e = e ? e : window.event;
        e.returnValue = false;
        if (!that.ajax.xmlhttp) {
            var l = that.ws + "?link=" + that.url + "&input=" + that.input.id;
            window.open(l, "Busca", "toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=yes, width=500, height=400");
            return false;
        }

        // Criar a caixa proximo a posicao do mouse e adiciona-la no documento
        if (that.seletor == null) {
            var pos = get_posicao_mouse(e);
            that.criar_caixa(pos);
            that.carregar_itens();
        }

        // Adicionar a caixa no documento
        that.seletor.janela.abrir(document.getElementsByTagName("body").item(0));

        return false;
    };
}


//
//     Classe seletor de datas
//
function class_calendario(div) {
// DIV div: elemento que armazena uma linha com um campo de data
//
    var that = this;
    this.id = class_calendario.instancias.length;
    class_calendario.instancias[this.id] = this;

    this.div_form   = div;
    this.seletor    = { janela:null, area_calendario:null };
    this.link       = null;
    this.mes        = 0;
    this.ano        = 0;
    this.min_ano    = 0;
    this.max_ano    = 0;
    this.pode_vazio = false;


    //
    //     Obtem o dia, mes ou ano (0, 1 ou 2) selecionado no div
    //
    this.get = function(item) {
    // Int item: codigo para obter um valor (0 = dia, 1 = mes, 2 = ano)
    //
        switch (item) {
        case 0:
        case 1:
            return that.div_form.getElementsByTagName("select").item(item).value;
        case 2:
            var elementos = that.div_form.getElementsByTagName("select");
            if (elementos.length == 3) {
                return elementos.item(2).value;
            }
            return that.div_form.getElementsByTagName("input").item(0).value;
        }
        return false;
    };
  
  
    //
    //     Obtem valor minimo do campo de ano
    //
    this.get_min_ano = function() {
        var elementos = that.div_form.getElementsByTagName("select");
        if (elementos.length != 3) {
            return 0;
        }
        var options = elementos.item(2).getElementsByTagName("option");
        if (that.pode_vazio) {
            return options.item(1).value;
        }
        return options.item(0).value;
    };


    //
    //    Obtem o valor maximo do campo de ano
    //
    this.get_max_ano = function() {
        var elementos = that.div_form.getElementsByTagName("select");
        if (elementos.length != 3) {
            var hoje = new Date();
            return hoje.getFullYear() + 500;
        }
        var options = elementos.item(2).getElementsByTagName("option");
        return options.item(options.length - 1).value;
    };
  

    //
    //     Define os eventos sobre uma celula que armazena um dia no calendario
    //
    this.definir_dia = function(td, dia) {
    // TD td: celula da tabela que contem o dia
    // Int dia: numero do dia que a celula contem
    //
        td.style.cursor = "pointer";

        // Ao passar o mouse sobre um dia
        td.onmouseover = function() {
            this.style.outline = "1px outset #FFFFFF";
            this.style.backgroundColor = "#FFFFFF";
        };

        // Ao tirar o mouse de um dia
        td.onmouseout = function() {
            this.style.outline = "none";
            this.style.backgroundColor = "transparent";
        };

        // Ao clicar em um dia
        td.onclick = function() {
            this.style.outline = "none";
            this.style.backgroundColor = "transparent";

            var elementos = that.div_form.getElementsByTagName("select");
            elementos.item(0).value = dia;
            elementos.item(1).value = that.mes + 1;
            if (elementos.length == 3) {
                elementos.item(2).value = that.ano;
            } else {
                that.div_form.getElementsByTagName("input").item(0).value = that.ano;
            }
            that.seletor.janela.fechar();
            that.div_form.getElementsByTagName("select").item(0).focus();
        };
    };


    //
    //     Define o mes e ano do calendario de acordo com os dados do formulario
    //
    this.set_mes_ano = function() {
        var hoje = new Date();
        var mes = hoje.getMonth();
        var ano = hoje.getFullYear();

        if (that.get(1) == 0) {
            that.mes = mes;
        } else {
            that.mes = that.get(1) - 1;
        }
        if (that.get(2) == 0) {
            that.ano = ano;
        } else {
            that.ano = that.get(2);
        }
        that.min_ano = that.get_min_ano();
        that.max_ano = that.get_max_ano();
    };


    //
    //     Cria um seletor de data
    //
    this.criar_caixa = function(pos) {
    // Object pos: posicao da caixa de data (com os atributos x e y em px)
    //
        var janela = new class_janela();
        var caixa = janela.criar_janela("Selecione uma data", pos.x - 200, pos.y - 100, 250, "auto");

        // Criar area para o calendario
        that.seletor.area_calendario = document.createElement("div");
        caixa.appendChild(that.seletor.area_calendario);

        // Criar o calendario
        that.atualizar_calendario(that.mes, that.ano);

        that.seletor.janela = janela;
    };
  
  
    //
    //     Atualiza o calendario para alguma data
    //
    this.atualizar_calendario = function() {
        var dias_semana = ["Dom", "Seg", "Ter", "Qua", "Qui", "Sex", "Sáb"];
        var meses = ["Janeiro", "Fevereiro", "Março", "Abril", "Maio", "Junho",
                     "Julho", "Agosto", "Setembro", "Outubro", "Novembro", "Dezembro"];

        var primeiro_dia = new Date(that.ano, that.mes, 1);
        var ultimo_dia = new Date(that.ano, that.mes + 1, 0);

        var cal = document.createElement("table");
        definir_classe(cal, "calendario");
        cal.style.margin = "3px auto 5px auto";
        {
            var thead = document.createElement("thead");
            var tbody = document.createElement("tbody");
        }//cal
        cal.appendChild(thead);
        cal.appendChild(tbody);

        // Preencher o thead
        var linha = document.createElement("tr");

        // Seta para esquerda
        var th = document.createElement("th");
        var a = document.createElement("a");
        definir_classe(a, "seta");
        var seta = document.createTextNode("←");
        a.appendChild(seta);
        th.appendChild(a);
        linha.appendChild(th);

        // Evento ao clicar na seta a esquerda
        th.onclick = function() {
            that.mes--;
            if (that.mes == -1) {
                that.mes = 11;
                if (that.ano > that.min_ano) {
                    that.ano--;
                } else {
                    that.ano = that.max_ano;
                }
            }
            that.atualizar_calendario();
        };

        // Titulo com Mes e ano
        if (msie) {
            var th = document.createElement("<th colspan='5'>");
        } else {
            var th = document.createElement("th");
            th.setAttribute("colspan", 5);
        }
        definir_classe(th, "titulo_calendario");
        var texto = document.createTextNode(meses[that.mes] + "/" + that.ano);
        th.appendChild(texto);
        linha.appendChild(th);

        // Seta para direita
        var th = document.createElement("th");
        var a = document.createElement("a");
        definir_classe(a, "seta");
        var seta = document.createTextNode("→");
        a.appendChild(seta);
        th.appendChild(a);
        linha.appendChild(th);

        // Evento ao clicar na seta a direita
        th.onclick = function() {
            that.mes++;
            if (that.mes == 12) {
                that.mes = 0;
                if (that.ano < that.max_ano) {
                    that.ano++;
                } else {
                    that.ano = that.min_ano;
                }
            }
            that.atualizar_calendario();
        };

        thead.appendChild(linha);

        // Dias da semana
        linha = document.createElement("tr");
        var i = 0;
        for (i = 0; i < 7; i++) {
            var th = document.createElement("th");
            th.style.width = "2em";
            var texto = document.createTextNode(dias_semana[i]);
            th.appendChild(texto);
            linha.appendChild(th);
        }
        thead.appendChild(linha);

        // Preencher o tbody

        // Primeira linha
        linha = document.createElement("tr");
        for (i = 1; i <= primeiro_dia.getDay(); i++) {
            var td = document.createElement("td");
            linha.appendChild(td);
        }
        for (i = 1; i <= 7 - primeiro_dia.getDay(); i++) {
            var td = document.createElement("td");
            var texto = document.createTextNode(i);
            td.appendChild(texto);
            linha.appendChild(td);
            that.definir_dia(td, i);
        }
        tbody.appendChild(linha);

        // Proximas linhas
        while (i <= ultimo_dia.getDate()) {
            var linha = document.createElement("tr");
            for (var s = 0; s <= 6; s++) {
                var td = document.createElement("td");
                if (i <= ultimo_dia.getDate()) {
                    var texto = document.createTextNode(i);
                    td.appendChild(texto);
                    that.definir_dia(td, i);
                }
                linha.appendChild(td);
                i++;
            }
            tbody.appendChild(linha);
        }

        that.seletor.area_calendario.style.textAlign = "center";
        that.seletor.area_calendario.style.border = "1px solid #CCCCCC";

        limpar(that.seletor.area_calendario);
        that.seletor.area_calendario.appendChild(cal);

        // Links de Atalho
        var div_links = document.createElement("div");
        div_links.style.fontSize = 'small';
        var atalhos = document.createTextNode("Atalhos: ");
        div_links.appendChild(atalhos);

        // Link para o dia de hoje
        var hoje = document.createElement("a");
        hoje.appendChild(document.createTextNode("Hoje"));
        hoje.onclick = function () {
            var hoje = new Date();
            var elementos = that.div_form.getElementsByTagName("select");
            elementos.item(0).value = hoje.getDate();
            elementos.item(1).value = hoje.getMonth() + 1;
            if (elementos.length == 3) {
                elementos.item(2).value = hoje.getFullYear();
            } else {
                that.div_form.getElementsByTagName("input").item(0).value = hoje.getFullYear();
            }
            that.seletor.janela.fechar();
            that.div_form.getElementsByTagName("select").item(0).focus();
        };
        div_links.appendChild(hoje);

        // Link para o dia nenhum
        if (that.pode_vazio) {
            var nenhum = document.createElement("a");
            nenhum.appendChild(document.createTextNode("Nenhum"));
            nenhum.onclick = function () {
                var elementos = that.div_form.getElementsByTagName("select");
                elementos.item(0).value = 0;
                elementos.item(1).value = 0;
                if (elementos.length == 3) {
                    elementos.item(2).value = 0;
                } else {
                    that.div_form.getElementsByTagName("input").item(0).value = 0;
                }
                that.seletor.janela.fechar();
                that.div_form.getElementsByTagName("select").item(0).focus();
            };
            div_links.appendChild(document.createTextNode(" | "));
            div_links.appendChild(nenhum);
        }

        that.seletor.area_calendario.appendChild(div_links);
    };


    //
    //     Abre a janela de selecao de data
    //
    this.abrir_calendario = function(e) {
    // Event e: evento ao abrir o calendario
    //
        that.set_mes_ano();
        if (that.seletor.janela == null) {
            var pos = get_posicao_mouse(e);
            that.criar_caixa(pos);
        } else {
            that.atualizar_calendario();
        }
        that.seletor.janela.abrir(document.getElementsByTagName("body").item(0));
    };


    //
    //     Adiciona um link ao div
    //
    this.adicionar_link = function() {

        // Incluir CSS de calendario como processing instruction
        var i = 0;
        var incluir_estilo = true;
        var regex = new RegExp(/\/calendario.css/);
        while (i < document.childNodes.length) {
            var c = document.childNodes.item(i);
            if (c.nodeType == 7 && c.target.toLowerCase() == 'xml-stylesheet') {
                if (regex.test(c.data)) {
                    incluir_estilo = false;
                    break;
                }
            }
            i++;
        }
        if (incluir_estilo) {
            try {
                var estilo = document.createProcessingInstruction('xml-stylesheet', 'href="' + wwwroot + 'layout/calendario.css" type="text/css" media="screen" charset="utf-8"');
                document.insertBefore(estilo, document.firstChild);
            } catch (e) {
                var estilo = document.createElement("link");
                estilo.setAttribute("rel", "stylesheet");
                estilo.setAttribute("type", "text/css");
                estilo.setAttribute("charset", "utf-8");
                estilo.setAttribute("media", "screen");
                estilo.setAttribute("href", wwwroot + "layout/calendario.css");
                document.getElementsByTagName("head").item(0).appendChild(estilo);
            }
        }

        // Criar imagem de um calendario
        var s = "Selecionar pelo Calendário";
        that.link = document.createElement("img");
        that.link.setAttribute("src", wwwroot + "imgs/icones/calendario.gif");
        that.link.setAttribute("alt", s);
        that.link.setAttribute("title", s);
        that.link.style.cursor = "pointer";
        that.link.onclick = that.abrir_calendario;

        var antigo = that.div_form.getElementsByTagName("img");
        if (antigo.length > 0) {
            that.div_form.removeChild(antigo.item(0));
        }

        that.div_form.appendChild(that.link);

        that.pode_vazio = that.div_form.getElementsByTagName("select").item(0).getElementsByTagName("option").item(0).value == 0;
        if (that.pode_vazio) {
            var anular = document.createElement("img");
            var s = "Nenhuma data";
            anular.setAttribute("src", wwwroot + "imgs/icones/cancelar.gif");
            anular.setAttribute("alt", s);
            anular.setAttribute("title", s);
            anular.style.cursor = "pointer";
            anular.style.marginLeft = "5px";
            anular.onclick = function() {
                var elementos = that.div_form.getElementsByTagName("select");
                elementos.item(0).value = 0;
                elementos.item(1).value = 0;
                if (elementos.length == 3) {
                    elementos.item(2).value = 0;
                } else {
                    that.div_form.getElementsByTagName("input").item(0).value = 0;
                }
            };
            that.div_form.appendChild(anular);
        }
    };

    this.adicionar_link();
}


//
//     Classe popup
//
function class_popup(link) {
// A link: link que deseja-se transformar em link para popup
//
    var that = this;
    this.id = class_popup.instancias.length;
    class_popup.instancias[this.id] = this;

    this.link          = link;
    this.url           = null;
    this.janela        = null;
    this.area_conteudo = null;
    this.ajax          = new class_ajax();


    //
    //     Cria uma caixa de popup
    //
    this.criar_caixa = function(pos) {
    // Object pos: posicao para criar a caixa de popup (com os atributos x e y em px)
    //
        // Criar janela
        var janela = new class_janela();
        var titulo = that.link.text.substr(0, 40);
        if (titulo != that.link.text) {
            titulo = titulo + "...";
        }
        var caixa = janela.criar_janela(titulo, pos.x - 450, pos.y - 300, 450, 300);
        {
            // Criar espaco para o conteudo
            that.area_conteudo = document.createElement("div");
            definir_classe(that.area_conteudo, "conteudo");
        }
        caixa.appendChild(that.area_conteudo);
        that.janela = janela;
    };
  
  
    //
    //     Atualiza o conteudo da pagina no popup
    //
    this.atualizar_conteudo = function() {
        limpar(that.area_conteudo);
        var xml = that.ajax.get_retorno("xml");
        var div = xml.getElementById("conteudo_popup");
        div.style.width = "auto";

        // Obter elementos filho do popup
        var filhos = div.getElementsByTagName("*");
        for (j = 0; j < filhos.length; j++) {
            filhos.item(j).style.width = "auto";
            filhos.item(j).style.margin = "0";
        }

        // Tentar carregar o conteudo do DIV para dentro do popup
        try {
            document.importNode(div, true);
            that.area_conteudo.appendChild(div);

        // Ou trocar o innerHTML de um pelo outro
        } catch (e) {
            that.area_conteudo.innerHTML = div.innerHTML;
        }
    };
  
  
    //
    //     Carrega o conteudo da pagina para o popup
    //
    this.carregar_conteudo = function() {
        var url = (that.url.indexOf("?") != -1) ? that.url + "&xml=1" : that.url + "?xml=1";
        that.ajax.set_funcao(that.atualizar_conteudo);
        that.ajax.exibir_carregando(that.area_conteudo);
        that.ajax.consultar("GET", url, true, null);
    };
  
  
    //
    //     Abre um popup
    //
    this.abrir_popup = function(e) {
    // Event e: evento para abrir o popup
    //
        if (that.janela == null) {
            var pos = get_posicao_mouse(e);
            that.criar_caixa(pos);
            that.carregar_conteudo();
        }
        that.janela.abrir(document.getElementsByTagName("body").item(0));
        return false;
    };
  
  
    //
    //     Define o link como popup
    //
    this.definir_link = function() {
        if ((!that.ajax.xmlhttp) || (that.url != null)) { return; }
        that.url = that.link.getAttribute("href");
        that.link.onclick = that.abrir_popup;
    };
  
    this.definir_link();
}
