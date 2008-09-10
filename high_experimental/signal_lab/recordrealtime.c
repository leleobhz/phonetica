/*
#if defined(HAVE_CONFIG_H)
  #include "config.h"
#endif
*/
#include <math.h>
#include <stdio.h>
#include <stdlib.h>

#if (defined(NEXT) || (defined(HAVE_LIBC_H) && (!defined(HAVE_UNISTD_H))))
  #include <libc.h>
#else
  #if (!(defined(_MSC_VER))) && (!(defined(MPW_C)))
    #include <unistd.h>
  #endif
  #include <string.h>
#endif
#include <errno.h>

#include "sndlib.h"
#include "clm.h"
#include "general2.h"
#include "general.h"


#if MACOS
  #include <console.h>
#endif
/*
#ifdef BEOS
  #define CHANNELS 2
#else
  #define CHANNELS 1
#endif
*/
#define DATA_TYPE MUS_LSHORT

FILE *fpt;
/*
int hps_now (indata *realdata,indata *imgdata, float *win,int length, int min_k)
{
  int i,k, num_harmonics, place, type;
  float array[length], arrayimag[length];

  
  num_harmonics=5;
  
  for (i=0;i<length;i++)
    {
      array[i]=(float)(*realdata);
      arrayimag[i]=(float)(*imgdata);
      realdata++;
      imgdata++;
    }
  

  type=1; //0 for dB and 1 for linear normalized
  mus_spectrum(array, arrayimag,win,length,type);

  for (i=1;i<=floor(length/num_harmonics);i++)
    {
      for (k=0;k<=num_harmonics;k++)	
	array[i] *= *(array+(k*i));
    }

  place = Get_Maximum(array, min_k , (int)floor(length/(2*num_harmonics)));
  printf("The value of place is =%d\n", place);
  return(place);
  
}
*/
int recordsetup( int *afd, short **ibuf, short **ibufsimag,int *bytes_per_read, float **window, float **pitch,  int BUFFER_SIZE, int READS)

{
  int bytes_per_sample;
  //int lowest_frequency = 120;
  // float mic_gain[1];
  /*
#if MACOS
  *argc = ccommand(argv);
#endif
  if (*argc == 1) {printf("usage: sndrecord outfile\n"); exit(0);}
  */
  *afd = -1;
  mus_sound_initialize();
  fpt = fopen("data.m","w");
  *window=(float *) mus_make_fft_window( 6 , BUFFER_SIZE, 0.4);
  // printing("window",BUFFER_SIZE,*window);
  //*min_k = 2*floor((*BUFFER_SIZE * lowest_frequency)/ *SAMPLING_RATE);
  printf("BUFFER SIZE INSIDE RECORD SETUP= %d",BUFFER_SIZE); 
  *pitch = (float *)malloc(sizeof(float)* READS);

  /* make sure the microphone is on 
  mic_gain[0] = 1.0;
  mus_audio_mixer_write(MUS_AUDIO_MICROPHONE,MUS_AUDIO_AMP,0,mic_gain);
  if (CHANNELS == 2) mus_audio_mixer_write(MUS_AUDIO_MICROPHONE,MUS_AUDIO_AMP,1,mic_gain);
 */
  /* open the output sound file */
  bytes_per_sample = mus_data_format_to_bytes_per_sample(DATA_TYPE);
  *bytes_per_read = BUFFER_SIZE * bytes_per_sample;

  /* prepare and open the microphone input line */
  *ibuf = (short *)CALLOC(BUFFER_SIZE,sizeof(short));
  *ibufsimag = (short *)CALLOC(BUFFER_SIZE,sizeof(short));
  
  return(0);
}

void close_everything(int *fd, int *bytes_per_read,float **pitch, short **ibuf, short **ibufsimag, int *afd, int READS)
{
  mus_sound_close_output(*fd,*bytes_per_read * READS);
  // FREE(*pitch);
  FREE(*ibuf);
  FREE(*ibufsimag);
  mus_audio_close(*afd);
  fclose(fpt);    
  return;  
}

