/*
** scan a command line for a given string and return the following double
** or the default if the string is not present
*/
# include "pitch_tools.h"

double  Scan_double(int argc, char **argv, char *flag, double value) {
  int i;

  for(i = 1; i < argc - 1; i++)
    if(strcmp(argv[i], flag) == 0)
      value = atof(argv[i + 1]);
#ifdef TC
  if(getnodeid() != 61 && getnodeid() != 62 && getnodeid() != 63)
    node_led_off();
#endif
  return(value);
}

/*
**  scan a command line for a given string and return TRUE if found 
*/
int Scan_flag(int argc, char **argv, char *flag) {
  int i;

  for(i = 1; i < argc; i++)
    if(strcmp(argv[i], flag) == 0) return(TRUE);
   
  return(FALSE);
}

/*
** scan a command line for a given string and return the following float
** or the default if the string is not present
*/
float Scan_float(int argc, char **argv, char *flag, float value) {
  int i;

  for(i = 1; i < argc - 1; i++)
    if(strcmp(argv[i], flag) == 0)
      value = atof(argv[i + 1]);
#ifdef TC
  node_led_off();
#endif
  return(value);
}

/*
** scan a command line for a given string and return the following int
** or the default if the string is not present
*/
int  Scan_int(int argc, char **argv, char *flag, int value) {
  int i;

  for(i = 1; i < argc - 1; i++)
    if(strcmp(argv[i], flag) == 0)
      value = atoi(argv[i + 1]);

  return(value);
}

/*
** scan a command line for a given string and return the following string
** or the default if the string is not present
*/
char *Scan_string(int argc, char **argv, char *flag, char *value) {
  int i;

  for(i = 1; i < argc - 1; i++)
    if(strcmp(argv[i], flag) == 0)
      return(argv[i + 1]);

  return(value);
}
