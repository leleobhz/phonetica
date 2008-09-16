#!/usr/bin/python
# -*- coding: utf-8 -*-


# IIR-filtteri (Infinite impulse Response)
#
# Jotta signaalin aika-ikkuna ei kasvaisi liian suureksi, on hyvä idea 
# laittaa näytteen tulos riipumaan sitä edeltävien näytteiden arvoista.
# Tällöin filtteröinnin vaikutukset tulevat koko signaalin laajuisiksi,
# sen sijaan että ne vaikuttaisivat vain aika-ikkunan pituiseen jaksoon 
# signaalista. Voimme myös halutessamme määritellä, että joku jakso vaikuttaa
# tulevaan osaan signaalista enemmän tai vähemmän kuin joku toinen.
# Tämä voidaan saada aikaan antamalla jakson näytteille pienemmät tai suuremmat
# kertoimet (esim. running means of 4:n kohdalla, kertoimien ei olisi tarvinnut
# välttämättä olla kaikille 1/4. Jos esimerkiksi aika-ikkunan 1 jaksolle olisi
# määritelty kerroin 2/5 ja lopuille 1/5, olisi 1 näyte ollut kaksinverroin 
# merkittävämpi. Mitään järkeä siinä ei sinänsä olisi ollut, mutta näin 
# ymmärtää idean helposti.)
# Kertoimien avulla voidaan myös määritellä mitä taajuuksia filtteri pyrkii
# suodattamaan milläkin ajanhetkellä (kuinka korkeita/kuinka matalia ja mistä 
# näytteestä mihin näytteeseen).

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



# Original C program Copyright by John Coleman in "Introducing Speech and Language Processing",
# Cambridge University Press, 2005
# Listing 3.3 on page 61

# filter.py - Filters infile to produce outfile using a fifth-order IIR filter
# with coefficients a1-a6 and b1-b6 (For an FIR filter, set a[0..5] to 0).


# Original C program Copyright by John Coleman in "Introducing Speech and 
# Language Processing",
# Cambridge University Press, 2005
# Listing 3.2 on page 52

# meansof4.py -Filters infile to produce outfile using running means of 4 with
# coefficients b1-b4.


from array import array
from sys import argv, exit
from slputils import signal_in, signal_out

# IIR-filtteri, FIR, jos a = {0,...,0}?
# parametrit: x = signaali, a = kertoimet edeltäville tuloksille,
# b = kertoimet alkuperäiselle signaalille
def filtteri(x, a, b):

	y = array('h')
	yf = array('f')
	for i in range(0, len(b)):
		y[i] = 0
		yf[i] = 0.0

	for i in range(len(b), len(x)):
		ytemp = 0
		for j in range(0, len(b)):
			if(j == 0):
				ytemp += b[j]*x[i-j]
			else:
				ytemp += b[j]*x[i-j] - a[j]*yf[i-j]
		y.append(ytemp)
	return y

def meansof4(x):
	# Coefficients for running means of 4
	a = [0, 0, 0, 0]
	b = [0.25, 0.25, 0.25, 0.25]
	return filtteri(x, a, b)

def highpass(x):
	# Coefficients for a high-pass filter 
	# (>3 kHz at 16000 samples/s)
	a = [0, -1.2323, 1.1667, -0.5207, 0.1459, -0.0160]
	b = [0.1275, -0.6377, 1.2755, -1.2755, 0.6377, -0.1275]
	return(filtteri(x, a, b))

