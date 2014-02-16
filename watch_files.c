#include <sys/types.h>
#include <sys/stat.h>
#include <fcntl.h>
#include <string.h>
#include <stdio.h>
#include <errno.h>
#include <sys/inotify.h>

void get_event (int fd, const char * target);
void handle_error (int error);

/* ----------------------------------------------------------------- */

int main (int argc, char *argv[])
{
        char target[FILENAME_MAX];
        int result;
        int fd;
        int wd;   /* watch descriptor */

        if (argc < 2) {
                fprintf (stderr, "Watching \n");
                strcpy (target, ".");
        }
        else {
                fprintf (stderr, "Watching %s\n", argv[1]);
                strcpy (target, argv[1]);
        }

        fd = inotify_init();
        if (fd < 0) {
                handle_error (errno);
                return 1;
        }

        wd = inotify_add_watch (fd, target, IN_ALL_EVENTS);
        if (wd < 0) {
                handle_error (errno);
                return 1;
        }

        while (1) {
                get_event(fd, target);
        }

        return 0;
}

/* ----------------------------------------------------------------- */
/* Allow for 1024 simultanious events */
#define BUFF_SIZE ((sizeof(struct inotify_event)+FILENAME_MAX)*1024)

void get_event (int fd, const char * target)
{
        ssize_t len, i = 0;
        char action[81+FILENAME_MAX] = {0};
        char buff[BUFF_SIZE] = {0};
        char old_filename[81+FILENAME_MAX] = {0};
        char full_filename[81+FILENAME_MAX] = {0};

        len = read (fd, buff, BUFF_SIZE);

        while (i < len) {
                struct inotify_event *pevent = (struct inotify_event *)&buff[i];
                char action[81+FILENAME_MAX] = {0};
                
		if (pevent->mask & IN_CLOSE_WRITE) {
                        if (pevent->len)
                                strcpy (action, pevent->name);
                        else
                                strcpy (action, target);

                        snprintf(old_filename, sizeof(old_filename), "%s/%s",
                                target, action);

                        if (strcmp(action, "config.php") == 0) {
                        	snprintf(full_filename, sizeof(full_filename), "%s%s",
                                	"/var/www/rasptimer/",action);
                                system("ipe-rw");
                                copy_file(old_filename, full_filename);
                      		remove(old_filename);
				system("ipe-ro");
                        }
                }

                i += sizeof(struct inotify_event) + pevent->len;
        }
}  /* get_event */

/* ----------------------------------------------------------------- */

void handle_error (int error)
{
        fprintf (stderr, "Error: %s\n", strerror(error));

}  /* handle_error */

/* ----------------------------------------------------------------- */

int copy_file(char *old_filename, char  *new_filename)
{
        FILE  *ptr_old, *ptr_new;
        int err = 0, err1 = 0, a;

        ptr_old = fopen(old_filename, "r");

        if(!ptr_old) {
                fprintf(stderr, "Could not open %s\n", old_filename);
                return  -1;
        }

        ptr_new = fopen(new_filename, "w");

        if(!ptr_new)
        {
                fprintf(stderr, "Could not open %s\n", new_filename);
                fclose(ptr_old);
                return  -1;
        }

        while(a = fgetc(ptr_old))
        {
                if (a == EOF) {
                        break;
                }
                if (fputc(a, ptr_new) == EOF) {
                        break;
                }
        }

        fclose(ptr_new);
        fclose(ptr_old);
        return  0;
}
