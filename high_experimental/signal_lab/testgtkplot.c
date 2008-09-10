#include <math.h>
#include <stdio.h>
#include <gtk/gtk.h>
#include <gdk/gdk.h>
#include "gtkplotcanvas.h"
#include "gtkplotps.h"
#include <stdlib.h>

GtkWidget **plots;
GtkPlotData *dataset[1];
gint nlayers = 0;
double *px2;
double *py2;
double *dx2;

void
quit ()
{
  gtk_main_quit();
  
}

GtkWidget *
new_layer(GtkWidget *canvas)
{
 gchar label[10];

 nlayers++;


 plots = (GtkWidget **)g_realloc(plots, nlayers * sizeof(GtkWidget *));

 sprintf(label, "%d", nlayers);
 

 plots[nlayers-1] = gtk_plot_new_with_size(NULL, .5, .35); //Size of the plot

 return plots[nlayers-1];
}


void
build_data(GtkWidget *active_plot, float* data, int length, float step)
{
 GdkColor color;
 int i;


  px2 = (double *)calloc(sizeof(double), length);
  py2 = (double *)calloc(sizeof(double), length);
  dx2 = (double *)calloc(sizeof(double), length);


 for (i=0;i<length;i++)
    {
      //printf("%d\n",i);
      px2[i]=step*i;
      py2[i]=(double) *(data+ i); 
      dx2[i]=step;
      // printf("%f\n",py[i]);
    }

  gdk_color_parse("blue", &color);
 gdk_color_alloc(gdk_colormap_get_system(), &color); 

 // GTK_PLOT(active_plot)->yscale = GTK_PLOT_SCALE_LOG10;

 dataset[0] = gtk_plot_dataset_new();
 gtk_plot_add_dataset(GTK_PLOT(active_plot), dataset[0]);
 gtk_plot_dataset_set_points(dataset[0], px2, py2, dx2, NULL, length);
 gtk_plot_dataset_set_symbol(dataset[0],
                             GTK_PLOT_SYMBOL_CIRCLE,
			     GTK_PLOT_SYMBOL_FILLED,
                             4, 1, &color); 
 /* gtk_plot_dataset_set_line_attributes(dataset[0],
                                      GTK_PLOT_LINE_SOLID,
                                      5, &color);*/
  

 gtk_plot_dataset_set_line_attributes(dataset[0],
                                      GTK_PLOT_LINE_SOLID,
                                      2, &color);

 gtk_plot_dataset_set_connector(dataset[0], GTK_PLOT_CONNECT_NONE);
 gtk_plot_dataset_set_legend(dataset[0], "Pitch");
}

int mainplot(int argc, char *argv[], float *data, int length, float step,int min_freq,  int max_freq){

 GtkWidget *window1;
 GtkWidget *vbox1;
 GtkWidget *scrollw1;
 GtkWidget *layout;
 GtkWidget *active_plot;
 GtkWidget *canvas;
 GdkColor color;
 gint page_width, page_height;
 // gfloat step= 0.01; // OJO CON ESTAS DOS
 //int max_freq= 1500; // OJO CON ESTA TAMIEN
 gfloat scale = 1.5;
 
 page_width = GTK_PLOT_LETTER_W * scale;
 page_height = GTK_PLOT_LETTER_H * scale;
 
 gtk_init(&argc,&argv);

 window1=gtk_window_new(GTK_WINDOW_TOPLEVEL);
 gtk_window_set_title(GTK_WINDOW(window1), "GtkPlot Pitch Detection");
 gtk_widget_set_usize(window1,550,650);
 gtk_container_border_width(GTK_CONTAINER(window1),0);

 gtk_signal_connect (GTK_OBJECT (window1), "destroy",
		     GTK_SIGNAL_FUNC (quit), NULL);

 vbox1=gtk_vbox_new(FALSE,0);
 gtk_container_add(GTK_CONTAINER(window1),vbox1);
 gtk_widget_show(vbox1);

 scrollw1=gtk_scrolled_window_new(NULL, NULL);
 gtk_container_border_width(GTK_CONTAINER(scrollw1),0);
 gtk_scrolled_window_set_policy(GTK_SCROLLED_WINDOW(scrollw1),
				GTK_POLICY_ALWAYS,GTK_POLICY_ALWAYS);
 gtk_box_pack_start(GTK_BOX(vbox1),scrollw1, TRUE, TRUE,0);
 gtk_widget_show(scrollw1);

 canvas = gtk_plot_canvas_new(page_width, page_height);
 GTK_PLOT_CANVAS_SET_FLAGS(GTK_PLOT_CANVAS(canvas), GTK_PLOT_CANVAS_DND_FLAGS);
 layout = canvas;
 gtk_container_add(GTK_CONTAINER(scrollw1),layout);
 gtk_layout_set_size(GTK_LAYOUT(layout), page_width, page_height);
 GTK_LAYOUT(layout)->hadjustment->step_increment = 5;
 GTK_LAYOUT(layout)->vadjustment->step_increment = 5;

 //next three lines for the background color
 gdk_color_parse("light blue", &color);
 gdk_color_alloc(gtk_widget_get_colormap(layout), &color);
 gtk_plot_layout_set_background(GTK_PLOT_LAYOUT(layout), &color);


 gtk_widget_show(layout);

 active_plot = new_layer(canvas);
 gdk_color_parse("light yellow", &color);
 gdk_color_alloc(gtk_widget_get_colormap(active_plot), &color);
 gtk_plot_set_background(GTK_PLOT(active_plot), &color);

 gdk_color_parse("light blue", &color);
 gdk_color_alloc(gtk_widget_get_colormap(layout), &color);
 gtk_plot_legends_set_attributes(GTK_PLOT(active_plot),
                                 NULL, 0,
				 NULL,
                                 &color);
 gtk_plot_set_range(GTK_PLOT(active_plot), 0. ,length*step, min_freq,max_freq);
 gtk_plot_axis_set_ticks(GTK_PLOT(active_plot), 0, 1., 5.);
 gtk_plot_axis_set_ticks(GTK_PLOT(active_plot), 1, 50.,100.);
 gtk_plot_axis_hide_title(GTK_PLOT(active_plot), GTK_PLOT_AXIS_TOP );
 gtk_plot_axis_hide_title(GTK_PLOT(active_plot), GTK_PLOT_AXIS_RIGHT);
 gtk_plot_axis_set_title(GTK_PLOT(active_plot), GTK_PLOT_AXIS_LEFT, "Frequency [Hz]" );
 gtk_plot_axis_set_title(GTK_PLOT(active_plot), GTK_PLOT_AXIS_BOTTOM , "Time[sec]" );

 //gtk_plot_grids_set_visible(GTK_PLOT(active_plot), TRUE, TRUE, TRUE, TRUE);
 gtk_plot_canvas_add_plot(GTK_PLOT_CANVAS(canvas), GTK_PLOT(active_plot), .1, .1); //Position of the plot
 gtk_plot_set_legends_border(GTK_PLOT(active_plot), 2, 3);
 gtk_plot_legends_move(GTK_PLOT(active_plot), .58, .05);
 gtk_widget_show(active_plot);


 build_data(active_plot,data,length, step);

 
 gtk_widget_show(window1);

 
 gtk_plot_layout_put_text(GTK_PLOT_LAYOUT(canvas), .40, .020, 0, 
                          "Times-BoldItalic", 16, NULL, NULL,
                          GTK_JUSTIFY_CENTER,
                          "Pitch Detection");
 
 gtk_plot_layout_export_ps(GTK_PLOT_LAYOUT(canvas), "plot.ps", 0, 0, 
                           GTK_PLOT_LETTER);
 
 gtk_main();
 
 free(px2);
 free(py2);
 free(dx2);

 return(0);
}


