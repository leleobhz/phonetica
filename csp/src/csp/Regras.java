/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
package csp;

import java.util.*;
import java.util.ArrayList;

/**
 *
 * @author mrebello
 */
public class Regras {

    Collection<Regra> lista_de_regras = new ArrayList<Regra>();

    public void Adiciona(String regra, Collection<String[]> macros) {
        lista_de_regras.add(new Regra(regra, macros));
    }

    public void Aplica(analise a) {
        for (Regra r : lista_de_regras) {
            if (r.Aplica(a)) {
                break;
            }
        }
    }
}
