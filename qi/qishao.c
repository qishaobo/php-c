#ifdef HAVE_CONFIG_H
#include "config.h"
#endif
#include "php.h"
#include "php_ini.h"
#include "ext/standard/info.h"
#include "php_qi.h"

zend_class_entry *qishao_ce;

PHP_METHOD(Qi, __construct)
{
    zend_printf("The is Cz class __construct!!</br>");
}

PHP_METHOD(Qi, createApp)
{
    zend_printf("The is Cz lcass createApp !</br>");
}

const zend_function_entry qi_methods[]={
    PHP_ME(Qi, __construct, NULL, ZEND_ACC_PUBLIC|ZEND_ACC_CTOR)
    PHP_ME(Qi, createApp, NULL, ZEND_ACC_PUBLIC)
    PHP_FE_END
};

QI_STARTUP_FUNCTION(qishao)
{
    zend_class_entry ce;
    INIT_CLASS_ENTRY(ce, "Qi", qi_methods);
    qishao_ce = zend_register_internal_class_ex(&ce, NULL, NULL TSRMLS_CC);
    return SUCCESS;
}
