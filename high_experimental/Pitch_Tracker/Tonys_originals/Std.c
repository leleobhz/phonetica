#include "pitch_tools.h"

FILE *Std_fopen(char *filename, char *mode) {
  if(strcmp(filename, "-") == 0) {
    if(strcmp(mode, "r") == 0) return(stdin);
    else if(strcmp(mode, "w") == 0 || strcmp(mode, "a") == 0) return(stdout);
    else Panic("Panic: Undefined operation: Std_fopen(\"%s\", \"%s\")\n",
	       filename, mode);
  }
  return(fopen(filename, mode));
}

