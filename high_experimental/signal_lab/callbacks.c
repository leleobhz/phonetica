#ifdef HAVE_CONFIG_H
#  include <config.h>
#endif

#include <math.h>
#include <gtk/gtk.h>
#include <gdk/gdk.h>
#include <stdio.h>
#include <string.h>
#include <stdlib.h>
#include "callbacks.h"
#include "interface.h"
#include "support.h"
#include "general.h"
//#include <gtkplotps.h>
//#include <gtkplotlayout.h>
#include <gtkplotcanvas.h>
#include "clm.h"

///////////////////////////////////////////////////////////////////////
// 
// select_file(2)_clicked open the file selection dialog
// and assign the name of the file selected to the global variable
// "inputfile" and "outputfile" declared in general.h. 
// write_file_input(output) write the file name in the main widget
//
///////////////////////////////////////////////////////////////////////

void
select_file_clicked                    (GtkButton       *button,
                                        gpointer         user_data)
{

inputfile = create_fileselection1 ();
gtk_widget_show (inputfile);

}


void
select_file2_clicked                   (GtkButton       *button,
                                        gpointer         user_data)
{

outputfile = create_fileselection2 ();
gtk_widget_show (outputfile);

}

void
write_file_input                       (GtkWidget       *win,
                                        gpointer         user_data)
{
char *filename;

filename = gtk_file_selection_get_filename (GTK_FILE_SELECTION(win));
gtk_entry_set_text (GTK_ENTRY(lookup_widget (main_window,"entry1") ),filename);

}


void
write_file_output                      (GtkWidget       *win,
                                        gpointer         user_data)
{

char *filename2;

filename2 = gtk_file_selection_get_filename (GTK_FILE_SELECTION(win));
gtk_entry_set_text (GTK_ENTRY(lookup_widget (main_window,"entry2") ),filename2);


}


///////////////////////////////////////////////////////////////////////
// 
// translate fills up the configuration for all the variables being read from the 
// widgets, and return them into a variable of type config, defined in "general.h"
//
///////////////////////////////////////////////////////////////////////

config 
translate(char *input_file, char *output_file, char* length, char* window, char* overlap, char* algorithm, char* minimum_frequency, char* maximum_frequency, char* threshold, char* duration,char* sampling_rate, char* zeropad)
{
  config info;
  

  info.input_file=input_file;
  printf("input file= %s\n", info.input_file);
  info.output_file=output_file;
  info.frame_length=atoi(length);
  info.overlap=atof(overlap);
  info.algorithm = algorithm;
  info.lowest_frequency =  atoi(minimum_frequency);
  info.maximum_frequency = atoi(maximum_frequency);
  info.threshold = atof(threshold);
  info.duration = atoi(duration);
  info.sampling_rate = atoi(sampling_rate);
  info.zeropad = atoi(zeropad);
  info.type = 1;


if (!strcmp(window,"Rectangular")) 	     
    info.window=0;
else if (!strcmp(window,"Hanning")) 
      info.window=1;
else if (!strcmp(window,"Welch")) 
      info.window=2;
else if (!strcmp(window,"Parzen")) 
      info.window=3;
else if (!strcmp(window,"Bartlett")) 
      info.window=4;
else if (!strcmp(window,"Hamming")) 
      info.window=5;
else if (!strcmp(window,"Blackman2")) 
      info.window=6;
else if (!strcmp(window,"Blackman3")) 
      info.window=7;
else if (!strcmp(window,"Blackman4")) 
      info.window=8;
else if (!strcmp(window,"Exponential")) 
      info.window=9;
else if (!strcmp(window,"Kaiser")) 
      info.window=10;
else if (!strcmp(window,"Cauchy")) 
      info.window=11;
else if (!strcmp(window,"Poisson")) 
      info.window=12;
else if (!strcmp(window,"Riemann")) 
      info.window=13;
else if (!strcmp(window,"Gaussian")) 
      info.window=14;
else if (!strcmp(window,"Tukey")) 
      info.window=15;
else  
  {
    printf("Wrong window, Rectangular set by default");
    info.window=0;
  }

return (info);
 
}


///////////////////////////////////////////////////////////////////////
// 
// pitch_detect_clicked is called by clicking the pitch detect button
// it reads all the entrances in the widgets, call the function "pitch_detection" 
// and calls the display function "mainplot"  
//
///////////////////////////////////////////////////////////////////////

void
pitch_detect_clicked                   (GtkWidget       *win,
                                        gpointer         user_data)
{
  char *input_file;
  char *output_file;
  char *length;
  char *window;
  char *algorithm;
  char *overlap;
  char *minimum_frequency;
  char *maximum_frequency;
  char *threshold;
  char *duration;
  char *sampling_rate;
  char *zeropad;
  
 config screen_info;

float *data;
int size_of_data = 0;
float step=0.0;

//config  configuration;

/* TEXT ENTRIES */
GtkEntry *input_file_entry = GTK_ENTRY(lookup_widget (win, "entry1"));
GtkEntry *output_file_entry = GTK_ENTRY(lookup_widget (win, "entry2"));
GtkEntry *minimum_frequency_entry = GTK_ENTRY(lookup_widget (win, "entry5"));
GtkEntry *maximum_frequency_entry = GTK_ENTRY(lookup_widget (win, "entry6"));
GtkEntry *threshold_entry = GTK_ENTRY(lookup_widget (win, "entry8"));
GtkEntry *duration_entry = GTK_ENTRY(lookup_widget (win, "entry7"));



/* COMBO ENTRIES */
GtkCombo *combo_frame_length = GTK_COMBO(lookup_widget (win, "combo4"));
GtkEntry *length_entry = GTK_ENTRY(combo_frame_length->entry);
GtkCombo *combo_window_type = GTK_COMBO(lookup_widget (win, "combo2"));
GtkEntry *window_entry = GTK_ENTRY(combo_window_type->entry);
GtkCombo *combo_overlap = GTK_COMBO(lookup_widget (win, "combo3"));
GtkEntry *overlap_entry = GTK_ENTRY(combo_overlap->entry);
GtkCombo *combo_algorithm = GTK_COMBO(lookup_widget (win, "combo5"));
GtkEntry *algorithm_entry = GTK_ENTRY(combo_algorithm->entry);
GtkCombo *combo_sampling_rate = GTK_COMBO(lookup_widget (win, "combo8"));
GtkEntry *sampling_rate_entry = GTK_ENTRY(combo_sampling_rate->entry);
GtkCombo *combo_zeropad = GTK_COMBO(lookup_widget (win, "combo9"));
GtkEntry *zeropad_entry = GTK_ENTRY(combo_zeropad->entry);


/* READING THE CURRENT VALUE OF THE VARIABLES */
input_file = gtk_entry_get_text(input_file_entry);
output_file = gtk_entry_get_text(output_file_entry);
length = gtk_entry_get_text(length_entry);
window = gtk_entry_get_text(window_entry);
overlap = gtk_entry_get_text(overlap_entry);
algorithm = gtk_entry_get_text(algorithm_entry);
minimum_frequency = gtk_entry_get_text(minimum_frequency_entry);
maximum_frequency = gtk_entry_get_text(maximum_frequency_entry);
threshold = gtk_entry_get_text(threshold_entry);
duration = gtk_entry_get_text(duration_entry);
sampling_rate = gtk_entry_get_text(sampling_rate_entry);
zeropad = gtk_entry_get_text(zeropad_entry);


/*GtkToggleButton *togglebutton = GTK_TOGGLE_BUTTON(lookup_widget(win, "radiobutton1"));*/


/* PRINTING THE VARIABLES IN THE SCREEN */
printf("\n***************************************\nINFO. READ FROM THE WIDGET\n***************************\n"); 
printf("The name of the input file is= %s \n",input_file);
printf("The name of the output file is= %s \n",output_file);
printf("The frame length is= %s \n",length);
printf("The window is= %s \n",window);
printf("The overlap is= %s \n",overlap); 
printf("The algorithm is= %s \n",algorithm);
printf("The minimum_frequency is= %s \n",minimum_frequency);
printf("The maximum_frequency is= %s \n",maximum_frequency);
printf("The threshold is= %s \n",threshold);
printf("The duration is= %s \n",duration);
printf("The sampling rate is= %s \n",sampling_rate);
printf("The zeropad rate is= %s \n",zeropad);

screen_info = translate(input_file, output_file, length, window, overlap, algorithm, minimum_frequency, maximum_frequency, threshold, duration, sampling_rate, zeropad);

data = pitch_detection(screen_info, &size_of_data, &step );
 
mainplot(argc_gen,argv_gen,data,size_of_data,step, screen_info.lowest_frequency, screen_info.maximum_frequency);

  free(data);

}


///////////////////////////////////////////////////////////////////////
// 
// kill_selected is called when the program is closed
//
///////////////////////////////////////////////////////////////////////


void
kill_selected                          (GtkObject       *object,
                                        gpointer         user_data)
{
 exit(1);
}


///////////////////////////////////////////////////////////////////////
// 
// real_time_detection_clicked is called with from the main widgetm
// it reads all the values selected in the widget, calls the real time plot
// where the detection is done and displayed. After that a summary plot is displayed.
//
///////////////////////////////////////////////////////////////////////



void
real_time_detection_clicked            (GtkWidget       *win,
                                        gpointer         user_data)
{
  char *input_file;
  char *output_file;
  char *length;
  char *window;
  char *algorithm;
  char *overlap;
  char *minimum_frequency;
  char *maximum_frequency;
  char *threshold;
  char *duration;
  char *sampling_rate;
  char *zeropad;
  
 config screen_info;

float *pitch_data;
int size_of_data = 0;
float step=0.0;

//config  configuration;

/* TEXT ENTRIES */
GtkEntry *input_file_entry = GTK_ENTRY(lookup_widget (win, "entry1"));
GtkEntry *output_file_entry = GTK_ENTRY(lookup_widget (win, "entry2"));
GtkEntry *minimum_frequency_entry = GTK_ENTRY(lookup_widget (win, "entry5"));
GtkEntry *maximum_frequency_entry = GTK_ENTRY(lookup_widget (win, "entry6"));
GtkEntry *threshold_entry = GTK_ENTRY(lookup_widget (win, "entry8"));
GtkEntry *duration_entry = GTK_ENTRY(lookup_widget (win, "entry7"));



/* COMBO ENTRIES */
GtkCombo *combo_frame_length = GTK_COMBO(lookup_widget (win, "combo4"));
GtkEntry *length_entry = GTK_ENTRY(combo_frame_length->entry);
GtkCombo *combo_window_type = GTK_COMBO(lookup_widget (win, "combo2"));
GtkEntry *window_entry = GTK_ENTRY(combo_window_type->entry);
GtkCombo *combo_overlap = GTK_COMBO(lookup_widget (win, "combo3"));
GtkEntry *overlap_entry = GTK_ENTRY(combo_overlap->entry);
GtkCombo *combo_algorithm = GTK_COMBO(lookup_widget (win, "combo5"));
GtkEntry *algorithm_entry = GTK_ENTRY(combo_algorithm->entry);
GtkCombo *combo_sampling_rate = GTK_COMBO(lookup_widget (win, "combo8"));
GtkEntry *sampling_rate_entry = GTK_ENTRY(combo_sampling_rate->entry);
GtkCombo *combo_zeropad = GTK_COMBO(lookup_widget (win, "combo9"));
GtkEntry *zeropad_entry = GTK_ENTRY(combo_zeropad->entry);


/* READING THE CURRENT VALUE OF THE VARIABLES */
input_file = gtk_entry_get_text(input_file_entry);
output_file = gtk_entry_get_text(output_file_entry);
length = gtk_entry_get_text(length_entry);
window = gtk_entry_get_text(window_entry);
overlap = gtk_entry_get_text(overlap_entry);
algorithm = gtk_entry_get_text(algorithm_entry);
minimum_frequency = gtk_entry_get_text(minimum_frequency_entry);
maximum_frequency = gtk_entry_get_text(maximum_frequency_entry);
threshold = gtk_entry_get_text(threshold_entry);
duration = gtk_entry_get_text(duration_entry);
sampling_rate = gtk_entry_get_text(sampling_rate_entry);
zeropad = gtk_entry_get_text(zeropad_entry);


/*GtkToggleButton *togglebutton = GTK_TOGGLE_BUTTON(lookup_widget(win, "radiobutton1"));*/


/* PRINTING THE VARIABLES IN THE SCREEN */

printf("\n***************************************\nINFO. READ FROM THE WIDGET\n***************************\n"); 
printf("The name of the input file is= %s \n",input_file);
printf("The name of the output file is= %s \n",output_file);
printf("The frame length is= %s \n",length);
printf("The window is= %s \n",window);
printf("The overlap is= %s \n",overlap); 
printf("The algorithm is= %s \n",algorithm);
printf("The minimum_frequency is= %s \n",minimum_frequency);
printf("The maximum_frequency is= %s \n",maximum_frequency);
printf("The threshold is= %s \n",threshold);
printf("The duration is= %s \n",duration);
printf("The sampling rate is= %s \n",sampling_rate);
printf("The zeropad rate is= %s \n",zeropad);

screen_info = translate(input_file, output_file, length, window, overlap, algorithm, minimum_frequency, maximum_frequency, threshold, duration, sampling_rate, zeropad);

step = (float) screen_info.frame_length/screen_info.sampling_rate;
size_of_data = floor(screen_info.duration * screen_info.sampling_rate / screen_info.frame_length);

pitch_data = realtime_plot(argc_gen,argv_gen, screen_info);

mainplot(argc_gen,argv_gen,pitch_data,size_of_data,step,screen_info.lowest_frequency, screen_info.maximum_frequency );
 
free(pitch_data);


}


///////////////////////////////////////////////////////////////////////
// 
// Visualization of the window selected.
//
///////////////////////////////////////////////////////////////////////


void
show_window_clicked                    (GtkWidget       *win,
                                        gpointer         user_data)
{
  float *window_data;
  char *length;
  char *window;
  int window_type;

  /* COMBO ENTRIES */
GtkCombo *combo_frame_length = GTK_COMBO(lookup_widget (win, "combo4"));
GtkEntry *length_entry = GTK_ENTRY(combo_frame_length->entry);
GtkCombo *combo_window_type = GTK_COMBO(lookup_widget (win, "combo2"));
GtkEntry *window_entry = GTK_ENTRY(combo_window_type->entry);

length = gtk_entry_get_text(length_entry);
window = gtk_entry_get_text(window_entry);

 if (!strcmp(window,"Rectangular")) 	     
   window_type=0;
 else if (!strcmp(window,"Hanning")) 
   window_type=1;
 else if (!strcmp(window,"Welch")) 
   window_type=2;
 else if (!strcmp(window,"Parzen")) 
   window_type=3;
 else if (!strcmp(window,"Bartlett")) 
   window_type=4;
 else if (!strcmp(window,"Hamming")) 
   window_type=5;
 else if (!strcmp(window,"Blackman2")) 
   window_type=6;
 else if (!strcmp(window,"Blackman3")) 
   window_type=7;
 else if (!strcmp(window,"Blackman4")) 
   window_type=8;
 else if (!strcmp(window,"Exponential")) 
   window_type=9;
 else if (!strcmp(window,"Kaiser")) 
   window_type=10;
 else if (!strcmp(window,"Cauchy")) 
   window_type=11;
 else if (!strcmp(window,"Poisson")) 
   window_type=12;
 else if (!strcmp(window,"Riemann")) 
   window_type=13;
 else if (!strcmp(window,"Gaussian")) 
   window_type=14;
 else if (!strcmp(window,"Tukey")) 
   window_type=15;
 else  {printf("unknown fft data window: %s",window); 
 exit(0);}

 window_data=(float *) mus_make_fft_window( window_type , 128, 0.4);
 mainplot(argc_gen,argv_gen,window_data,128,.1, 0,1);
 
}

