/*
 * SIMP
 * Descricao: Exemplo de programa em C que faz um pedido ao Web Service
 * Autor: Rubens Takiguti Ribeiro
 * Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
 * E-mail: rubens@tecnolivre.com.br
 * Versao: 1.0.0.2
 * Data: 28/11/2007
 * Modificado: 09/01/2008
 * License: LICENSE.TXT
 * Copyright (C) 2007  Rubens Takiguti Ribeiro
 * --
 * Obs: Requer a biblioteca libxml2
 * Uso: ./exemplo host
 * Compilacao (exemplo):
 *   Normal: gcc exemplo.c -o exemplo -I /usr/include/libxml2/ -lxml2
 *   Debug:  gcc exemplo.c -o exemplo -I /usr/include/libxml2/ -lxml2 -DDEBUG=1
 *   Otima:  gcc exemplo.c -o exemplo -I /usr/include/libxml2/ -lxml2 -Wall -Wextra -O3
 */
 
/* Bibliotecas Padrao */
#include <string.h>
#include <stdio.h>
#include <stdlib.h>

/* Bibliotecas de leitura de XML */
#include <libxml/tree.h>
#include <libxml/parser.h>
#include <libxml/xpath.h>
#include <libxml/xpathInternals.h>

#if !defined(LIBXML_XPATH_ENABLED) || !defined(LIBXML_SAX1_ENABLED) || \
    !defined(LIBXML_OUTPUT_ENABLED)
#error XPath nao suportado
#endif

/* Bibliotecas de conexao via Socket */
#include <sys/types.h>
#include <sys/socket.h>
#include <netinet/in.h>
#include <netdb.h>

/* Tipos de retorno */
#define OK          0
#define ERRO_PARAM  1
#define ERRO_SOCKET 2

/* Constentes */
#ifndef DEBUG
#define DEBUG 0   /* Modo debug (0 - inativo / 1 - ativo) */
#endif
#define URN          "server.simp" /* Nome do Web Service           */
#define SERVER_PORT            80  /* Porta de conexao TCP/IP       */
#define MAX_SIZE          2097152  /* Tamanho maximo do Buffer (2M) */

/* Checagem da TREE XML */
#ifndef LIBXML_TREE_ENABLED
#error TREE support not compiled in
#endif


/** Conecta ao servidor via socket */
int conectar(const char *host);

/** Realiza uma consulta no Web Service */
int consultar(const int s, const char *host, const char *entidade,
              const char* codigo, char **nome);

/** Interpreta uma resposta XML obtendo o nome */
int parse_xml(const char *content, char **nome);


/**
 * Funcao Principal
 */
int main(int argc, char *argv[]) {
    char *nome = NULL;          /* Nome consultado */
    char *host = NULL;          /* Host            */
    int s;                      /* Socket          */
    
    nome = (char *)malloc(128 * sizeof(char));
    
    /* Checar argumentos */
    if (argc != 2) {
        fprintf(stderr, "Forma de uso: %s host\n", argv[0]);
        return ERRO_PARAM;
    } else {
        host = argv[1];
    }

    /* Conectar ao servidor via socket */
    s = conectar(argv[1]);
    if (!s) {
        fprintf(stderr, "Erro ao conectar via socket\n");
        return ERRO_SOCKET;
    }

    /* Consultar no Web Service e obter retorno */
    consultar(s, host, "usuario", "1", &nome);

    /* Fechando a conexao */
    shutdown(s, SHUT_RDWR);
    
    free(nome);
    return OK;
}


/**
 * Conecta ao servidor via socket
 * @return id do socket conectado ou 0 em caso de erro
 */
int conectar(const char *host) {
    struct hostent *h;          /* Host      */
    struct sockaddr_in sin;     /* Socket IN */
    int s;                      /* Socket    */
    
    /* Converter Host para IP */
    h = gethostbyname(host);
    if (!h) {
        fprintf(stderr, "Host desconhecido: %s\n", host);
        return 0;
    }

    /* Montar estrutura dos dados do endereco */
    bzero((char *)&sin, sizeof(sin));
    sin.sin_family = AF_INET;
    bcopy(h->h_addr, (char *)&sin.sin_addr, h->h_length);
    sin.sin_port = htons(SERVER_PORT);

    /* Abrir o socket */
    if ((s = socket(PF_INET, SOCK_STREAM, 0)) < 0) {
        fprintf(stderr, "Erro de socket\n");
        return 0;
    }
    if (connect(s, (struct sockaddr *)&sin, sizeof(sin)) < 0) {
        fprintf(stderr, "Erro de conexao\n");
        return 0;
    }
    return s;
}


/**
 * Realiza uma consulta no Web Service
 */
int consultar(const int s, const char *host, const char *entidade,
              const char* codigo, char **nome) {
    char buf[MAX_SIZE];   /* Buffer                  */
    char *xml;            /* XML a ser enviado       */
    char *request;        /* Requisicao XML          */
    char *response;       /* Resposta XML            */
    char *content;        /* Conteudo recebido       */
    int  result;          /* Resultado das operacoes */

    /* Montando o Pedido XML no formato SOAP */
    memset(buf, 0, sizeof(buf));
    sprintf(buf,
        "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n"
        "<SOAP-ENV:Envelope"
        "  SOAP-ENV:encodingStyle=\"http://schemas.xmlsoap.org/soap/encoding/\""
        "  xmlns:SOAP-ENV=\"http://schemas.xmlsoap.org/soap/envelope/\""
        "  xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\""
        "  xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\""
        "  xmlns:SOAP-ENC=\"http://schemas.xmlsoap.org/soap/encoding/\""
        "  xmlns:si=\"http://soapinterop.org/xsd\""
        "  xmlns:consulta=\"urn:%s\">\n"
        "<SOAP-ENV:Body>\n"
        "<consulta:consultar>\n"
        "  <entidade xsi:type=\"xsd:string\">%s</entidade>\n"
        "  <codigo xsi:type=\"xsd:integer\">%s</codigo>\n"
        "</consulta:consultar>\n"
        "</SOAP-ENV:Body>\n"
        "</SOAP-ENV:Envelope>",
        
        URN,
        entidade,
        codigo
    );
    xml = (char *)malloc((strlen(buf) + 1) * sizeof(char));
    strcpy(xml, buf);

    /* Montando Pedido HTTP (cabecalho HTTP + conteudo) */
    memset(buf, 0, sizeof(buf));
    sprintf(buf,
        "POST %s HTTP/1.1\n"
        "Host: %s\n"
        "Content-Type: %s\n"
        "SOAPAction: \"uri:consulta/consultar\"\n"
        "Content-Length: %d\n"
        "User-Agent: NuSOAP\n"
        "\n"
        "%s",

        /* Action    */ "/simp/webservice/index.php", /* TODO: Mudar caso necessario */
        /* Host      */ host,
        /* Mime-type */ "text/xml; charset=utf-8",
        /* Length    */ strlen(xml),
        /* XML       */ xml
    );
    request = (char *)malloc((strlen(buf) + 1) * sizeof(char));
    strcpy(request, buf);

    /* Enviando o Pedido */
    send(s, request, strlen(request), 0);
    
    #if DEBUG == 1
    puts("Enviando Pedido:");
    puts("-----------------------------------------------");
    puts(request);
    puts("-----------------------------------------------");
    #endif

    /* Recebendo a Resposta */
    memset(buf, 0, sizeof(buf));
    recv(s, buf, MAX_SIZE, 0);
    response = (char *)malloc((strlen(buf) + 1) * sizeof(char));
    strcpy(response, buf);
    
    #if DEBUG == 1
    puts("Recebendo resposta:");
    puts("-----------------------------------------------");
    puts(response);
    puts("-----------------------------------------------");
    #endif
    
    /* Obter conteudo recebido sem o cabecalho HTTP */
    content = strstr(response, "\r\n\r\n");
    if (content != NULL) {
        content += 4;
    } else {
        content = strstr(response, "\n\n");
        if (content != NULL) {
            content += 2;
        } else {
            fprintf(stderr, "Erro ao interpretar pacote HTTP recebido\n");
            return 0;
        }
    }
    
    /* Interpretar conteudo recebido */
    result = parse_xml(content, nome);
    
    /* Desalocando memoria */
    free(xml);
    free(request);
    free(response);
    
    if (!result) {
        fprintf(stderr, "Erro ao interpretar conteudo XML recebido\n");
        return 0;
    }
    
    printf("Nome: %s\n", *nome);
    
    return 1;
}


/**
 * Interpreta uma resposta XML obtendo o nome
 */
int parse_xml(const char *content, char **nome) {
    xmlDoc *doc = NULL;
    xmlXPathContextPtr xpath_context = NULL;
    xmlXPathObjectPtr xpath_obj = NULL;
    
    /* Testar a versao da libxml2 */
    xmlInitParser();
    LIBXML_TEST_VERSION
    
    /* Criar documento XML */
    doc = xmlParseDoc(BAD_CAST content);
    if (doc == NULL) {
        fprintf(stderr, "Arquivo XML invalido\n");
        xmlCleanupParser();
        xmlFreeDoc(doc);
        return 0;
    }
    
    /* Criar XPath */
    xpath_context = xmlXPathNewContext(doc);
    if (xpath_context == NULL) {
        fprintf(stderr, "Erro ao criar XPath\n");
        xmlCleanupParser();
        xmlFreeDoc(doc);
        return 0;
    }
    
    /* Realizar a busca usando o XPath */
    xpath_obj = xmlXPathEvalExpression(BAD_CAST "//*/nome", xpath_context);
    if (xpath_obj == NULL) {
        fprintf(stderr, "Erro ao realizar a busca usando XPath\n");
        xmlCleanupParser();
        xmlXPathFreeContext(xpath_context); 
        xmlFreeDoc(doc); 
        return 0;
    }
    
    /* Se nao achou */
    if (!xpath_obj->nodesetval->nodeNr) {
        xmlCleanupParser();
        xmlXPathFreeObject(xpath_obj);
        xmlXPathFreeContext(xpath_context);
        xmlFreeDoc(doc);
        return 0;
    }

    /* Copiar valor consultado */
    strcpy(*nome, (char *)xpath_obj->nodesetval->nodeTab[0]->children->content);
    
    xmlCleanupParser();
    xmlXPathFreeObject(xpath_obj);
    xmlXPathFreeContext(xpath_context);
    xmlFreeDoc(doc);
    return 1;
}

