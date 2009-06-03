package csp;

import java.io.*;
import java.util.ArrayList;
import java.util.Collection;

/**
 *
 * @author mrebello
 */
public class Main {
    static String arq_regras = "regras.txt";
    static String file_in = "-";
    static String file_out = "-";
    static String file_err = "-";
    static boolean depuracao = false;
    static int Num_Tipos = 10;
    static Regras R[] = new Regras[Num_Tipos + 1];
    static String depura = "";

    public static String Parser(String palavra) {
        analise a = new analise(palavra);
        int pos_anterior;
        do {
            pos_anterior = a.posicao;
            for (int t = 0; t < Num_Tipos; t++) {
                R[t].Aplica(a);
            }
        } while (pos_anterior != a.posicao);
        if (depuracao) depura += a.depura;
        return a.saida;
    }

    public static void main(String[] args) throws IOException {
        // TODO code application logic here
        String palavras = "";
        Collection<String[]> macros = new ArrayList<String[]>();
        for (int t = 0; t < Num_Tipos; t++) {
            R[t] = new Regras();
        }
        for (String a : args) {
            if (a.startsWith("-i=")) {
                file_in = a.substring(3);
            } else if (a.startsWith("-o=")) {
                file_out = a.substring(3);
            } else if (a.startsWith("-e=")) {
                file_err = a.substring(3);
            } else if (a.startsWith("-r=")) {
                arq_regras = a.substring(3);
            } else if (a.startsWith("-d")) {
                depuracao = true;
            } else {
                if (!a.startsWith("-")) {
                    if(palavras.length()>0) palavras+="!";
                    palavras += a;
                } else {
                    System.out.println("Uso:\n" +
                            "java -jar csp.jar [-i=inputfile] [-o=outputfile] [-r=rulefile] [-d] [-e=debugfile] [text]\n\n" +
                            "Use inputfile/outputfile/debugfile sendo - para stdin/stdout/stderr (padrão caso não indicado)\n" +
                            "Use -d para ativar a depuração\n" +
                            "Se text especificado, aplica a regra a text, senão lê de inputfile.");
                    return;
                }
            }
        }

        // Lê regras e compila expressões regulares
        BufferedReader f_regras = new BufferedReader(new InputStreamReader(new FileInputStream(arq_regras), "UTF8"));
        String l;
        int t = 0;
        boolean m = true;
        while ((l = f_regras.readLine()) != null) {
            l = l.trim();
            if (m) {  // Lê macros até encontrar '---'
                if (l.equals("---")) {
                    m = false;
                } else if (!l.startsWith("//") && l.length() > 0) {
                    macros.add(l.split("="));
                }
            } else {
                if (l.equals("---")) {
                    t = Math.min(t++, Num_Tipos);
                } else if (!l.startsWith("//") && l.length() > 0) {
                    R[t].Adiciona(l, macros);
                }
            }
        }

        BufferedWriter out;
        if (file_out.equals("-")) {
            out = new BufferedWriter(new OutputStreamWriter(System.out, "UTF8"));
        } else {
            out = new BufferedWriter(new OutputStreamWriter(new FileOutputStream(file_out), "UTF8"));
        }

        BufferedWriter err;
        if (file_err.equals("-")) {
            err = new BufferedWriter(new OutputStreamWriter(System.err, "UTF8"));
        } else {
            err = new BufferedWriter(new OutputStreamWriter(new FileOutputStream(file_err), "UTF8"));
        }

        if (palavras.length() > 0) {
            for (String x : palavras.split("!")) {
                out.write(Parser(x) + "\n");
                if (depuracao) err.write(depura+"\n\n");
                depura = "";
            }
        } else {
            BufferedReader in;
            if (file_in.equals("-")) {
                in = new BufferedReader(new InputStreamReader(System.in, "UTF8"));
            } else {
                in = new BufferedReader(new InputStreamReader(new FileInputStream(file_in), "UTF8"));
            }
            String x;
            while ((x = in.readLine()) != null) {
                out.write(Parser(x) + "\n");
                if (depuracao) err.write(depura+"\n\n");
                depura = "";
            }
            in.close();
        }
        out.close();
        err.close();

    }
}
