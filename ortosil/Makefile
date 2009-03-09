CC=gcc
CFLAGS=-Wall -I.
DEPS = acentosil.h
OBJ = ortosil_013.o

%.o: %.c $(DEPS)
	$(CC) -c -o $@ $< $(CFLAGS)

ortosil: $(OBJ)
	gcc -o $@ $^ $(CFLAGS)

.PHONY: clean

clean:
	rm -f *.o core *~

uninstall:
	rm -f *.o core *~ ortosil
