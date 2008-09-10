#include <math.h>
#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include "sndlib.h"
#include "clm.h"
#include <gtk/gtk.h>
#include <gdk/gdk.h>
#include <gtkplotcanvas.h>
#include "general.h"

///////////////////////////////////////////////////////////////////////
// 
// Printing different types into the data.m for debugging process
//
///////////////////////////////////////////////////////////////////////


FILE *fpt;

void printing( char *name, int length, float *input)
 
{
  int col;
  fprintf(fpt,"%s=[",name);
  for (col = 0; col < length ;col++)
    fprintf(fpt,"%f \n",input[col]);
  fprintf(fpt,"];");
  return;

}

void printing_int( char *name,int length, int *input)
 
{
  int col;
  fprintf(fpt,"%s=[",name);
  for (col = 0; col < length ;col++)
    fprintf(fpt,"%d \n",input[col]);
  fprintf(fpt,"];");
  return;

}


void printing_mus_type( char *name,int length, MUS_SAMPLE_TYPE *input)
 
{
  int col;
  fprintf(fpt,"%s=[",name);
  for (col = 0; col < length ;col++)
    fprintf(fpt,"%f \n",MUS_SAMPLE_TO_FLOAT(input[col]));
  fprintf(fpt,"];");
  return;

}

///////////////////////////////////////////////////////////////////////
// 
// Calculates a simple maximum in a frame from the position "start" 
// to "start+length. Consider valid only values above thershold
//
///////////////////////////////////////////////////////////////////////

int Get_Maximum(float *block, int start,int length, float threshold)
{
  int Position, i;
  float Maximum;
  

  Maximum=0;
  Position=0;
  for (i=start;i<length;i++)
    if (block[i]>Maximum) 
      {
	Maximum=block[i];
	Position=i;
      }
  // printf("maximum=%.12f\n",Maximum);
  if (Maximum> threshold) return(Position);
  else return(-1);

  return(Position);
}

///////////////////////////////////////////////////////////////////////
// 
// Parabolic interpolation to find the maximum more accurately
//
///////////////////////////////////////////////////////////////////////

float parabolic_interpolation(float* array, int place){
  float a, b, c;
  a = array[place - 1];
  b = array[place];
  c = array[place + 1];
  // we need to check place not on edge
  return(.5*(a-c)/(a-2*b+c));
}

float hps (MUS_SAMPLE_TYPE *realdata,MUS_SAMPLE_TYPE *imgdata, float *win,int length,float threshold, int min_k)

{
  int i,k, num_harmonics, place, type;
  float array[length], arrayimag[length], correction;

  
  num_harmonics=5;
  

  for (i=0;i<length;i++)
    {
      array[i]=MUS_SAMPLE_TO_FLOAT(*realdata++);
      arrayimag[i]=MUS_SAMPLE_TO_FLOAT(*imgdata++);
      // realdata++;
      //imgdata++;
    }
  
  type=1; //0 for dB and 1 for linear normalized
  mus_spectrum(array, arrayimag,win,length,type);
 
  
  
  for (i=1;i<=floor(length/num_harmonics);i++)
    {
	for (k=0;k<=num_harmonics;k++)	
	  array[i] *= *(array+(k*i));
    }

place = Get_Maximum(array, min_k , (int)floor(length/num_harmonics), threshold);
correction = parabolic_interpolation(array, place);

//printf("Array[place] = %f\t", array[place]);
//printf(" correction =  %f\n",correction);
return(place+correction);

}

float hps_realtime (short *realdata,short *imgdata, float *win,int length,float threshold, int min_k)

{
  int i,k, num_harmonics, place, type;
  float array[length], arrayimag[length], correction;

  
  num_harmonics=5;
  
  
  for (i=0;i<length;i++)
    {
      array[i]=(float)(*realdata++);
      arrayimag[i]=(float)(*imgdata++);
      // realdata++;
      //imgdata++;
    }
 

  type=1; //0 for dB and 1 for linear normalized
  mus_spectrum(array, arrayimag,win,length,type);
 
  
  
  for (i=1;i<=floor(length/num_harmonics);i++)
    {
	for (k=0;k<=num_harmonics;k++)	
	  array[i] *= *(array+(k*i));
    }

place = Get_Maximum(array, min_k , (int)floor(length/num_harmonics), threshold);
correction = parabolic_interpolation(array, place);

//printf("Array[place] = %f\t", array[place]);
//printf(" correction =  %f\n",correction);
return(place+correction);

}

/*
int* make_window(int window_type,int length)
{
  int *ventana, i;
  float *window;

  ventana = (int *) malloc(sizeof(int)*length);
  window=(float *) mus_make_fft_window( window_type , length, 0.4);
  for (i=0; i < length; i++)
	 ventana[i] =(int) (window[i]*1000);
  return(ventana);
}
*/

///////////////////////////////////////////////////////////////////////
// 
// pitch_detection receives the configuration read from the widget, 
// split the file into frames and calculate the pitch of each frame
// returns a vector with the pitch values
//
///////////////////////////////////////////////////////////////////////

float* pitch_detection(config info, int *size, float *step)
{
  int fd,i,n,chans,frames,samples, min_k, number_of_frames = 0, sampling_rate=0;
  MUS_SAMPLE_TYPE **bufs, **bufsimag;
  char *output;
  float *window, frame_length, *pitch=0 ;
  int zero_pad = info.zeropad;
  int BUFFER_SIZE = info.frame_length;
  float overlap = info.overlap;
  int window_type = info.window; 
  int lowest_frequency = info.lowest_frequency;


  output= "output.snd";
  mus_sound_initialize();       
  fd = mus_sound_open_input(info.input_file);
  
  //  fpt = fopen("data.m","w");

  if (fd != -1)
    {
      chans = mus_sound_chans(info.input_file);
      sampling_rate = mus_sound_srate(info.input_file);
      frames = mus_sound_frames(info.input_file); /*Samples per channel*/
      samples = mus_sound_samples(info.input_file);
      frame_length =  (float)BUFFER_SIZE/(float)sampling_rate;
      number_of_frames = (int)(1/overlap)*(floor(frames/BUFFER_SIZE)-1)+1;
      min_k = floor((lowest_frequency*(zero_pad*BUFFER_SIZE))/sampling_rate); 

 printf("\n\n***************************\nINFO. FROM THE FILE HEADER\n***************************\n");
      printf("chans=%d\nsampling_rate=%d\nframes_length[msec]=%f\nsamples=%d\nnumber_of_frames=%d, overlap= %f\nmin_k=%d,from freq=%d Hz\n\n",chans,sampling_rate,frame_length*1000.0,samples,number_of_frames,overlap, min_k,lowest_frequency);
      
      // Creating the window, 0.4 is the default for the Kaiser window
      window=(float *) mus_make_fft_window( window_type , zero_pad*BUFFER_SIZE, 0.4);

      // Allocating memory for the buffers
      bufs = (MUS_SAMPLE_TYPE **)calloc(chans,sizeof(MUS_SAMPLE_TYPE *));
      for (i=0;i<chans;i++) bufs[i] = (MUS_SAMPLE_TYPE *)calloc(zero_pad*BUFFER_SIZE,sizeof(MUS_SAMPLE_TYPE));
      bufsimag = (MUS_SAMPLE_TYPE **)calloc(chans,sizeof(MUS_SAMPLE_TYPE *));
      for (i=0;i<chans;i++) bufsimag[i] = (MUS_SAMPLE_TYPE *)calloc(zero_pad*BUFFER_SIZE,sizeof(MUS_SAMPLE_TYPE));
          
      pitch =(float *) malloc(sizeof(float) * number_of_frames);
   

      // MAIN LOOP //

      for (i=0,n=0;n<number_of_frames;i+=floor(BUFFER_SIZE*overlap), n++)	
	{
	  mus_file_seek_frame(fd,i);
	  mus_file_read(fd,0,BUFFER_SIZE-1,chans,bufs);
	  pitch[n]=((float)sampling_rate*2/(zero_pad*BUFFER_SIZE))*hps(*bufs, *bufsimag, window,zero_pad* BUFFER_SIZE,info.threshold,min_k);
	  printf("Frame %d of %d; pitch = %f [hz]\n",n,number_of_frames, pitch[n]);
	}
      //printing( "pitch",number_of_frames , pitch);

      //Closing the input file
      mus_sound_close_input(fd);

      //Deallocating memory
      for (i=0;i<chans;i++) free(bufs[i]);      
      free(bufs);
      for (i=0;i<chans;i++) free(bufsimag[i]);      
      free(bufsimag);
      //free(pitch);
    }
  else {
    printf("\nCouldn't open the input file\n");
    fprintf(stderr,"%s: %s ",info.input_file,mus_audio_error_name(mus_audio_error()));
  }
  //  fclose(fpt);
  *size=number_of_frames;
  *step=overlap*BUFFER_SIZE/sampling_rate;
  return(pitch); 
}

