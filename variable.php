PHP_FUNCTION(type_test)
{
    zval *uservar;

    if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "z", uservar) == FAILURE) {

        RETURN_NULL();

    }

    switch (Z_TYPE_P(uservar)) {

        case IS_NULL:
            php_printf("NULL ");
            break;

        case IS_BOOL:
            php_printf("Boolean: %s ", Z_LVAL_P(uservar) ? "TRUE" : "FALSE");
            break;

        case IS_LONG:
            php_printf("Long: %ld ", Z_LVAL_P(uservar));
            break;

        case IS_DOUBLE:
            php_printf("Double: %f ", Z_DVAL_P(uservar));
            break;

        case IS_STRING:
            php_printf("String: ");
            PHPWRITE(Z_STRVAL_P(uservar), Z_STRLEN_P(uservar));
            php_printf(" ");
            break;

        case IS_RESOURCE:
            php_printf("Resource ");
            break;

        case IS_ARRAY:
            php_printf("Array ");
            break;

        case IS_OBJECT:
            php_printf("Object ");
            break;

        default:
            php_printf("Unknown ");

    }

    RETURN_TRUE;
}

//开始定义php语言中的函数gettype
PHP_FUNCTION(gettype)
{
  //arg间接指向调用gettype函数时所传递的参数。是一个zval**结构
  //所以我们要对他使用__PP后缀的宏。
  zval **arg;
  //这个if的操作主要是让arg指向参数～
  if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "Z", &arg) == FAILURE) {
    return;
  }
  //调用Z_TYPE_PP宏来获取arg指向zval的类型。
  //然后是一个switch结构，RETVAL_STRING宏代表这gettype函数返回的字符串类型的值
  switch (Z_TYPE_PP(arg)) {
    case IS_NULL:
      RETVAL_STRING("NULL", 1);
      break;
    case IS_BOOL:
      RETVAL_STRING("boolean", 1);
      break;
    case IS_LONG:
      RETVAL_STRING("integer", 1);
      break;
    case IS_DOUBLE:
      RETVAL_STRING("double", 1);
      break;
    case IS_STRING:
      RETVAL_STRING("string", 1);
      break;
    case IS_ARRAY:
      RETVAL_STRING("array", 1);
      break;
    case IS_OBJECT:
      RETVAL_STRING("object", 1);
      break;
    case IS_RESOURCE:
      {
        char *type_name;
        type_name = zend_rsrc_list_get_rsrc_type(Z_LVAL_PP(arg) TSRMLS_CC);
        if (type_name) {
          RETVAL_STRING("resource", 1);
          break;
        }
      }
    default:
      RETVAL_STRING("unknown type", 1);
  }
}


整数类型	
Z_LVAL(zval)
Z_LVAL_P(&zval)
Z_LVAL_PP(&&zval)

浮点类型	
Z_DVAL(zval)
Z_DVAL_P(&zval)
Z_DVAL_PP(&&zval)

布尔类型	
Z_BVAL(zval)
Z_BVAL_P(&zval)
Z_BVAL_PP(&&zval)

字符串类型	
Z_STRVAL(zval)
Z_STRVAL_P(&zval)
Z_STRVAL_PPP(&&zval)

取得长度：
Z_STRLEN(zval)
Z_STRLEN_P(&zval)
Z_STRLEN_PP(&&zval)

数组类型	
Z_ARRVAL(zval)
Z_ARRVAL_P(&zval)
Z_ARRVAL_PP(&&zval)

资源类型	
Z_RESVAL(zval)
Z_RESVAL_P(&zval)
Z_RESVAL_PP(&&zval)

使用上表可以设置一个变量的类型和值。例如，创建一个值为10的整数变量lvar:

zval lvar;
Z_TYPE(lvar) = IS_LONG;
z_LVAL(lvar) = 10;

如果用PHP脚本的话，相当于一下代码：
$lvar = 10;



static int collator_compare_func( const void* a, const void* b TSRMLS_DC )
{
  Bucket *f;
  Bucket *s;
  zval result;
  zval *first;
  zval *second;

  f = *((Bucket **) a);
  s = *((Bucket **) b);

  first = *((zval **) f->pData);
  second = *((zval **) s->pData);

  if( INTL_G(compare_func)( &result, first, second TSRMLS_CC) == FAILURE )
    return 0;

  if( Z_TYPE(result) == IS_DOUBLE )
  {
    if( Z_DVAL(result) < 0 )
      return -1;
    else if( Z_DVAL(result) > 0 )
      return 1;
    else
      return 0;
  }

  convert_to_long(&result);

  if( Z_LVAL(result) < 0 )
    return -1;
  else if( Z_LVAL(result) > 0 )
    return 1;

  return 0;
}