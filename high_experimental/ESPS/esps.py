"""
esps.py
by Kyle Gorman (kgorman@ling.upenn.edu)
Python module for speech processing and acoustic analysis using ESPS

For more information on esps.py, or to obtain the latest version, visit 
http://www.ling.upenn.edu/~kgorman/papers/esps.html

If you get the following error:

ImportError: No module named stats

You need to install the stats package to use that module (if you don't need the 
module that generates that error, simply don't import it). The stats package can
be obtained here: 

http://www.nmr.mgh.harvard.edu/Neural_Systems_Group/gary/python/stats.py

If you get the following error:

ImportError: No module named numpy

You need to install NumPy to use that module (or simply don't import that 
module, if you don't need it). NumPy can be obtained here:

http://numpy.scipy.org/
"""

from os import path,popen,system # used in nearly every program here

textgrid = """
### TEXTGRID FUNCTIONS ####

Manipulate Praat TextGrids

(n.b. on TextGrid IO: Each tier is represented as a Python list. Each entry in 
the list, interval or point, is a tuple in the list, in proper temporal order 
If necessary, do numeric sort by the first value of the tuple. The first value 
in the tuple of each list entry is the string associated with it. In the case of
a point tier, the second value of the list-entry tupleis the time of the point, expressed as a float. In the case of a interval tier, the second and third 
values of the list-entry tuple contain start and stop times as floats.)

EXAMPLE(S) ~ 

<code>
from esps import read,write
grid = read('test.TextGrid') # read in a TextGrid
write(grid,'new.TextGrid') # write that TextGrid to a new file
</code>
"""

def read(inputFile):
    """ 
    def read(inputFile):

    Input: name of Praat TextGrid file
    Output: List of tiers, which are lists of (string,time) tuples (in the case
    of point tiers), or (string,start,stop) tuples (in the case of interval 
    tiers)
    """
    file = open(inputFile,'r') # read it in
    lines = file.readlines() # sadly read it all into memory
    file.close() # close it out
    lines.pop(0) # file type
    lines.pop(0) # object class
    lines.pop(0) # blank
    tiers = [] # data structure that will contain all the tiers
    if 'xmin' in lines[0]: # long TextGrid if this is true
        lines.pop(0) # xmin
        lines.pop(0) # xmax
        lines.pop(0) # tiers?
        junk,nTiers = lines.pop(0).rstrip().split(' = ') # size
        lines.pop(0) # item []
        for i in range(int(nTiers)): # loop over the tiers
            lines.pop(0) # the first tier's start
            if 'IntervalTier' in lines[0]: # we can check directly for interval
                lines.pop(0) # class type
                lines.pop(0) # tier name
                lines.pop(0) # xmin
                lines.pop(0) # xmax
                junk,nInter = lines.pop(0).rstrip().split(' = ') # size
                intervalTier = [] # data struct, gonna be full of tuples
                for j in range(0,int(nInter)): # loop over interval tier itself
                    lines.pop(0) # interval number
                    junk,xmin = lines.pop(0).rstrip().split(' = ') # start
                    junk,xmax = lines.pop(0).rstrip().split(' = ') # stop
                    junk,label = lines.pop(0).rstrip().split(' = ') # label
                    intervalTier.append((float(xmin),float(xmax), \
                                                            label.strip('"')))
                tiers.append(intervalTier) # now write it into big structure
            else: # if point tier
                lines.pop(0) # class type
                lines.pop(0) # tier name
                lines.pop(0) # xmin
                lines.pop(0) # xmax
                junk,nInter = lines.pop(0).rstrip().split(' = ') # size
                pointTier = [] # data struct, gonna be full of tuples
                for j in range(0,int(nInter)): # loop over point tier itself
                    lines.pop(0) # point number
                    junk,time = lines.pop(0).rstrip().split(' = ') # time
                    junk,label = lines.pop(0).rstrip().split(' = ') # label
                    pointTier.append((float(time),label.strip('"'))) # save it
                tiers.append(pointTier) # now write it into big structure
    else: # short TextGrid format
        lines.pop(0) # xmin
        lines.pop(0) # xmax
        lines.pop(0) # tiers
        nTiers = lines.pop(0).rstrip() # size
        for i in range(0,int(nTiers)): # loop over the tiers
            if 'IntervalTier' in lines[0]: 
                lines.pop(0) # class type
                lines.pop(0) # tier name
                lines.pop(0) # xmin
                lines.pop(0) # xmax
                nInter = lines.pop(0).rstrip() # interval size
                intervalTier = [] # data struct, gonna be full of tuples
                for j in range(int(nInter)): # loop over the ier
                    xmin = float(lines.pop(0).rstrip()) # start
                    xmax = float(lines.pop(0).rstrip()) # stop
                    label = lines.pop(0).rstrip() # label
                    intervalTier.append((xmin,xmax,label.strip('"'))) # save
                tiers.append(intervalTier) # now write it into big structure
            else: # point tier
                lines.pop(0) # class type
                lines.pop(0) # tier name
                lines.pop(0) # xmin
                lines.pop(0) # xmax
                nInter = lines.pop(0).rstrip() # interval size
                pointTier = [] # data struct, gonna be full of tuples
                for j in range(int(nInter)): # loop over the ier
                    time = float(lines.pop(0).rstrip()) # time
                    label = lines.pop(0).rstrip() # label
                    pointTier.append((time,label.strip('"'))) # save it
                tiers.append(pointTier) # now write it into big structure
    return tiers # now we are done

# write a Praat TextGrid
def write(list,outputFile,format=None):
    """ 
    def write(list,outputFile,format=None):

    Input: lists of tiers, which are lists of (string,time) tuples (in the 
    case of point tiers) in temporal order and/or lists of (string,start,stop) 
    tuples (in the case of interval tiers), an output filename, and an output
    filename(,format i.e. TextGrid is 'short' format if non-null)
    Output: none, but TextGrid is printed to output file 
    """
    file = open(outputFile,'w') # open for writing
    xmin,xmax = (),None # positive and negative infinity
    if (format): # nonnull, so short
        file.write('File type = "ooTextFile"\n') # write header first line
        file.write('Object class = "TextGrid"\n\n') # 2nd and third line 
        for tier in list: # loop over tiers
            if len(tier[0]) > 2: # interval
                if tier[0][0] < xmin: # if a smaller xmin
                    xmin = tier[0][0] # save it
                if tier[-1][2] > xmax: # if a bigger xmax
                    xmax = tier[-1][1] # save it
            else: # point tier
                if tier[0][0] < xmin: # if a smaller xmin
                    xmin = tier[0][0] # save it
                if tier[-1][0] > xmax: # if a bigger xmax
                    xmax = tier[-1][0] # save it
        file.write(str(xmin) + '\n') # xmin
        file.write(str(xmax) + '\n') # xmax
        file.write('<exists>\n') # tiers line
        file.write(str(len(list)) + '\n') # number of tiers
        tCounter = 1 # keep track of the number of tiers
        for tier in list: # loop over tiers
            if len(tier[0]) > 2: # is it start/stop or just point?
                file.write('"IntervalTier"\n') # class label
                file.write('"' + str(tCounter) + '"\n') # n/m
                file.write(str(xmin) + '\n') # xmin
                file.write(str(xmax) + '\n') # xmax
                file.write(str(len(tier)) +'\n') # number of intervals
                iCounter = 1 # keep track of the number of intervals
                for intrvl in tier: # loop over vals
                    file.write(str(intrvl[0]) + '\n') # xmin
                    file.write(str(intrvl[1]) + '\n') # xmax
                    file.write('"' + intrvl[2] + '"\n') # label
                    iCounter = iCounter + 1 # increment interval counter
            else: # type is point tier
                file.write('"TextTier"\n') # class label
                file.write('"' + str(tCounter) + '"\n') 
                file.write(str(xmin) + '\n') # xmin
                file.write(str(xmax) + '\n') # xmax
                file.write(str(len(tier)) +'\n')
                pCounter = 1 # keep track of the number of points
                for point in tier: # loop over vals
                    file.write(str(point[0]) + '\n')
                    file.write('"' + point[1] + '"\n') 
                    pCounter = pCounter + 1 # increment point counter
            tCounter = tCounter + 1 # increment tier counter
    else: # long format TextGrid
        file.write('File type = "ooTextFile"\n') # write header first line
        file.write('Object class = "TextGrid"\n\n') # 2nd and third line 
        for tier in list: # loop over tiers
            if len(tier[0]) > 2: # is it interval?
                if tier[0][0] < xmin: # if a smaller xmin
                    xmin = tier[0][0] # save it
                if tier[-1][1] > xmax: # if a bigger xmax
                    xmax = tier[-1][1] # save it
            else: # point tier
                if tier[0][0] < xmin: # if a smaller xmin
                    xmin = tier[0][0] # save it
                if tier[-1][0] > xmax: # if a bigger xmax
                    xmax = tier[-1][0] # save it
        file.write('xmin = ' + str(xmin) + '\n') # xmin
        file.write('xmax = ' + str(xmax) + '\n') # xmax
        file.write('tiers? <exists>\n') # tiers line
        file.write('size = ' + str(len(list)) + '\n') # number of tiers
        file.write('item []:\n') # last piece of header
        tCounter = 1 # keep track of the number of tiers
        for tier in list: # loop over tiers
            file.write('    item [' + str(tCounter) + ']:\n') # item number
            if len(tier[0]) > 2: # is it start/stop or just point?
                file.write('        class = "IntervalTier"\n') # class label
                file.write('        name = "' + str(tCounter) + '"\n') # n/m
                file.write('        xmin = ' + str(xmin) + '\n') # xmin
                file.write('        xmax = ' + str(xmax) + '\n') # xmax
                file.write('        intervals: size = ' + str(len(tier)) +'\n')
                iCounter = 1 # keep track of the number of intervals
                for intrvl in tier: # loop over vals
                    file.write('        intervals [' + str(iCounter) + ']:\n')
                    file.write('            xmin = ' + str(intrvl[0]) + '\n')
                    file.write('            xmax = ' + str(intrvl[1]) + '\n')
                    file.write('            text = "' + intrvl[2] + '"\n') 
                    iCounter = iCounter + 1 # increment interval counter
            else: # type is point tier
                file.write('        class = "TextTier"\n') # class label
                file.write('        name = "' + str(tCounter) + '"\n') 
                file.write('        xmin = ' + str(xmin) + '\n') # xmin
                file.write('        xmax = ' + str(xmax) + '\n') # xmax
                file.write('        points: size = ' + str(len(tier)) +'\n')
                pCounter = 1 # keep track of the number of points
                for point in tier: # loop over vals
                    file.write('        points [' + str(pCounter) + ']:\n')
                    file.write('            time = ' + str(point[0]) + '\n')
                    file.write('            mark = "' + point[1] + '"\n') 
                    pCounter = pCounter + 1 # increment point counter
            tCounter = tCounter + 1 # increment tier counter
    file.close() # close it out

def readMLF(inputFile):
    """ 
    def readMLF(inputFile):

    Input: name of HTK .mlf file created by issuing the command HVITE -o SM...
    Output: A list of tuples. Each tuple is a (string,list) pair. The string
    corresponds to the string denoting the file used to generate the .mlf. 
    The list is a grid, a list of tiers. The list is always two items long 
    (but not a tuple for conformity to the TextGrid functions above). The first
    list is the phones list. The second list is the word list. Each one of these
    lists is a list of (string,start,stop) tuples. By passing each list in the 
    top-level tuple to an appropriately named file via writeTextGrid(), you can
    create TextGrids for all the files in an .mlf. 
    """
    file = open(inputFile,'r') # open file
    lines = file.readlines() # and read it in
    file.close() # let the file go
    lines.pop(0) # get rid of the useless first line
    name,word,words,phones,gridList = '',(),[],[],[] # out of scope of loop
    sr = 10000000 # 100 ns sampling rate for .mlfs, i think this is fixed?
    for line in lines: # loop over lines
        if line[0] == '"': # look for filename
            gridList.append((name,[words,phones])) # write out that
            word,words,phones = '',[],[] # reset these
            folder,item = path.split(line.lstrip('"').rstrip().rstrip('"'))
            name,ext = path.splitext(item) # get file name
        else: # actual data
            strings = line.split() # get line
            if len(strings) == 4: # string and word
                if len(word) == 2: # not the blank initial one
                    words.append((word[0],float(strings[0])/sr,word[1]))
                word = (float(strings[0])/sr,strings[3]) # save word for later
                phones.append((float(strings[0])/sr,float(strings[1])/sr,
                                                           strings[2])) # phones
            elif len(strings) == 3: # string only, if it's 1, do nothing
                phones.append((float(strings[0])/sr,float(strings[1])/sr,
                                                           strings[2])) # phones
            else: # string is period, dump al the data and the word
                words.append((word[0],phones[len(phones)-1][1],word[1]))
                word = () # clean this up for later
    gridList.pop(0) # hack to save a bunch of conditional checking
    return gridList # this is a list of grids which are lists and so on

arpabet = """
### ARPABET FUNCTIONS ###

Get ARPABET phones by features
"""

def monothongs():
    return ['IY','UW','IH','UH','EH','AH','AE','AO','AA']

def diphthongs():
    return ['EY','OW','OY','AY','AW']

def rhoticVowels():
    return ['AOR','AAR','IHR','URH','EHR','ER']

def vowels():
    return monothongs() + diphthongs() + rhoticVowels()

def vlStops():
    return ['P','T','K'] 

def vdStops():
    return ['B','D','G'] 

def stops():
    return vlStops() + vdStops()

def affricates():
    return ['JH','CH']

def vlFricatives(): 
    return ['SH','TH','S','F','H']

def vdFricatives():
    return ['DH','ZH','V','Z']

def fricatives():
    return vdFricatives() + vlFricatives()

def vlObstruents():
    return vlFricatives() + vlStops() + ['CH']

def vdObstruents():
    return vdFricatives() + vdStops() + ['JH']

def obstruents():
    return vlObstruents() + vdObstruents()

def nasals():
    return ['M','N','NG']

def approximants():
    return ['R','Y','L','W']

def sonorants():
    return nasals() + approximants()

def voiceless():
    return vlFricatives() + vlStops() + ['CH']

def voiced():
    return vdFricatives() + vdStops() + ['JH'] + sonorants()

def consonants():
    return voiceless() + voiced()

def arpabet():
    return vowels() + consonants()

get_f0 = """
### F0/RMS FUNCTIONS ###

Do F0 and/or RMS analysis

EXAMPLE(S) ~ 

<code>
import esps as E
f0s = E.F0('test.wav',0.002,100,450) # extract F0 for male speaker
wf0s = E.whiskerSquash(f0s) # squash whisker outliers
lf0s = E.logNormal(wf0s) # log-normalize
slope,intercept = E.linearFit(lf0s) # linear regression parameters
pf0s = E.percentileSquash(f0s) # squash outside 10-90th percentile
zf0s = E.zNormal(pf0s) # z-normalize
polys = E.legendreFit(zf0s,4) # Legendre Polynomials (n=4) fitting
f0_sample = E.F0[E.f0Sample(f0s,1.2)] # get the f0 value nearest to 1.2 S
f0_rangle = E.f0Slice(f0s,0.8,2.2) # get f0 samples between 0.8 and 2.2 S
</code>

HOW TO CITE ~ 

To cite get_f0, you can cite a paper by one of the authors (David Talkin) on 
the RAPT algorithm used; it appeared in a volume in 1995:

@incollection{talkin1995,
    Author = {Talkin, David},
    Booktitle = {Speech Coding and Synthesis},
    Editor = {Kleijn, W.B. and Paliwal, K.K.},
    Publisher = {Elsevier},
    Title = {{A Robust Algorithm for Pitch Tracking (RAPT)}},
    Year = {1995}}

Or just the get_f0 manual:

@manual{get_f0,
    Author = {Talkin, David and Lin, Derek},
    Organization = {{Entropic Research Laboratory}},
    Title = {get_f0}}
"""

def F0(file,sr=0.01,xmin=100,xmax=500):
    """ 
    def F0(file,sr=0.01,xmin=100,xmax=500):

    Input: wav file name(,sampling rate,min f0,max f0)
    Output: List of (time,F0) tuples 
    """
    system('export USE_ESPS_COMMON="off"') # turn off Common
    paramFile = open('params','w') # make param file
    paramFile.write('float\tmin_f0\t= '+str(xmin)+';\n') # min
    paramFile.write('float\tmax_f0\t= '+str(xmax)+';\n') # max
    paramFile.close() # shut her down
    folder,item = path.split(file) # get folder
    name,ext = path.splitext(item) # get extension
    out = '/tmp/' + name + '.f0' # output file
    system('get_f0 -i ' + str(sr) + ' -P params ' + file + ' ' + out) # call 
    offset = float(popen('hditem -i start_time ' + out).readline().rstrip()) # 1
    fstep = float(popen('hditem -i frame_step ' + out).readline().rstrip()) # fs
    f0s = [] # data structure for f0s
    for line in popen('pplain ' + out,'r'): # open f0 file
        value, junk, junk, junk = line.rstrip().split() # split it
        if float(value) > 0: # test if it's non-zero
            f0s.append((offset,float(value))) # now a tuple
        offset = offset + fstep # either way, increment time
    system('rm -f ' + out) # clean up the mess
    system('rm -f params') # and the params
    return f0s # a tuple list of value, 

def RMS(file,sr=0.01):
    """ 
    def RMS(file,sr=0.01):

    Input: wav file name(,sampling rate)
    Output: List of (time,RMS) tuples 
    """
    system('export USE_ESPS_COMMON="off"') # turn off Common
    folder,item = path.split(file) # get folder
    name,ext = path.splitext(item) # get extension
    out = '/tmp/' + name + '.f0' # output file for get_f0
    system('get_f0 -i ' + str(sr) + ' ' + file + ' ' + out) # call 
    offset = float(popen('hditem -i start_time ' + out).readline().rstrip())
    fstep = float(popen('hditem -i frame_step ' + out).readline().rstrip())
    rmss = [] # data structure for Rms values 
    for line in popen('pplain ' + out,'r'): # open f0 file
        junk, junk, value, junk = line.rstrip().split() # split the data
        rmss.append((offset,float(value))) # append useful stuff
        offset = offset + fstep
    system('rm ' + out) # clean up the mess
    return rmss

def both(file,sr=0.01,xmin=100,xmax=500):
    """ 
    def both(file,sr=0.01,xmin=100,xmax=500):

    Input: wav file name(,sampling rate,min f0,max f0)
    Output: List of (time,F0) tuples, list of (time,RMS) tuples 
    """
    system('export USE_ESPS_COMMON="off"') # turn off Common
    paramFile = open('params','w') # make param file
    paramFile.write('float\tmin_f0\t= '+str(xmin)+';\n') # min
    paramFile.write('float\tmax_f0\t= '+str(xmax)+';\n') # max
    paramFile.close() # shut her down
    folder,item = path.split(file) # get folder
    name,ext = path.splitext(item) # get extension
    out = '/tmp/' + name + '.f0' # output file for get_f0
    system('get_f0 -i ' + str(sr) + ' -P params ' + file + ' ' + out) # call 
    offset = float(popen('hditem -i start_time ' + out).readline().rstrip())
    fstep = float(popen('hditem -i frame_step ' + out).readline().rstrip())
    f0s = [] # data structure for f0s values 
    rmss = [] # data structure for Rms values 
    for line in popen('pplain ' + out,'r'): # open f0 file
        f0, junk, rms, junk = line.rstrip().split() # split the data
        if float(f0) > 0:
            f0s.append((offset,float(f0))) # tuple now
        rmss.append((offset,float(rms))) # tuple now
        offset = offset + fstep # increment current time by the framestep
    system('rm ' + out) # clean up the mess
    return f0s,rmss

def readPitchTier(inputFile):
    """ 
    def readPitchTier(inputFile):

    Input: Praat PitchTier file name
    Output: List of (time,F0) tuples 
    """
    file = open(inputFile,'r') # read it in 
    lines = file.readlines() # sadly, put it all into memory
    file.close() # close it out
    lines.pop(0) # file type
    lines.pop(0) # object class
    lines.pop(0) # blank
    f0s = [] # f0 best candidates
    if 'xmin' in lines[0]: # long PitchTier if this is true
        lines.pop(0) # xmin
        lines.pop(0) # xmax
        lines.pop(0) # size
        while lines: # loop over lines, sorta
            lines.pop(0) # 'points [n]' header
            line = lines.pop(0).split(' = ') # split
            time = float(line[1])
            line = lines.pop(0).split(' = ') # split
            f0s.append((time,float(line[1]))) # tuple here
    else: # short PitchTier
        lines.pop(0) # xmin
        lines.pop(0) # xmax
        lines.pop(0) # size
        while lines: # loop over lines, sorta
            f0s.append((float(lines.pop(0)),float(lines.pop(0)))) # tuple
    return f0s # return statement

def percentileSquash(f0s,lo=.1,hi=.9):
    """ 
    def percentileSquash(f0s,lo=.1,hi=.9):
    
    Input: list of (time,F0) tuples(,low percentile, high percentile)
    Output: list of percentile-squashed (time,F0) tuples 
    """
    sF0s = sorted([f0 for (time,f0) in f0s]) # destructure into sorted f0 vals
    xmin = sF0s[int(round(lo*len(sF0s)))] # get min via rank
    xmax = sF0s[int(round(hi*len(sF0s)))] # get max via rank
    return [(time,f0) for (time,f0) in f0s if (xmin < f0 < xmax)]

def whiskerSquash(f0s):
    """ 
    def whiskerSquash(f0s):

    Input: list of (time,F0) tuples
    Output: list of whisker-squashed (time,F0) tuples 
    """
    sF0s = sorted([f0 for (time,f0) in f0s]) # destructure into sorted f0 vals
    Q1 = sF0s[int(round(.25*len(sF0s)))] # get min via rank
    Q3 = sF0s[int(round(.75*len(sF0s)))] # get max via rank
    xmin = Q1-1.5*(Q3-Q1) # find min
    xmax = Q3+1.5*(Q3-Q1) # find max
    return [(time,f0) for (time,f0) in f0s if (xmin < f0 < xmax)] # filter

def zNormal(f0s):
    """ 
    def zNormal(f0s):


    Input: list of (time,F0) tuples
    Output: list of z-normalized (time,F0) tuples
     """
    from stats import stdev # it's easiest this way
    nF0s = [f0 for (time,f0) in f0s] # destructure 
    mu = sum(nF0s)/len(nF0s) # get mean
    sigma = stdev(nF0s) # get s.d.
    return [(time,(f0-mu)/sigma) for (time,f0) in f0s] # apply normalization

def logNormal(f0s):
    """ 
    def logNormal(f0s):

    Input: list of (time,F0) tuples
    Output: list of log-normalized (time,F0) tuples 
    """
    from math import log # not part of the base, i guess?
    nF0s = [f0 for (time,f0) in f0s] # destructure 
    xmin = min(nF0s) # get min
    xmax = max(nF0s) # get max
    return [(time,(1/log(xmax/xmin))*log(f0/xmin)) for (time,f0) in f0s] # norm

def linearFit(f0s):
    """ 
    def linearFit(f0s):

    Input: list of (time,F0) tuples
    Output: slope, corrected intercept
    """
    from stats import linregress # this is just a wrapper
    slope,intercept,a,b,c = linregress([f0 for (time,f0) in f0s],
                                        [time-f0s[0][0] for (time,f0) in f0s])
    return slope,intercept # return slope, and corrected intercept

def legendreFit(f0s,n=3):
    """ 
    def legendreFit(f0s,n=3):

    Input: list of (time,F0) tuples, and the number of polynomials (n<8)
    Output: list of length-normalized polynomial weights
    """
    import numpy as N # need numpy for matrix functions
    assert type(n) == int and 0 < n <8,'n must be integer between 0 and 8'  
    # step one: interpolate
    interF0s = [] # interpolation f0 value list
    timeStep = f0s[1][0] - f0s[0][0]
    for i in range(len(f0s)-1):
        interF0s.append(f0s[i][1])
        if ((f0s[i+1][0] - timeStep) - f0s[i][0]) > 0.0001:
            f0Dif = f0s[i][1] - f0s[i+1][1]
            timeDif = ((f0s[i+1][0] - timeStep) - f0s[i][0]) 
            nSamples = int(round(timeDif/timeStep)) - 1
            f0Step = f0Dif/nSamples
            for j in range(nSamples): # loop over issing samples
                interF0s.append(f0s[i][1]+((j+1)*f0Step)) # append interpol
    interF0s.append(f0s[-1][1]) # last case, can't leave this off
    left = (max(interF0s) - min(interF0s))/2 # norm factor for orthonormalizaion
    right = min(interF0s) + left # norm factor for orthonormalization
    interF0s = [(f0-right)/left for f0 in interF0s] # orthonormalize in place
    length = len(interF0s) # need this a couple times 
    nZer = N.ones(length) # n = 0: can't find a cloesd form for this anywhere
    nOne = N.linspace(-1,1,length) # n = 1 
    nTwo = N.array((.5)*(3*(nOne**2)-1)) # n = 2 
    nTre = N.array((.5)*(5*(nOne**3)-3*nOne)) # n = 3 
    nFor = N.array((.125)*(35*(nOne**4)-30*(nOne**2)+3)) # n = 4 
    nFiv = N.array((.125)*(63*(nOne**5)-70*(nOne**3)+15*nOne)) # n = 5
    nSix = N.array((.0625)*(231*(nOne**6)-315*(nOne**4)+105*nOne**2)-5) # n = 6
    nSev = N.array((.0625)*(429*(nOne**7)-639*(nOne**5)+315*nOne**3)-35*nOne) #7
    basis = N.matrix([nZer,nOne,nTwo,nTre,nFor,nFiv,nSix,nSev]) # basis set
    return ((N.matrix(interF0s)*N.transpose(basis[:n+1,:]))/length).tolist()[0]

def f0Sample(f0s,sampleTime):
    """  
    def f0Sample(f0s,sampleTime):

    Input: a list of (time,f0) tuples, a time to be sampled at 
    Output: returns the (0-initial) index of the nearest sample 
    """
    from bisect import bisect # bianry search code
    index = bisect([time for (time,f0) in f0s],sampleTime) # list index
    if f0s[index][0] - sampleTime > sampleTime -f0s[index-1][0]: # go left
        return index - 1 # left side
    else: # go right
        return index # right side

def f0Slice(f0s,start,stop):
    """
    def f0Slice(f0s,start,stop):

    Input: a list of (time,f0) tuples, start time, stop time
    Output: a list of tuples that fall between the start and stop times
    """
    from bisect import bisect # binary search, sorta 
    i = bisect([time for (time,f0) in f0s],start) # leftside, inclusive
    j = bisect([time for (time,f0) in f0s],stop) # rightside, exclusive
    return f0s[i:j] # return if inside this

"""
### FORMANT FUNCTIONS ###

Get formant frequencies and bandwidths

EXAMPLE(S) ~ 

<code>
import esps as e
freqs,bands = e.formant('test.wav',0.001) # extract formants at 1 ms timestep
region = e.formantSlice(freqs,1.2,2.0) # grab region of a vowel, for instance
f1region = [(f1,f2) for (time,f1,f2,f3,f4) in region] # f1/f2 during the vowel
</code>

HOW TO CITE ~ 

To cite formant, you can cite the paper on the algorithm used, from the 
proceedings of ICASSP83:

@inproceedings{Secrest1983,
    Author = {Secrest, B.G. and Doddington, G.R.},
    Booktitle = {Proceedeings ICASSP83},
    Pages = {1352--1355},
    Title = {{An integrated pitch tracking algorithm for speech systems}},
    Year = {1983}}

You can also cite the formant manual: 

@manual{formant,
    Author = {Talkin, David},
    Organization = {{Entropic Research Laboratory}},
    Title = {formant},
    Year = {1993}}
"""

def formant(file,sr=0.01):
    """
    def formant(file,sr=0.01):

    Input: a audio file name (, sampling rate)
    Output: list of (time,f1,f2,f3,f4) tuples, list of (time,b1,b2,b3,b4) tuples
    """
    system('export USE_ESPS_COMMON="off"') # turn off Common
    folder,item = path.split(file) # get prefix
    name,ext = path.splitext(item) # get extension
    system('formant -B 999999999 -O /tmp -i '+ str(sr) + ' ' +  file) # call 
    offset = float(popen('hditem -i start_time /tmp/'+name+'.fb').readline())
    fstep = 1/float(popen('hditem -i record_freq /tmp/'+name+'.fb').readline())
    freqs,bands = [],[] # data-structs
    for line in popen('pplain /tmp/'+name+'.fb','r'): # open fb file
        formants = line.rstrip().split() # split line 
        freqs.append(tuple([offset] + formants[:4])) # list formant freqs
        bands.append(tuple([offset] + formants[4:])) # list formant bands
        offset = offset + fstep # increment time  
    system('rm /tmp/' + name + '.*') # clean up the mess
    return freqs,bands # return both lists

def formantSample(formants,sampleTime):
    """
    def formantSample(formants,sampleTime):

    Input: a list of (time,f1,f2,f3,f4) tuples (or bandwidth tuples), a time to 
    be sampled at
    Output: returns the (0-initial) index of the nearest sample
    """
    from bisect import bisect # binary search
    index = bisect([time for (time,f1,f2,f3,f4) in formants],sampleTime) # index
    if formants[index][0] - sampleTime > sampleTime - formants[index-1][0]: # lt
        return index - 1 # left side
    else: # go right
        return index # right side

def formantSlice(formants,start,stop):
    """
    def formantSlice(formants,star,stop):
    
    Input: a list of (time,f1,f2,f3,f4) tuples (or bandwidth tuples), start 
    time, stop time
    Output: a list of tuples that fall between the start and stop times
    """
    from bisect import bisect # binary search, sorta
    i = bisect([time for (time,f1,f2,f3,f4) in formants],start) # leftside
    j = bisect([time for (time,f1,f2,f3,f4) in formants],stop) # right side
    return formants[i:j] # return if inside

sgram = """
SPECTROGRAM FUNCTIONS ~ 

Get spectral intensities

EXAMPLE(S) ~ 

<code>
import esps as e # import module
ffts = e.FFT('test.wav') # do the extraction
syl = e.FFTSlice(ffts,0.2,0.4) # get a region
avg = e.FFTAverage(syl) # get the spectral avverage in that region
balance = e.balance(avg) # get Sluitjer and van Heven spectral balance coefs
slope,intercept = e.tilt(syl) # get Thiessen and Saffran slope and interncept
</code>

HOW TO CITE ~ 

The algorithm used is a version of the well-known Fast Fourier Transform, 
originally discovered by Gauss. You can, however, cite the sgram manual:

@manual{sgram,
    Author = {Burton, David and Johnson, Rod and Shore, John},
    Organization = {{Entropic Research Laboratory}},
    Title = {sgram},
    Year = {1997}}

The balance technique is modified from Thiessen and Saffran 2004:

@article{Thiessen2004,
    Author = {Thiessen, Erik and Saffran, Jenny},
    Journal = {Perception \& Psychophysics},
    Number = {5},
    Pages = {779--791},
    Title = {{Spectral tilt as a cue to word segmentation in infancy and 
    adulthood}},
    Volume = {66},
    Year = {2004}}

The bands used for the spectral tilt calculation are as suggested by Sluitjer
and van Heven 1996:

@article{sluitjer1996,
    Author = {Sluijter, Agaath M.C. and van Heven, Vincent J.},
    Journal = {Journal of the Acoustical Society of America},
    Number = {4},
    Pages = {2471--2485},
    Title = {{Spectral balance as an acoustic correlate of lingustic stress}},
    Volume = {100},
    Year = {1996}}
"""

def FFT(file):
    """
    def FFT(file):

    Input: wav file name
    Output: a list of (time, list of energy bins) tuples   
    """
    system('export USE_ESPS_COMMON="off"') # turn off Common
    folder,item = path.split(file) # get prefix
    name,ext = path.splitext(item) # get extension
    system('sgram -m wb '+' '+file+' /tmp/'+name+'.sg')
    offset = float(popen('hditem -i start_time /tmp/'+name+'.sg').readline())
    fstep = 1/float(popen('hditem -i record_freq /tmp/'+name+'.sg').readline())
    slices = [] # data structure
    for line in popen('pplain /tmp/'+name+'.sg'): # open fb file
        powers = line.rstrip().split() # split it up
        powers.pop(0) # the first one is junk
        slices.append((offset,[int(power) for power in powers])) # stor
        offset = offset + fstep # increment time
    system('rm /tmp/'+name+'.sg') # clean that up
    return slices # return the data

def tilt(fft):
    """
    def tilt(fft):

    Input: a single (time, list of energies) tuple
    Output: the spectral tilt slope and intercept, defined by regression on the
    energy between 500 Hz and 4 KHz. 
    """
    from stats import linregress # need this to calculate
    band = fft[1][6:48] # between 500 Hz and 4 KHz, assuming CD-qual audio
    slope,intercept,a,b,c = linregress(range(len(band)),band) # do the regress
    return slope,intercept

def balance(fft):
    """
    def balance(fft):
    
    Input: a single (time, list of energies) tuple
    Output: a tuple of the energies in the bands (0:500 Hz, 500:1000 Hz, 1:2 
    KHz, 2-4 KHz)
    """
    from math import log # need this
    bandOne = log(sum(fft[1][0:6])) # f0, 0:500 Hz
    bandTwo = log(sum(fft[1][6:13])) # f1, 500:1000 Hz
    bandThr = log(sum(fft[1][13:25])) # f2, 1:2 KHz
    bandFor = log(sum(fft[1][25:48])) # f3 and f4, 2:4 KHz
    return (bandOne,bandTwo,bandThr,bandFor) # return 

def FFTSample(ffts,sampleTime):
    """
    def FFTSample(ffts,sampleTime):

    Input: a list of (time,list of energies) tuples, a time to be sampled at
    Output: returns the (0-initial) index of the nearest sample
    """
    from bisect import bisect # binary search
    index = bisect([time for (time,energies) in ffts],sampleTime) # index
    if ffts[index][0] - sampleTime > sampleTime - ffts[index-1][0]: # lt
        return index - 1 # left side
    else: # go right
        return index # right side

def FFTAverage(ffts):
    """
    def FFTAverage(ffts):

    Input: a list of (time, list of energies) tuples
    Output: a single (tme,list of energies) tuple, averaging across inputs
    """
    time, energies = 0, [0] * len(ffts[0][1]) # list of zeros 
    for fft in ffts: # loop over observations
        time = time + fft[0] # time
        for i in range(len(fft[1])): # energies
            energies[i] = energies[i] + fft[1][i] # add each one
    return (time/len(ffts),[energy/len(ffts) for energy in energies]) # return

def FFTSlice(ffts,start,stop):
    """
    def FFTSlice(ffts,star,stop):
    
    Input: a list of (time,list of energy) tuples, start time, stop time
    Output: a list of tuples that fall between the start and stop times
    """
    from bisect import bisect # binary search, sorta
    i = bisect([time for (time,powers) in ffts],start) # leftside
    j = bisect([time for (time,powers) in ffts],stop) # right side
    return ffts[i:j] # return if inside
