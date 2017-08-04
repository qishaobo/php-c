1、线程安全宏定义
在TSRM/TSRM.h文件中有如下定义
#define TSRMLS_FETCH()       void ***tsrm_ls = (void ***) ts_resource_ex(0, NULL)
#define TSRMLS_FETCH_FROM_CTX(ctx) void ***tsrm_ls = (void ***) ctx
#define TSRMLS_SET_CTX(ctx)   ctx = (void ***) tsrm_ls
#define TSRMG(id, type, element)   (((type) (*((void ***) tsrm_ls))[TSRM_UNSHUFFLE_RSRC_ID(id)])->element)
#define TSRMLS_D   void ***tsrm_ls
#define TSRMLS_DC  , TSRMLS_D
#define TSRMLS_C   tsrm_ls
#define TSRMLS_CC  , TSRMLS_C

在ext/xsl/php_xsl.h有这么一段话
/* In every utility function you add that needs to use variables.                                                                    
   in php_xsl_globals, call TSRM_FETCH(); after declaring other.
   variables used by that function, or better yet, pass in TSRMLS_CC
   after the last function argument and declare your utility function
   with TSRMLS_DC after the last declared argument.  Always refer to
   the globals in your function as XSL_G(variable).  You are.
   encouraged to rename these macros something shorter, see
   examples in any other php module directory.
*/
1.在方法定义时加上TSRMLS_D（如果方法没有参数用这个）或者TSRMLS_DC（有1个以上的参数）
2.在方法调用时用TSRMLS_C（如果方法没有参数用这个）或者TSRMLS_CC（有1个以上的参数）

应该可以这样理解
第一个后缀字母D表示定义，即D=Define，第一个后缀字母C表示调用，即C=Call，而第二个后缀字母C是不是表示逗号呢？ C=Comma (逗号)
TSRMLS_D就是定义了，所以是  void ***tsrm_ls
TSRMLS_DC是带逗号的定义，所以是 , void ***tsrm_ls
TSRMLS_C是调用，即tsrm_ls
TSRMLS_CC是调用并带逗号，即 ,tsrm_ls
所以一个是形参、一个是实参
可以这样使用
int php_myext_action(int action_id, char *message TSRMLS_DC);
php_myext_action(42, "The meaning of life" TSRMLS_CC);
一般推荐使用tsrm_ls指针定义的方式来保证线程安全
TSRMLS_FETCH调用需要一定的处理时间。这在单次迭代中并不明显，但是随着你的线程数增多，随着你调用TSRMLS_FETCH()的点的增多，你的扩展就会显现出这个瓶颈。因此，请谨慎的使用它。 注意：为了和c++编译器兼容，请确保将TSRMLS_FETCH()和所有变量定义放
在给定块作用域的顶部（任何其他语句之前）。因为TSRMLS_FETCH()宏自身有多种不同的解析方式，因此最好将它作为变量定义的最后一行


2、PHP的生命周期
PHP的最多的两种运行模式是WEB模式、CLI模式，无论哪种模式，PHP工作原理都是一样的，作为一种SAPI运行。
1、当我们在终端敲入php这个命令的时候，它使用的是CLI。
它就像一个web服务器一样来支持php完成这个请求，请求完成后再重新把控制权交给终端。
2、当使用Apache作为宿主时，当一个请求到来时，PHP会来支持完成这个请求
PHP_MINIT_FUNCTION  初始化module时运行 
PHP_MSHUTDOWN_FUNCTION  当module被卸载时运行 
PHP_RINIT_FUNCTION  当一个REQUEST请求初始化时运行 
PHP_RSHUTDOWN_FUNCTION  当一个REQUEST请求结束时运行 
PHP_MINFO_FUNCTION  这个是设置phpinfo中这个模块的信息 
PHP_GINIT_FUNCTION  初始化全局变量时 
PHP_GSHUTDOWN_FUNCTION  释放全局变量时


这里有一段代码，可以测试一下
PHP_MINIT_FUNCTION(test)
{
  minit_time = time(NULL);
  return SUCCESS;
}

PHP_MSHUTDOWN_FUNCTION(test)
{
  FILE *fp=fopen("mshutdown.txt","a+");
  fprintf(fp,"%ld\n",time(NULL));
  fclose(fp);
  return SUCCESS;
}

PHP_RINIT_FUNCTION(test)
{
  rinit_time = time(NULL);
  return SUCCESS;
}

PHP_RSHUTDOWN_FUNCTION(test)
{
  FILE *fp=fopen("rshutdown.txt","a+");
  fprintf(fp,"%ld\n",time(NULL));
  fclose(fp);
  return SUCCESS;
}

PHP_MINFO_FUNCTION(test)
{
  php_info_print_table_start();
  php_info_print_table_header(, "module info", "enabled");
  php_info_print_table_end();
  /* Remove comments if you have entries in php.ini
  DISPLAY_INI_ENTRIES();
  */
}

PHP_FUNCTION(test)
{
  php_printf("%d",time_of_minit);
  php_printf("%d",time_of_rinit);
  return;
}


3、段错误调试
Linux下的C程序常常会因为内存访问错误等原因造成segment fault（段错误）此时如果系统core dump功能是打开的，那么将会有内存映像转储到硬盘上来，之后可以用gdb对core文件进行分析，还原系统发生段错误时刻的堆栈情况。这对于我们发现程序bug很有帮助。
使用ulimit -a可以查看系统core文件的大小限制；使用ulimit -c [kbytes]可以设置系统允许生成的core文件大小。
ulimit -c 0 不产生core文件
ulimit -c 100 设置core文件最大为100k
ulimit -c unlimited 不限制core文件大小
步骤：
1、当发生段错误时，我们查看ulimit -a （core file size (blocks, -c) 0）并没有文件， 
2、设置 ：ulimit -c unlimited 不限制core文件大小
3、运行程序 ，发生段错误时会自动记录在core中 （php -f WorkWithArray.php）
4、ls -al core.* 在那个文件下（-rw------- 1 leconte leconte 139264 01-06 22:3 1 core.2065）
5、使用gdb 运行程序和段错误记录的文件。（gdb ./test core.2065）
6、会提哪行有错。
很多系统默认的core文件大小都是0，我们可以通过在shell的启动脚本/etc/bashrc或者~/.bashrc等地方来加入 ulimit -c 命令来指定core文件大小，从而确保core文件能够生成。
除此之外，还可以在/proc/sys/kernel/core_pattern里设置core文件的文件名模板，详情请看core的官方man手册。


4、常见的变量操作宏
CG    -> Complier Global      编译时信息，包括函数表等(zend_globals_macros.h:32)
EG    -> Executor Global      执行时信息(zend_globals_macros.h:43)
PG    -> PHP Core Global      主要存储php.ini中的信息
SG    -> SAPI Global          SAPI信息

1、SG  针对SAPI信息 在main/SAPI.h文件中
typedef struct _sapi_globals_struct {
  void *server_context;
  sapi_request_info request_info;
  sapi_headers_struct sapi_headers;
  int read_post_bytes;
  unsigned char headers_sent;
  struct stat global_stat;
  char *default_mimetype;
  char *default_charset;
  HashTable *rfc1867_uploaded_files;
  long post_max_size;
  int options;
  zend_bool sapi_started;
  double global_request_time;
  HashTable known_post_content_types;
  zval *callback_func;
  zend_fcall_info_cache fci_cache;
  zend_bool callback_run;
} sapi_globals_struct;


看一下SG的定义
BEGIN_EXTERN_C()
#ifdef ZTS
# define SG(v) TSRMG(sapi_globals_id, sapi_globals_struct *, v)
SAPI_API extern int sapi_globals_id;
#else
# define SG(v) (sapi_globals.v)
extern SAPI_API sapi_globals_struct sapi_globals;
#endif
SAPI_API void sapi_startup(sapi_module_struct *sf);
SAPI_API void sapi_shutdown(void);
SAPI_API void sapi_activate(TSRMLS_D);
SAPI_API void sapi_deactivate(TSRMLS_D);
SAPI_API void sapi_initialize_empty_request(TSRMLS_D);
END_EXTERN_C()
成员都在sapi_globals_struct这里了
那么我么可以这样调用
SG(default_mimetype)
SG(request_info).request_uri
可以感受一下这么一段代码


static int sapi_cgi_send_headers(sapi_headers_struct *sapi_headers TSRMLS_DC)
{
  char buf[SAPI_CGI_MAX_HEADER_LENGTH];
  sapi_header_struct *h;
  zend_llist_position pos;
  long rfc2616_headers = 0;
  if(SG(request_info).no_headers == 1) {
    return SAPI_HEADER_SENT_SUCCESSFULLY;
  }
  if (SG(sapi_headers).http_response_code != 200) {
    int len;
    len = sprintf(buf, "Status: %d\r\n", SG(sapi_headers).http_response_code);
    PHPWRITE_H(buf, len);
  }
  if (SG(sapi_headers).send_default_content_type) {
    char *hd;
    hd = sapi_get_default_content_type(TSRMLS_C);
    PHPWRITE_H("Content-type:", sizeof("Content-type: ")-1);
    PHPWRITE_H(hd, strlen(hd));
    PHPWRITE_H("\r\n", 2);
    efree(hd);
  }
  h = zend_llist_get_first_ex(&sapi_headers->headers, &pos);
  while (h) {
    PHPWRITE_H(h->header, h->header_len);
    PHPWRITE_H("\r\n", 2);
    h = zend_llist_get_next_ex(&sapi_headers->headers, &pos);
  }
  PHPWRITE_H("\r\n", 2);
  return SAPI_HEADER_SENT_SUCCESSFULLY;
}
 2、EG  Executor Globals
EG获取的是struct _zend_execution_globals结构体中的数据


struct _zend_execution_globals {
 ...
 HashTable symbol_table;  /* 全局作用域，如果没有进入函数内部，全局＝活动 */
 HashTable *active_symbol_table; /* 活动作用域，当前作用域 */
 ...
}
通常，使用EG(symbol_table)获取的是全局作用域中的符号表，使用EG(active_symbol_table)获取的是当前作用域下的符号表
例如 来定义$foo = 'bar'
zval *fooval;
 
MAKE_STD_ZVAL(fooval);
ZVAL_STRING(fooval, "bar", 1);
ZEND_SET_SYMBOL(EG(active_symbol_table), "foo", fooval);
或者从符号表中查找$foo
zval **fooval;
if(zend_hash_find(&EG(symbol_table), "foo", sizeof("foo"), (void **)&fooval) == SUCCESS) {
    RETURN_STRINGL(Z_STRVAL_PP(fooval), Z_STRLEN_PP(fooval));
} else {
    RETURN_FALSE;
}
上面的代码中，EG(active_symbol_table) == &EG(symbol_table)
3、CG() 用来访问核心全局变量。(zend/zend_globals_macros.h)
4、PG() PHP全局变量。我们知道php.ini会映射一个或者多个PHP全局结构。(main/php_globals.h)
5、FG() 文件全局变量。大多数文件I/O或相关的全局变量的数据流都塞进标准扩展出口结构。(ext/standard/file.h)



5、获取变量的类型和值
#define Z_TYPE(zval)        (zval).type
#define Z_TYPE_P(zval_p)    Z_TYPE(*zval_p)
#define Z_TYPE_PP(zval_pp)  Z_TYPE(**zval_pp)
比如获取一个变量的类型


void describe_zval(zval *foo)
{
  if ( Z_TYPE_P(foo) == IS_NULL )
  {
    php_printf("这个变量的数据类型是： NULL");
  }
  else
  {
    php_printf("这个变量的数据类型不是NULL，这种数据类型对应的数字是： %d", Z_TYPE_P(foo));
  }
}

有这么几种类型
#define IS_NULL     0
#define IS_LONG     1
#define IS_DOUBLE   2
#define IS_BOOL     3
#define IS_ARRAY    4
#define IS_OBJECT   5
#define IS_STRING   6
#define IS_RESOURCE 7
#define IS_CONSTANT 8
#define IS_CONSTANT_ARRAY   9
#define IS_CALLABLE 10


php_printf()函数是内核对printf()函数的一层封装，我们可以像使用printf()函数那样使用它，以一个P结尾的宏的参数大多是*zval型变量。 此外获取变量类型的宏还有两个，分别是Z_TYPE和Z_TYPE_PP，前者的参数是zval型，而后者的参数则是**zval
比如gettype函数的实现


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

获取变量的值，有这么多宏来获取
Long
Boolean
Double
String value
String length
Z_LVAL( )
Z_BVAL( )
Z_DVAL( )
Z_STRVAL( )
Z_STRLEN( )
Z_LVAL_P( )
Z_BVAL_P( )
Z_DVAL_P( )
Z_STRVAL_P( )
Z_STRLEN_P( )
Z_LVAL_PP( )
Z_BVAL_PP( )
Z_DVAL_PP( )
Z_STRVAL_PP( )
Z_STRLEN_PP( )
HashTable
Object
Object properties
Object class entry
Resource value
Z_ARRVAL( )
Z_OBJ( )
Z_OBJPROP( )
Z_OBJCE( )
Z_RESVAL( )
Z_ARRVAL_P( )
Z_OBJ_P( )
Z_OBJPROP_P( )
Z_OBJCE_P( )
Z_RESVAL_P( )
Z_ARRVAL_PP( )
Z_OBJ_PP( )
Z_OBJPROP_PP( )
Z_OBJCE_PP( )
Z_RESVAL_PP( )


rot13函数的实现

PHP_FUNCTION(rot13)
{
 zval **arg;
 char *ch, cap;
 int i;
   
 if (ZEND_NUM_ARGS( ) != 1 || zend_get_parameters_ex(1, &arg) == FAILURE) {
   WRONG_PARAM_COUNT;
 }
 *return_value = **arg;
 zval_copy_ctor(return_value);
 convert_to_string(return_value);
   
 for(i=0, ch=return_value->value.str.val;
   i<return_value->value.str.len; i++, ch++) {
    cap = *ch & 32;
    *ch &= ~cap;
    *ch = ((*ch>='A') && (*ch<='Z') ? ((*ch-'A'+13) % 26 + 'A') : *ch) | cap;
  }
}
要获取变量的值，也应该使用Zend定义的宏进行访问。对于简单的标量数据类型、Boolean，long，double， 使用Z_BVAL, Z_LVAL, Z_DVAL


void display_values(zval boolzv, zval *longpzv, zval **doubleppzv)
{
 if (Z_TYPE(boolzv) == IS_BOOL) {
  php_printf("The value of the boolean is : %s\n", Z_BVAL(boolzv) ? "true" : "false");
 }
 if(Z_TYPE_P(longpzv) == IS_LONG) {
  php_printf("The value of the long is: %ld\n", Z_LVAL_P(longpzv));
 }
 if(Z_TYPE_PP(doubleppzv) == IS_DOUBLE) {
  php_printf("The value of the double is : %f\n", Z_DVAL_PP(doubleppzv));
 }
}
对于字符串类型，因为它含有两个字段char * (Z_STRVAL) 和 int (Z_STRLEN)，因此需要用两个宏来进行取值，因为需要二进制安全的输出这个字符串


void display_string(zval *zstr)
{
 if (Z_TYPE_P(zstr) != IS_STRING) {
  php_printf("The wronng datatype was passed!\n");
  return ;
 }
 PHPWRITE(Z_STRVAL_P(zstr), Z_STRLEN_P(zstr));
}
因为数组在zval中是以HashTable形式存在的，因此使用Z_ARRVAL()进行访问


void display_zval(zval *value)
{
  switch (Z_TYPE_P(value)) {
    case IS_NULL:
      /* 如果是NULL，则不输出任何东西 */
      break;
  
    case IS_BOOL:
      /* 如果是bool类型，并且true，则输出1，否则什么也不干 */
      if (Z_BVAL_P(value)) {
        php_printf("1");
      }
      break;
    case IS_LONG:
      /* 如果是long整型，则输出数字形式 */
      php_printf("%ld", Z_LVAL_P(value));
      break;
    case IS_DOUBLE:
      /* 如果是double型，则输出浮点数 */
      php_printf("%f", Z_DVAL_P(value));
      break;
    case IS_STRING:
      /* 如果是string型，则二进制安全的输出这个字符串 */
      PHPWRITE(Z_STRVAL_P(value), Z_STRLEN_P(value));
      break;
    case IS_RESOURCE:
      /* 如果是资源，则输出Resource #10 格式的东东 */
      php_printf("Resource #%ld", Z_RESVAL_P(value));
      break;
    case IS_ARRAY:
      /* 如果是Array，则输出Array5个字母！ */
      php_printf("Array");
      break;
    case IS_OBJECT:
      php_printf("Object");
      break;
    default:
      /* Should never happen in practice,
       * but it's dangerous to make assumptions
       */
       php_printf("Unknown");
       break;
  }
}

一些类型转换函数
ZEND_API void convert_to_long(zval *op);
ZEND_API void convert_to_double(zval *op);
ZEND_API void convert_to_null(zval *op);
ZEND_API void convert_to_boolean(zval *op);
ZEND_API void convert_to_array(zval *op);
ZEND_API void convert_to_object(zval *op);
ZEND_API void convert_to_string(zval *op ZEND_FILE_LINE_DC);


6、常量的实例化
我们可以这样实例化

PHP_MINIT_FUNCTION(consts) //模块初始化时定义常量
{
  REGISTER_LONG_CONSTANT("CONSTS_MEANING_OF_LIFE", 42, CONST_CS | CONST_PERSISTENT);
  REGISTER_DOUBLE_CONSTANT("CONSTS_PI", 3.1415926, CONST_PERSISTENT);
  REGISTER_STRING_CONSTANT("CONSTS_NAME", "leon", CONST_CS|CONST_PERSISTENT);
}

PHP_RINIT_FUNCTION(consts) //每次请求时定义常量
{
  char buffer[40];
  srand((int)time(NULL));
  snprintf(buffer, sizeof(buffer), "%d", rand());
  REGISTER_STRING_CONSTANT("CONSTS_RAND", estrdup(buffer), CONST_CS);
  return SUCCESS;
}

常见的宏
/*注册LONG类型常量*/
#define REGISTER_LONG_CONSTANT(name, lval, flags)  zend_register_long_constant((name), sizeof(name), (lval), (flags), module_number TSRMLS_CC)
 /*注册double类型常量*/
#define REGISTER_DOUBLE_CONSTANT(name, dval, flags)  zend_register_double_constant((name), sizeof(name), (dval), (flags), module_number TSRMLS_CC)
/*注册STRING类型常量*/
#define REGISTER_STRING_CONSTANT(name, str, flags)  zend_register_string_constant((name), sizeof(name), (str), (flags), module_number TSRMLS_CC)
/*注册STRING类型常量*/
#define REGISTER_STRINGL_CONSTANT(name, str, len, flags)  zend_register_stringl_constant((name), sizeof(name), (str), (len), (flags), module_number TSRMLS_CC)

7、全局变量
#php-fpm 生成 POST|GET|COOKIE|SERVER|ENV|REQUEST|FILES全局变量的流程
php_cgi_startup() -> php_module_startup() -> php_startup_auto_globals() -> 保存变量到symbol_table符号表
php_cgi_startup()在 fpm/fpm/fpm_main.c中定义
php_module_startup() 在main/main.c中定义
php_startup_auto_globals() 在main/php_variables.h中定义
zend_hash_update(&EG(symbol_table), "_GET", sizeof("_GET") + 1, &vars, sizeof(zval *), NULL);
/* 读取$_SERVER变量 */
static PHP_FUNCTION(print_server_vars) {
  zval **val;
  if (zend_hash_find(&EG(symbol_table), "_SERVER", sizeof("_SERVER"), (void **)&val) == SUCCESS) {
    RETURN_ZVAL(*val, 1, 0);
  }else{
   RETURN_FALSE;
  }
}
/* 读取$_SERVER[$name] */
ZEND_BEGIN_ARG_INFO(print_server_var_arginfo, 0)
  ZEND_ARG_INFO(0, "name")
ZEND_END_ARG_INFO()
static PHP_FUNCTION(print_server_var) {
  char *name;
  int name_len;
  zval **val;
  HashTable *ht_vars = NULL;
  HashPosition pos;
  zval **ret_val;
  if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "|s!", &name, &name_len) == FAILURE) {
    RETURN_NULL();
  }
  if (zend_hash_find(&EG(symbol_table), "_SERVER", sizeof("_SERVER"), (void **)&val) == SUCCESS) {
    ht_vars = Z_ARRVAL_PP(val);
    //此处需传入大于name长度+1的值，因为字符串值后面需要'\0'
    if (zend_hash_find(ht_vars, name, name_len+1, (void **)&ret_val) == SUCCESS) {       RETURN_STRING(Z_STRVAL_PP(ret_val), 0);
    }else{
      RETURN_NULL();
    }
  }else{
    RETURN_NULL();
  }
}


8、包装第三方库
配置(config.m4)

SEARCH_PATH="/usr/local /usr"   #lib搜索的目录
SEARCH_FOR="/include/curl/curl.h" #lib头文件的路径
if test -r $PHP_LIBS/$SEARCH_FOR; then
  LIBS_DIR=$PHP_LIBS
else # search default path list
  AC_MSG_CHECKING([for libs files in default path])
  for i in $SEARCH_PATH ; do
    if test -r $i/$SEARCH_FOR; then
      LIBS_DIR=$i        #搜索到的lib的路径
      AC_MSG_RESULT(found in $i)
    fi
  done
fi
/*验证lib是否存在*/
if test -z "$LIBS_DIR"; then
  AC_MSG_RESULT([not found])
  AC_MSG_ERROR([Please reinstall the libs distribution])
fi
/*编译的时候添加lib的include目录, -I/usr/include*/
PHP_ADD_INCLUDE($LIBS_DIR/include)
LIBNAME=curl      #lib名称 
LIBSYMBOL=curl_version #lib的一个函数，用来PHP_CHECK_LIBRARY验证lib
/*验证lib*/
PHP_CHECK_LIBRARY($LIBNAME,$LIBSYMBOL, 
[
  PHP_ADD_LIBRARY_WITH_PATH($LIBNAME, $LIBS_DIR/$PHP_LIBDIR, LIBS_SHARED_LIBADD) #编译的时候链接lib, -llibcurl
  AC_DEFINE(HAVE_LIBSLIB,1,[ ])
],[
  AC_MSG_ERROR([wrong libs lib version or lib not found])
],[
  -L$LIBS_DIR/$PHP_LIBDIR -lm
]) 
PHP_SUBST(LIBS_SHARED_LIBADD)


9、用于返回的宏
//这些宏都定义在Zend/zend_API.h文件里
#define RETVAL_RESOURCE(l)              ZVAL_RESOURCE(return_value, l)
#define RETVAL_BOOL(b)                  ZVAL_BOOL(return_value, b)
#define RETVAL_NULL()                   ZVAL_NULL(return_value)
#define RETVAL_LONG(l)                  ZVAL_LONG(return_value, l)
#define RETVAL_DOUBLE(d)                ZVAL_DOUBLE(return_value, d)
#define RETVAL_STRING(s, duplicate)         ZVAL_STRING(return_value, s, duplicate)
#define RETVAL_STRINGL(s, l, duplicate)     ZVAL_STRINGL(return_value, s, l, duplicate)
#define RETVAL_EMPTY_STRING()           ZVAL_EMPTY_STRING(return_value)
#define RETVAL_ZVAL(zv, copy, dtor)     ZVAL_ZVAL(return_value, zv, copy, dtor)
#define RETVAL_FALSE                    ZVAL_BOOL(return_value, 0)
#define RETVAL_TRUE                     ZVAL_BOOL(return_value, 1)
#define RETURN_RESOURCE(l)              { RETVAL_RESOURCE(l); return; }
#define RETURN_BOOL(b)                  { RETVAL_BOOL(b); return; }
#define RETURN_NULL()                   { RETVAL_NULL(); return;}
#define RETURN_LONG(l)                  { RETVAL_LONG(l); return; }
#define RETURN_DOUBLE(d)                { RETVAL_DOUBLE(d); return; }
#define RETURN_STRING(s, duplicate)     { RETVAL_STRING(s, duplicate); return; }
#define RETURN_STRINGL(s, l, duplicate) { RETVAL_STRINGL(s, l, duplicate); return; }
#define RETURN_EMPTY_STRING()           { RETVAL_EMPTY_STRING(); return; }
#define RETURN_ZVAL(zv, copy, dtor)     { RETVAL_ZVAL(zv, copy, dtor); return; }
#define RETURN_FALSE                    { RETVAL_FALSE; return; }
#define RETURN_TRUE                     { RETVAL_TRUE; return; }
其实，除了这些标量类型，还有很多php语言中的复合类型我们需要在函数中返回，如数组和对象，我们可以通过RETVAL_ZVAL与RETURN_ZVAL来操作它们


10、hashTable的遍历函数
//基于long key的操作函数
zval *v3;
MAKE_STD_ZVAL(v3);
ZVAL_STRING(v3, "value3", 1);
zend_hash_index_update(names, 0, &v3, sizeof(zval *), NULL);//按数字索引键更新HashTable元素的值
zval **v4;
zend_hash_index_find(names, 1, &v4); //按数字索引获取HashTable元素的值
php_printf("v4 : ");
PHPWRITE(Z_STRVAL_PP(v4), Z_STRLEN_PP(v4));
php_printf("\n");
ulong idx;
idx = zend_hash_index_exists(names, 10);//按数字索引查找HashTable，如果找到返回 1， 反之则返回 0
zend_hash_index_del(names, 2);    //按数字索引删除HashTable元素
//hashTable的遍历函数
zend_hash_internal_pointer_reset(names); //初始化hash指针
zend_hash_internal_pointer_reset_ex(names, &pos);//初始化hash指针，并付值给pos
zend_hash_get_current_data(names, (void**) &val); //获取当前hash存储值,data should be cast to void**, ie: (void**) &data
zend_hash_get_current_data_ex(names, (void**) &val, &pos) == SUCCESS; //获取当前hash存储值
zend_hash_get_current_key(names, &key, &klen, &index, 0) == HASH_KEY_IS_LONG
zend_hash_get_current_key_ex(names, &key, &klen, &index, 0, &pos) == HASH_KEY_IS_LONG; //读取hashtable当前的KEY，返回值会有两种 HASH_KEY_IS_LONG | HASH_KEY_IS_STRING ，分别对应array("value")，array("key"=>"value")两种hashtable
zend_hash_move_forward(names);
zend_hash_move_forward_ex(names, &pos); //hash指针移至下一位
//HashTable长度
php_printf("%*carray(%d) {\n", depth * 2, ' ', zend_hash_num_elements(Z_ARRVAL_P(zv))
一个简单的函数


function hello_array_strings($arr) {
  if (!is_array($arr)) return NULL;
  printf("The array passed contains %d elements ", count($arr));
  foreach($arr as $data) {
    if (is_string($data)) echo "$data ";
  }
}
PHP内核实现


PHP_FUNCTION(hello_array_strings)
{
  zval *arr, **data;
  HashTable *arr_hash;
  HashPosition pointer;
  int array_count;

  if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "a", &arr) == FAILURE) {
    RETURN_NULL();
  }

  arr_hash = Z_ARRVAL_P(arr);
  array_count = zend_hash_num_elements(arr_hash);
  php_printf("The array passed contains %d elements ", array_count);
  
  for(zend_hash_internal_pointer_reset_ex(arr_hash, &pointer); zend_hash_get_current_data_ex(arr_hash, (void**) &data, &pointer) == SUCCESS; zend_hash_move_forward_ex(arr_hash, &pointer)) {
    if (Z_TYPE_PP(data) == IS_STRING) {
      PHPWRITE(Z_STRVAL_PP(data), Z_STRLEN_PP(data));
      php_printf(" ");
    }
  }

  RETURN_TRUE;
}

  //遍历hashTable
  zend_hash_internal_pointer_reset_ex(Z_ARRVAL_P(arr), &pos);

  while (zend_hash_get_current_data_ex(Z_ARRVAL_P(arr), (void **) &tmp, &pos) == SUCCESS) {
    switch ((*tmp)->type) {
      case IS_STRING:
        smart_str_appendl(&implstr, Z_STRVAL_PP(tmp), Z_STRLEN_PP(tmp));
        break;

      case IS_LONG: {
        char stmp[MAX_LENGTH_OF_LONG + 1];
        str_len = slprintf(stmp, sizeof(stmp), "%ld", Z_LVAL_PP(tmp));
        smart_str_appendl(&implstr, stmp, str_len);
      }
        break;

      case IS_BOOL:
        if (Z_LVAL_PP(tmp) == 1) {
          smart_str_appendl(&implstr, "1", sizeof("1")-1);
        }
        break;

      case IS_NULL:
        break;

      case IS_DOUBLE: {
        char *stmp;
        str_len = spprintf(&stmp, 0, "%.*G", (int) EG(precision), Z_DVAL_PP(tmp));
        smart_str_appendl(&implstr, stmp, str_len);
        efree(stmp);
      }
        break;

      case IS_OBJECT: {
        int copy;
        zval expr;
        zend_make_printable_zval(*tmp, &expr, &copy);
        smart_str_appendl(&implstr, Z_STRVAL(expr), Z_STRLEN(expr));
        if (copy) {
          zval_dtor(&expr);
        }
      }
        break;

      default:
        tmp_val = **tmp;
        zval_copy_ctor(&tmp_val);
        convert_to_string(&tmp_val);
        smart_str_appendl(&implstr, Z_STRVAL(tmp_val), Z_STRLEN(tmp_val));
        zval_dtor(&tmp_val);
        break;

    }

    if (++i != numelems) {
      smart_str_appendl(&implstr, Z_STRVAL_P(delim), Z_STRLEN_P(delim));
    }
    zend_hash_move_forward_ex(Z_ARRVAL_P(arr), &pos);
  }



//data type
ZEND_FUNCTION(shop_hello) {
    char *name, *greeting, *strg;
    long name_len, greeting_len, len;

    if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "ss", &name, &name_len, &greeting, &greeting_len) == FAILURE) {
        RETURN_NULL();
    }

    len = spprintf(&strg, 0, "ok", "shop", name, greeting);
    RETURN_STRINGL(strg, len, 0);
}




zend_parse_parameters() 类型说明符
修饰符	附加参数的类型	描述
b	zend_bool		Boolean 值
l	long			integer (long) 值
d	double			float (double) 值
s	char*, int		二进制的安全串
h	HashTable*		数组的哈希表



#define RETURN_RESOURCE(l) { RETVAL_RESOURCE(l); return; }
#define RETURN_BOOL(b) { RETVAL_BOOL(b); return; }
#define RETURN_NULL() { RETVAL_NULL(); return;}
#define RETURN_LONG(l) { RETVAL_LONG(l); return; }
#define RETURN_DOUBLE(d) { RETVAL_DOUBLE(d); return; }
#define RETURN_STRING(s, duplicate) { RETVAL_STRING(s, duplicate); return; }
#define RETURN_STRINGL(s, l, duplicate) { RETVAL_STRINGL(s, l, duplicate); return; }
#define RETURN_EMPTY_STRING() { RETVAL_EMPTY_STRING(); return; }
#define RETURN_ZVAL(zv, copy, dtor) { RETVAL_ZVAL(zv, copy, dtor); return; }
#define RETURN_FALSE   { RETVAL_FALSE; return; }
#define RETURN_TRUE   { RETVAL_TRUE; return; }