#!/usr/bin/python
# -*- coding: utf-8 -*-

# Suodattaminen (Filtering) ja
# Runnins means of 4
#
# Jos ajattelemme signaalia graafisessa muodossa, koostuu se laaksoista ja
# ja huipuista.
# Suodattamisen tarkoitus on muokata alkuperäistä signaalia, eli poistaa siitä 
# joitakin huippuja tai laaksoja, jotka ovat jonkin rajaarvon 
# ylä- tai alapuolella.
# Jos signaalin taustalla on korkeita ääniä tai joku on yskäissyt 
# nauhoituksen aikana
# voivat nuo vääristymät aiheuttaa virheitä analyysissä jos niitä ei poisteta.
# Yksi yksikertainen tapa muokata signaalia lokaalisti eli muokata jotain 
# näytettä suhteessa sen lähimpiin näytteisiin on liikkuva keskiarvo 
# (Runnins means of 4). Esimerkissä on valittu näytteiden lukumääräksi 4, mutta
# se voisi olla mikä tahansa luku.
# Laskiessamme liikkuvaa keskiarvoa otamme ensin näytteet 1-4, laskemme ne 
# yhteen ja jaamme tuloksen neljällä (joka on siis sama kuin se että kerrotaan 
# jokainen arvo 1/4). Sitten laskemme näytteet 2-5, 3-6, 4-7, ... jne. kunnes
# koko signaalin kaikki näytteet on käyty läpi.
# Hyöty tästä on siinä, että uudessa keskiarvoistetussa signaalissa nopeasti
# muuttuvat kohdat tasoittuvat ja hitaammin muuttuvat kohdat tulevat selvemmin 
# esille.
# Jos ajattelemme asiaa taajuuden kannalta, ovat nopeasti muuttuvat kohdat
# korkeampien taajuuksien komponentteja ja hitaammin muuttuvat kohdat
# matalampien taajuuksien komponentteja.
# Niän korkean taajuuden tapahtumat tulevat tasoitetuksi. Filttereitä kutsutaan
# eri nimillä riipuen kumpia taajuuksia pyritään tasoittamaan. Jos tasoitetaan
# korkeita taajuuksia kutsutaan filtteriä low pass filtteriksi.
# Yksi ongelma tässä on. Jotta saisimme signaalia tasoitetuksi tarpeeksi tulee
# aika-ikkunoista todella isoja. Aika-ikkuna tarkoittaa sitä kuinka monta 
# näytettä keskiarvoistetaan (tässä siis 4).
# Yksi ratkaisu tähän on IIR-filtteri.



# Original C program Copyright by John Coleman in "Introducing Speech and 
# Language Processing",
# Cambridge University Press, 2005
# Listing 3.2 on page 52

# meansof4.py -Filters infile to produce outfile using running means of 4 with
# coefficients b1-b4.

from array import array
from sys import argv, exit
from slputils import signal_in, signal_out

if len(argv) != 3:
	exit("usage: " + argv[0] + " <input_file> <output_file>")

infile = argv[1]
outfile = argv[2]

x = signal_in(infile)
y = array('h',[0,0,0]) # The first three elements ar initialized to zero


signal_out(y,outfile)
exit(0)
