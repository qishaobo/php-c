PHP_FUNCTION(array_column)
{
	zval **zcolumn = NULL, **zkey = NULL, **data;
	HashTable *arr_hash;
	HashPosition pointer;

	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "hZ!|Z!", &arr_hash, &zcolumn, &zkey) == FAILURE) {
		return;
	}

	if ((zcolumn && !array_column_param_helper(zcolumn, "column" TSRMLS_CC)) ||
	    (zkey && !array_column_param_helper(zkey, "index" TSRMLS_CC))) {
		RETURN_FALSE;
	}

	array_init(return_value);
	for (zend_hash_internal_pointer_reset_ex(arr_hash, &pointer);
			zend_hash_get_current_data_ex(arr_hash, (void**)&data, &pointer) == SUCCESS;
			zend_hash_move_forward_ex(arr_hash, &pointer)) {
		zval **zcolval, **zkeyval = NULL;
		HashTable *ht;

		if (Z_TYPE_PP(data) != IS_ARRAY) {
			/* Skip elemens which are not sub-arrays */
			continue;
		}
		ht = Z_ARRVAL_PP(data);

		if (!zcolumn) {
			/* NULL column ID means use entire subarray as data */
			zcolval = data;

			/* Otherwise, skip if the value doesn't exist in our subarray */
		} else if ((Z_TYPE_PP(zcolumn) == IS_STRING) &&
		    (zend_hash_find(ht, Z_STRVAL_PP(zcolumn), Z_STRLEN_PP(zcolumn) + 1, (void**)&zcolval) == FAILURE)) {
			continue;
		} else if ((Z_TYPE_PP(zcolumn) == IS_LONG) &&
		    (zend_hash_index_find(ht, Z_LVAL_PP(zcolumn), (void**)&zcolval) == FAILURE)) {
			continue;
		}

		/* Failure will leave zkeyval alone which will land us on the final else block below
		 * which is to append the value as next_index
		 */
		if (zkey && (Z_TYPE_PP(zkey) == IS_STRING)) {
			zend_hash_find(ht, Z_STRVAL_PP(zkey), Z_STRLEN_PP(zkey) + 1, (void**)&zkeyval);
		} else if (zkey && (Z_TYPE_PP(zkey) == IS_LONG)) {
			zend_hash_index_find(ht, Z_LVAL_PP(zkey), (void**)&zkeyval);
		}

		Z_ADDREF_PP(zcolval);
		if (zkeyval && Z_TYPE_PP(zkeyval) == IS_STRING) {
			add_assoc_zval(return_value, Z_STRVAL_PP(zkeyval), *zcolval);
		} else if (zkeyval && Z_TYPE_PP(zkeyval) == IS_LONG) {
			add_index_zval(return_value, Z_LVAL_PP(zkeyval), *zcolval);
		} else if (zkeyval && Z_TYPE_PP(zkeyval) == IS_OBJECT) {
			SEPARATE_ZVAL(zkeyval);
			convert_to_string(*zkeyval);
			add_assoc_zval(return_value, Z_STRVAL_PP(zkeyval), *zcolval);
		} else {
			add_next_index_zval(return_value, *zcolval);
		}
	}
}




			if (use_type) {
				MAKE_STD_ZVAL(key);
				/* Set up the key */
				switch (key_type) {
					case HASH_KEY_IS_LONG:
						Z_TYPE_P(key) = IS_LONG;
						Z_LVAL_P(key) = num_key;

						/*ZVAL_LONG(data, year);*/

						break;

					case HASH_KEY_IS_STRING:
						ZVAL_STRINGL(key, string_key, string_key_len - 1, 1);
						break;
				}
			}


    array_init(return_value);
	if (context->notifier && context->notifier->ptr && context->notifier->func == user_space_stream_notifier) {
		add_assoc_zval_ex(return_value, ZEND_STRS("notification"), context->notifier->ptr);
		Z_ADDREF_P(context->notifier->ptr);
	}
	
	ALLOC_INIT_ZVAL(options);
	ZVAL_ZVAL(options, context->options, 1, 0);
	add_assoc_zval_ex(return_value, ZEND_STRS("options"), options);