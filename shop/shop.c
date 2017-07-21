/*
  +----------------------------------------------------------------------+
  | PHP Version 5                                                        |
  +----------------------------------------------------------------------+
  | Copyright (c) 1997-2016 The PHP Group                                |
  +----------------------------------------------------------------------+
  | This source file is subject to version 3.01 of the PHP license,      |
  | that is bundled with this package in the file LICENSE, and is        |
  | available through the world-wide-web at the following url:           |
  | http://www.php.net/license/3_01.txt                                  |
  | If you did not receive a copy of the PHP license and are unable to   |
  | obtain it through the world-wide-web, please send a note to          |
  | license@php.net so we can mail you a copy immediately.               |
  +----------------------------------------------------------------------+
  | Author:                                                              |
  +----------------------------------------------------------------------+
*/

/* $Id$ */

#ifdef HAVE_CONFIG_H
#include "config.h"
#endif

#include "php.h"
#include "php_ini.h"
#include "ext/standard/info.h"
#include "php_shop.h"

/* If you declare any globals in php_shop.h uncomment this:
ZEND_DECLARE_MODULE_GLOBALS(shop)
*/

/* True global resources - no need for thread safety here */
static int le_shop;

/* {{{ PHP_INI
 */
/* Remove comments and fill if you need to have entries in php.ini
PHP_INI_BEGIN()
    STD_PHP_INI_ENTRY("shop.global_value",      "42", PHP_INI_ALL, OnUpdateLong, global_value, zend_shop_globals, shop_globals)
    STD_PHP_INI_ENTRY("shop.global_string", "foobar", PHP_INI_ALL, OnUpdateString, global_string, zend_shop_globals, shop_globals)
PHP_INI_END()
*/
/* }}} */

/* Remove the following function when you have successfully modified config.m4
   so that your module can be compiled into PHP, it exists only for testing
   purposes. */

/* Every user-visible function in PHP should document itself in the source */
/* {{{ proto string confirm_shop_compiled(string arg)
   Return a string to confirm that the module is compiled in */
PHP_FUNCTION(shop)
{
	char *arg = NULL;
	int arg_len, len;
	char *strg;

	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "s", &arg, &arg_len) == FAILURE) {
		return;
	}

	len = spprintf(&strg, 0, "Congratulations! You have successfully modified ext/%.78s/config.m4. Module %.78s is now compiled into PHP.", "shop", arg);
	RETURN_STRINGL(strg, len, 0);
}


PHP_FUNCTION(shop_test)
{
        long a, b, c;

        if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "ll", &a, &b) == FAILURE) {
                return;
        }

	c = a*b;

        RETURN_LONG(c);
}

PHP_FUNCTION(shop_hello) {
    char *name, *greeting, *strg;
    long name_len, greeting_len, len;

    if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "ss", &name, &name_len, &greeting, &greeting_len) == FAILURE) {
        RETURN_NULL();
    }


    len = spprintf(&strg, 0, "%s %s", name, greeting);
    RETURN_STRINGL(strg, len, 0);
}

PHP_FUNCTION(shop_sort)
{
      	Bucket **elems, *temp;
	HashTable *hash;
	zval *values, *keys, *pzval;
	HashPosition pos_values, pos_keys;
	zval **entry_keys, **entry_values;
	int num_keys, num_values, num, i=0, j, v;
        Bucket *p;

	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "aa", &keys, &values) == FAILURE) {
	     return;
	}


	num_keys = zend_hash_num_elements(Z_ARRVAL_P(keys));
	num_values = zend_hash_num_elements(Z_ARRVAL_P(values));

	num = num_keys + num_values;
        
        array_init_size(return_value, num);

	for (p = Z_ARRVAL_P(keys)->pListHead, i = 0; p; p = p->pListNext, i++) {
            Z_ADDREF_PP((zval**)p->pData);
            zend_hash_quick_update(Z_ARRVAL_P(return_value), p->arKey, p->nKeyLength, p->h, p->pData, sizeof(zval*), NULL);
	}

        for (p = Z_ARRVAL_P(values)->pListHead, j = 0; p; p = p->pListNext, j++) {
            Z_ADDREF_PP((zval**)p->pData);
            zend_hash_index_update(Z_ARRVAL_P(return_value), (p->h)+i+1, p->pData, sizeof(zval*), NULL);
        }


/*

 php_array_merge(Z_ARRVAL_P(return_value), Z_ARRVAL_P(keys), 0  TSRMLS_CC);  
          php_array_merge(Z_ARRVAL_P(return_value), Z_ARRVAL_P(values), 0  TSRMLS_CC);
  */             

	/*RETURN_LONG(num);*/
}


/* }}} */
/* The previous line is meant for vim and emacs, so it can correctly fold and 
   unfold functions in source code. See the corresponding marks just before 
   function definition, where the functions purpose is also documented. Please 
   follow this convention for the convenience of others editing your code.
*/


/* {{{ php_shop_init_globals
 */
/* Uncomment this function if you have INI entries
static void php_shop_init_globals(zend_shop_globals *shop_globals)
{
	shop_globals->global_value = 0;
	shop_globals->global_string = NULL;
}
*/
/* }}} */

/* {{{ PHP_MINIT_FUNCTION
 */
PHP_MINIT_FUNCTION(shop)
{
	/* If you have INI entries, uncomment these lines 
	REGISTER_INI_ENTRIES();
	*/
	return SUCCESS;
}
/* }}} */

/* {{{ PHP_MSHUTDOWN_FUNCTION
 */
PHP_MSHUTDOWN_FUNCTION(shop)
{
	/* uncomment this line if you have INI entries
	UNREGISTER_INI_ENTRIES();
	*/
	return SUCCESS;
}
/* }}} */

/* Remove if there's nothing to do at request start */
/* {{{ PHP_RINIT_FUNCTION
 */
PHP_RINIT_FUNCTION(shop)
{
	return SUCCESS;
}
/* }}} */

/* Remove if there's nothing to do at request end */
/* {{{ PHP_RSHUTDOWN_FUNCTION
 */
PHP_RSHUTDOWN_FUNCTION(shop)
{
	return SUCCESS;
}
/* }}} */

/* {{{ PHP_MINFO_FUNCTION
 */
PHP_MINFO_FUNCTION(shop)
{
	php_info_print_table_start();
	php_info_print_table_header(2, "shop support", "enabled");
	php_info_print_table_end();

	/* Remove comments if you have entries in php.ini
	DISPLAY_INI_ENTRIES();
	*/
}
/* }}} */

/* {{{ shop_functions[]
 *
 * Every user visible function must have an entry in shop_functions[].
 */
const zend_function_entry shop_functions[] = {
	PHP_FE(shop,	NULL)		/* For testing, remove later. */
	PHP_FE(shop_test, NULL)
        PHP_FE(shop_hello, NULL)
        PHP_FE(shop_sort, NULL)
	PHP_FE_END	/* Must be the last line in shop_functions[] */
};
/* }}} */

/* {{{ shop_module_entry
 */
zend_module_entry shop_module_entry = {
	STANDARD_MODULE_HEADER,
	"shop",
	shop_functions,
	PHP_MINIT(shop),
	PHP_MSHUTDOWN(shop),
	PHP_RINIT(shop),		/* Replace with NULL if there's nothing to do at request start */
	PHP_RSHUTDOWN(shop),	/* Replace with NULL if there's nothing to do at request end */
	PHP_MINFO(shop),
	PHP_SHOP_VERSION,
	STANDARD_MODULE_PROPERTIES
};
/* }}} */

#ifdef COMPILE_DL_SHOP
ZEND_GET_MODULE(shop)
#endif

/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * End:
 * vim600: noet sw=4 ts=4 fdm=marker
 * vim<600: noet sw=4 ts=4
 */
