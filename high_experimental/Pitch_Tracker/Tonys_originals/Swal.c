# include "pitch_tools.h"

void Swal(char *from, char *to, int nbytes) {
  int i;

  if(nbytes % 4 != 0)
    Panic("Swal: called with nbytes: %d\n", nbytes);

  for(i = 0; i < nbytes; i += 4) {
    char tmp;
    tmp = from[i + 0];
    to[i + 0] = from[i + 3];
    to[i + 3] = tmp;
    tmp = from[i + 1];
    to[i + 1] = from[i + 2];
    to[i + 2] = tmp;
  }
}

int Swal_fread(void *ptr, int size, int nitems, FILE *stream) {
  int nread;
  nread = fread(ptr, size, nitems, stream);
  Swal((char*) ptr, (char*) ptr, size * nread);
  return(nread);
}
