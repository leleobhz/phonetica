//
// SIMP
// Descricao: JavaScript para mover objetos
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.ufla.br
// Versao: 1.0.0.8
// Data: 12/06/2007
// Modificado: 13/04/2009
// License: LICENSE.TXT
// Copyright (C) 2007  Rubens Takiguti Ribeiro
//
document.onmousemove = mover;
document.onmouseup   = soltar;


// Variaveis globais
{
    var pos       = null;
    var offset    = null;
    var flutuante = null;
    var obj_foco  = null;
    var clone     = null;
    var metade    = screen.width / 2;
}


//
//     Define um objeto que move outro
//
function objeto_movel(obj, obj_movel) {
// Object obj: objeto que servira como base para mover outro (por exemplo o titulo de uma janela)
// Object obj_movel: objeto que se movera
//
    if (obj == undefined || obj_movel == undefined) {
        return false;
    }

    obj.style.cursor = "move";
    obj.style.zIndex = 0;
    obj.onmousedown = function(e) {
        if (flutuante != null) {
            return false;
        }
        if (obj_foco != null) {
            obj_foco.style.zIndex = 0;
        }
        obj_foco = obj_movel;

        flutuante = obj_movel;
        offset = get_offset(flutuante, e);

        flutuante.opacity_original = flutuante.style.opacity;
        flutuante.width_original = flutuante.style.width;

        if (flutuante.id == "menu") {
            var pai = flutuante.parentNode;

            clone = flutuante.cloneNode(true);
            clone.style.display = "none";
            pai.appendChild(clone);
            flutuante.style.width = flutuante.offsetWidth + "px";
            flutuante.style.position = "absolute";
            clone.style.display = "block";

        } else {
            flutuante.style.width = (flutuante.offsetWidth - 2) + "px";
            flutuante.style.position = "absolute";
        }

        pos = get_posicao_mouse(e);
        flutuante.style.top  = (pos.y - offset.y) + "px";
        flutuante.style.left = (pos.x - offset.x) + "px";
        flutuante.style.opacity = 0.5;
        flutuante.style.zIndex = 1;
        
        return false;
    };
    return true;
}


//
//     Move um objeto
//
function mover(e) {
// Event e: evento ao mover o mouse
//
    if (flutuante) {
        e = e || window.event;
        pos = get_posicao_mouse(e);
        flutuante.style.top  = (pos.y - offset.y) + "px";
        flutuante.style.left = (pos.x - offset.x) + "px";
    }
    return !flutuante;
}


//
//     Solta um objeto
//
function soltar() {
    if (flutuante != undefined) {
        flutuante.style.opacity = flutuante.opacity_original;
        flutuante.style.width  = flutuante.width_original;
        flutuante.style.zIndex = 1;
        if (flutuante.id == "menu") {
            var principal  = document.getElementById("conteudo_principal");
            var secundario = document.getElementById("conteudo_secundario");
            var conteudo   = principal.parentNode;
            limpar(conteudo);

            // Menu na direita
            if (pos.x <= metade) {
                conteudo.appendChild(secundario);
                conteudo.appendChild(principal);

            // Menu na esquerda
            } else  {
                conteudo.appendChild(principal);
                conteudo.appendChild(secundario);
            }

            // Remover clone
            flutuante.style.opacity  = clone.style.opacity;
            flutuante.style.position = clone.style.position;
            flutuante.style.width    = clone.style.width;
            secundario.removeChild(clone);
        }
    }
    pos       = null;
    flutuante = null;
    clone     = null;
    offset    = null;
}


//
//     Recupera a posicao do mouse
//
function get_posicao_mouse(e) {
// Event e: evento para obter a posicao do mouse
//
    e = e || window.event;
    if (e.pageX || e.pageY) {
        return { x:e.pageX, y:e.pageY };
    }
    if (document.body.scrollTop) {
        return {
            x:e.clientX + document.body.scrollLeft - document.body.clientLeft,
            y:e.clientY + document.body.scrollTop  - document.body.clientTop
        };
    }
    return {
        x:e.clientX + document.documentElement.scrollLeft - document.documentElement.clientLeft,
        y:e.clientY + document.documentElement.scrollTop  - document.documentElement.clientTop
    };
}


//
//     Recupera a posicao do objeto (retorna objeto com atributos x e y em px)
//
function get_posicao(obj) {
// Object obj: objeto que deseja-se saber a posicao
//
    var left = 0;
    var top  = 0;

    while (obj.offsetParent) {
        left += obj.offsetLeft;
        top  += obj.offsetTop;
        obj   = obj.offsetParent;
    }

    left += obj.offsetLeft;
    top  += obj.offsetTop;

    return { x:left, y:top };
}


//
//     Recupera a posicao onde clicou no objeto
//
function get_offset(obj, e) {
// Object obj: objeto a ser checado
// Event e: evento do mouse
//
    e = e || window.event;
    var pos_doc   = get_posicao(obj);
    var pos_mouse = get_posicao_mouse(e);
    return { x:pos_mouse.x - pos_doc.x, y:pos_mouse.y - pos_doc.y };
}
