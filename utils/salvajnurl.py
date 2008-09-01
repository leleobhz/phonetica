#!/usr/bin/python
# -*- coding: utf8 -*-

"""
	A Aplication to save Jornal Nacional videos separated by date, extract the audio separatelly and create metadata file...
	Copyright (C) 2007 Leonardo Silva Amaral

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License version 2 as published by
	the Free Software Foundation.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA
"""

import os
import sys
import re
import urllib
import optparse
import getopt
import shutil
import operator
import time
import progressbar
import subprocess

def main(argv):

	# Set the progressbar:
	widgets = ['Iniciando o processo de recepção e conversão dos arquivos... ', progressbar.Percentage(), ' ', progressbar.Bar(marker=progressbar.RotatingMarker())]
	global progress
	progress = progressbar.ProgressBar(widgets=widgets, maxval=len(parse_jn()[1])*100)
	global progress_count
	progress_count = 0
	app = check_depends()
	datar = 1
	interactive = 0
	try:
		opts, args = getopt.getopt(argv, "dhio:", ["help", "output="])
	except getopt.GetoptError:
		# print help information and sys.exit:
		usage()
		sys.sys.exit(2)
	for o, a in opts:
		if o in ("-h", "--help"):
			usage()
			sys.exit()
		if o in ("-o", "--output"):
			diretorio_saida = a
#		if o == "-d":
#			datar = 1
		if o == "-i":
			interactive = 1
	
	try:
		diretorio_saida
	except NameError:
		diretorio_saida = "."
	
	if datar == 1:
		from time import strftime
		# Para a data corrente
		#data = strftime("%Y%m%d")
		
		# Para a data do arquivo
		# <!-- Globo.com Fri Nov 02 23:35:59 BRST 2007 -->
		data=re.findall(r'<!-- Globo.com (.*) -->', parse_jn()[0])
		data=time.strptime(str(data), "['%a %b %d %H:%M:%S %Z %Y']")
		# Data = ['Fri Nov 02 23:35:59 BRST 2007']
		data=time.strftime("%Y%m%d", data)
		
		diretorio_saida = diretorio_saida +"/" + data 
		shutil.rmtree(diretorio_saida,1)
	
	video_saida = diretorio_saida + "/VIDEOS"
	audio_saida = diretorio_saida + "/AUDIOS"
	
	if interactive is 1:
		print "O video sera salvo em %s.\nO audio sera salvo em %s." % (video_saida,audio_saida)
		espera = raw_input("Pressione <enter> para continuar.")
	
	try:
		os.makedirs(video_saida)
		os.makedirs(audio_saida)
	except OSError, valorerro:
		if operator.contains(str(valorerro),"Errno 17") :
			#shutil.rmtree(diretorio_saida,1)
			sys.exit (3)
		
	for i in parse_jn()[1]:
		progress.update(progress_count)
		id = re.findall(r'GIM*[0-9]*',i)
		id = id[0]
		id = re.findall(r'[0-9].*',id)
		id = id[0]
		#print id
		conteudodohtml = urllib.urlopen(i).read()
		conteudodohtml = urllib.unquote_plus(conteudodohtml)
		#titulo = re.findall('<meta name="title" content="([^>]+)"',conteudodohtml)
		#titulo = re.findall('<title\b[^>]*>Globo Vídeo – Player Notícias - VIDEO - (.*?)</title>"',conteudodohtml)
		titulo = re.findall('\<title\>Globo V\ídeo \– Player Not\ícias \- VIDEO \- (.*)\<\/title\>',conteudodohtml)
		data = re.findall('<meta name=\'dtnoticia\' content=\'([^>]+)\'',conteudodohtml)
		titulo = str(titulo[0])
		data = str(data[0])
		#print titulo
		videolink = "http://playervideo.globo.com/webmedia/GMCMidiaASX?midiaId=%s|banda=N|ext.asx" % (id)
		#programa = "mplayer"
		#argumento = ("-dumpstream", "-dumpfile", "./VIDEOS/"+str(id), "-playlist", str(videolink), ">", str(id)+".log", "2>&1")
		#print argumento
		#os.execvp(programa, (programa,) +  argumento)
		#print ("mplayer -dumpstream -dumpfile ./VIDEOS/%s -playlist \"%s\" > %s.log 2>&1" % (str(id),str(videolink),str(id)))
		
#		mplayer = "%s -dumpstream -dumpfile %s/%s -playlist \"%s\" 2>&1" % (app[1],str(video_saida),str(id),str(videolink))
		ffmpeg = "%s -i %s/%s %s/%s.mp3 2>&1" % (app[0],str(video_saida),str(id),str(audio_saida),str(id))
		
		if interactive == 1:
			print "O video a ser salvo agora e o \"%s\" e o ID dele e o %s, de %s." % (titulo,str(id),data)
			confirma = raw_input("Continuar? s/n.")
			if confirma != "s" or confirma != "S":
				break
		
		log = open(diretorio_saida + "/salvajn.log","a")
		metadados = open("%s/%s.metadata" % (str(video_saida),str(id)) ,"w")
		log.write("Iniciando o dumping do arquivo %s\n" % (id))
		#print "Iniciando o dumping do arquivo \"%s\"" % (titulo)
		log.flush()
#		log.write("Command: %s\n" % (str(mplayer)))
		# Video write
		mencoder(str(videolink),str(video_saida)+"/"+str(id))
#		output_mplayer=str(os.popen("%s" % (mplayer)).read())
#		for line in output_mplayer:
#			log.write (line)
#		log.write("")
		log.write("Iniciando a conversao de audio do arquivo %s\n" % (id))
		#print "Iniciando a conversao de audio do arquivo \"%s\"" % (titulo)
		log.flush()
		log.write ("Command: %s\n" % (str(ffmpeg)))

		#Audio part: to be done.
#		output_ffmpeg=str(os.popen("%s" % (ffmpeg)).read())
#		for line in output_ffmpeg:
#			log.write (line)
		metadados.write("ID:%s\nArquivo:%s\nData:%s\n" % (str(id),str(titulo),str(data)))
		log.close()
		metadados.close()
		#os.rename("%s/VIDEOS/%s","%s/VIDEOS/%s.asf" % video_saida,str(id),video_saida,titulo)
		os.rename(video_saida+"/"+id,video_saida+"/"+titulo+".flv")
		#os.rename("%s/AUDIOS/%s" % (audio_saida,str(id)),"%s/AUDIOS/%s.mp3" % audio_saida,titulo)
#		os.rename(audio_saida+"/"+id+".mp3",audio_saida+"/"+titulo+".mp3")
		
		os.rename(video_saida+"/"+id+".metadata", video_saida+"/"+titulo+".metadata")
		
		#os.system("cd ./VIDEOS ; mv %s \"%s\".asf" % (id, titulo))
		#os.system("cd ./AUDIOS ; mv %s.mp3 \"%s\".mp3" % (id, titulo))
	
	progress.finish()

class ProgramMissing(Exception):
	def __init__(self,value):
		self.value = value
	def __str__(self):
		return repr(self.value)

def check_depends():
	widgets = ['Procurando pelos programas necessários... ', progressbar.Percentage(), ' ', progressbar.Bar(marker=progressbar.RotatingMarker())]
	pbar = progressbar.ProgressBar(widgets=widgets, maxval=len ((os.environ['PATH'].split(os.pathsep)))).start()
	mplayer = ""
	ffmpeg = ""
	count=1
	for i in os.environ['PATH'].split(os.pathsep):
		pbar.update(count)
		count = count+1
		if os.path.exists(i+"/ffmpeg"):
			ffmpeg = i+"/ffmpeg"
		if os.path.exists(i+"/mplayer"):
			mplayer = i+"/mplayer"
	pbar.finish()

# Thanks to gpolo@freenode (#python)

	if not ffmpeg:
		try:
			raise ProgramMissing('Este programa requer o ffmpeg para funcionar. Por favor, instale-o e execute este programa novamente.')
		except ProgramMissing, e:
			print (e.value)
			sys.exit (1)
		
	if not mplayer:
		try:
			raise ProgramMissing('Este programa requer o mplayer para funcionar. Por favor, instale-o e execute este programa novamente.')
		except ProgramMissing, e:
			print (e.value)
			sys.exit (1)
	return (ffmpeg, mplayer)

def usage():
	print ("SalvaJN\n\nOpcoes:\n\t-h \\ --help: mostra esta mensagem de ajuda\n\t-d: cria uma pasta com a data, e la salva os arquivos\n\t-o diretorio \\ --output=diretorio: seleciona o caminho a salvar. Por padrao, este caminho e o diretorio corrente. \n\nNesta versao SVN, o -d esta desativado e a politica padrao e de qualquer forma colocar o diretorio padrao")

def parse_jn ():
	# Aim: Parse the initial page for all video links

	html = urllib.urlopen("http://jornalnacional.globo.com/Jornalismo/JN/0,,3586,00.html").read()
	listadelinks =re.findall(r'GmcPlay\(\'(.*)\'\)', html)
	return (html, listadelinks)

def parse_asx (link):
	""" Download the asx and get the mms stream. """
	
	# <ref href="mms://windowsmedia.globo.com/_fechado/globocom/jornalismo/2/jornal_nacional/2007/11/03/EFCGJ_T_750596_wmbl.wmv?url=0200004304195138788KT7AGYyY%2BuBl7rgeZ4cKFg%3D%3D&WMContentBitrate=200000"/>
	mms = re.findall('href="(.*)"', urllib.urlopen(link).read())
	return (mms)

def mencoder(link,destin):
	from string import join
	aim = (parse_asx(link))
	#mencoder  -of lavf -oac copy -ovc copy -lavfopts i_certify_that_my_video_stream_does_not_use_b_frames 
#	process = subprocess.Popen(['mencoder', '-of', 'lavf', '-lavfopts', 'i_certify_that_my_video_stream_does_not_use_b_frames', '-oac', 'copy', '-ovc', 'copy', '-o', str(destin), str(aim[0])], stdout=subprocess.PIPE, stderr=subprocess.PIPE)
	process = subprocess.Popen(['mencoder', '-of', 'lavf', '-lavfopts', "format=flv", '-oac', 'copy', '-ovc', 'copy', '-o', str(destin), str(aim[0])], stdout=subprocess.PIPE, stderr=subprocess.PIPE)
	#process = subprocess.Popen(['mencoder', '-oac', 'copy', '-ovc', 'copy', '-o', str(destin), str(aim[0])], stdout=subprocess.PIPE, stderr=subprocess.PIPE)
	global pid_mencoder
	pid_mencoder=process.pid
	while True:
		buffer = []
		while True:
			char = process.stdout.read(1)
			if char == '\r':
				aLine = join( buffer, '' )
				buffer = []
				break
			else:
				buffer.append(char)

		print aLine

		if aLine: 
			if re.findall('\(...\)', aLine) and re.match('^Pos:*', aLine): 
				aLine = re.findall('\(...\)', aLine)[0]
				percentage = aLine.lstrip("\(")
				percentage = percentage.rstrip("\)%")
				percentage = int(percentage) 
				progress_count_mencoder = progress_count + percentage
				progress.update(progress_count_mencoder)
#	progress_count = progress_count + progress_count_mencoder


if __name__ == "__main__":
	main(sys.argv[1:])

