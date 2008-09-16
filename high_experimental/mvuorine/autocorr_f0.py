# F0 havaitseminen autokorrelaatio avulla
# 
# Toinen tapa F0 etsimiseen on autokorrelaatio.
# Tämänkin menetelmän toimimiseksi jokin ylä- ja alaraja
# tulee f0:n arviolle asettaa esim. max. 180 Hz ja min.80 Hz .
# (Oikeasti arvioiden tulisi olla puhuja kohtaisia.)
# Idea autokorrelaatiossa on löytää kuinka pitkä väli signaalin
# toistojen välillä on kullakin ajan hetkellä. 
# Toistojen väli on sama asia kuin signaalin jakso.  
# Toiston pituus vaihtelee tietysti riippuen siitä 
# mitä kohtaa signaalista ollaan käsittelemässä. 
# Tämän vuoksi autokorrelaatio tuleekin suorittaa koko signaalin
# pituudelle, jotta kaikki tärkeät arviot saadaan talteen.
# Menetelmä on seuraava, eli valitaan joku alkukohta ja kopioidaan 
# siitä eteenpäin joku tietty osa signaalia. Sitten siirrytään johonkin  
# toiseen alkukohtaa edempänä signaalia ja otetaan siitä samanpituinen osa 
# kuin otettiin aikaisemmin. Lopuksi verrataan näitä osia toisiinsa. 
# Jos kahden palan huoput ja laaksot eivät sovi hyvin toisiinsa
# tai linjaudu toistensa päälle, voidaan todeta että erillaisuuden aste 
# on suuri. Sovittamista jatketaan niin kauan, että saadaan sellainen
# näytteen pituus, jossa huoput ja laaksot menevät sopivasti toistensa
# päälle. Tämä näytteen pituus on signaalin perustaajuus f0.

#!/usr/bin/python
# -*- coding: utf-8 -*-

# Original C program Copyright by John Coleman in "Introducing Speech and
# Language Processing",
# Cambridge University Press, 2005
# Listing 4.5 on page 92

# autocorr_f0.py -Pitch prediction by short-time autocorrelation. f0_out (the
# sample-by-sample f0 track) is written to outfile.

from sys import argv, exit
from slputils import signal_in, signal_out
from array import array
from correl import correl

SR = 16000
MINF0 = 80
MAXF0 = 180
bot = SR/MINF0
top = SR/MAXF0

if len(argv) != 3:
	exit("usage: " + argv[0] + " <input_file> <output_file>")

infile = argv[1]
outfile = argv[2]

x_in = signal_in(infile)

# Make a floating-point version of x_in, called x_in_f
x_in_f = array('f')
x_in_f.fromlist(x_in.tolist())

# 512 to offset lag introduced by windowing
f0 = array('h')
f0.fromlist(512 * [0])

print "Working..."
for i in range(0, len(x_in_f)-512):
	ans = correl(x_in_f[i:], x_in_f[i:], 512)

	maxlag = SR/MAXF0
	max = 0
	for j in range(top, bot+1):
		if ans[j] > max:
			maxlag = j
			max = ans[j]

	f0.append(SR/maxlag)
	if f0[-1] >= MAXF0: f0[-1] = 0

signal_out(f0, outfile)
exit(0)
