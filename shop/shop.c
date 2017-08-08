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
#include "ext/standard/php_string.h"
#include "ext/standard/php_smart_str_public.h"
#include "ext/standard/php_smart_str.h"
#include "php_shop.h"
#include <string.h>  

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

PHP_FUNCTION(shop_date) {
    char  *date, *month, *day, *year;
    long date_len;
    int i;

    if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "s", &date, &date_len) == FAILURE) {
        RETURN_NULL();
    }

    month = "02";
    year = 2018;
    day = 12;
       
    zval *data;
    MAKE_STD_ZVAL(data);
    /* ZVAL_LONG(data, year);*/
  	array_init(data);
  	for (i = 0; i < 5; i++) {
  	 add_index_long(data, i, i*3);
  	}
 
	array_init(return_value);
    add_assoc_zval(return_value, "data", data);
	add_assoc_string(return_value, "month", month, 1);
	add_assoc_long(return_value, "day", day);
	add_assoc_long(return_value, "year", year);
    add_assoc_string(return_value, "date", date, 1);
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

PHP_FUNCTION(to_tree)
{
        zval *arr1, *arr2;
        HashTable *harr1, *harr2;
        Bucket *p, *p2;
        int num_arr1, num_arr2, num, i, j, n;
        zval *second, *first;
        char *name;

        if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "a", &arr1) == FAILURE) {
             return;
        }

        harr1 = Z_ARRVAL_P(arr1);

        num_arr1 = zend_hash_num_elements(harr1);
        num = num_arr1;

        array_init_size(return_value, num);

        for (p = harr1->pListHead, i = 0; p; p = p->pListNext, i++) {
            Z_ADDREF_PP((zval**)p->pData);
         
            printf("num-1 %ld \n", i);
            second = *((zval **) p->pData);
            harr2 = Z_ARRVAL_P(second);
            for (p2 = harr2->pListHead; p2; p2 = p2->pListNext) {
                printf("num-2 %s \n", p2->arKey);
                first = *((zval **) p2->pData);
                  convert_to_string(first);   
                    printf("num-2-2 %s \n", Z_LVAL_P(first));
            }

            zend_hash_quick_update(Z_ARRVAL_P(return_value), p->arKey, p->nKeyLength, p->h, p->pData, sizeof(zval*), NULL);
        }
}

PHP_FUNCTION(tree_to_array)
{
    zval *arr;
    HashTable *harr;
    char *pid, *id, *strg;
    int num, pid_len, id_len, len;

    if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "a|ss", &arr, &id, &id_len, &pid, &pid_len) == FAILURE) {
        return;
    }

	if (ZEND_NUM_ARGS() < 2) {
		id = "id";
	}

	if (ZEND_NUM_ARGS() < 3) {
        pid = "pid";
    }

    harr = Z_ARRVAL_P(arr);
    num = zend_hash_num_elements(harr);
    array_init_size(return_value, num);
    create_arr(harr, return_value, id, pid, 0, 0);
}


void create_arr(HashTable *harr, zval *return_value, char *id, char *pid, int pid_val, int level)
{
    zval *arr, *arr_son;
    HashTable *harr_son;
    Bucket *p, *p2;
    int num_arr, num_arr_son,i;
    zval *arr_data, **arr_id, **arr_pid;
    int data, id_val;

    level++;
    for (p = harr->pListHead, i = 0; p; p = p->pListNext, i++) {
        Z_ADDREF_PP((zval**)p->pData);
        harr_son = Z_ARRVAL_P(*((zval **) p->pData));

        if (SUCCESS == zend_hash_find(harr_son, pid, sizeof(pid), (void **) &arr_pid)){
            data = Z_LVAL_PP(arr_pid);

            if (data == pid_val) {
                zend_hash_quick_update(Z_ARRVAL_P(return_value), p->arKey, p->nKeyLength, p->h, p->pData, sizeof(zval*), NULL);
                add_assoc_long(*((zval **) p->pData), "level", level);

	            if (SUCCESS == zend_hash_find(harr_son, id, sizeof(id), (void **) &arr_id)) {
	                id_val = Z_LVAL_PP(arr_id);
	            }

                create_arr(harr, return_value, id, pid, id_val, level);
            }
        }
    }
}

PHP_FUNCTION(to_tree_s)
{
        zval *arr1, *arr2, **entry, *data;;
        HashTable *harr1, *harr2;
        Bucket *p, *p2;
        int num_arr1, num_arr2, num, i, j, n;
        zval *second, first;
        char *name;
        HashPosition pos;

        if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "a", &arr1) == FAILURE) {
             return;
        }

        harr1 = Z_ARRVAL_P(arr1);

        num_arr1 = zend_hash_num_elements(harr1);
        num = num_arr1;

        array_init_size(return_value, num);

        for (p = harr1->pListHead, i = 0; p; p = p->pListNext, i++) {
            Z_ADDREF_PP((zval**)p->pData);

            printf("num-1 %ld \n", i);
            second = *((zval **) p->pData);
            harr2 = Z_ARRVAL_P(second);

            zend_hash_internal_pointer_reset_ex(harr2, &pos);
            while (zend_hash_get_current_data_ex(harr2, (void **)&entry, &pos) == SUCCESS) {
                MAKE_STD_ZVAL(data);
                zend_hash_get_current_key_zval_ex(harr2, data, &pos);
                
                convert_to_string(data);               
                convert_to_string_ex(entry);

 printf("num-2-2 %s-%s \n", Z_STRVAL_P(data), Z_LVAL_PP(entry));
                /*
                if (Z_TYPE_P(data) == IS_LONG) {
                    printf("num-2-1 %s \n", Z_LVAL_P(data));
                } else if (Z_TYPE_P(data) == IS_STRING) {
                    printf("num-2-2 %s \n", Z_STRVAL_P(data));
                } else {
                    printf("num-2-3 \n");
                }

               convert_to_string_ex(entry);

               if (Z_TYPE_PP(entry) == IS_LONG) {
                    printf("num-3-1 %ld \n", Z_LVAL_PP(entry));
                } else if (Z_TYPE_PP(entry) == IS_STRING) {
                    printf("num-3-2 %s \n", Z_STRVAL_PP(entry));
                } else {
                    printf("num-3-3\n");
                }
 */
                 zend_hash_move_forward_ex(harr2, &pos);
            }


            zend_hash_quick_update(Z_ARRVAL_P(return_value), p->arKey, p->nKeyLength, p->h, p->pData, sizeof(zval*), NULL);
        }
}



PHP_FUNCTION(hello_array_strings)
{
	zval *arr1, *arr2;
	HashTable *harr1, *harr2;
	Bucket *p;
	int num_arr1, num_arr2, num, i, j;

	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "aa", &arr1, &arr2) == FAILURE) {
	     return;
	}

	harr1 = Z_ARRVAL_P(arr1);
	harr2 = Z_ARRVAL_P(arr2);

	num_arr1 = zend_hash_num_elements(harr1);
	num_arr2 = zend_hash_num_elements(harr2);
	num = num_arr1 + num_arr2;
        
    array_init_size(return_value, num);

	for (p = harr1->pListHead, i = 0; p; p = p->pListNext, i++) {
        Z_ADDREF_PP((zval**)p->pData);
        zend_hash_quick_update(Z_ARRVAL_P(return_value), p->arKey, p->nKeyLength, p->h, p->pData, sizeof(zval*), NULL);
	}

    for (p = harr2->pListHead, j = 0; p; p = p->pListNext, j++) {
        Z_ADDREF_PP((zval**)p->pData);
        zend_hash_index_update(Z_ARRVAL_P(return_value), (p->h)+i+1, p->pData, sizeof(zval*), NULL);
    }
}



/* {{{ shop_functions[]
 *
 * Every user visible function must have an entry in shop_functions[].
 */
const zend_function_entry shop_functions[] = {
	PHP_FE(shop,	NULL)		/* For testing, remove later. */
	PHP_FE(shop_test, NULL)
        PHP_FE(shop_hello, NULL)
        PHP_FE(shop_sort, NULL)
        PHP_FE(shop_date, NULL)
        PHP_FE(to_tree, NULL)
        PHP_FE(tree_to_array, NULL)
	PHP_FE(hello_array_strings, NULL)
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
