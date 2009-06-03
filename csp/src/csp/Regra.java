/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
package csp;

import java.util.*;
import java.util.regex.*;

/**
 *
 * @author mrebello
 */
public class Regra {

    Pattern pre;
    Pattern texto;
    Pattern pos;
    String troca;
    String nome;
    boolean especial; // tem troca de 1 ou 2 em troca


    String Macros(String t, Collection<String[]> macros) {
        String r = t;
        for (String[] k : macros) {
            r = r.replace(k[0], k[1]);
        }
        return r;
    }

    Regra(String regra, Collection<String[]> macros) {
        nome = regra;
        String x[] = regra.split(":");
        if (x[0].length() > 0) {
            x[0] = x[0].trim() + "\\z";
        }
        pre = Pattern.compile(Macros(x[0], macros));
        texto = Pattern.compile(Macros(x[1].trim(), macros));
        String y[] = x[2].trim().split("=");
        try {
            pos = Pattern.compile(Macros(y[0].trim(), macros));
            troca = y[1].trim();
        } catch (Exception e) {
            pos = Pattern.compile("");
            troca = "";
        }
        especial = (troca.matches(".*\\d.*"));
    }

    public boolean Aplica(analise a) {
        if (a.posicao >= a.texto.length()) {
            return false;
        }
        String tp = a.texto.substring(0, a.posicao);
        if (pre.pattern().length() > 0 && !pre.matcher(tp).find()) return false;       // Regra não aplica
        if (texto.pattern().length() > 0) {
            String p = a.texto.substring(a.posicao);
            Matcher m = texto.matcher(p);
            if (!m.lookingAt()) return false;      // Regra não aplica
            String t = p.substring(0, m.end());
            String r = p.substring(m.end());
            if ((pos.pattern().length() > 0) && (!pos.matcher(r).lookingAt())) return false;  // Regra não aplica
            // Regra aplica
            if (especial) {
                String n = troca;
                for(int x=1;x<=Math.min(9, m.groupCount());x++) {
                    try {
                        n = n.replace(Integer.toString(x), m.group(x));
                    } catch (Exception e) {                      
                    }
                }
                a.saida += n;
            } else {
                a.saida += troca;
            }
            a.posicao += m.end();
            a.depura += tp + ":" + t + ":" + r + "  <-> " + nome + "\n";
        }
        return true;
    }
}
