dnl $Id$
dnl config.m4 for extension qi

dnl Comments in this file start with the string 'dnl'.
dnl Remove where necessary. This file will not work
dnl without editing.

dnl If your extension references something external, use with:

PHP_ARG_WITH(qi, for qi support,
dnl Make sure that the comment is aligned:
[  --with-qi             Include qi support])

dnl Otherwise use enable:

PHP_ARG_ENABLE(qi, whether to enable qi support,
dnl Make sure that the comment is aligned:
[  --enable-qi           Enable qi support])

if test "$PHP_QI" != "no"; then
  dnl Write more examples of tests here...

  dnl # --with-qi -> check with-path
  dnl SEARCH_PATH="/usr/local /usr"     # you might want to change this
  dnl SEARCH_FOR="/include/qi.h"  # you most likely want to change this
  dnl if test -r $PHP_QI/$SEARCH_FOR; then # path given as parameter
  dnl   QI_DIR=$PHP_QI
  dnl else # search default path list
  dnl   AC_MSG_CHECKING([for qi files in default path])
  dnl   for i in $SEARCH_PATH ; do
  dnl     if test -r $i/$SEARCH_FOR; then
  dnl       QI_DIR=$i
  dnl       AC_MSG_RESULT(found in $i)
  dnl     fi
  dnl   done
  dnl fi
  dnl
  dnl if test -z "$QI_DIR"; then
  dnl   AC_MSG_RESULT([not found])
  dnl   AC_MSG_ERROR([Please reinstall the qi distribution])
  dnl fi

  dnl # --with-qi -> add include path
  dnl PHP_ADD_INCLUDE($QI_DIR/include)

  dnl # --with-qi -> check for lib and symbol presence
  dnl LIBNAME=qi # you may want to change this
  dnl LIBSYMBOL=qi # you most likely want to change this 

  dnl PHP_CHECK_LIBRARY($LIBNAME,$LIBSYMBOL,
  dnl [
  dnl   PHP_ADD_LIBRARY_WITH_PATH($LIBNAME, $QI_DIR/$PHP_LIBDIR, QI_SHARED_LIBADD)
  dnl   AC_DEFINE(HAVE_QILIB,1,[ ])
  dnl ],[
  dnl   AC_MSG_ERROR([wrong qi lib version or lib not found])
  dnl ],[
  dnl   -L$QI_DIR/$PHP_LIBDIR -lm
  dnl ])
  dnl
  dnl PHP_SUBST(QI_SHARED_LIBADD)

  PHP_NEW_EXTENSION(qi, 
    qi.c		\
    qishao.c,            
    $ext_shared)
fi
