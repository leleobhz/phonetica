/* Pitch extractor and pitch period marking program by Chris Tuerk, 
   Cambridge University Engineering Department, Trumpington Street,
   Cambridge, CB2 1PZ, England, U.K.

1.   Modified August 1993 by R.E.Donovan, also of CUED.

2.   Modified June 1994 by S.R.Waterhouse, also of CUED.
    Modified to be independent of ajr's libraries - Makefile installed.
    HP compatibility installed.

See file pitch_track.readme for extra details.

*/

#ifndef HTKCOMPILE

# include "pitch_tools.h"

#endif


#include <stdio.h>         /* Standard Libraries */
#include <stdlib.h>
#include <string.h>
#include <ctype.h>
#include <math.h>

/* if HTKCOMPILE flag is set include the HTK libraries */

#ifdef HTKCOMPILE
#include "/tools/htk/HTK_V1.4C/HTKLib/HShell.h"
#include "/tools/htk/HTK_V1.4C/HTKLib/HMath.h"
#include "/tools/htk/HTK_V1.4C/HTKLib/HSigP.h"
#include "/tools/htk/HTK_V1.4C/HTKLib/HSpIO.h"
#include "/tools/htk/HTK_V1.4C/HTKLib/HLabel.h"
#include "/tools/htk/HTK_V1.4C/HTKLib/HDbase.h"  
#include "/tools/htk/HTK_V1.4C/HTKLib/HModel.h"
#endif


# define THRESHOLD              0.75
# define LOW_THRESHOLD          0.6
# define HIGHER_THRESHOLD       0.96
# define THRESHOLD1             0.75
# define THRESHOLD2             0.80
# define THRESHOLD3             0.90
# define THRESHOLD4             0.96

#ifndef SGICOMPILE
# define NUM_FRAMES             800  /* suns fall over if num_frames gets  */
#endif                               /* bigger than this - titchy memory i */
                                     /* think.                             */
#ifdef SGICOMPILE
# define NUM_FRAMES             2000
#endif

#ifdef HPCOMPILE
# define NUM_FRAMES             2000
#endif                               /* HP compatibility added - S.R.W */

#define MAX_CANDS               130
#define GROUP_SIZE              200
#define PENALTY                 0.50
#define VBIG                    99E99
#define MAX_SEP                 0.125

#ifndef HTKCOMPILE
static int DEFAULT_HEADER_SIZE = 12;
static int DEFAULT_SAMPLING_RATE = 16000;
#endif

static int DEFAULT_MIN_PITCH = 40;
static int DEFAULT_MAX_PITCH = 200;

void help(char message[])
{
  fprintf(stderr," in the help routine...\n");
  fprintf(stderr, "%s\n", message);
  exit(1);
}

int round(float num)
{
  if (num - (int) num > 0.5) return((int) num + 1);
  else return((int) num);
}

float absof(float num)
{
  if (num < 0) return(-num);
  else return(num);
}

float square(float num)
{
  return(num * num);
}

float pos(float num)
{
  if (num < 0)
    return(-1 * num);
  else
    return(num);
}

int pfunc(int i, int j)
{
  return(i * 256 + j);
}

void warp(int start, int end, int pitch[NUM_FRAMES][MAX_CANDS], 
     float corr[NUM_FRAMES][MAX_CANDS], int peak_ct[NUM_FRAMES], int start_at,
     short possible[GROUP_SIZE][MAX_CANDS][MAX_CANDS],
     int best_path[MAX_CANDS][GROUP_SIZE+1], float best_cost[],
     int *cand_ct, float best_no_bad[]) 
{ 
  /* arrays will be indexed 1 to m or n, as opposed to starting at 0 */

  int pred[GROUP_SIZE + 1][MAX_CANDS];
  int i, j, k, last_k;
  float cost[2][MAX_CANDS], temp_cost[MAX_CANDS];
  float min;
  int at;
  int num_bad;

  for (i = 0; i < GROUP_SIZE + 1; i++)
    for (j = 0; j < MAX_CANDS; j++)
      pred[i][j] = 0;

  /* initialize */
  cost[0][start_at] = 1.0 - corr[start][start_at];
  pred[1][start_at] = pfunc(1,1);

  for (i = start + 1; i <= end; i++) {       /* index in terms of actual # */
    for (j = 1; j < peak_ct[i]; j++) {
    /* for each j, check all possible paths - using both the possible
       array - and the pred array; choose the one with the least
       resultant cost */
      for (k = 1; k < peak_ct[i-1]; k++) {
        temp_cost[k] = VBIG;
        if ((pred[i-1-start+1][k] > 0) && (possible[i-1-start+1][k][j])) {
          temp_cost[k] = cost[0][k] + (1.0 - corr[i][j]);
	}
      }
      /* find the minimum */
      at = -1;
      min = VBIG;
      for (k = 1; k < peak_ct[i-1]; k++) {
        if ((temp_cost[k] < VBIG) && (temp_cost[k] < min)) {
          min = temp_cost[k];
          at = k;
	}
      }
      if (at == -1) {
        cost[1][j] = VBIG;
      }
      else {
        cost[1][j] = temp_cost[at];
        pred[i-start+1][j] = pfunc(i-1-start+1, at);
      }    
    }
    for (j = 1; j < peak_ct[i]; j++)
      cost[0][j] = cost[1][j];
  }

  min = VBIG;
  for (j = 1; j < peak_ct[end]; j++) {
    if (cost[0][j] < min) {
      min = cost[0][j];
      at = j;
    }
  }

  best_cost[*cand_ct] = cost[0][at];
  last_k = at;
  for (i = end; i >= start; i--) {
    best_path[*cand_ct][i-start+1] = last_k;
    k = pred[i-start+1][last_k] % 256;
    last_k = k;
  }

  /* find the number of bad and calculate best_no_bad accordingly */
  num_bad = 0;
  for (i = start; i <= end; i++)
    if (corr[i][best_path[*cand_ct][i-start+1]] == PENALTY)
      num_bad++;
  if (num_bad < round((end-start+1.0)/2.0))
    best_no_bad[*cand_ct] = best_cost[*cand_ct] - (PENALTY * num_bad);
  else
    best_no_bad[*cand_ct] = best_cost[*cand_ct];  

  *cand_ct += 1;
}

void fix_roots(int pitch[NUM_FRAMES][MAX_CANDS], float corr[NUM_FRAMES][MAX_CANDS], 
          int from_frame, int from_root, int peak_ct[], 
          short possible[GROUP_SIZE][MAX_CANDS][MAX_CANDS], int total_frames,
          int start, int end)
{
  int difference,i,j,done,cur_frame,last_root;

  done = 0;
  cur_frame = from_frame + 2;
  while ((cur_frame < total_frames) && (! done)) {
    for (i = 1; i < peak_ct[cur_frame]; i++)
      if (absof(1.0 - 
       (pitch[cur_frame][i] * 1.0/pitch[from_frame][from_root])) < MAX_SEP)
        done = 1;
    if (! done)
      cur_frame++;
  }
  difference = cur_frame - from_frame - 1;
  if (cur_frame > end + 1) /* only need to go as far as this segment */
    cur_frame = end + 1;
  last_root = from_root;
  for (i = from_frame + 1; i < cur_frame; i++) {
    pitch[i][peak_ct[i]] = pitch[from_frame][from_root];
    corr[i][peak_ct[i]] = PENALTY;          /****** ISSUE HERE **********/
    possible[i-1-start+1][last_root][peak_ct[i]] = 1;
    /* connect it to the following frame */
    if (i + 1 - start + 1 <= end) {
      for (j = 1; j < peak_ct[i+1]; j++) {
        if (absof(1.0 - (pitch[i+1][j] * 1.0/pitch[i][peak_ct[i]])) < MAX_SEP)
          possible[i-start+1][peak_ct[i]][j] = 1;
      }
    }
    last_root = peak_ct[i];
    peak_ct[i] += 1;
    if (peak_ct[i] > MAX_CANDS) {
      help("MAX_CANDS not big enough");
    }
  }
}

void fix_roots_back(int pitch[NUM_FRAMES][MAX_CANDS], 
               float corr[NUM_FRAMES][MAX_CANDS], 
          int from_frame, int from_root, int peak_ct[], 
          short possible[GROUP_SIZE][MAX_CANDS][MAX_CANDS],
          int start)
{
  int difference;
  int i, j;
  int done;
  int cur_frame, last_root;

  done = 0;
  cur_frame = from_frame - 2;
  while ((cur_frame >= 0) && (! done)) {
    for (i = 1; i < peak_ct[cur_frame]; i++)
      if (absof(1.0 - 
          (pitch[cur_frame][i] * 1.0/pitch[from_frame][from_root])) < MAX_SEP)
        done = 1;
    if (! done)
      cur_frame--;
  }
  difference = from_frame - cur_frame - 1;
  if (cur_frame < start)   /* only fix the ones in this segment */
    cur_frame = start;
  last_root = from_root;
  for (i = from_frame - 1; i >= cur_frame; i--) {
    pitch[i][peak_ct[i]] = pitch[from_frame][from_root];
    corr[i][peak_ct[i]] = PENALTY;          /****** ISSUE HERE **********/
    possible[i-start+1][peak_ct[i]][last_root] = 1;
    /* connect it to the previous frame */
    if (i - 1 - start + 1 > 0) {
      for (j = 1; j < peak_ct[i-1]; j++) {
        if (absof(1.0 - (pitch[i-1][j] * 1.0/pitch[i][peak_ct[i]])) < MAX_SEP)
          possible[i-1-start+1][j][peak_ct[i]] = 1;
      }
    }
    last_root = peak_ct[i];
    peak_ct[i] += 1;
    if (peak_ct[i] > MAX_CANDS) {
      help("MAX_CANDS not big enough");
    }
  }
}

void make_possible(int start, int end, int pitch[NUM_FRAMES][MAX_CANDS], 
              short possible[GROUP_SIZE][MAX_CANDS][MAX_CANDS], int peak_ct[],
              float corr[NUM_FRAMES][MAX_CANDS], int total_frames)
{
  int i, j, k;
  int yes;

  for (i = 0; i < GROUP_SIZE; i++)
    for (j = 0; j < MAX_CANDS; j++)
      for (k = 0; k < MAX_CANDS; k++)
        possible[i][j][k] = 0;

  /* possible is indexed with start = 1 */
  for (i = start; i < end; i++) {
    for (j = 1; j < peak_ct[i]; j++) {
      for (k = 1; k < peak_ct[i+1]; k++) {
        if (absof(1.0 - (pitch[i+1][k] * 1.0/pitch[i][j])) < MAX_SEP)
          possible[i-start + 1][j][k] = 1;
      }
    }
  }

  /* now, we need to check to make sure that each root has at least one
     successor root */
  for (i = start; i < end; i++) {
    for (j = 1; j < peak_ct[i]; j++) {
      yes = 0;
      for (k = 1; k < peak_ct[i+1]; k++) {
        if (possible[i-start+1][j][k]) {
          yes = 1;
          k = peak_ct[i+1];
	}
      }
      if (! yes)
        fix_roots(pitch, corr, i, j, peak_ct, possible, total_frames, start, end);
    }
  }

  /* and also make sure that each root can be reached */
  for (i = start + 1; i <= end; i++) {
    for (j = 1; j < peak_ct[i]; j++) {
      yes = 0;
      for (k = 1; k < peak_ct[i-1]; k++) {
        if (possible[i-1-start+1][k][j]) {
          yes = 1;
          k = peak_ct[i-1];
	}
      }
      if (! yes)
        fix_roots_back(pitch, corr, i, j, peak_ct, possible, start);
    }
  }
}

float find_sq_eng(short *speech, int x_start, int len)
{
  int i;
  float total = 0.0;

  for (i = 0; i < len; i++)
    total += square(speech[x_start + i]);
  total = total/len;
  total = sqrt(total);
  return(total); 
}

float get_threshold_no_phone(int x_start, int length, short *speech, 
                             float global_rms)
{
  float eng, threshold;

  eng = (find_sq_eng(speech, x_start, length)/global_rms);
  if (eng >= 0.4)
    threshold = THRESHOLD1;
  if ((eng < 0.4) && (eng >= 0.25))
    threshold = THRESHOLD2;
  if ((eng < 0.25) && (eng >= 0.1))
    threshold = THRESHOLD3;
  if (eng < 0.1)
    threshold = THRESHOLD4;
  return(threshold);
}

int identify_peak_candidates(float rho[], int min, int max, short *peak_set,
                         int x_start, int verbose, short *speech,
                         float global_rms)
{
  int i;
  int count = 0;
  float ave_rho = 0.0;
  float threshold;

  for (i = min; i <= max; i++)
    ave_rho += rho[i];
  ave_rho = ave_rho / (max - min + 1);
  if (ave_rho < 0.85) { /* too high and we really don't have periodicity */
    for (i = min; i <= max; i++) {
      threshold = get_threshold_no_phone(x_start, i, speech, global_rms);
      if (threshold <= THRESHOLD2)
        threshold -= 0.10;
      if ((i < max) &&  (rho[i] > rho[i+1])) 
        if ((i > min) && (rho[i] > rho[i-1])) {
if ((verbose) && (rho[i] >= threshold)) 
   printf("peak at %d thresh %f rho %f\n", i, threshold, rho[i]);

          if (rho[i] >= threshold) 
            peak_set[count++] = i;
	}
    }
  }
  return(count);
}

float calculate_rho_first(short *speech, int length, int xstart, int min_range)
{
  static float square_x_total, square_y_total;
  int j;
  int ystart;
  float sum, ynorm, xnorm = 0;
  float rho;

  ystart = xstart + length;
  sum = xnorm = ynorm = 0.0;
  if (length == min_range) {
    square_x_total = square_y_total = 0.0;
    for (j = 0; j < length; j++) {
      square_x_total += square(speech[xstart + j]);
      square_y_total += square(speech[ystart + j]);
    }
  }
  else {
    square_x_total += square(speech[xstart + length - 1]);
    square_y_total -= square(speech[xstart + length - 1]);
    square_y_total += square(speech[ystart + length - 2]);
    square_y_total += square(speech[ystart + length - 1]);
  }
  for (j = 0; j < length; j++) 
    sum += speech[xstart + j] * speech[ystart + j];
  xnorm = sqrt(square_x_total);
  ynorm = sqrt(square_y_total);
  rho = pos(sum / (xnorm * ynorm));
  return(rho);
}

int share(int at1, int at2, int pitch[NUM_FRAMES][MAX_CANDS], int start, int end,
      int best_path[MAX_CANDS][GROUP_SIZE+1]) 
{
  int i, sharing = 0;

  for (i = start; i <= end; i++)
    if (pitch[i][best_path[at1][i-start+1]] == 
        pitch[i][best_path[at2][i-start+1]]) {
      sharing++;
    }
  return(sharing);
}

void assign_targ(int targ_pitch[], int start, int end, 
            int best_path[MAX_CANDS][GROUP_SIZE+1], int at,
            int pitch[NUM_FRAMES][MAX_CANDS])
{
  int i;
  
  for (i = start; i <= end; i++)
    targ_pitch[i] = pitch[i][best_path[at][i-start+1]];
}



/* Swap: swap byte order of *p */
static void Swap(short *p)
{
   char temp,*q;
   
   q = (char*) p;
   temp = *q; *q = *(q+1); *(q+1) = temp;
}




/* ------------------ Process Command Line ------------------------- */


void ReportUsage(void)
{

#ifndef HTKCOMPILE

  int Default_header_size = DEFAULT_HEADER_SIZE;
  int Default_sampling_rate = DEFAULT_SAMPLING_RATE;

#endif

  int Default_min_pitch = DEFAULT_MIN_PITCH;
  int Default_max_pitch = DEFAULT_MAX_PITCH;


#ifndef HTKCOMPILE

  fprintf(stderr,"\nUSAGE: cmt_pitch.CPU [options] Waveform_file\n \n\n");

#endif

#ifdef HTKCOMPILE

  fprintf(stderr,"\nUSAGE: cmt_htk.CPU [options] Waveform_file\n \n\n");

#endif


  fprintf(stderr," Option                                             Default\n\n");
  fprintf(stderr," -t fn   Pitch against time output file             srcfn.ptm\n");
  fprintf(stderr," -p fn   Pitch period output file                   srcfn.ppd\n");

#ifndef HTKCOMPILE

  fprintf(stderr," -h N    Headersize in bytes                        %d\n",Default_header_size);
  fprintf(stderr," -s N    Sampling frequency in Hz                   %d\n",Default_sampling_rate);
  fprintf(stderr," -b      Byte swap input file                       Don't\n");

#endif

  fprintf(stderr," -n N    Minimum pitch allowed                      %d\n",Default_min_pitch);
  fprintf(stderr," -x N    Maximum pitch allowed                      %d\n",Default_max_pitch);

  fprintf(stderr," -o      Information on output files                \n");

#ifdef HTKCOMPILE

  fprintf(stderr," -F fmt  Set input data file format to fmt          HTK\n");
#endif

  fprintf(stderr,"\n");

  exit(1);
}



void main(int argc, char *argv[])
{
  FILE *fwav, *ppd, *ptm;
  int byte_swap = 0;
  int headersize, sampling_rate, min_pitch, max_pitch;
  int filesize, total = 0, min_amp = 0, max_amp = 0, ten_ms;
  int min_range, max_range, verbose = 0, low_range, high_range;
  short *speech;
  int i, j;
  float mean, global_rms = 0.0, sample_interval;
  float *rho, eng;
  int x_start, num_peaks;
  short *peak_set;
  int pitch[NUM_FRAMES][MAX_CANDS], frame_ct = 0, peak_ct[NUM_FRAMES]; 
  float corr[NUM_FRAMES][MAX_CANDS];
  int start_frame, end_frame, done, counter, at, at_bad;
  short possible[GROUP_SIZE][MAX_CANDS][MAX_CANDS];
  int best_path[MAX_CANDS][GROUP_SIZE+1], cand_ct;
  float best_cost[MAX_CANDS], best_no_bad[MAX_CANDS], min, best, min_bad;
  int cur_end, at2;
  int target, index, keep_going, end_pt;
  int male;
  int num_bad, ct1, ct2, num_shared;
  float ave1, ave2;
  int targ_pitch[NUM_FRAMES], choice1[NUM_FRAMES], choice2[NUM_FRAMES];
  int ave_pit_len, ave_ct, need_fix;
  char message[100];  
  float time,start_time,out_pitch;
  int mstime;
  int length,start,last_length,last_start;
  int len_stem;
  char *stem;
  int end_of_file;
  char *ptmfn,*ppdfn;

#ifndef HTKCOMPILE 
  char *stem_ptmfn,*stem_ppdfn;
#endif

#ifdef HTKCOMPILE
  char *s;
  DataFile src;  /* src is then a DataFile type structure */
  FileFormat ff; /* ff is then a FileFormat type */
  char *wavfn;              /* Waveform input file name */
  char *tempptmfn=NULL;     /* temporary storage for command line specified ptmfn */
  char *tempppdfn=NULL;     /* temporary storage for command line specified ppdfn */
  min_pitch = DEFAULT_MIN_PITCH;
  max_pitch = DEFAULT_MAX_PITCH;
#endif

#ifndef HTKCOMPILE

  if (argc < 2) {
    ReportUsage();
    exit(1);
  }


 if (Scan_flag(argc, argv, "-o") == -1) {
   fprintf(stderr,"\n\nsrcfn.ptm  -  contains time (in seconds) against pitch (in Hz)\nsrcfn.ppd  -  contains length of voiced pitch period (in samples) against\n              start position of pitch period (in samples)\n\n");
   exit(1);
 }


  if ((fwav = Std_fopen(argv[argc - 1], "r")) == NULL) {
    fprintf(stderr,"Cannot open input file %s\n",argv[argc - 1]);
    exit(1);
  }




/* gets the stem from the input filename and creates the 
   output filenames from this, if not otherwise specified */

  len_stem = strcspn(argv[argc - 1],".");

  if ((stem = (char *)calloc((len_stem+1),sizeof(char))) == NULL) {
    fprintf(stderr,"Cannot create stem character array");
    exit(1);
  }

  *strncpy(stem,argv[argc - 1],len_stem);

  if ((stem_ptmfn = (char *)calloc((len_stem+5),sizeof(char))) == NULL) {
    fprintf(stderr,"Cannot create stem_ptmfn character array");
    exit(1);
  }

 if ((stem_ppdfn = (char *)calloc((len_stem+5),sizeof(char))) == NULL) {
    fprintf(stderr,"Cannot create stem_ppdfn character array");
    exit(1);
  }


  sprintf(stem_ptmfn,"%s.ptm",stem);
  sprintf(stem_ppdfn,"%s.ppd",stem);

  ptmfn = Scan_string(argc, argv, "-t", stem_ptmfn);
  ppdfn = Scan_string(argc, argv, "-p", stem_ppdfn);

  if ((ptm = Std_fopen(ptmfn, "w")) == NULL) {
    fprintf(stderr,"Cannot open output file %s\n",ptmfn);
    exit(1);
  }

  if ((ppd = Std_fopen(ppdfn, "w")) == NULL) {
    fprintf(stderr,"Cannot open output file %s\n",ppdfn);
    exit(1);
  }


  headersize = Scan_int(argc, argv, "-h", DEFAULT_HEADER_SIZE);
  sampling_rate= Scan_int(argc, argv, "-s", DEFAULT_SAMPLING_RATE);
  min_pitch= Scan_int(argc, argv, "-n", DEFAULT_MIN_PITCH);
  max_pitch= Scan_int(argc, argv, "-x", DEFAULT_MAX_PITCH);
  byte_swap= Scan_flag(argc, argv, "-b");


/* print out the options being used */

  fprintf(stderr,"Waveform input filename        : %s\n",argv[argc - 1]);
  fprintf(stderr,"Pitch against time output file : %s\n",ptmfn);
  fprintf(stderr,"Pitch period output file       : %s\n",ppdfn);
  fprintf(stderr,"Headersize                     : %d\n",headersize);
  fprintf(stderr,"Sampling_rate                  : %d\n",sampling_rate);
  fprintf(stderr,"Min_pitch                      : %d\n",min_pitch);
  fprintf(stderr,"Max_pitch                      : %d\n",max_pitch);
  if (byte_swap == -1)
    fprintf(stderr,"Byte swapping data\n");
  


  for (i = 0; i < NUM_FRAMES; i++)
    targ_pitch[i] = choice1[i] = choice2[i] = 0;

  /* load in the speech file */
  Panic_fseek(fwav, 0, 2);
  filesize = (ftell(fwav) - headersize) / sizeof(short);
  Panic_fseek(fwav, headersize, 0);
  speech = Panic_short_array(filesize);
  Panic_fread(speech, sizeof(*speech), filesize, fwav);
  

  /* byte swap if nesscessary */
  if (byte_swap == -1) {
    for (i = 0; i < filesize; i++) 
      Swap(&speech[i]);
  }

#endif

#ifdef HTKCOMPILE

  InitShell(argc,argv);
  InitMath(FALSE);
  if (NumArgs()==0)
    ReportUsage();
  while (NextArg() == SWITCHARG) {
    s = GetSwtArg();
    if (strlen(s)!=1)
      HError(1,"Bad switch %s; must be single letter",s);
    switch(s[0]){
    case 'o':
      fprintf(stderr,"\n\nsrcfn.ptm  -  contains time (in seconds) against pitch (in Hz)\nsrcfn.ppd  -  contains length of voiced pitch period (in samples) against\n              start position of pitch period (in samples)\n\n");
      exit(1);
      break;
    case 'n':
      min_pitch = GetIntArg();
      break;
    case 'x':
      max_pitch = GetIntArg();
      break;
    case 't':
      tempptmfn =  GetStrArg();
      break;
    case 'p':
      tempppdfn =  GetStrArg();
      break;
    case 'F':
      if (NextArg() != STRINGARG)
	HError(1,"Data File format expected");
      if((ff = Str2Format(GetStrArg())) == ALIEN)
	HError(0,"Warning ALIEN Data file format set");
      SetFormat(ff);
      break;
    default:
      HError(1,"Unknown switch %s",s);
    }
  }
  if (NextArg()!=STRINGARG)
    HError(1,"Waveform file name expected");
  wavfn = GetStrArg();


/* gets the stem from the input filename and creates the 
   output filenames from this, if not otherwise specified */

  len_stem = strcspn(wavfn,".");

  if ((stem = (char *)calloc((len_stem+1),sizeof(char))) == NULL) {
    fprintf(stderr,"Cannot create stem character array");
    exit(1);
  }

  *strncpy(stem,wavfn,len_stem);

  if (tempptmfn == NULL) {
    if ((ptmfn = (char *)calloc((len_stem+5),sizeof(char))) == NULL) {
      fprintf(stderr,"Cannot create ptmfn character array");
     exit(1);
    }
    sprintf(ptmfn,"%s.ptm",stem);
  }
  else
    ptmfn = tempptmfn;

  if (tempppdfn == NULL) {
    if ((ppdfn = (char *)calloc((len_stem+5),sizeof(char))) == NULL) {
      fprintf(stderr,"Cannot create ppdfn character array");
      exit(1);
    }
  sprintf(ppdfn,"%s.ppd",stem);
  }
  else
    ppdfn = tempppdfn;

  if ((ptm = fopen(ptmfn, "w")) == NULL) {
    fprintf(stderr,"Cannot open output file %s\n",ptmfn);
    exit(1);
  }

  if ((ppd = fopen(ppdfn, "w")) == NULL) {
    fprintf(stderr,"Cannot open output file %s\n",ppdfn);
    exit(1);
  }


/* print out the options being used */

  fprintf(stderr,"Waveform input filename        : %s\n",wavfn);
  fprintf(stderr,"Pitch against time output file : %s\n",ptmfn);
  fprintf(stderr,"Pitch period output file       : %s\n",ppdfn);
  fprintf(stderr,"Min_pitch                      : %d\n",min_pitch);
  fprintf(stderr,"Max_pitch                      : %d\n",max_pitch);
  
  SpOpen(wavfn,&src);
  if (src.sampKind != WAVEFORM)
    HError(99,"Waveform file expected");

  sampling_rate = 10000000/src.sampPeriod;
  filesize = src.nSamples;
  

  for (i = 0; i < NUM_FRAMES; i++)
    targ_pitch[i] = choice1[i] = choice2[i] = 0;

  /* load in the speech file */
  if ((speech = (short *)calloc(filesize,sizeof(short))) == NULL) {
    fprintf(stderr,"Cannot create speech array");
    exit(1);
  }
  for (i = 0; i < filesize; i++)
    GetSample(&src,i,&speech[i]);

#endif

/* The rest is independent of the HTKCOMPILE flag */

  for (i = 0; i < filesize; i++)
    total += speech[i];
  mean = (total * 1.0) / filesize;
  for (i = 0; i < filesize; i++) {
    speech[i] -= mean;
    if (speech[i] > max_amp)
      max_amp = speech[i];
    if (speech[i] < min_amp)
      min_amp = speech[i];
  }

  for (i = 0; i < filesize; i++)
    global_rms += (speech[i] * speech[i]);
  global_rms = sqrt(global_rms/(filesize * 1.0));

  sample_interval = 1.0 / (float) sampling_rate;
  ten_ms = (int) (1.0 * sampling_rate) / 100;
  min_range = (int) ((1.0/max_pitch)/sample_interval);
  max_range = (int) ((1.0/min_pitch)/sample_interval);

  if ((rho = (float *)calloc(max_range + 1,sizeof(float))) == NULL) {
    fprintf(stderr,"Cannot create rho array");
    exit(1);
  }

  if ((peak_set = (short *)calloc(MAX_CANDS,sizeof(short))) == NULL) {
    fprintf(stderr,"Cannot create peak_set array");
    exit(1);
  }

  x_start = 0;

  while (x_start + 2 * max_range < filesize) {
    for (i = 0; i <= max_range; i++)
      rho[i] = 0.0;
    eng = find_sq_eng(speech, x_start, max_range - min_range + 1);
    if (eng >= 0.05 * global_rms)  {
      for (i = min_range; i <= max_range; i++) {
        rho[i] = calculate_rho_first(speech, i, x_start, min_range);
      }
    }
    num_peaks = identify_peak_candidates(rho, min_range, 
                max_range, peak_set, x_start,
                verbose, speech, global_rms);
    if (num_peaks > MAX_CANDS) {
      sprintf(message, "MAX_CANDS not big enough for %d\n", num_peaks);
      help(message);
    }
    peak_ct[frame_ct] = num_peaks + 1;
    for (i = 0; i < num_peaks; i++) {
      pitch[frame_ct][i+1] = peak_set[i];
      corr[frame_ct][i+1] = rho[peak_set[i]]; 
    }
    frame_ct++;
    if (frame_ct >= NUM_FRAMES) {
      sprintf(message, "NUM_FRAMES not big enough for %d\n", frame_ct);
      help(message);
    }
    x_start += ten_ms;
  }


/* The stuff in finp didnt seem to be very useful - so i got rid of that output
   file - R.E.D (23:8:93) 


 fprintf(finp, "%d\n", frame_ct);
  for (i = 0; i < frame_ct; i++) {
    fprintf(finp, "%d ", peak_ct[i]);
    for (j = 1; j < peak_ct[i]; j++)
      fprintf(finp, "%d %f\n", pitch[i][j], corr[i][j]);
  }
  fclose(finp);
*/
/****
  fscanf(finp, "%d", &frame_ct);
  for (i = 0; i < frame_ct; i++) {
    fscanf(finp, "%d", peak_ct + i);
    for (j = 1; j < peak_ct[i]; j++)
      fscanf(finp, "%d %f", &(pitch[i][j]), &(corr[i][j]));
  } 
****/
  done = 0;
  counter = 0;
  while (! done) {
    /* get a group */
    while ((counter < frame_ct) && (peak_ct[counter] == 1)) counter++;
    if (counter >= frame_ct) done = 1;
    if (! done) {
      start_frame = counter;
      keep_going = 1;
      while (keep_going) {  /* hack so we don't miss singulars */
        while ((counter < frame_ct) && (peak_ct[counter] > 1)) counter++;
        if ((counter+1 < frame_ct) && (peak_ct[counter+1] > 1) &&
            (counter+2 < frame_ct) && (peak_ct[counter+2] > 1)) 
          counter = counter + 1;
        else {
          end_frame = counter-1;
          keep_going = 0;
	}
      }
      /* got a group */
      if (end_frame > start_frame) {
        make_possible(start_frame, end_frame, pitch, possible, peak_ct, corr, 
                      frame_ct);

        cand_ct = 0;
        for (j = 1; j < peak_ct[start_frame]; j++) {
        warp(start_frame, end_frame, pitch, corr, peak_ct, j, 
             possible, best_path, best_cost, &cand_ct, best_no_bad);
        }
        if (cand_ct >= MAX_CANDS) {
          sprintf(message, "cand_ct %d too big for MAX_CANDS", cand_ct);
          help(message);
        }

        /* Choosing which string to use */
        min = min_bad = VBIG;
        for (i = 0; i < cand_ct; i++) {
          if (best_cost[i] < min) {
            min = best_cost[i];
            at = i;
	  }
          if (best_no_bad[i] < min_bad) {
            min_bad = best_no_bad[i];
            at_bad = i;
	  }
	}
   
        if (at == at_bad) {
	  assign_targ(targ_pitch, start_frame, end_frame, best_path, at,
                      pitch);
	}
        else {   /* lets look at the options */
          if ((num_shared = share(at, at_bad, pitch, start_frame, 
                                  end_frame, best_path))) {
            num_bad = 0;
            for (i = start_frame; i <= end_frame; i++)
              if ((corr[i][best_path[at_bad][i-start_frame+1]] == PENALTY) &&
                  (pitch[i][best_path[at_bad][i-start_frame+1]] !=
                   pitch[i][best_path[at][i-start_frame+1]]))
                num_bad++;
            if ((end_frame-start_frame+1 == num_shared + num_bad) ||
               (num_bad > round((end_frame-start_frame+1.0-num_shared)/2.0))) {
              /* keep the original */
               assign_targ(targ_pitch, start_frame, end_frame, best_path, at,
                           pitch);
	    }
            else {  
              /* figure out which one to use by calculating average over
                 non-penalty portions */
              ave1 = ave2 = 0.0;
              ct1 = ct2 = 0;
              for (i = start_frame; i <= end_frame; i++) {
                if (corr[i][best_path[at][i-start_frame+1]] != PENALTY) {
                  ave1 += (1.0 - corr[i][best_path[at][i-start_frame+1]]);
                  ct1++;
		}
                if (corr[i][best_path[at_bad][i-start_frame+1]] != PENALTY) {
                  ave2 += (1.0 - corr[i][best_path[at_bad][i-start_frame+1]]);
                  ct2++;
		}
              }
              ave1 /= ct1;
              ave2 /= ct2;
              if (ave1 < ave2) {
                /* use at */
	        assign_targ(targ_pitch, start_frame, end_frame, best_path, at,
                            pitch);
              } 
              else {
                /* use at_bad */
	        assign_targ(targ_pitch, start_frame, end_frame, best_path, 
                            at_bad, pitch);
	      }
	    }
	  }
          else {  
            /* they dont share a common path - so lets check if one is
               much better than the other, and if not, lets delay the
               choice until we have analysed the entire sentence */
            ave1 = ave2 = 0.0;
            ct1 = ct2 = 0;
            for (i = start_frame; i <= end_frame; i++) {
              if (corr[i][best_path[at][i-start_frame+1]] != PENALTY) {
                ave1 += 1.0 - corr[i][best_path[at][i-start_frame+1]];
                ct1++;
	      }
              if (corr[i][best_path[at_bad][i-start_frame+1]] != PENALTY) {
                ave2 += 1.0 - corr[i][best_path[at_bad][i-start_frame+1]];
                ct2++;
	      }
	    }
            ave1 /= ct1;
            ave2 /= ct2;
            if (ave1 < ave2 - 0.03)  /* use at */
	      assign_targ(targ_pitch, start_frame, end_frame, best_path, 
                          at, pitch);
            else
              if (ave2 < ave1 - 0.03) /* use at_bad */
	        assign_targ(targ_pitch, start_frame, end_frame, best_path, 
                            at_bad, pitch);
              else {  /* save these 2 choices for later resolution */
	        assign_targ(choice1, start_frame, end_frame, best_path, 
                            at, pitch);
	        assign_targ(choice2, start_frame, end_frame, best_path, 
                            at_bad, pitch);
	      }
	  }
	}
      }
    }
  }

  /* now resolve any choices */
  need_fix = 0;
  ave_pit_len = ave_ct = 0;
  for (i = 0; i < frame_ct; i++) {
    if (targ_pitch[i] > 0) {
      ave_pit_len += targ_pitch[i];
      ave_ct++;
    }
    if (choice1[i] > 0)
      need_fix = 1;
  }
  if (ave_ct) 
    ave_pit_len /= ave_ct;
  else {
    if (male) ave_pit_len = 125;  /* SUSPECT HARD NUMBERING HERE **********/
    else ave_pit_len = 71;        /* But its not used i think - R.E.D */
    printf("average pitch length being set to fixed number\nwhich i think ought to be scaled by the sampling frequency - but isnt. - R.E.D");
  }
  if (need_fix) {
    done = counter = 0;
    while (! done) {
      while ((counter < frame_ct) && (choice1[counter] == 0)) counter++;
      if (counter >= frame_ct) done = 1;
      if (! done) {
        start_frame = counter;
        while ((counter < frame_ct) && (choice1[counter] > 0)) counter++;
        end_frame = counter-1;
        /* get the averages and choose the one closest to ave_pit_len */
        ave1 = ave2 = 0.0;
        ct1 = 0;
        for (i = start_frame; i <= end_frame; i++) {
          ave1 += choice1[i];
          ave2 += choice2[i];
          ct1++;
	}
        ave1 /= ct1;
        ave2 /= ct1;
        if (absof(ave1 - ave_pit_len) < absof(ave2 - ave_pit_len)) {
          for (i = start_frame; i <= end_frame; i++)
            targ_pitch[i] = choice1[i];
	}
        else {
          for (i = start_frame; i <= end_frame; i++)
            targ_pitch[i] = choice2[i];
	}
      }
    }
  }
  
  done = counter = 0;
  while (! done) {
    while ((counter < frame_ct) && (targ_pitch[counter] == 0)) counter++;
    if (counter >= frame_ct) done = 1;
    if (! done) {
      start_frame = counter;
      while ((counter < frame_ct) && (targ_pitch[counter] > 0)) counter++;
      end_frame = counter-1;
      x_start = start_frame * ten_ms;
      low_range = (int) ((1.0-MAX_SEP) * targ_pitch[start_frame]);
      if (low_range < min_range) low_range = min_range;
      high_range = (int) ((1.0+MAX_SEP) * targ_pitch[start_frame]);
      if (high_range > max_range) high_range = max_range;
      cur_end = x_start;
      end_pt = (end_frame * ten_ms) + (2 * targ_pitch[end_frame]);
      while (cur_end < end_pt) {
        for (i = 0; i <= max_range; i++)
          rho[i] = 0.0;
        best = 0.0;
        for (i = low_range; i <= high_range; i++) {
          rho[i] = calculate_rho_first(speech, i, x_start, low_range);
          if (rho[i] > best) {
            best = rho[i];
            at2 = i;
	  }
	}
        fprintf(ppd, "%d %d\n", at2, x_start);
        x_start += at2;
        index = 1 + x_start/ten_ms;
        if (index < start_frame) index = start_frame;
        if (index > end_frame) index = end_frame;
        target = targ_pitch[index];
        low_range = (int) ((1.0-MAX_SEP) * target);
        if (low_range < min_range) low_range = min_range;
        high_range = (int) ((1.0+MAX_SEP) * target);
        if (high_range > max_range) high_range = max_range;
        cur_end = x_start + at2;
      }
    }
  }
  fclose(ppd);

/* now reopen the output file written so far for reading, and from this 
   calculate a pitch file of time against pitch.  Resolution = 1 ms
   R.E.D 24:8:93 */


  if ((ppd = fopen(ppdfn, "r")) == NULL) {
    fprintf(stderr,"Cannot reopen output file %s\n",ppdfn);
    exit(1);
  }

  time = 0.0;
  mstime = 0;
  out_pitch = 0.0;
  end_of_file = 0;

  last_length = 0;
  last_start = 0;
  fscanf(ppd, "%d", &length);
  fscanf(ppd, "%d", &start);
  start_time = start*1.0/sampling_rate;


  while ( 1 == 1 ) {
    while (start_time < time) {
      
      last_length = length;
      last_start = start;

      if (fscanf(ppd, "%d", &length) == EOF) {
	end_of_file = 1;
	break;
      }
      fscanf(ppd, "%d", &start);
      
      start_time = start*1.0/sampling_rate;
    }
    
    if (end_of_file == 1)
      break;
    
    /* if voiced then interpolate for the pitch */

    if (start == last_start + last_length)
      out_pitch = ( (sampling_rate*time - last_start)/((start-last_start)*length) 
	       +(start - sampling_rate*time)/((start-last_start)*last_length))
	       *sampling_rate;
    else
      out_pitch = 0.0;

    fprintf(ptm,"%f %f\n",time,out_pitch);

    mstime++;
    time = mstime/1000.0;
  }

/* if there's an unvoiced/silent bit at the end then output zeros to the 
   output file */

  if  (filesize > (start + 3*length) ) {
    while (sampling_rate*time < filesize) {
      fprintf(ptm,"%f %f\n",time,0.0);
      mstime++;
      time = mstime/1000.0;
    }
  }

  fclose(ppd);
  fclose(ptm);




  fclose(fwav);
}




/* Here's the debugging area 
        fprintf(fres, "%s %s %d %d %d\n", speaker, sentence, start_frame,
                end_frame, cand_ct);
        for (i = 0; i < cand_ct; i++)
          for (j = start_frame; j <= end_frame; j++)
            fprintf(fres, "%d %5.3f ", pitch[j][best_path[i][j-start_frame+1]],
                    corr[j][best_path[i][j-start_frame+1]]);
        for (i = start_frame * 16; i <= end_frame * 16; i++)
        fprintf(fres, "%d ", keil_len[i]);

        bad = 0;
        for (i = start_frame; i <= end_frame; i++) {
          if ((keil_len[i * 16]) &&
              (absof(1.0 - (keil_len[i*16] * 1.0/
                   pitch[i][best_path[at][i-start_frame+1]])) > MAX_SEP))
            bad++;
	}
        if (bad) {
          if (male) fbad = fopen("/home/jam1/cmt11/MALE_BAD", "a");
          else fbad = fopen("/home/jam1/cmt11/FEMALE_BAD", "a");
          fprintf(fbad, "4 %s %s %d %d 0 0 %d %d 0 300 4 2 1\n", 
            speaker, sentence, start_frame, end_frame, 
            start_frame-5, end_frame+1);
          for (i = start_frame; i <= end_frame; i++) 
            for (j = 1; j < peak_ct[i]; j++)
              fprintf(fbad, "%d %d %d ", i, pitch[i][j], 
                  (int) (100 - (corr[i][j] * 100)));
            fprintf(fbad, "-10000.0 0 x\n");
          fprintf(fbad, "4 1\n");
          for (i = 0; i < cand_ct; i++) {
            for (j = start_frame; j < end_frame; j++)
              fprintf(fbad, "%d %d %d %d ", j, 
                   pitch[j][best_path[i][j-start_frame+1]],
                   j+1, pitch[j+1][best_path[i][j+1-start_frame+1]]);
	  }
          fprintf(fbad, "-10000.0 0 0 0\n");

          fprintf(fbad, "2 1\n");
          for (i = 0; i < cand_ct; i++) {
            fprintf(fbad, "%d %d %5.2f ", start_frame - 4, 
                 pitch[start_frame][best_path[i][1]], best_cost[i]);
	  }
          fprintf(fbad, "-10000.0 0 x\n");

          fprintf(fbad, "4 2\n");
          for (i = start_frame * 16; i < end_frame * 16; i++)
            fprintf(fbad, "%d %d %d %d ", i * 10/ten_ms, keil_len[i], 
                    (i+1) * 10/160, keil_len[i+1]);
          fprintf(fbad, "-10000.0 0 0 0\n");
          fclose(fbad);
	}

*****/

