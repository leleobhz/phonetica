# include "pitch_tools.h"

char **Read_table(char *filename, int *pnentry) {
  FILE	*ftxt;
  int	i, j, textsize;
  char  **entryv, *text;

  ftxt = Panic_fopen(filename, "r");
  Panic_fseek(ftxt, (long) 0, 2);
  textsize = ftell(ftxt);
  text = Panic_malloc(textsize);
  Panic_fseek(ftxt, (long) 0, 0);
  Panic_fread(text, 1, textsize, ftxt);
  Panic_fclose(ftxt);

  if(text[textsize - 1] != '\n')
    fprintf(stderr, "Warning: Read_table: file does not end in \\n: %s\n",
	    filename);

  *pnentry = 0;
  for(i = 0; i < textsize; i++) 
    if(text[i] == '\n') (*pnentry)++;

  entryv = (char**) Panic_pointer_array(*pnentry);
  j = 0;
  entryv[j++] = text;
  for(i = 0; i < textsize - 1; i++)
    if(text[i] == '\n') {
      text[i] = '\0';
      entryv[j++] = text + i + 1;
    }
  text[textsize - 1] = '\0';
  
  return(entryv);
}
