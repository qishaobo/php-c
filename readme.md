1 ./ext_skel --extname="shop"
2 config.m4 

PHP_ARG_WITH(shop, for shop support,
dnl Make sure that the comment is aligned:
[  --with-shop             Include shop support])

dnl Otherwise use enable:

PHP_ARG_ENABLE(shop, whether to enable shop support,
dnl Make sure that the comment is aligned:[  --enable-shop           Enable shop support])
[  --enable-shop           Enable shop support])
if test "$PHP_SHOP" != "no"; then

3 write functions or object

4 
phpize 
./configure
make
make install



create functions
just see shop folder



create object(folder <qi>)
1.config.m4
  PHP_NEW_EXTENSION(qi, 
    qi.c		\
    qishao.c,            
    $ext_shared)

2.qi.c

/* {{{ PHP_MINIT_FUNCTION
 */
QI_STARTUP_FUNCTION(qi)
{
	/* If you have INI entries, uncomment these lines 
	REGISTER_INI_ENTRIES();
	*/
        ZEND_MODULE_STARTUP_N(qishao) (INIT_FUNC_ARGS_PASSTHRU);
	return SUCCESS;
}
/* }}} */