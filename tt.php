		
Bucket *p;

		convert_to_array_ex(keys);

		repl = (zval ***)safe_emalloc(num, sizeof(zval **), 0);
		for (p = Z_ARRVAL_PP(keys)->pListHead, i = 0; p; p = p->pListNext, i++) {
			add_index_long(return_value, i, ((zval **)p->pData))
		}


		zend_hash_index_update(Z_ARRVAL_P(return_value), i, ((zval **)p->pData, sizeof(zval *), NULL);