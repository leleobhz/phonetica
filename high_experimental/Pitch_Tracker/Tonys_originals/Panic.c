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

FILE *Panic_fopen(char *filename, char *mode) {
  FILE *stream;

  RETRY8(((stream = fopen(filename, mode)) == NULL));
  if(stream == NULL)
    Panic("Panic_fopen(\"%s\", \"%s\") == NULL\n", filename, mode);
  return(stream);
}

FILE *Panic_fopen_3bits(char *bit0, char *bit1, char *bit2, char *mode) {
  char filename[MAXPATHLEN];
  FILE *stream;

  strncpy(filename, bit0, MAXPATHLEN - 1);
  strncat(filename, bit1, MAXPATHLEN - 1 - strlen(filename));
  strncat(filename, bit2, MAXPATHLEN - 1 - strlen(filename));

  RETRY8(((stream = fopen(filename, mode)) == NULL));
  if(stream == NULL)
    Panic("Panic_fopen_3bits(\"%s\", \"%s\", \"%s\", \"%s\", \"%s\") == NULL\n"
	  , bit0, bit1, bit2, mode);
  return(stream);
}

void Panic_fclose(FILE *stream) {
  int nfclose;

  RETRY8(((nfclose = fclose(stream)) == EOF));
  if(nfclose == EOF)
    Panic("Panic_fclose(%x) == EOF\n", (int) stream);
}

void Panic_fread(void *ptr, int size, int nitems, FILE *stream) {
  int nread;
  if((nread = fread(ptr, size, nitems, stream)) != nitems)
    Panic("Panic_fread(%x, %d, %d, %x) == %d\n", (int) ptr, size, nitems, 
	  (int) stream, nread);
}

void Panic_fwrite(void *ptr, int size, int nitems, FILE *stream) {
  int nwrite;
  if((nwrite = fwrite(ptr, size, nitems, stream)) != nitems)
    Panic("Panic_fwrite(%x, %d, %d, %x) == %d\n", (int) ptr, size, nitems, 
	  (int) stream, nwrite);
}

void Panic_fprintf_args(int argc, char **argv, FILE *stream) {
  int i;

  for(i = 0; i < argc - 1; i++) 
    if(fprintf(stream, "%s ", argv[i]) == EOF)
      Panic("Panic_fprintf_args: failed writing %s\n", argv[i]);
  if(fprintf(stream, "%s\n", argv[argc - 1]) == EOF)
     Panic("Panic_fprintf_args: failed writing %s\n", argv[argc - 1]);
  fflush(stream);
}

void Panic_fseek(FILE *stream, long offset, int ptrname) {
  if(fseek(stream, offset, ptrname) == -1)
    Panic("Panic_fseek(%x, %d, %d) == -1\n", (int) stream, offset, ptrname);
}

	    
