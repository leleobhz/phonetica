#include "pitch_tools.h"

char *Char_array(int size) {
  return((char*) malloc((unsigned) (size * sizeof(char))));
}

uchar *Uchar_array(int size) {
  return((uchar*) malloc((unsigned) (size * sizeof(uchar))));
}

short *Short_array(int size) {
  return((short*) malloc((unsigned) (size * sizeof(short))));
}

ushort *Ushort_array(int size) {
  return((ushort*) malloc((unsigned) (size * sizeof(short))));
}

int *Int_array(int size) {
  return((int*) malloc((unsigned) (size * sizeof(int))));
}

float *Float_array(int size) {
  return((float*) malloc((unsigned) (size * sizeof(float))));
}

double *Double_array(int size) {
  return((double*) malloc((unsigned) (size * sizeof(double))));
}

void **Pointer_array(int size) {
  return((void**) malloc((unsigned) (size * sizeof(void*))));
}

char *Panic_char_array(int size) {
  char *array = Char_array(size);
  if(array == NULL) Panic("Panic_char_array(%d) == NULL\n", size);
  return(array);
}

uchar *Panic_uchar_array(int size) {
  uchar *array = Uchar_array(size);
  if(array == NULL) Panic("Panic_uchar_array(%d) == NULL\n", size);
  return(array);
}

short *Panic_short_array(int size) {
  short *array = Short_array(size);
  if(array == NULL) Panic("Panic_short_array(%d) == NULL\n", size);
  return(array);
}

ushort *Panic_ushort_array(int size) {
  ushort *array = Ushort_array(size);
  if(array == NULL) Panic("Panic_ushort_array(%d) == NULL\n", size);
  return(array);
}

int *Panic_int_array(int size) {
  int *array = Int_array(size);
  if(array == NULL) Panic("Panic_int_array(%d) == NULL\n", size);
  return(array);
}

float *Panic_float_array(int size) {
  float *array = Float_array(size);
  if(array == NULL) Panic("Panic_float_array(%d) == NULL\n", size);
  return(array);
}

double *Panic_double_array(int size) {
  double *array = Double_array(size);
  if(array == NULL) Panic("Panic_double_array(%d) == NULL\n", size);
  return(array);
}

void **Panic_pointer_array(int size) {
  void **array = Pointer_array(size);
  if(array == NULL) Panic("Panic_pointer_array(%d) == NULL\n", size);
  return(array);
}

char **Char_2d_array(int size0, int size1) {
  char **array0;

  if((array0 = (char**) malloc(size0 * sizeof(char*) + size0 * size1)) !=NULL){
    char *array1 = (char*) (array0 + size0);
    int i;
    
    for(i = 0; i < size0; i++)
      array0[i] = array1 + i * size1;
  }
  return(array0);
}

uchar **Uchar_2d_array(int i_size, int j_size) {
  return((uchar**) Char_2d_array(i_size, j_size * sizeof(uchar)));
}

short **Short_2d_array(int i_size, int j_size) {
  return((short**) Char_2d_array(i_size, j_size * sizeof(short)));
}

ushort **Ushort_2d_array(int i_size, int j_size) {
  return((ushort**) Char_2d_array(i_size, j_size * sizeof(ushort)));
}

int **Int_2d_array(int i_size, int j_size) {
  return((int**) Char_2d_array(i_size, j_size * sizeof(int)));
}

float **Float_2d_array(int i_size, int j_size) {
  return((float**) Char_2d_array(i_size, j_size * sizeof(float)));
}

double **Double_2d_array(int i_size, int j_size) {
  return((double**) Char_2d_array(i_size, j_size * sizeof(double)));
}

void ***Pointer_2d_array(int i_size, int j_size) {
  return((void***) Char_2d_array(i_size, j_size * sizeof(void*)));
}

char **Panic_char_2d_array(int i_size, int j_size) {
  char **array = Char_2d_array(i_size, j_size);
  if(array == NULL)
    Panic("Panic_char_2d_array(%d, %d) == NULL\n", i_size, j_size);
  return(array);
}

uchar **Panic_uchar_2d_array(int i_size, int j_size) {
  uchar **array = Uchar_2d_array(i_size, j_size);
  if(array == NULL)
    Panic("Panic_uchar_2d_array(%d, %d) == NULL\n", i_size, j_size);
  return(array);
}

short **Panic_short_2d_array(int i_size, int j_size) {
  short **array = Short_2d_array(i_size, j_size);
  if(array == NULL)
    Panic("Panic_short_2d_array(%d, %d) == NULL\n",i_size, j_size);
  return(array);
}

ushort **Panic_ushort_2d_array(int i_size, int j_size) {
  ushort **array = Ushort_2d_array(i_size, j_size);
  if(array == NULL)
    Panic("Panic_ushort_2d_array(%d, %d) == NULL\n",i_size, j_size);
  return(array);
}

int **Panic_int_2d_array(int i_size, int j_size) {
  int **array = Int_2d_array(i_size, j_size);
  if(array == NULL)
    Panic("Panic_int_2d_array(%d, %d) == NULL\n", i_size, j_size);
  return(array);
}

float **Panic_float_2d_array(int i_size, int j_size) {
  float **array = Float_2d_array(i_size, j_size);
  if(array == NULL)
    Panic("Panic_float_2d_array(%d, %d) == NULL\n", i_size, j_size);
  return(array);
}

double **Panic_double_2d_array(int i_size, int j_size) {
  double **array = Double_2d_array(i_size, j_size);
  if(array == NULL)
    Panic("Panic_double_2d_array(%d, %d) == NULL\n", i_size, j_size);
  return(array);
}

void ***Panic_pointer_2d_array(int i_size, int j_size) {
  void ***array = Pointer_2d_array(i_size, j_size);
  if(array == NULL)
    Panic("Panic_pointer_2d_array(%d, %d) == NULL\n", i_size, j_size);
  return(array);
}

char ***Char_3d_array(int size0, int size1, int size2) {
  char ***array0;

  if((array0 = (char***) malloc(size0 * sizeof(char**) + 
				size0 * size1 * sizeof(char*) +
				size0 * size1 * size2)) != NULL) {
    char **array1 = (char**) (array0 + size0);
    char  *array2 = (char*)  (array1 + size0 * size1);
    int  i, j;

    for(i = 0; i < size0; i++) {
      array0[i] = array1 + i * size1;
      for(j = 0; j < size1; j++)
	array0[i][j] = array2 + (i * size1 + j) * size2;
    }
  }
  
  return(array0);
}

uchar ***Uchar_3d_array(int i_size, int j_size, int k_size) {
  return((uchar***) Char_3d_array(i_size, j_size, k_size * sizeof(uchar)));
}

short ***Short_3d_array(int i_size, int j_size, int k_size) {
  return((short***) Char_3d_array(i_size, j_size, k_size * sizeof(short)));
}

ushort ***Ushort_3d_array(int i_size, int j_size, int k_size) {
  return((ushort***) Char_3d_array(i_size, j_size, k_size * sizeof(ushort)));
}

int ***Int_3d_array(int i_size, int j_size, int k_size) {
  return((int***) Char_3d_array(i_size, j_size, k_size * sizeof(int)));
}

float ***Float_3d_array(int i_size, int j_size, int k_size) {
  return((float***) Char_3d_array(i_size, j_size, k_size * sizeof(float)));
}

double ***Double_3d_array(int i_size, int j_size, int k_size) {
  return((double***) Char_3d_array(i_size, j_size, k_size * sizeof(double)));
}

void ****Pointer_3d_array(int i_size, int j_size, int k_size) {
  return((void****) Char_3d_array(i_size, j_size, k_size * sizeof(void*)));
}

char ***Panic_char_3d_array(int i_size, int j_size, int k_size) {
  char ***array = Char_3d_array(i_size, j_size, k_size);
  if(array == NULL)
    Panic("Panic_char_3d_array(%d, %d, %d) == NULL\n", i_size, j_size, k_size);
  return(array);
}

uchar ***Panic_uchar_3d_array(int i_size, int j_size, int k_size) {
  uchar ***array = Uchar_3d_array(i_size, j_size, k_size);
  if(array == NULL)
    Panic("Panic_uchar_3d_array(%d, %d, %d) == NULL\n", i_size, j_size,k_size);
  return(array);
}

short ***Panic_short_3d_array(int i_size, int j_size, int k_size) {
  short ***array = Short_3d_array(i_size, j_size, k_size);
  if(array == NULL)
    Panic("Panic_short_3d_array(%d, %d, %d) == NULL\n", i_size, j_size,k_size);
  return(array);
}

ushort ***Panic_ushort_3d_array(int i_size, int j_size, int k_size) {
  ushort ***array = Ushort_3d_array(i_size, j_size, k_size);
  if(array == NULL)
    Panic("Panic_ushort_3d_array(%d, %d, %d) == NULL\n", i_size,j_size,k_size);
  return(array);
}

int ***Panic_int_3d_array(int i_size, int j_size, int k_size) {
  int ***array = Int_3d_array(i_size, j_size, k_size);
  if(array == NULL)
    Panic("Panic_int_3d_array(%d, %d, %d) == NULL\n", i_size, j_size,k_size);
  return(array);
}

float ***Panic_float_3d_array(int i_size, int j_size, int k_size) {
  float ***array = Float_3d_array(i_size, j_size, k_size);
  if(array == NULL)
    Panic("Panic_float_3d_array(%d, %d, %d) == NULL\n", i_size, j_size,k_size);
  return(array);
}

double ***Panic_double_3d_array(int i_size, int j_size, int k_size) {
  double ***array = Double_3d_array(i_size, j_size, k_size);
  if(array == NULL)
    Panic("Panic_double_3d_array(%d, %d, %d) == NULL\n", i_size,j_size,k_size);
  return(array);
}

void ****Panic_pointer_3d_array(int i_size, int j_size, int k_size) {
  void ****array = Pointer_3d_array(i_size, j_size, k_size);
  if(array == NULL)
    Panic("Panic_pointer_3d_array(%d, %d, %d) == NULL\n",i_size,j_size,k_size);
  return(array);
}

/******************************************/
/* temporarily retained for compatability */
/******************************************/

Tools_type *Make_array(int size) {
  return((Tools_type*) malloc((unsigned) (size * sizeof(Tools_type))));
}

Tools_type*** Make_pyramidal_array(size)
int size;
{
  Tools_type ***tmp0, **tmp1, *tmp2;
  int i, j;

  /* grab space for pointers, then the real storage, then set up pointers */
  if((tmp0 = (Tools_type***) malloc((unsigned) (size * sizeof(*tmp0))))
                                                                     != NULL &&
     (tmp1 = (Tools_type**)  malloc((unsigned) (size * size * sizeof(*tmp1))))
                                                                     != NULL &&
     (tmp2  = Make_array((size * (size + 1) * (size + 2)) / 6))      != NULL) {
    /* set up tmp0 to point into tmp1 */
    for(i = 0; i < size; i++) tmp0[i] = tmp1 + i * size;

    /* set up tmp1 to point into tmp2 */
    for(i = 0; i < size; i++) {
      /* these pointers are valid */ 
      for(j = 0; j <= i; j++) 
	tmp0[i][j] = tmp2 + (i * (i + 1) * (i + 2)) / 6 + (j * (j + 1)) / 2;
      /* and these are invalid: better a core dump than corrupt data */
      for(j = i + 1; j < size; j++) tmp0[i][j] = NULL;
    }
  }
  else tmp0 = (Tools_type***) NULL;

  return(tmp0);
}

Tools_type** Make_triangular_array(size)
int size;
{
  Tools_type **tmp0, *tmp1;
  int i;

  /* grab space for pointers, then the real storage, then set up pointers */
  if((tmp0 = (Tools_type**) malloc((unsigned) (size * sizeof(*tmp0))))!=NULL &&
     (tmp1 = Make_array((size * (size + 1)) / 2)) != NULL)
      for(i = 0; i < size; i++) tmp0[i] = tmp1 + (i * (i + 1)) / 2;
  else tmp0 = (Tools_type**) NULL;

  return(tmp0);
}

Tools_type  **Make_weight_matrix(p)
Machine_type *p;
{
  Tools_type **array, *tmp;
  int i, size = (p->sta_op * (p->sta_op - 1) -
                 p->hidden * (p->hidden - 1)) / 2 +
                (p->length - p->sta_op) * p->sta_op;
  
  /* allocate space for the table of indices */
  if((array = (Tools_type**) 
               malloc((unsigned) (p->length * sizeof(Tools_type*)))) != NULL &&
     (tmp   = Make_array(size)) != NULL) {
    Tools_type *curr = tmp;    

    /* store NULL for invalid indices except the first one */
    array[0] = tmp;
    for(i = 1; i < p->hidden; i++) array[i] = (Tools_type*) NULL;

    /* point to space for hidden unit links */
    for(i = p->hidden; i < p->sta_op; i++) {
      array[i] = curr; 
      curr    += i;
    }

    /* grab space for state and external output unit links */
    for(i = p->sta_op; i < p->length; i++) {
      array[i] = curr; 
      curr    += p->sta_op;
    }
  }
  else array = (Tools_type**) NULL;

  return(array);
}




