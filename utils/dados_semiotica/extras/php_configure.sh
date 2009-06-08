#!/bin/sh
#
# Este script define o comando de configuracao de compilacao PHP
# com os modulos necessarios para o correto funcionamento do Simp.
#
# Observacoes:
# * --with-apxs2 deve ser modificado
# * --prefix deve ser modificado
# * --build , --host e --target devem ser modificados
#
# Modulos opcionais (mas recomendados):
# * --with-gd --enable-gd-native-ttf --with-ttf --with-freetype-dir
# * --enable-exif
# * --with-pear
# * --with-imap --with-imap-ssl --with-berberos
# * --width-ldap
#
# Modulos de SGBD (requer pelo menos um tipo):
# * --with-mysql --with-mysql-sock
# * --with-mysqli
# * --width-pdo --width-pdo-mysql
# * --with-pgsql
# * --with-pdo-pgsql
# * --with-oci8
# * --with-pdo-oci
# * --with-sqlite --enable-sqlite-utf8
# * --with-pdo-sqlite
#
# Autor: Rubens Takiguti Ribeiro
# E-mail: rubens@tecnolivre.ufla.br
# Versao: 1.0.0.0
# Data: 07/07/2008
# Modificado: 07/07/2008
# Copyright (C) 2008  Rubens Takiguti Ribeiro
#
./configure \
  --with-apxs2=/usr/local/apache2/bin/apxs \
  --build=i686-pc-linux-gnu \
  --host=i686-pc-linux-gnu \
  --target=i386-redhat-linux-gnu \
  --prefix=/usr/local/apache2/php \
  --enable-sigchild \
  --enable-cli \
  --enable-libxml \
  --with-imap --with-imap-ssl --with-kerberos \
  --with-pcre-regex \
  --with-zlib \
  --with-bz2 \
  --with-gd \
  --enable-gd-native-ttf --with-ttf --with-freetype-dir \
  --with-jpeg-dir --with-png-dir --with-xpm-dir \
  --enable-exif \
  --with-ldap \
  --with-mcrypt \
  --with-mysql=/usr/local/mysql --with-mysql-sock \
  --with-mysqli \
  --with-pgsql \
  --with-oci8 \
  --with-sqlite --enable-sqlite-utf8 \
  --enable-pdo \
  --with-pdo-mysql \
  --with-pdo-pgsql \
  --with-pdo-oci \
  --with-pdo-sqlite \
  --enable-bcmath \
  --enable-reflection \
  --enable-session \
  --enable-simplexml \
  --enable-sockets \
  --with-regex \
  --enable-zip \
  --with-pear
