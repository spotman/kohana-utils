<?php defined('SYSPATH') OR die('No direct script access.');


class Util_ORM extends Kohana_ORM {

    public function belongs_to(array $config = NULL)
    {
        if ( $config )
        {
            $this->_belongs_to = array_merge($this->_belongs_to, $config);
        }

        return parent::belongs_to();
    }

    public function has_one(array $config = NULL)
    {
        if ( $config )
        {
            $this->_has_one = array_merge($this->_has_one, $config);
        }

        return parent::has_one();
    }

    public function has_many(array $config = NULL)
    {
        if ( $config )
        {
            $this->_has_many = array_merge($this->_has_many, $config);
        }

        return parent::has_many();
    }

    public function load_with(array $config = NULL)
    {
        if ( $config )
        {
            $this->_load_with = array_merge($this->_load_with, $config);
        }

        return parent::load_with();
    }

    public function list_columns()
    {
        $cache_key = $this->table_name().':list_columns()';
        $columns = Kohana::cache($cache_key);

        if ( ! $columns )
        {
            $columns = parent::list_columns();
            Kohana::cache($cache_key, $columns);
        }

        return $columns;
    }

    public function get_id()
    {
        return $this->pk();
    }

    public function group_by_primary_key()
    {
        return $this->group_by($this->object_primary_key());
    }

    public function object_primary_key()
    {
        return $this->object_name().'.'.$this->primary_key();
    }

    /**
     * @return $this
     */
    public function randomize()
    {
        return $this->order_by(DB::expr('RAND()'));
    }

    /**
     * Связывает элементы аласа (с указанными первичными ключами) с текущей моделью
     *
     * @param string $alias
     * @param array $far_keys
     * @return $this
     */
    public function link_related($alias, array $far_keys)
    {
        return $this->_update_related_alias_foreign_key_field($alias, $far_keys, $this->pk());
    }

    /**
     * Отвязывает элементы алиаса (с указанными первичными ключами) от текущей модели
     *
     * @param string $alias
     * @param array|NULL $far_keys
     * @return $this
     */
    public function unlink_related($alias, array $far_keys = NULL)
    {
        // Если элементы не указаны
        if ( ! $far_keys )
        {
            // Попробуем достать их из алиаса
            $relation = $this->_get_relation($alias);
            $relation_data = $relation->find_all()->as_array($relation->primary_key());
            $far_keys = array_keys($relation_data);
        }

        // Если нет связанных элементов, то и делать нечего
        if ( ! $far_keys )
            return $this;

        return $this->_update_related_alias_foreign_key_field($alias, $far_keys, NULL);
    }

    /**
     * Проставляет полю внешнего ключа у алиаса значение первичного ключа текущей модели
     *
     * @param string $alias
     * @param array $far_keys
     * @param int|NULL $value
     * @return $this
     */
    protected function _update_related_alias_foreign_key_field($alias, array $far_keys, $value)
    {
        $relation = $this->_get_relation($alias);

        $query = DB::update($relation->table_name())
            ->set(array($this->_has_many[$alias]['foreign_key'] => $value))
            ->where($relation->primary_key(), "IN", $far_keys);

        $query->execute($this->_db);

        return $this;
    }

    /**
     * @param string $alias
     * @return ORM
     */
    protected function _get_relation($alias)
    {
        return $this->get($alias);
    }


    public function join_related($relation_alias)
    {
        if (isset($this->_belongs_to[$relation_alias]))
        {
            throw new HTTP_Exception_501;
//            $model = $this->_related($column);
//
//            // Use this model's column and foreign model's primary key
//            $col = $model->_object_name.'.'.$model->_primary_key;
//            $val = $this->_object[];
//
//            $foreign_key = $this->_belongs_to[$column]['foreign_key'];
//
//            // Make sure we don't run WHERE "AUTO_INCREMENT column" = NULL queries. This would
//            // return the last inserted record instead of an empty result.
//            // See: http://mysql.localhost.net.ar/doc/refman/5.1/en/server-session-variables.html#sysvar_sql_auto_is_null
//            if ($val !== NULL)
//            {
//                $model->where($col, '=', $val)->find();
//            }

        }
        elseif (isset($this->_has_one[$relation_alias]))
        {
            throw new HTTP_Exception_501;
//            $model = $this->_related($column);
//
//            // Use this model's primary key value and foreign model's column
//            $col = $model->_object_name.'.'.$this->_has_one[$column]['foreign_key'];
//            $val = $this->pk();
//
//            $model->where($col, '=', $val)->find();
//
//            return $this->_related[$column] = $model;
        }
        elseif (isset($this->_has_many[$relation_alias]))
        {
            $model = ORM::factory($this->_has_many[$relation_alias]['model']);

            if (isset($this->_has_many[$relation_alias]['through']))
            {
                throw new HTTP_Exception_501;

//                // Grab has_many "through" relationship table
//                $through = $this->_has_many[$column]['through'];
//
//                // Join on through model's target foreign key (far_key) and target model's primary key
//                $join_col1 = $through.'.'.$this->_has_many[$column]['far_key'];
//                $join_col2 = $model->_object_name.'.'.$model->_primary_key;
//
//                $model->join($through)->on($join_col1, '=', $join_col2);
//
//                // Through table's source foreign key (foreign_key) should be this model's primary key
//                $col = $through.'.'.$this->_has_many[$column]['foreign_key'];
//                $val = $this->pk();
            }
            else
            {
                // Simple has_many relationship, search where target model's foreign key is this model's primary key
                return $this->_join(
                    $model->table_name(),
                    $this->_has_many[$relation_alias]['foreign_key'],
                    $this->object_name().'.'.$this->primary_key(),
                    $model->object_name()
                );
            }
        }
        else
        {
            throw new Kohana_Exception('The related model alias :property does not exist in the :class class',
                array(':property' => $relation_alias, ':class' => get_class($this)));
        }
    }

    /**
     * @param $table_name
     * @param $on_left
     * @param $on_right
     * @param null $table_alias
     * @return $this
     */
    protected function _join($table_name, $on_left, $on_right, $table_alias = NULL)
    {
        if ( ! $table_alias )
        {
            $table_alias = $table_name;
        }

        return $this
            ->join(array($table_name, $table_alias))
            ->on($table_alias.'.'.$on_left, '=', $on_right);
    }

    protected function _join_model(ORM $model, $on_left, $on_right)
    {
        return $this->_join($model->table_name(), $on_left, $on_right, $model->object_name());
    }

    /**
     * @param string $term String to search for
     * @param array $search_columns Columns to search where
     * @return ORM[]
     */
    protected function search($term, array $search_columns)
    {
        $this->and_where_open();

        foreach ( $search_columns as $search_column )
        {
            $this->or_where($search_column, 'LIKE', '%'.$term.'%');
        }

        return $this->and_where_close()->find_all();
    }

    protected function _autocomplete($query, array $search_fields)
    {
        /** @var ORM[] $results */
        $results = $this->search($query, $search_fields);

        $output = array();

        foreach ( $results as $item )
        {
            $output[] = $item->autocomplete_formatter();
        }

        return $output;
    }

    /**
     * @throws Kohana_Exception
     * @return array
     */
    protected function autocomplete_formatter()
    {
        throw new Kohana_Exception('Implement autocomplete_formatter for class :class_name',
            array(':class_name' => get_class($this)));
    }

}