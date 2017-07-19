dnl $Id$
dnl config.m4 for extension shop

dnl Comments in this file start with the string 'dnl'.
dnl Remove where necessary. This file will not work
dnl without editing.

dnl If your extension references something external, use with:

 PHP_ARG_WITH(shop, for shop support,
dnl Make sure that the comment is aligned:
[  --with-shop             Include shop support])

dnl Otherwise use enable:

PHP_ARG_ENABLE(shop, whether to enable shop support,
dnl Make sure that the comment is aligned:[  --enable-shop           Enable shop support])
[  --enable-shop           Enable shop support])
if test "$PHP_SHOP" != "no"; then
  dnl Write more examples of tests here...

  dnl # --with-shop -> check with-path
  dnl SEARCH_PATH="/usr/local /usr"     # you might want to change this
  dnl SEARCH_FOR="/include/shop.h"  # you most likely want to change this
  dnl if test -r $PHP_SHOP/$SEARCH_FOR; then # path given as parameter
  dnl   SHOP_DIR=$PHP_SHOP
  dnl else # search default path list
  dnl   AC_MSG_CHECKING([for shop files in default path])
  dnl   for i in $SEARCH_PATH ; do
  dnl     if test -r $i/$SEARCH_FOR; then
  dnl       SHOP_DIR=$i
  dnl       AC_MSG_RESULT(found in $i)
  dnl     fi
  dnl   done
  dnl fi
  dnl
  dnl if test -z "$SHOP_DIR"; then
  dnl   AC_MSG_RESULT([not found])
  dnl   AC_MSG_ERROR([Please reinstall the shop distribution])
  dnl fi

  dnl # --with-shop -> add include path
  dnl PHP_ADD_INCLUDE($SHOP_DIR/include)

  dnl # --with-shop -> check for lib and symbol presence
  dnl LIBNAME=shop # you may want to change this
  dnl LIBSYMBOL=shop # you most likely want to change this 

  dnl PHP_CHECK_LIBRARY($LIBNAME,$LIBSYMBOL,
  dnl [
  dnl   PHP_ADD_LIBRARY_WITH_PATH($LIBNAME, $SHOP_DIR/$PHP_LIBDIR, SHOP_SHARED_LIBADD)
  dnl   AC_DEFINE(HAVE_SHOPLIB,1,[ ])
  dnl ],[
  dnl   AC_MSG_ERROR([wrong shop lib version or lib not found])
  dnl ],[
  dnl   -L$SHOP_DIR/$PHP_LIBDIR -lm
  dnl ])
  dnl
  dnl PHP_SUBST(SHOP_SHARED_LIBADD)

  PHP_NEW_EXTENSION(shop, shop.c, $ext_shared)
fi
