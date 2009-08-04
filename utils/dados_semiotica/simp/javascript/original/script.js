//
// SIMP
// Descricao: JavaScript utilizado pelas paginas (AJAX)
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.ufla.br
// Versao: 1.0.1.5
// Data: 12/06/2007
// Modificado: 29/07/2009
// TODO: Funcionar no IE(ca)
// License: LICENSE.TXT
// Copyright (C) 2007  Rubens Takiguti Ribeiro
//


/// Metodos Auxiliares


//
//     Insere um elemento apos outro
//
function inserir_apos(pai, elemento, referencia) {
// Object pai: elemento pai
// Object elemento: elemento a ser inserido
// Object referencia: elemento referencia
//
    if (referencia.nextSibling) {
        pai.insertBefore(elemento, referencia.nextSibling);
    } else {
        pai.appendChild(elemento);
    }
}


//
//     Insere um elemento no inicio de um container
//
function inserir_inicio(pai, elemento) {
// Object pai: elemento pai
// Object elemento: elemento a ser colocado no inicio do elemento pai
//
    if (pai.hasChildNodes()) {
        pai.insertBefore(elemento, pai.firstChild);
    } else {
        pai.appendChild(elemento);
    }
}


/// Funcoes extras para evitar erros de XML
function and(a, b) { return a && b; }
function  lt(a, b) { return a <  b; }
function  le(a, b) { return a <= b; }
function  gt(a, b) { return a >  b; }
function  ge(a, b) { return a >= b; }


/// Operacoes Iniciais
window.onload = iniciar;
window.onkeydown = checar_tecla;


/// Variaveis globais
{
    var url_atual      = "";
    var foco           = true;
    var timer_foco     = false;
    var timer_hora     = false;
    var input_busca    = false;
    var timer_busca    = false;
    var limite_tamanho = 250;

    // Lista de checkbox marcados ou desmarcados
    var checkbox_marcados  = new Array();

    // Lista de instancias de classes
    class_tremer.instancias = new Array();
    class_piscar.instancias = new Array();
    class_fechar.instancias = new Array();
}


//
//     Instrucoes iniciais
//
function iniciar(foco) {
// Bool foco: dar foco ao primeiro campo do primeiro formulario da pagina
//
    // Se ja estao ativados os timers
    if (timer_foco && timer_hora) {
        clearInterval(timer_foco);
        clearTimeout(timer_hora);
    }
    
    // Percorre os elementos do documento alterando-os quando desejado
    definir_atributos();

    // Se deseja colocar o foco no primeiro elemento do primeiro formulario
    if (foco) {
        timer_foco = window.setTimeout("set_foco();", 700);
    }
    timer_hora = window.setInterval("atualizar_hora();", 1000);
    //document.normalize();

    return true;
}


//
//     Checa a tecla clicada
//
function checar_tecla(e) {
// Event e: evento disparado pelo teclado
//
    var k = e.keyCode ? e.keyCode : e.which;
    switch (k) {
    case 116: // F5: Atualizar
        var url = (url_atual != "") ? url_atual : window.location.href;
        if (url.indexOf("xml=1") > -1) {
            var vt_url = url.split("?");
            url = vt_url.shift() + "?";
            var vt_param = vt_url[0].split("&");
            var parametros = new Array();
            for (var i = 0; i < vt_param.length; i++) {
                if (vt_param[i] != "xml=1") {
                    parametros.push(vt_param[i]);
                }
            }
            url = url + parametros.join("&");
        }
        window.location.replace(url);
        return false;
    }
    return true;
}


//
//     Classe para requisicoes HTTP remotas
//
function class_ajax() {
    var that = this;

    this.flag_erro  = true;  // Bool Flag que indica se exibe mensagem de erro ou nao
    this.xmlhttp    = null;  // Object XMLHttpRequest
    this.url        = null;  // String URL de destino
    this.usuario    = null;  // String nome do usuario p/ autenticacao HTTP
    this.senha      = null;  // String senha do usuario p/ autenticacao HTTP
    this.funcao     = null;  // String funcao a ser chamada apos carregamento
    this.carregando = null;  // Object mensagem de carregando


    //
    //     Define o usuario e a senha
    //
    this.set_credencial = function(usuario, senha) {
    // String usuario: nome do usuario para acesso autenticado
    // String senha: senha do usuario para acesso autenticado
    //
        that.usuario = usuario;
        that.senha   = senha;
    };


    //
    //     Define o metodo usado apos a requisicao ser chamada
    //
    this.set_funcao = function(funcao) {
    // Function funcao: funcao a ser chamada apos o carregamento assincrono dos dados
    //
        that.funcao = funcao;
    };


    //
    //     Realiza uma requisicao HTTP remota
    //
    this.consultar = function(metodo, url, assincrona, dados, flag_erro) {
    // String metodo: metodo utilizado na requisicao (POST ou GET)
    // String url: endereco de destino dos dados
    // Bool assincrona: requisicao assincrona (true) ou sincrona (false)
    // String dados: dados formatados de forma x-www-form-urlencoded
    // Bool flag_erro: flag que indica se deve exibir os erros ou nao
    //
        that.url = url;
        if (!that.xmlhttp) {
            window.location.replace(url);
            return false;
        }
        if (!dados) { dados = null; }
        if (flag_erro != undefined) { that.flag_erro = flag_erro; }

        try {
            var url_requisicao = that.url + (that.url.indexOf("?") >= 0 ? "&" : "?") + "xml=1";
            if (that.usuario && that.senha) {
                that.xmlhttp.open(metodo.toUpperCase(), url_requisicao, assincrona, that.usuario, that.senha);
            } else {
                that.xmlhttp.open(metodo.toUpperCase(), url_requisicao, assincrona);
            }
            that.xmlhttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            that.xmlhttp.onreadystatechange = this.processar;
            that.xmlhttp.send(dados);
        } catch (e) {
            window.alert("Erro ao consultar site: " + e.message);
            return false;
        }
        return true;
    };
  
  
    //
    //     Cria um DIV "Carregando..."
    //
    this.criar_carregando = function() {
        if (that.div_carregando) {
            return that.div_carregando.cloneNode(1);
        }

        // Criar DIV
        var div = document.createElement("div");
        div.style.visibility = 'hidden';
        definir_classe(div, "carregando");
        {
            // Criar IMG
            var img = document.createElement("img");
            img.setAttribute("src", wwwroot + "imgs/icones/carregando.gif");

            // Criar Texto
            var texto = document.createTextNode("Carregando...");    
        }
        if (img != undefined) {
            div.appendChild(img);
        }
        div.appendChild(texto);

        div.onclick = that.retirar_carregando;

        // Armazenar um backup na memoria
        that.div_carregando = div.cloneNode(1);

        return div;
    };


    //
    //     Coloca um "Carregando..." em algum elemento durante o carregamento
    //
    this.exibir_carregando = function(elemento) {
    // Object elemento: elemento que vai conter o elemento
    //
        var div = this.criar_carregando();

        if (elemento) {
            if (typeof elemento != "number") {
                tirar_visibilidade(elemento);
                div.style.position = "absolute";
                div.style.left = "inherit";
                div.style.top = "inherit";
                inserir_inicio(elemento, div);
            } else {
                document.getElementsByTagName("body").item(0).appendChild(div);
            }
        }

        div.style.visibility = 'visible';
        that.carregando = div;
    };
  
  
    //
    //     Retira o "Carregando..." da pagina
    //
    this.retirar_carregando = function() {
        if (that.carregando == null || that.carregando.parentNode == null) { return; }
        that.carregando.parentNode.removeChild(that.carregando);
        delete that.carregando;
        that.carregando = null;
    };


    //
    //     Processa um resultado
    //
    this.processar = function() {
        if (that.xmlhttp.readyState == 4) {
            switch (that.xmlhttp.status) {
            case 200:
                that.funcao();
                break;
            case 503:
                if (that.flag_erro) {
                    window.alert("O sistema está operando acima do esperado.\nRecomenda-se sair e voltar mais tarde.");
                }
                break;
            default:
                if (that.flag_erro) {
                    window.alert("Erro " + that.xmlhttp.status + ": " + that.xmlhttp.statusText);
                }
                break;
            }
            if (that.carregando) {
                that.retirar_carregando();
            }
        }
    };


    //
    //     Cria uma instancia de XMLHttpRequest
    //
    this.criar_xmlhttp = function() {
        if (window.XMLHttpRequest) {
            return new XMLHttpRequest();
        } else if (window.ActiveXObject) {
            var versoes = ["MSXML2.XMLHttp",
                           "MSXML.XMLHttp",
                           "Microsoft.XMLHttp"];
            for (var i = 0; i < versoes.length; i++) {
                try {
                    return new ActiveXObject(versoes[i]);
                } catch (e) {
                    // Tentar outro
                }
            }
        }
        return false;
    };


    //
    //     Obtem o retorno do processamento
    //
    this.get_retorno = function(tipo, opcao) {
    // String tipo: tipo de retorno desejado (xml, text, headers ou header)
    // String opcao: opcao desejada em caso de escolha do tipo header
    //
        switch (tipo.toLowerCase()) {
        case "xml":
            return that.xmlhttp.responseXML;
        case "text":
            return that.xmlhttp.responseText;
        case "headers":
            return that.xmlhttp.getAllResponseHeaders();
        case "header":
            return that.xmlhttp.getResponseHeader(opcao);
        }
        return false;
    };


    // Criar o XMLHttpRequest
    this.xmlhttp = this.criar_xmlhttp();
}


//
//     Funcao para enviar os dados de um formulario
//
function submeter(form, carregando) {
// Form form: formulario a ser submetido
// Bool carregando: flag que indica se sera colocado um carregando ou nao na pagina
//
    if (msie) { return true; } // TODO: funcionar no IE
    var centro = document.getElementById("centro");
    var ajax = new class_ajax();

    if (!centro || !ajax.xmlhttp) { return true; }
    
    var url = form.getAttribute("action");
    var dados = get_dados(form);
    var metodo = form.getAttribute("method");

    if (carregando != undefined) {
        ajax.exibir_carregando(carregando);
    }

    if (metodo.toLowerCase() == "get") {
        url = url + (url.indexOf("?") >= 0 ? "&" : "?") + dados;
    }

    ajax.set_funcao(atualizar);
    ajax.consultar(metodo, url, true, dados);
    return false;
}


//
//     Funcao para carregar um link da pagina
//
function carregar(link, carregando, setar_foco) {
// A link: link que sera transformado em uma requisicao Ajax
// Bool carregando: flag indicando se deve aparecer um carregando na pagina ou nao
// Bool setar_foco: flag indicando se deve ser definido o foco ao abrir a pagina ou nao
//
    if (msie) { return true; } // TODO: funcionar no IE
    setar_foco = (setar_foco == undefined) ? false : setar_foco;
    var url = link.getAttribute("href");
    var centro = document.getElementById("centro");

    // Criar objeto Ajax
    var ajax = new class_ajax();
    if (!centro || !ajax.xmlhttp) {
        window.location.replace(url);
        return true;
    }
    if (carregando != 0) {
        ajax.exibir_carregando(carregando);
    }

    // Montar endereco absoluto (necessario para requisicoes Ajax)
    if (url.indexOf('http:') < 0) {
        url = wwwroot + url;
    }

    // Realizar a requisicao remota
    ajax.foco = setar_foco;
    ajax.set_funcao(atualizar);
    ajax.consultar("GET", url, true, null);
    return false;
}


//
//     Recupera os dados do formulario retornando na forma x-www-form-urlencoded
//
function get_dados(form) {
// Form form: formulario que deseja-se obter os dados de entrada pelo usuario
//
    var param = new Array();
    for (var i = 0; i < form.elements.length; i++) {
        var elemento = form.elements[i];
        if (elemento.name == undefined || elemento.disabled) { continue; }
        switch (elemento.getAttribute("type")) {
        case "checkbox":
        case "radio":
            if (elemento.checked) {
                var parametro = elemento.name + "=" + encodeURIComponent(elemento.value);
                param.push(parametro);
            }
            break;
        case "submit":
            if (elemento.getAttribute("clicou") == 1) {
                var parametro = elemento.name + "=" + encodeURIComponent(elemento.value);
                param.push(parametro);
            }
            break;
        default:
            var parametro = elemento.name + "=" + encodeURIComponent(elemento.value);
            param.push(parametro);
            break;
        }
    }
    return param.join("&");
}


//
//     Clona um campo
//
function clonar(obj, limite) {
// Object obj: objeto a ser clonado
// Int limite: numero limite de clones
//

    //
    //     Atualiza os atributos id e for com um sufixo
    //
    this.atualizar_clone = function(obj, sufixo) {
    // Object obj: objeto a ter seus atributos atualizados
    // String sufixo: sufixo a ser adicionado nos campos ID e FOR
    //
        if (obj.nodeType != 1) { return; }
        obj.def = 0;
        try {
            if (obj.hasAttribute("id")) { obj.id = obj.id + sufixo; }
            if (obj.hasAttribute("for")) { obj.setAttribute("for", obj.getAttribute("for") + sufixo); }
        } catch (e) {
            if (obj.getAttribute("id")) { obj.setAttribute("id", obj.getAttribute("id") + sufixo); }
            if (obj.htmlFor) { obj.htmlFor = obj.htmlFor + sufixo; }
        }
        if (obj.hasChildNodes()) {
            var c = obj.firstChild;
            while (c != null) {
                that.atualizar_clone(c, sufixo);
                c = c.nextSibling;
            }
        }
    };


    //
    //     Atualizar labels
    //
    this.atualizar_labels = function(obj) {
    // Object obj: objeto a ter seus labels atualizados
    //
        var padrao = that.get_label(obj);
        var c = obj.nextSibling;

        // O primeiro clone eh o segundo
        var i = 2;

        while (c != null && c.clonado != undefined) {
            var label = that.get_label(c);
            var novo_label = document.createTextNode(padrao.nodeValue + " " + i);
            label.parentNode.replaceChild(novo_label, label);
            c = c.nextSibling;
            i++;
        }
    };


    //
    //     Obtem o label de um objeto
    //
    this.get_label = function(obj) {
    // Object obj: objeto a ser investigado (recursivamente) em busca do primeiro label
    //
        if (obj.nodeType == 3 && get_tag(obj.parentNode) == "label") {
            return obj;
        }
        var f = obj.firstChild;
        while (f != null) {
            var l = that.get_label(f);
            if (l != null) { return l; }
            f = f.nextSibling;
        }
        return null;
    };


    //
    //     Obtem o ultimo clone de um elemento e sua posicao
    //
    this.get_ultimo = function(obj) {
    // Object obj: objeto a ser investigado em busca do ultimo clone
    //
        var u = obj;
        var c = obj.nextSibling;
        var i = 1;
        while (c != null && c.clonado != undefined) {
            u = c;
            c = c.nextSibling;
            i++;
        }
        return { obj:u, pos:i };
    };


    // Atributos
    that = this;
    this.ultimo = this.get_ultimo(obj);
    if (!this.id) { this.id = 1; }
    this.id++;

    // Checar se estourou o limite
    if (limite != undefined && this.ultimo.pos >= limite) {
        window.alert("São permitidos no máximo " + limite + " elementos");
        return false;
    }

    // Gerar clone
    var obj_clone = obj.cloneNode(true);
    obj_clone.clonado = true;
    obj_clone.original = obj;
    this.atualizar_clone(obj_clone, this.ultimo.pos + 1);

    // Botao de remover clone
    if (obj_clone.appendChild) {
        var bt_remover = document.createElement("input");
        definir_classe(bt_remover, "botao");
        bt_remover.setAttribute("type", "button");
        bt_remover.setAttribute("value", "Remover");
        
        // Acao do botao
        bt_remover.onclick = function() {
            var clone = this.parentNode;
            var original = clone.original;
            clone.parentNode.removeChild(clone);
            that.atualizar_labels(original);
        };
        obj_clone.appendChild(bt_remover);
    }

    // Inserir clone
    var u = this.ultimo.obj;
    inserir_apos(u.parentNode, obj_clone, u);

    this.atualizar_labels(obj);
    iniciar();
    return true;
}


//
//     Funcao que atualiza o conteudo da pagina
//     TODO: funcionar no IE(ca)
//
function atualizar() {
    url_atual = this.url;

    // Recuperar dados
    var head   = document.getElementsByTagName("head").item(0);
    var nav    = document.getElementById("navegacao");
    var centro = document.getElementById("centro");

    var xml = this.get_retorno("xml");

    // Se a sessao expirou
    if (!xml || !xml.documentElement || xml.getElementById('pagina_login')) {
        window.location.replace(url_atual);
        return false;
    }

    // Atualizar Processing Instructions
    var i = 0;
    while (i < document.childNodes.length) {
        var c = document.childNodes.item(i);
        if ((c.nodeType == 7) || (c.nodeType == 8)) {
            document.removeChild(c);
        } else {
            i++;
        }
    }

    for (i = xml.childNodes.length - 1; i >= 0; i--) {
        var c = xml.childNodes.item(i);
        try {
            if (c.nodeType == 7) {
                if (c.sheet != null) {
                    var j = 0;
                    for (j = 0; j < c.sheet.media.length; j++) {
                        if (c.sheet.media.item(j) == "screen") {
                            var novo = document.importNode(c, true);
                            document.insertBefore(novo, document.firstChild);
                        }
                    }
                } else {
                    var novo = document.importNode(c, true);
                    document.insertBefore(novo, document.firstChild);
                }
            } else if (c.nodeType == 8) {
                var novo = document.importNode(c, true);
                document.appendChild(novo);
            }
        } catch (e) {
            // Ignorar
        }
    }

    // Atualizar HEAD
    var vhead2 = xml.documentElement.getElementsByTagName("head");
    if (vhead2) {
        var head2 = vhead2.item(0);
        var pai = head.parentNode;
        try {
            var novo = document.importNode(head2, true);
            pai.replaceChild(novo, head);
        } catch (e) {

            // Apagar tags
            var itens_head = head.childNodes;
            while (itens_head.length > 0) {
                head.removeChild(itens_head.item(0));
            }

            // Inserir novas tags
            var itens_head2 = head2.childNodes;
            for (i = 0; i < itens_head2.length; i++) {
                if (itens_head2.item(i).nodeType != 1) { continue; }
                var item = clonar_tag(itens_head2.item(i));
                try {
                    head.appendChild(item);
                } catch (e) {
                    alert("Erro: " + e.message + "\n\nRecomenda-se desabilitar o JavaScript.");
                }
            }
        }
    }

    // Atualizar barra de navegacao
    var nav2 = xml.getElementById("navegacao");
    if (nav2) {
        var pai = nav.parentNode;
        try {
            pai.replaceChild(nav2, nav);
        } catch (e) {
            try {
                nav.innerHTML = nav2.innerHTML;
            } catch (e) {
                window.alert("Erro ao atualizar barra de navegacao:\n" + e.message + "\n\nRecomenda-se desabilitar o Javascript");
            }
        }
    }

    // Atualizar centro
    var centro2 = xml.getElementById("centro");
    if (centro2) {
        var pai = centro.parentNode;
        try {
            var novo = document.importNode(centro2, true);
            pai.replaceChild(novo, centro);
        } catch (e) {
            try {
                centro.innerHTML = centro2.innerHTML;
            } catch (e) {
                window.alert("Erro ao atualizar o conteudo da pagina:\n" + e.message + "\n\nRecomenda-se desabilitar o Javascript");
            }
        }
    } else {
        var conteudo = xml.getElementById("conteudo");
        if (conteudo) {
            limpar(centro);
            centro.appendChild(conteudo);
        } else {
            var body2 = xml.documentElement.getElementsByTagName("body");
            if (body2) {
                limpar(centro);
                centro.appendChild(body2.item(0))
            } else {
                window.alert("Documento incorreto");
            }
        }
    }

    // Atualizar titulo
    document.title = document.getElementsByTagName("title").item(0).innerHTML;

    // Atualizar ID do body
    var id_body = xml.documentElement.getElementsByTagName("body").item(0).id;
    document.getElementsByTagName("body").item(0).id = id_body ? id_body : "";

    // Reiniciar documento
    iniciar(this.foco);
    return true;
}


//
//     Define atributos de objetos
//
function definir_atributos() {
    if (navegador == "firefox") {
        var objects = document.getElementsByTagName("object");
        if (objects && objects.length) {
            for (var i = 0; i < objects.length; i++) {
                object = objects.item(i);
                var pai = object.parentNode;
                c = object.cloneNode(true);
                document.importNode(c, true);
                pai.insertBefore(c, object);
                pai.removeChild(object);
            }
        }
    }

    var body = document.getElementsByTagName("body").item(0);
    var itens = body.getElementsByTagName("*");
    var tam = itens.length;

    var captcha = document.getElementById("captcha");
    if (captcha && !captcha.def && possui_classe(captcha.parentNode, "captcha_imagem")) {
    
        // Criar botao de mudar imagem
        try {
            var b = document.createElement("input");
            b.setAttribute("type", "button");
        } catch (e) {
            var b = document.createElement("<input type='button'/>");
        }
        b.setAttribute("value", "Mudar imagem");
        b.setAttribute("title", "Clique aqui em caso de dificuldade de leitura do texto da imagem");
        definir_classe(b, "botao");
        
        // Evento do botao
        b.onclick = function() {
            var img = this.parentNode.getElementsByTagName("img").item(0);
            var vt = img.getAttribute("src").split("?");
            img.setAttribute("src", vt[0] + "?c=" + Math.random());
        };
        captcha.parentNode.appendChild(b);
        captcha.def = 1;
    }

    for (var i = 0; i < tam; i++) {
        var item = itens.item(i);

        // Se o item ja foi definido
        if (item.def) { continue; }

        // Checar classe
        var tag = get_tag(item);
        switch (get_classe(item)) {
        case "drag":
            var item_movel = document.getElementById("drag_" + item.id);
            if (item_movel) {
                objeto_movel(item, item_movel);
            }
            item.def = 1;
            break;
        case "erro":
            if (tag == "div") {
                var obj_tremer = new class_tremer(item, 4);
                obj_tremer.tremer(1000);
            }
            break;
        case "aviso":
            if (tag == "div") {
                var obj_piscar = new class_piscar(item, 1, 0.1);
                obj_piscar.piscar();
            }
            break;
        case "relacionamento":
            if (tag == "a") {
                var seletor = new class_seletor(item);
                item.def = 1;
            }
            break;
        case "hierarquia":
            if (tag == "a") {
                var seletor = new class_hierarquia(item);
                item.def = 1;
            }
            break;
        case "data":
            if (tag == "div") {
                var seletor_data = new class_calendario(item);
                item.def = 1;
            }
            break;
        case "popup":
            if (tag == "a") {
                var popup = new class_popup(item);
                item.def = 1;
            }
            break;
        case "botao":
            if (item.getAttribute("type") == "submit") {
                item.setAttribute("clicou", "0");
                item.onclick = function() {
                    this.setAttribute("clicou", "1");
                    return true;
                };
                var form = item.parentNode;
                while ((get_tag(form) != "form") && form.parentNode) {
                    form = form.parentNode;
                }
                if (get_tag(form) == "form") {
                    var novo = function() {
                        var vt = this.getElementsByTagName('input');
                        var i = 0;
                        for (i = 0; i < vt.length; i++) {
                            var s = vt.item(i);
                            if (s.getAttribute("clicou") == 1) {
                                var h = document.createElement("input");
                                h.setAttribute("type", "hidden");
                                h.setAttribute("name", s.getAttribute("name"));
                                h.setAttribute("value", s.getAttribute("value"));
                                this.appendChild(h);
                                s.setAttribute("disabled", "disabled");
                                if (s.getAttribute("value").length >= 7) {
                                    s.setAttribute("antes", s.getAttribute("value"));
                                    s.setAttribute("value", "Aguarde");
                                    s.style.backgroundImage = "url(" + wwwroot + "imgs/icones/carregando_form.gif)";
                                    s.style.backgroundRepeat = "no-repeat";
                                }
                            }
                        }
                    };
                    if (!form.onsubmit) {
                        form.onsubmit = function() { return true; };
                    }
                    form.onsubmit = juntar_funcoes(form.onsubmit, novo);
                }
            }
            break;
        case "botao_clonar":
            var codigo = "var elemento = " + item.getAttribute("alt") + ";";
            eval(codigo);
            if (elemento) {
                elemento.style.display = "block";
                try {
                    item.setAttribute("type", "button");
                    item.removeAttribute("alt");
                    definir_classe(item, "botao");
                } catch (e) {
                    var novo_item = document.createElement("<input type='button'/>");
                    novo_item.setAttribute("value", item.getAttribute("value"));
                    novo_item.onclick = item.onclick;
                    definir_classe(novo_item, "botao");
                    item.parentNode.replaceChild(novo_item, item);
                }
            }
            item.def = 1;
            break;
        }

        switch (item.getAttribute("rel")) {
        case "blank":
            item.setAttribute("target", "_blank");
            item.def = 1;
            break;
        case "checar":
            if (tag == "a") {
                checar_link(item);
                item.def = 1;
            }
            break;
        }
    }

    // Percorrer formularios
    for (var i = 0; i < document.forms.length; i++) {
        var form = document.forms[i];
        var fields = form.getElementsByTagName("fieldset");
        for (var j = 0; j < fields.length; j++) {
            atualizar_fieldset(fields[j]);
        }
        var campos = form.getElementsByTagName("*");
        for (var j = 0; j < campos.length; j++) {
            atualizar_campo(campos.item(j));
        }

        var metas = form.getElementsByTagName("meta");
        for (var j = 0; j < metas.length; j++) {
            var meta = metas.item(j);
            var campo = document.getElementById(meta.getAttribute("name"));
            if (campo != null) {
                atualizar_info_campo(form, campo, meta.getAttribute("content"));
            }
        }
    }

    // Voltar ao topo
    var topo = document.getElementById("voltar_topo");
    if (topo) {
        topo.onclick = function(e) {
            e = e ? e : window.event;
            e.returnValue = false;
            window.scroll(0, 0);
            return false;
        };
    }

    // Menu flutuante
    var menu = document.getElementById("menu");
    if (menu) {
        var strongs = menu.getElementsByTagName("strong");
        for (var i = 0; i < strongs.length; i++) {
            objeto_movel(strongs.item(i), menu);
        }
    }
}


//
//     Atualiza um fieldset incluindo botao de expandir
//
function atualizar_fieldset(fieldset) {
//
//
    var legend = fieldset.getElementsByTagName("legend").item(0);

    botao = document.createElement("img");
    botao.setAttribute("src", wwwroot + "imgs/icones/menos.gif");
    botao.setAttribute("alt", "Abrir/Fechar");
    botao.setAttribute("title", "Abrir/Fechar");
    botao.style.backgroundColor = "#FFFFFF";
    botao.style.cursor = "pointer";
    botao.style.marginRight = ".5em";

    botao.onclick = function() {
        var f = this.parentNode.parentNode;
        var c = f.firstChild;
        var fechou = false;
        while (c != null) {
            if (c.nodeType == 1 && get_tag(c) != "legend") {
                if (c.getAttribute("display_anterior") != null) {
                    c.style.display = c.getAttribute("display_anterior");
                    c.removeAttribute("display_anterior");
                } else {
                    c.setAttribute("display_anterior", c.style.display);
                    c.style.display = "none";
                    fechou = true;
                }
            }
            c = c.nextSibling;
        }
        if (fechou) {
            this.setAttribute("src", wwwroot + "imgs/icones/mais.gif");
        } else {
            this.setAttribute("src", wwwroot + "imgs/icones/menos.gif");
        }
    };

    espaco = document.createTextNode(" ");

    legend.insertBefore(botao, legend.firstChild);
}


//
//     Atualiza um elemento do formulario
//
function atualizar_campo(campo) {
// Object campo: campo do formularo
//
    switch (campo.nodeName) {
    case "input":
        if (campo.getAttribute("type") == "password") {
            var aviso = document.createElement("span");
            aviso.appendChild(document.createTextNode("Caps Lock"));
            aviso.style.fontSize = "0.8em";
            aviso.style.display = "none";

            campo.aviso = aviso;
            campo.checado = false;
            inserir_apos(campo.parentNode, aviso, campo);
            campo.onkeypress = function(e) {
                var k = e.keyCode ? e.keyCode : e.which;

                // Se precionou Caps Lock
                if (this.checado && k == 20) {
                    this.aviso.style.display = this.aviso.style.display == "inline" ? "none" : "inline";
                }

                this.checado = true;
                var shift = e.shiftKey;

                // Obs: ç = 231 / Ç 199

                // Se obteve uma tecla maiuscula
                if (entre(k, 65, 90) || k == 199) {

                    // se shift: nao usou caps lock
                    this.aviso.style.display = shift ? "none" : "inline";
                }

                // Se obteve uma tecla minuscula
                if (entre(k, 97, 122) || k == 231) {

                    // se shift: usou caps lock
                    this.aviso.style.display = shift ? "inline" : "none";
                }
                return true;
            };
        }
        break;
//    case "select":
//    case "textarea":
    }
}


//
//     Atualiza um elemento do formulario com informacoes
//
function atualizar_info_campo(form, campo, id_campo) {
// Object form: formulario
// Object campo: campo do formularo
// String id_campo: identificador do campo
//
    var labels = form.getElementsByTagName("label");
    var label = false;
    for (var i = 0; i < labels.length; i++) {
        var id_for = "";
        try {
           id_for = labels.item(i).hasAttribute("for") ? labels.item(i).getAttribute("for") : "";
        } catch (e) {
           id_for = labels.item(i).getAttribute("htmlFor");
        }
        if (id_for == campo.id) {
            label = labels.item(i);
        }
    }
    if (!label) { return false; }
    campo.label = label;
    ultima_boia = false;

    campo.onfocus = function() {
        if (ultima_boia) {
            ultima_boia.style.visibility = "hidden";
        }
        var boia = this.label.parentNode.getElementsByTagName("img");
        if (boia && boia.length > 0) {
            boia = boia.item(0);
            boia.style.visibility = "visible";
        } else {
            var boia = document.createElement("img");
            boia.setAttribute("src", wwwroot + "imgs/icones/ajuda.gif");
            boia.setAttribute("title", "Ajuda sobre o campo");
            boia.style.cursor = "pointer";
            boia.style.display = "block";
            boia.style.position = "absolute";
            boia.style.marginLeft = "-10px";
            label.style.clear = "none";

            boia.id_campo = id_campo;
            boia.janela = false;
            boia.onclick = function(e) {
                if (!this.janela) {
                    var pos = get_posicao_mouse(e);
                    this.janela = new class_janela();
                    var caixa = this.janela.criar_janela("Carregando...", pos.x, pos.y + 5, 300);

                    atualizar_info_atributo(this.janela , campo, this.id_campo);
                }
                this.janela.abrir(document.getElementsByTagName("body").item(0));
            };
            if (label.getElementsByTagName("input").length > 0) {
                label.parentNode.appendChild(boia);
            } else {
                label.parentNode.insertBefore(boia, label);
            }
        }
        ultima_boia = boia;
    };
    return true;
}


//
//     Consuta as informacoes de um atributo e preenche o objeto
//
function atualizar_info_atributo(janela, campo, id_campo) {
// Object janela: janela que recebera as informacoes
// Object campo: campo associado a ajuda
// String id_campo: identificador do campo
//
    var that = this;
    this.ajax = new class_ajax();
    obj = janela.caixa;

    //
    //     Atualiza a janela de ajuda com a descricao consultada com AJAX
    //
    this.atualizar_descricao = function() {
        var xml = that.ajax.get_retorno("xml");

        // Se veio com erros
        if (xml.documentElement.getElementsByTagName("erro").length > 0) {
            janela.set_titulo("Erro");
            that.inserir_valor(obj, "Erro", "Dados do campo não disponíveis");
            return;
        }

        // Atualizar titulo da janela
        var descricao = that.get_atributo(xml.documentElement, "descricao");
        janela.set_titulo("Ajuda do campo " + descricao);

        // Checar de acordo com o tipo de campo
        switch (campo.nodeName.toLowerCase()) {
        case "select":
            that.inserir_valor(obj, "Instrução", "Escolha um elemento da lista");
            return;
        case "input":
            switch (campo.getAttribute("text")) {
            case "text":
            case "password":
                break;
            case "checkbox":
                that.inserir_valor(obj, "Instrução", "Marque as opções desejadas");
                return;
            case "radio":
                that.inserir_valor(obj, "Instrução", "Escolha uma opção");
                return;
            case "file":
                that.inserir_valor(obj, "Instrução", "Escolha um arquivo");
                return;
            }
            break;
        case "textarea":
            break;
        }

        var tipo = that.get_atributo(xml.documentElement, "tipo");
        var minimo = that.get_atributo(xml.documentElement, "minimo");
        var maximo = that.get_atributo(xml.documentElement, "maximo");
        var obrigatorio = that.get_atributo(xml.documentElement, "pode_vazio") == '0';
        var unico = that.get_atributo(xml.documentElement, "unico") == '1';

        if (!minimo) {
            minimo = "indefinido";
        }
        if (!maximo) {
            maximo = "indefinido";
        }

        switch (tipo) {
        case "int":
            that.inserir_valor(obj, "Tipo", "Número Inteiro");
            that.inserir_valor(obj, "Intervalo", "de " + minimo + " até " + maximo);
            if (obrigatorio) {
                that.inserir_valor(obj, "Obrigatório", "Sim");
            }
            if (unico) {
                that.inserir_valor(obj, "Único no Sistema", "Sim (não podem existir 2 iguais)");
            }
            break;
        case "float":
            that.inserir_valor(obj, "Tipo", "Número Real");
            that.inserir_valor(obj, "Intervalo", "de " + minimo + " até " + maximo);
            if (obrigatorio) {
                that.inserir_valor(obj, "Obrigatório", "Sim");
            }
            if (unico) {
                that.inserir_valor(obj, "Único no Sistema", "Sim (não podem existir 2 iguais)");
            }
            break;
        case "string":
            var validacao = xml.documentElement.getElementsByTagName("validacao");
            that.inserir_valor(obj, "Tipo", "Texto");
            if (minimo == maximo) {
                that.inserir_valor(obj, "Tamanho", "exatamente " + minimo + " caracteres");
            } else {
                that.inserir_valor(obj, "Tamanho", "entre " + minimo + " e " + maximo + " caracteres");
            }
            if (obrigatorio) {
                that.inserir_valor(obj, "Obrigatório", "Sim");
            }
            if (unico) {
                that.inserir_valor(obj, "Único no Sistema", "Sim (não podem existir 2 iguais)");
            }
            if (validacao.length) {
                var instrucoes = that.get_atributo(validacao.item(0), "instrucoes");
                var exemplo = that.get_atributo(validacao.item(0), "exemplo");
                if (instrucoes) {
                    that.inserir_valor(obj, "Instruções", instrucoes);
                }
                if (exemplo) {
                    that.inserir_valor(obj, "Exemplo", exemplo);
                }
            }
            break;
        case "binario":
            that.inserir_valor(obj, "Tipo", "Texto binário");
            that.inserir_valor(obj, "Tamanho", "entre " + minimo + " e " + maximo);
            if (obrigatorio) {
                that.inserir_valor(obj, "Obrigatório", "Sim");
            }
            if (unico) {
                that.inserir_valor(obj, "Único no Sistema", "Sim (não podem existir 2 iguais)");
            }
            break;
        case "char":
            that.inserir_valor(obj, "Tipo", "Caractere");
            if (obrigatorio) {
                that.inserir_valor(obj, "Obrigatório", "Sim");
            }
            if (unico) {
                that.inserir_valor(obj, "Único no Sistema", "Sim (não podem existir 2 iguais)");
            }
            break;
        case "bool":
            that.inserir_valor(obj, "Tipo", "Sim ou Não");
            that.inserir_valor(obj, "Instruções", "Marque a opção em caso positivo ou deixe desmarcada em caso negativo");
            break;
        case "data":
            var unico = that.get_atributo(xml.documentElement, "unico");
            that.inserir_valor(obj, "Tipo", "Data");
            if (unico) {
                that.inserir_valor(obj, "Único no Sistema", "Sim (não podem existir 2 iguais)");
            }
            break;
        }
    };

    //
    //     Insere uma chave/valor na janela de ajuda
    //
    this.inserir_valor = function(obj, label, valor) {
    // Object obj: objeto que recebera a linha
    // String label: texto do label
    // String valor: texto do valor
    //
        var p = document.createElement("p");
        {
            var strong = document.createElement("strong");
            strong.appendChild(document.createTextNode(label + ":"));
            p.appendChild(strong);
            p.appendChild(document.createTextNode(" " + valor));
        }
        obj.appendChild(p);
    };

    //
    //     Obtem um atributo de um elemento recebido por AJAX
    //
    this.get_atributo = function(xml, atributo) {
    // Object xml: elemento XML que deseja-se obter o atributo
    // String atributo: nome do atributo desejado
    //
        var valor = xml.getElementsByTagName(atributo);
        if (!valor.length || !valor.item(0).hasChildNodes()) {
            return false;
        }
        return valor.item(0).firstChild.nodeValue;
    };

    // Realizar a consulta com AJAX
    var link = wwwroot + "webservice/atributo.xml.php?id=" + id_campo;
    this.ajax.set_funcao(that.atualizar_descricao);
    this.ajax.consultar("GET", link, true, null);
}


//
//     Checa se um valor esta dentro de um intervalo
//
function entre(valor, minimo, maximo) {
// Numeric valor: valor a ser testado
// Numeric minimo: valor minimo possivel
// Numeric maximo: valor maximo possivel
//
    return valor >= minimo && valor <= maximo;
}


//
//     Classe Tremer
//
function class_tremer(item, offset) {
// Object item: elemento que vai tremer
// Int offset: deslocamento horizontal do elemento
//
    this.id = class_tremer.instancias.length;
    class_tremer.instancias[this.id] = this;

    this.obj_tremer   = item.cloneNode(true);
    this.obj_original = item;
    this.offset       = (offset > 0) ? offset : 4;
    this.timer        = null;
    this.i            = 0;


    //
    //     Faz um objeto comecar a tremer
    //
    this.tremer = function(tempo) {
    // Int tempo: tempo que vai levar para parar de tremer
    //
        if (this.obj_tremer.innerHTML.length < limite_tamanho) {

            // Ajustar o objeto que vai tremer
            var pos = get_posicao(this.obj_original);
            this.obj_tremer.style.position = "absolute";
            this.obj_tremer.style.margin = "0px";
            this.obj_tremer.style.left = pos.x + "px";
            this.obj_tremer.style.top = pos.y + "px";

            // Substituir o objeto original pelo que vai tremer
            this.obj_original.parentNode.replaceChild(this.obj_tremer, this.obj_original);

            // Comecar a tremer
            this.timer = window.setInterval("class_tremer.instancias[" + this.id + "].mover()", 50);
            window.setTimeout("class_tremer.instancias[" + this.id + "].parar_tremer()", tempo);
        }
    };


    //
    //     Faz o objeto ir para um lado ou para o outro
    //
    this.mover = function() {
        try {
            this.i = 1 - this.i;
            var left = (this.i == 1) ? (-1 * this.offset) : this.offset;
            var pos = parseInt(this.obj_tremer.style.left) + left;
            this.obj_tremer.style.left = pos + "px";
        } catch (e) {
            this.parar_tremer();
        }
    };


    //
    //     Faz o objeto parar de tremer
    //
    this.parar_tremer = function() {
        clearInterval(this.timer);
    };
}


//
//     Classe Piscar
//
function class_piscar(item, vezes, passo) {
// Object item: elemento que vai piscar
// Int vezes: numero de vezes que o elemento vai piscar
// Int passo: passo em que o atributo opacity vai caminhar (1 em 1 por padrao)
//
    this.id = class_piscar.instancias.length;
    class_piscar.instancias[this.id] = this;

    this.obj_piscar = item;
    this.passo      = (passo > 0) ? passo : 0.1;
    this.opacity    = (item.style.opacity != "") ? parseFloat(item.style.opacity) : 1.0;
    this.timer      = null;
    this.vezes      = parseInt(vezes);
    this.sentido    = false;


    //
    //     Faz um objeto comecar a piscar
    //
    this.piscar = function() {
        if (this.obj_piscar.innerHTML.length < limite_tamanho) {
            this.obj_piscar.piscando = true;
            this.obj_piscar.style.opacity = parseFloat(this.opacity);
            this.sentido = false;
            this.timer = window.setInterval("class_piscar.instancias[" + this.id + "].mudar()", 50);
        }
    };
  
  
    //
    //     Faz o objeto mudar a opacidade
    //
    this.mudar = function() {
        try {
            var f = parseFloat(this.obj_piscar.style.opacity);
            f = (this.sentido) ? f + this.passo : f - this.passo;

            if (f <= 0) {
                f = 0;
                this.obj_piscar.style.opacity = f;
                this.sentido = true;
                this.vezes--;
            } else if (f >= this.opacity) {
                f = this.opacity;
                this.obj_piscar.style.opacity = f;
                this.sentido = false;
            } else {
                this.obj_piscar.style.opacity = f;
            }

            if ((this.vezes <= 0) && (f == this.opacity)) {
                this.parar_piscar();
            }
        } catch (e) {
            this.parar_piscar();
        }
    };
  
  
    //
    //     Faz um objeto parar de piscar
    //
    this.parar_piscar = function() {
        this.obj_piscar.piscando = false;
        this.sentido = false;
        clearInterval(this.timer);
    };
}


//
//     Faz um objeto sumir
//
function fechar(obj) {
// Object obj: objeto a ser fechado
//
    if (!obj.piscando) {
        var obj_fechar = new class_fechar(obj);
        obj_fechar.dissolver(0.1);
    }
}


//
//     Classe Fechar
//
function class_fechar(item) {
// Object item: elemento a ser fechado
//
    this.id = class_fechar.instancias.length;
    class_fechar.instancias[this.id] = this;

    this.obj_fechar = item;
    this.passo      = 0.1;
    this.timer      = null;


    //
    //     Dissolve um objeto
    //
    this.dissolver = function(passo) {
    // Int passo: passo em que o elemento vai perder a opacity (padrao de 1 em 1)
    //
        if (passo > 0) {
            this.passo = parseFloat(passo);
        }
        if (this.obj_fechar.innerHTML.length < limite_tamanho) {
            if (this.obj_fechar.style.opacity == "") {
            this.obj_fechar.style.opacity = 1.0;
            }
            this.timer = window.setInterval("class_fechar.instancias[" + this.id + "].mudar_dissolver()", 50);
        } else {
            this.obj_fechar.parentNode.removeChild(this.obj_fechar);
            delete this.obj_fechar;
            this.obj_fechar = null;
        }
    };

  
    //
    //     Reduz a opacidade de um objeto
    //
    this.mudar_dissolver = function() {
        this.obj_fechar.style.opacity -= this.passo;
        if (this.obj_fechar.style.opacity <= 0) {
            this.parar_timer();
        }
    };
  

    //
    //     Para de dissolver um objeto
    //
    this.parar_timer = function() {
        clearInterval(this.timer);
        this.obj_fechar.parentNode.removeChild(this.obj_fechar);
        delete this.obj_fechar;
        this.obj_fechar = null;
    };
}


//
//     Define o foco no primeiro formulario
//
function set_foco() {
    try {
        for (var f = 0; f < document.forms.length; f++) {
            for (var i = 0; i < document.forms[f].length; i++) {
                if (document.forms[f][i].getAttribute("type") != "hidden" &&
                    !document.forms[f][i].getAttribute("disabled")) {
                    document.forms[f][i].focus();
                    return
                }
            }
        }
    } catch(e) {
        // Deixa queto, nao e' importante mesmo
    }
}


//
//     Atualiza a hora no menu
//
function atualizar_hora() {
    var hl =  document.getElementById("hora_local");
    if (!hl) { return; }
    var pai = hl.parentNode;
    var nova_hl = document.createElement(hl.nodeName);
    nova_hl.id = hl.id;

    var hoje = new Date();
    var h = hoje.getHours();
    var m = hoje.getMinutes();
    var s = hoje.getSeconds();

    if (h < 10) { h = "0" + h; }
    if (m < 10) { m = "0" + m; }
    if (s < 10) { s = "0" + s; }

    var texto = document.createTextNode(h + ":" + m + ":" + s);
    nova_hl.appendChild(texto);

    pai.replaceChild(nova_hl, hl);
}


//
//     Cria um cookie
//
function setcookie(nome, valor) {
// String nome: nome do cookie
// String valor: valor do cookie
//
    var expires = new Date();
    expires.setDate(expires.getDate() + 7);

    var c = nome + "=" + encodeURIComponent(valor) + "; expires=" + expires.toGMTString() + "; path=" + path;
    if (!localhost) {
        c += "; domain=" + dominio;
    }
    document.cookie = c;
};


//
//     Recupera um cookie
//
function getcookie(nome) {
// String nome: nome do cookie a ser recuperado
//
    var reMatchCookie = new RegExp ( "(?:; )?" + nome + "=([^;]*);?" );
    return (reMatchCookie.test(document.cookie) ? decodeURIComponent(RegExp.$1) : null);
};


//
//     Abre ou fecha um link de ajuda
//
function mostrar_ajuda(link) {
// A link: link que serve de botao para abrir/fechar o texto de ajuda
//
    var caixa = link.parentNode;
    var vet = caixa.getElementsByTagName("blockquote");
    if (!vet.length) { return false; }
    var blockquote = vet[0];

    if (get_classe(blockquote) == "hide") {
        setcookie("expandir", "1");
        definir_classe(caixa, "bloco_ajuda_aberto");
        definir_classe(blockquote, "visivel");
        link.setAttribute("title", "Esconder Ajuda");
    } else {
        setcookie("expandir", "0");
        definir_classe(caixa, "bloco_ajuda_fechado");
        definir_classe(blockquote, "hide");
        link.setAttribute("title", "Expandir Ajuda");
    }
    link.removeAttribute("href");
    return false;
}


//
//     Define uma classe ao elemento
//
function definir_classe(obj, classe) {
// Object obj: objeto que deseja-se definir uma classe
// String classe: classe CSS a ser aplicada
//
    obj.className = classe;
}


//
//     Adiciona uma classe ao elemento
//
function adicionar_classe(obj, classe) {
// Object obj: objeto que deseja-se adicionar uma classe
// String classe: classe CSS a ser adicionada
//
    var classes = get_classe(obj);
    if (classes == null) {
        definir_classe(obj, classe);
    } else {
        var vt = classes.split(" ");
        var i = 0;
        var ja_tem = false;
        while (i < vt.length) {
            if (classe == vt[i]) {
                ja_tem = true;
                break;
            }
            i++;
        }
        if (!ja_tem) {
            classes = classes + " " + classe;
            definir_classe(obj, classe);
        }
    }
}


//
//     Retira uma classe do elemento
//
function remover_classe(obj, classe) {
// Object obj: objeto que deseja-se remover uma classe
// String classe: classe CSS a ser removida
//
    if (classe == undefined) {
        obj.removeAttribute("class");
        return;
    }

    var classes = get_classe(obj);
    if (classes != null) {
        var vt = classes.split(" ");
        var i = 0;
        var vt2 = new Array();
        while (i < vt.length) {
            if (classe != vt[i]) {
                vt2.push(vt[i]);
            }
            i++;
        }
        if (vt2.length >= 1) {
            definir_classe(obj, vt2.join(" "));
        } else {
            obj.removeAttribute("class");
        }
    }
}


//
//     Testa a classe de um elemento
//
function possui_classe(obj, classe) {
// Object obj: objeto que deseja-se checar a classe
// String classe: classe a ser testada
//
    var classes = get_classe(obj);
    if (classes != null) {
        var vt = classes.split(" ");
        var i = vt.length - 1;
        while (i >= 0) {
            if (classe == vt[i]) {
                return true;
            }
            i--;
        }
    }
    return false;
}


//
//     Recupera a classe de um elemento
//
function get_classe(obj) {
// Object obj: objeto que deseja-se obter a classe
//
    if (obj.className) {
        return obj.className;
    } else if (obj.getAttribute) {
        return obj.getAttribute("class");
    }
    return null;
}


//
//     Recupera o nome da tag
//
function get_tag(obj) {
    return obj.nodeName.toLowerCase();
}


//
//     Funcao para limpar um elemento recursivamente
//
function limpar(obj) {
// Object obj: objeto a ser limpado recursivamente
//
    while (obj.hasChildNodes()) {
        obj.removeChild(obj.firstChild);
    }
}


//
//     Seleciona ou Desmarca todos os checkbox
//
function marcar_checkbox(id) {
// String id: id do fieldset
//
    var f = document.getElementById(id);
    if (!f) {
        return;
    }

    if (checkbox_marcados[id] == undefined) {
        checkbox_marcados[id] = false;
    }

    var el = f.getElementsByTagName('input');
    for (var i = 0; i < el.length; i++) {
        if (el[i].type == 'checkbox' && el[i].disabled == false) {
            el[i].checked = checkbox_marcados[id] ? false : true;
        }
    }
    checkbox_marcados[id] = !checkbox_marcados[id];
}


//
//     Faz um elemento aparecer ou sumir
//
function mudar(id) {
// String id: id do objeto
//
    var obj = document.getElementById(id);
    if (!obj) { return false; }
    if (get_classe(obj) == "hide") {
        definir_classe(obj, "bloco");
    } else {
        definir_classe(obj, "hide");
    }
    return false;
}


//
//     Mascaras usadas em campos de texto
//
function mascara(e, input, tipo, blur, local) {
// Event e: evento para acionar a mascara
// Object input: input de texto que deseja-se testar
// String tipo: tipo de mascara a ser checada (int, float, uint e ufloat)
// Bool blur: flag indicando se o input acaba de parder o foco ou nao
// String local: codigo da localidade
//
    var valor = input.value;
    var exemplo = "";
    var exp;
    var fim;
    local = local.toLowerCase();

    this.set_valido = function(valido, input) {
        var vt_img = input.parentNode.getElementsByTagName("img");
        if (vt_img.length > 0) {
            var img = vt_img.item(0);
        } else {
            var img = document.createElement("img");
        }
        var src = wwwroot + "imgs/icones/" + (valido ? "valido.gif" : "invalido.gif");
        var alt = valido ? "válido" : "inválido";
        img.setAttribute("src", src);
        img.setAttribute("alt", "campo " + alt);
        img.setAttribute("title", "campo " + alt);
        if (vt_img.length == 0) {
            img.style.marginLeft = "4px";
            img.style.float = "left";
            img.style.position = "absolute";
            input.style.width = (input.offsetWidth - 30) + "px";
            input.parentNode.appendChild(img);
        }
    };

    switch (local.toLowerCase()) {
    case "pt_br":
    case "pt_br.utf-8":
    case "portuguese_brazil.1252":
        switch (tipo) {
        case "digitos":
            exemplo = "000 ou 001 ou 002 ...";
            exp     = /^[0-9]*$/;
            fim     = /^[0-9]*$/;
            break;
        case "letras":
            exemplo = "abc";
            exp     = /^[A-Za-z]*$/;
            fim     = /^[A-Za-z]*$/;
            break;
        case "moeda":
            exemplo = "0 ou -0,12 ou 1000,23 ou -1000,30";
            exp     = /^[-]?(0[,]?[0-9]{0,2}|[1-9]{1}[0-9]*[,]?[0-9]{0,2})?$/;
            fim     = /^(0|[-]?0,[0-9]{1,2}|[-]?[1-9]{1}[0-9]*([,]{1}[0-9]{1,2})?)$/;
            break;
        case "int":
            exemplo = "0 ou 1000 ou -1000";
            exp     = /^(0|[-]?([1-9]{1}[0-9]*)?)$/;
            fim     = /^(0|[-]?[1-9]{1}[0-9]*)$/;
            break;
        case "float":
            exemplo = "0 ou -0,123 ou 1000,1 ou -1000,1";
            exp     = /^[-]?(0[,]?[0-9]*|[1-9]{1}[0-9]*[,]?[0-9]*)?$/;
            fim     = /^(0|[-]?0,[0-9]+|[-]?[1-9]{1}[0-9]*([,]{1}[0-9]+)?)$/;
            break;
        case "uint":
            exemplo = "0 ou 1000";
            exp     = /^(0|[1-9]{1}[0-9]*)$/;
            fim     = /^(0|[1-9]{1}[0-9]*)$/;
            break;
        case "ufloat":
            exemplo = "0 ou 0,1 ou 1000 ou 1000,1";
            exp     = /^(0|0[,]?[0-9]*|[1-9]{1}[0-9]*[,]?[0-9]*)?$/;
            fim     = /^(0|[1-9]{1}[0-9]*)([,]{1}[0-9]+)?$/;
            break;
        }
        break;
    default:
        return true;
    }

    if (valor.length == 0) { return true; }

    // Checar se o campo esta OK ou se esta no caminho certo
    var re = new RegExp((blur == 1) ? fim : exp);

    var re_final = new RegExp(fim);
    this.set_valido(re_final.test(valor), input);

    // Se nao passou no teste
    if (!re.test(valor)) {
        if (blur != 1) {
            input.value = input.valor_antigo;
        }
        return false;
    }
    return true;
}


//
//     Ativa o timer de busca
//
function ativar_timer_busca(input, dados) {
// Input input: input de texto com a palavra a ser buscada
// String dados: dados sobre a classe e o campo de busca utilizado
//
    if (timer_busca) {
        window.clearTimeout(timer_busca);
    }
    input_busca = input;
    timer_busca = setTimeout("buscar('" + dados + "')", 1000);
}


//
//     Busca uma palavra
//
function buscar(dados) {
// String dados: dados sobre a classe e o campo de busca utilizado
//
    if (!input_busca) { return false; }
    var that   = this;
    this.input = input_busca;
    this.div   = input_busca.nextSibling;
    this.ajax  = new class_ajax();
    this.url   = wwwroot + "webservice/busca.xml.php?dados=" + dados + "&busca=" + input_busca.value;


    //
    //     Atualiza a lista de resultados encontrados
    //
    this.atualizar_itens = function() {
        var xml = that.ajax.get_retorno("xml");
        var resultados = xml.documentElement.getElementsByTagName("resultado");
        limpar(that.div);
        if (resultados.length == 0) {
            var msg = document.createTextNode("Resultado(s) Semelhante(s): nenhum");
            that.div.appendChild(msg);
        } else {
            var msg = document.createTextNode("Resultado(s) Semelhante(s): " + resultados.length);
            var select = document.createElement("select");
            select.setAttribute("size", "7");
            select.onchange = function() {
                that.input.value = this.value;
            };

            var a = document.createElement("a");
            a.setAttribute("href", "#");
            a.onclick = function(e) {
                e = e ? e : window.event;
                e.returnValue = false;
                limpar(that.div);
                return false;
            };
            a.onkeypressed = a.onclick;
            var texto_fechar = document.createTextNode("Fechar");
            a.appendChild(texto_fechar);

            for (var i = 0; i < resultados.length; i++) {
                var valor = resultados.item(i).firstChild.nodeValue;
                var option = document.createElement("option");
                var texto = document.createTextNode(valor);
                option.appendChild(texto);
                option.setAttribute("value", valor);
                option.ondblclick = function() {
                    limpar(that.div);
                    return false;
                };
                select.appendChild(option);
            }
            that.div.appendChild(msg);
            that.div.appendChild(select);
            that.div.appendChild(a);
        }
        return true;
    };


    //
    //     Consulta assincronamente
    //
    this.consultar = function() {
        that.div.style.width = "100%";
        if (that.input.value.length > 0) {
            that.ajax.set_funcao(that.atualizar_itens);
            that.ajax.exibir_carregando(div);
            that.ajax.consultar("GET", that.url, true, null);
        } else {
            limpar(that.div);
        }
        return true;
    };

    this.consultar();
    return true;
}


//
//     Checa se um elemento esta no vetor
//
function in_array(i, vet) {
// Mixed i: elemento a ser buscado no vetor
// Array vet: vetor a ser utilizado
//
    for (var j in vet) {
        if (i == j) {
            return true;
        }
    }
    return false;
}


//
//     Checa se o link e' valido ou nao
//
function checar_link(link) {
// A link: link a ser verificado
//
    var url_link = link.getAttribute("href");
    if (url_link.indexOf("http://") == -1) {
        url_link = wwwroot + url_link;
    }
    var url = wwwroot + "webservice/checar_link.xml.php?link=" + encodeURIComponent(url_link);

    var ajax = new class_ajax();
    if (!ajax.xmlhttp) { return; }
    ajax.set_funcao(
    function() {
        var xml = this.get_retorno("xml");
        if (!xml) {
            return false;
        }
        try {
            var resultado = parseInt(xml.documentElement.firstChild.data);
        } catch (e) {
            try {
                var resultado = parseInt(xml.documentElement.getElementsByTagName("resultado").item(0).firstChild.data);
            } catch (e2) {
                return false;
            }
        }
        switch (resultado) {
        case 0: // Link Valido
        case 1: // Link Indeterminado
            // Faz nada
            break;
        case 2: // Link Invalido
            link.style.color = "#990000";
            link.style.textDecoration = "line-through";

            var texto = document.createTextNode(" (link quebrado) ");
            var small = document.createElement("small");
            small.appendChild(texto);

            var novo = document.createElement("a");
            var busca = (link.getAttribute("title") != null) ? link.getAttribute("title") : link.getAttribute("href");
            novo.setAttribute("href", "http://www.go" + "ogl" + "e.com.br/search?q=" + encodeURIComponent(busca));
            novo.setAttribute("target", "_blank");
            var texto_novo = document.createTextNode("Buscar no Go" + "og" + "le");
            novo.appendChild(texto_novo);
            small.appendChild(novo);

            if (link.nextSibling) {
                link.parentNode.insertBefore(small, link.nextSibling);
            } else {
                link.parentNode.appendChild(small);
            }
            break;
        }
        return false;
    }
    );
    ajax.consultar("GET", url, true, null, false);
}


//
//     Tira a visibilidade dos elementos internos (recursivamente)
//
function tirar_visibilidade(obj) {
// Object obj: objeto que deseja-se tirar a visibilidade
//
    if (obj.hasChildNodes()) {
        var filhos = obj.getElementsByTagName("*");
        for (var i = 0; i < filhos.length; i++) {
            var filho = filhos.item(i);
            filho.style.visibility = "hidden";
            tirar_visibilidade(filho);
        }
    }
}


//
//     Clona uma tag
//
function clonar_tag(obj) {
// Object obj: objeto a ser clonado
//
    var item = document.createElement(get_tag(obj), obj.innerHTML);
    for (j = obj.attributes.length - 1; j >= 0; j--) {
        item.setAttribute(obj.attributes.item(j).name, obj.attributes.item(j).value);
    }
    return item;
}


//
//     Adiciona uma nova funcao no inicio de outra
//
function juntar_funcoes(velho, novo) {
// Function velho: funcao antiga
// Function novo: funcao nova
//
    if (!velho) {
        return novo;
    }
    velho = velho.toString();
    novo  = novo.toString();

    var p1 = velho.indexOf("{") + 1;
    var p2 = velho.lastIndexOf("}");
    velho = velho.substring(p1, p2);

    var p1 = novo.indexOf("{") + 1;
    var p2 = novo.lastIndexOf("}");
    novo = novo.substring(p1, p2);

    var saida = new Function(novo + velho);
    return saida;
}
