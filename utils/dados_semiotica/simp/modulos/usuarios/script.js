var r = document.getElementById('usuario-radio_geracao_senha_0');
definir_operacao_senha(r);
if (r) {
    r.onclick();
}
var r = document.getElementById('usuario-radio_geracao_senha_1');
definir_operacao_senha(r);


//
//     Define uma funcao de evento para o input
//
function definir_operacao_senha(input) {
    if (!input) { return false; }
    input.onclick = function () {
        var field = document.getElementById('usuario-fieldset_ff64a1c43498d955147518733ac88c7c');
        if (this.checked) {
            field.style.display = (this.getAttribute("value") == 1) ? "none" : "block";
        }
    };
}