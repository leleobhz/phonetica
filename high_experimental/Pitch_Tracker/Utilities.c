#include "pitch_tools.h"

#ifdef TC
void Blink(int n) {
  int i;

  for(i = 0; i < n; i++) {
    node_led_on();
    usleep(333333);
    node_led_off();
    usleep(333333);
  }
  sleep(2);
}

void Panic(format, ...)
char *format;
{
  va_list args;

  va_start(args, format);
  if(getnodeid() == B011_NODEID) (void) vfprintf(stderr, format, args);
  else while(1) Blink(strlen(format));
  exit(1);
}
#else
void Panic(va_alist)
va_dcl
{
  va_list args;
  char    *fmt;

  va_start(args);
  fmt = va_arg(args, char*);
  (void) vfprintf(stderr, fmt, args);
  va_end(args);
  exit(1);
}
#endif

char *Panic_malloc(int size) {
  char *address;

  if(size == 0) fprintf(stderr, "Panic_malloc warning: zero byte request\n");

  if((address = malloc(size)) == NULL)
    Panic("Panic_malloc(%d) == NULL\n", size);
  return(address);
}

void Panic_free(char *address) {
  if(address == NULL) Panic("Panic_free(NULL)\n");
  free(address);
#ifdef FREE_RETURNS_STATUS
  if((char*) free(address) == NULL)
    Panic("Panic_free(%x) == NULL\n", (int) address);
#endif
}

void Panic_fread(void *ptr, int size, int nitems, FILE *stream) {
  int nread;
  if((nread = fread(ptr, size, nitems, stream)) != nitems)
    Panic("Panic_fread(%x, %d, %d, %x) == %d\n", (int) ptr, size, nitems, 
	  (int) stream, nread);
}

void Panic_fseek(FILE *stream, long offset, int ptrname) {
  if(fseek(stream, offset, ptrname) == -1)
    Panic("Panic_fseek(%x, %d, %d) == -1\n", (int) stream, offset, ptrname);
}

short *Short_array(int size) {
  return((short*) malloc((unsigned) (size * sizeof(short))));
}

short *Panic_short_array(int size) {
  short *array = Short_array(size);
  if(array == NULL) Panic("Panic_short_array(%d) == NULL\n", size);
  return(array);
}

FILE *Std_fopen(char *filename, char *mode) {
  if(strcmp(filename, "-") == 0) {
    if(strcmp(mode, "r") == 0) return(stdin);
    else if(strcmp(mode, "w") == 0 || strcmp(mode, "a") == 0) return(stdout);
    else Panic("Panic: Undefined operation: Std_fopen(\"%s\", \"%s\")\n",
	       filename, mode);
  }
  return(fopen(filename, mode));
}

int Scan_flag(int argc, char **argv, char *flag) {
  int i;

  for(i = 1; i < argc; i++)
    if(strcmp(argv[i], flag) == 0) return(TRUE);
   
  return(FALSE);
}

int  Scan_int(int argc, char **argv, char *flag, int value) {
  int i;

  for(i = 1; i < argc - 1; i++)
    if(strcmp(argv[i], flag) == 0)
      value = atoi(argv[i + 1]);

  return(value);
}

char *Scan_string(int argc, char **argv, char *flag, char *value) {
  int i;

  for(i = 1; i < argc - 1; i++)
    if(strcmp(argv[i], flag) == 0)
      return(argv[i + 1]);

  return(value);
}
