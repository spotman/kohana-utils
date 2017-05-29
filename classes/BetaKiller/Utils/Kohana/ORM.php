<?php
namespace BetaKiller\Utils\Kohana;

use BetaKiller\Utils\Kohana\ORM\OrmInterface;

class ORM extends \Kohana_ORM implements OrmInterface
{
    public function getModelName(OrmInterface $object = null)
    {
        return static::detectModelName($object);
    }

    protected static function detectModelName(OrmInterface $object = null)
    {
        $className = $object ? get_class($object) : static::class;

        // Try namespaces first
        $pos = strrpos($className, '\\');

        if ($pos === false) {
            // Use legacy naming
            $pos = 5; // "Model_" is 6 letters
        }

        return substr($className, $pos + 1);
    }

    public function belongs_to(array $config = NULL)
    {
        if ($config) {
            $this->_belongs_to = array_merge($this->_belongs_to, $config);
        }

        return parent::belongs_to();
    }

    public function has_one(array $config = NULL)
    {
        if ($config) {
            $this->_has_one = array_merge($this->_has_one, $config);
        }

        return parent::has_one();
    }

    public function has_many(array $config = NULL)
    {
        if ($config) {
            $this->_has_many = array_merge($this->_has_many, $config);
        }

        return parent::has_many();
    }

    public function load_with(array $config = NULL)
    {
        if ($config) {
            $this->_load_with = array_merge($this->_load_with, $config);
        }

        return parent::load_with();
    }

    public function list_columns()
    {
        $cache_key = $this->table_name().':list_columns()';
        $columns = \Kohana::cache($cache_key);

        if (!$columns) {
            $columns = parent::list_columns();
            \Kohana::cache($cache_key, $columns);
        }

        return $columns;
    }

    /**
     * @param \BetaKiller\Utils\Kohana\ORM\OrmInterface $model
     *
     * @return bool
     */
    public function isEqualTo(OrmInterface $model)
    {
        return ($this->get_id() === $model->get_id());
    }

    public function get_id()
    {
        return $this->pk();
    }

    /**
     * @param int $value
     *
     * @return $this
     */
    public function set_id($value)
    {
        return $this->set($this->primary_key(), $value);
    }

    /**
     * @param int  $id
     * @param bool $allow_missing
     *
     * @return OrmInterface
     * @throws \Kohana_Exception
     */
    public function get_by_id($id, $allow_missing = false)
    {
        $model = $this->filter_primary_key($id)->find();

        if (!$allow_missing && !$model->loaded())
            throw new \Kohana_Exception('Model with id :id does not exists', [':id' => $id]);

        return $model;
    }

    /**
     * @return $this
     */
    public function group_by_primary_key()
    {
        return $this->group_by($this->object_primary_key());
    }

    /**
     * @return string
     */
    public function object_primary_key()
    {
        return $this->object_column($this->primary_key());
    }

    /**
     * @param string $column
     *
     * @return string
     */
    public function object_column($column)
    {
        return $this->object_name().'.'.$column;
    }

    /**
     * @param array|integer $ids
     * @return $this
     */
    public function filter_primary_key($ids)
    {
        return $this->where($this->object_primary_key(), 'IN', (is_array($ids) ? $ids : array($ids)));
    }

    /**
     * @return $this
     */
    public function randomize()
    {
        return $this->order_by(\DB::expr('RAND()'));
    }

    /**
     * @param string    $name
     * @param array     $sequence
     *
     * @return $this|OrmInterface
     */
    public function order_by_field_sequence($name, array $sequence)
    {
        return $this->order_by(\DB::expr('FIELD('.$name.', "'.implode('", "', $sequence).'")'), 'ASC');
    }

    /**
     * Enables the query to be cached for a specified amount of time.
     * Cache lifetime is taken from config file called "clt.php" with structure <table name> => <seconds>
     *
     * @param string|integer $lifetime number of seconds to cache or cache lifetime config key
     * @return  $this
     * @uses    Kohana::$cache_life
     */
    public function cached($lifetime = NULL)
    {
        // Do nothing if caching is not enabled
        if (!\Kohana::$caching)
            return $this;

        if (!is_int($lifetime)) {
            $key = $this->table_name();

            if (is_string($lifetime)) {
                $key .= '.'.$lifetime;
            }

            $group = \Kohana::$config->load('clt');
            $lifetime = $group ? $group->get($key, 60) : 60;
        }

        parent::cached($lifetime);

        return $this;
    }

    /**
     * Returns TRUE if column exists in database
     *
     * @param string $name
     * @return bool
     */
    public function has_column($name)
    {
        $columns = $this->list_columns();
        return isset($columns[$name]);
    }

    /**
     * Связывает элементы алиаса (с указанными первичными ключами) с текущей моделью
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
        if (!$far_keys) {
            // Попробуем достать их из алиаса
            $relation = $this->_get_relation($alias);
            $relation_data = $relation->find_all()->as_array($relation->primary_key());
            $far_keys = array_keys($relation_data);
        }

        // Если нет связанных элементов, то и делать нечего
        if (!$far_keys)
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

        $query = \DB::update($relation->table_name())
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

    /**
     * @param $relation_name
     * @param $model
     * @return $this
     * @throws \HTTP_Exception_501
     * @throws \Kohana_Exception
     */
    public function filter_related($relation_name, ORM $model)
    {
        return $this
            ->join_related($relation_name)
            ->where($model->object_primary_key(), '=', $model->pk());
    }

    /**
     * @param string        $relation_alias
     * @param string|null   $table_alias
     *
     * @return $this|OrmInterface
     * @throws \HTTP_Exception_501
     * @throws \Kohana_Exception
     */
    public function join_related($relation_alias, $table_alias = NULL)
    {
        if (isset($this->_belongs_to[$relation_alias])) {
            $model = $this->_related($relation_alias);
            $foreign_key = $this->_belongs_to[$relation_alias]['foreign_key'];

            $this->join_on_model(
                $model,
                $model->primary_key(),
                $this->object_column($foreign_key),
                $table_alias
            );
        } elseif (isset($this->_has_one[$relation_alias])) {
            throw new \HTTP_Exception_501;
//            $model = $this->_related($column);
//
//            // Use this model's primary key value and foreign model's column
//            $col = $model->_object_name.'.'.$this->_has_one[$column]['foreign_key'];
//            $val = $this->pk();
//
//            $model->where($col, '=', $val)->find();
//
//            return $this->_related[$column] = $model;
        } elseif (isset($this->_has_many[$relation_alias])) {
            $model = ORM::factory($this->_has_many[$relation_alias]['model']);

            if (isset($this->_has_many[$relation_alias]['through'])) {
                // Grab has_many "through" relationship table
                $through_table = $this->_has_many[$relation_alias]['through'];
                $through_table_alias = $relation_alias.':through';

                $this
                    ->join_on(
                        $through_table,
                        $this->_has_many[$relation_alias]['foreign_key'],
                        $this->object_name().'.'.$this->primary_key(),
                        $through_table_alias
                    )
                    ->join_on(
                        $model->table_name(),
                        $model->primary_key(),
                        $through_table_alias.'.'.$this->_has_many[$relation_alias]['far_key'],
                        $table_alias ?: $model->object_name()
                    );
            } else {
                // Simple has_many relationship, search where target model's foreign key is this model's primary key
                return $this->join_on(
                    $model->table_name(),
                    $this->_has_many[$relation_alias]['foreign_key'],
                    $this->object_name().'.'.$this->primary_key(),
                    $table_alias ?: $model->object_name()
                );
            }
        } else {
            throw new \Kohana_Exception('The related model alias :property does not exist in the :class class',
                array(':property' => $relation_alias, ':class' => get_class($this)));
        }

        return $this;
    }

//    protected function _join_through($ltable, $ltable_pk, $ttable, $ttable_lkey, $ttable_rkey, $rtable, $rtable_pk)
//    {
//    }

//    protected function filter_related_ids($relation_alias, $ids)
//    {
//        $ids = is_array($ids) ? $ids : array($ids);
//
//        if (isset($this->_belongs_to[$relation_alias]))
//        {
//            $col = $this->_belongs_to[$relation_alias]['foreign_key'];
//        }
//        elseif (isset($this->_has_one[$relation_alias]))
//        {
//            throw new HTTP_Exception_501;
//        }
//        elseif (isset($this->_has_many[$relation_alias]))
//        {
//            $model = ORM::factory($this->_has_many[$relation_alias]['model']);
//
//            $col = $model->object_primary_key();
//        }
//        else
//        {
//            throw new Kohana_Exception('The related model alias :property does not exist in the :class class',
//                array(':property' => $relation_alias, ':class' => get_class($this)));
//        }
//
//        return $this->where($col, 'IN', $ids);
//    }

    /**
     * @param $table_name
     * @param $table_key
     * @param $equal_key
     * @param string|NULL $table_alias
     * @param string|NULL $join_type
     * @return $this
     */
    protected function join_on($table_name, $table_key, $equal_key, $table_alias = NULL, $join_type = NULL)
    {
        if ( ! $table_alias ) {
            $table_alias = $table_name;
        }

        return $this
            ->join(array($table_name, $table_alias), $join_type ?: 'LEFT')
            ->on($table_alias.'.'.$table_key, '=', $equal_key);
    }

    protected function join_on_model(ORM $model, $on_left, $on_right, $table_alias = NULL)
    {
        return $this->join_on(
            $model->table_name(),
            $on_left,
            $on_right,
            $table_alias ?: $model->object_name()
        );
    }

    /**
     * @param string $term String to search for
     * @param array $search_columns Columns to search where
     * @return $this
     */
    protected function search_query($term, array $search_columns)
    {
        if ($term) {
            // Split into words
            $words = explode(' ', $term);

            // Make AND for every word
            foreach ($words as $word) {
                $this->and_where_open();

                foreach ( $search_columns as $search_column ) {
                    $this->or_where($search_column, 'LIKE', '%'.$word.'%');
                }

                $this->and_where_close();
            }
        }

        return $this->cached('search');
    }

    public function autocomplete($term, array $search_fields, $as_key_label_pairs = false)
    {
        /** @var ORM[] $results */
        $results = $this->search_query($term, $search_fields)->find_all();

        $output = array();

        foreach ( $results as $item ) {
            if ($as_key_label_pairs) {
                $output[$item->autocomplete_formatter_key()] = $item->autocomplete_formatter_label();
            } else {
                $output[] = $item->autocomplete_formatter();
            }
        }

        return $output;
    }

    /**
     * Returns prepared data for autocomplete
     *
     * @return array
     */
    protected function autocomplete_formatter()
    {
        return array(
            'id'    =>  $this->autocomplete_formatter_key(),
            'text'  =>  $this->autocomplete_formatter_label(),
        );
    }

    /**
     * Returns "key" for autocomplete formatter
     * @return int|string
     */
    protected function autocomplete_formatter_key()
    {
        return $this->get_id();
    }

    /**
     * Returns "label" for autocomplete formatter
     *
     * @throws \Kohana_Exception
     * @return string
     */
    protected function autocomplete_formatter_label()
    {
        throw new \Kohana_Exception('Implement :class::autocomplete_formatter_label() method',
            array(':class' => get_class($this)));
    }

    /**
     * Creates custom SELECT query from current db builder queue
     */
    protected function _build_custom_select()
    {
        $this->_build(\Database::SELECT);

        $this->_db_builder->from(array($this->_table_name, $this->_object_name));

        return $this;
    }

    /**
     * @return \Database_Result|int
     */
    protected function _execute_query()
    {
        return $this->_db_builder->execute($this->_db);
    }

    protected function compile($buildSelect = true)
    {
        $buildSelect && $this->_build_custom_select();
        return $this->_db_builder->compile($this->_db);
    }

    protected function select_all_columns()
    {
        $this->_db_builder->select_array($this->_build_select());
        return $this;
    }

    /**
     * Compile current query as a subquery and make COUNT(*) with from it
     * @return integer
     */
    public function compile_as_subquery_and_count_all()
    {
        $sql = 'SELECT COUNT(*) AS total FROM (' . $this->compile() . ') AS x';

        $query = \DB::query(\Database::SELECT, $sql);

        $result = $query->execute($this->_db);

        return (int)$result->get('total');
    }

    /**
     * Get field alias for COUNT(N) expression
     * @return string
     */
    public function get_sql_counter_alias()
    {
        return $this->object_name().'_counter';
    }

    /**
     * Get field alias for GROUP_CONCAT(N) expression
     * @param string $field
     * @return string
     */
    public function get_sql_column_group_concat_alias($field)
    {
        return $this->object_name().'_'.$field.'_group_concat';
    }

    /**
     * Get field alias for CONCAT(N) expression
     * @param string $field
     * @return string
     */
    public function get_sql_column_concat_alias($field)
    {
        return $this->object_name().'_'.$field.'_concat';
    }

    /**
     * @param $columns
     * @return $this
     */
    protected function select_array($columns)
    {
        $this->_db_builder->select_array($columns);
        return $this;
    }

    /**
     * @param array $ids
     * @param bool $not_in
     *
     * @return $this|\ORM|static
     */
    public function filter_ids(array $ids, $not_in = FALSE)
    {
        return $this->where($this->object_primary_key(), $not_in ? 'NOT IN': 'IN', $ids);
    }

    /**
     * Checks whether a column value is unique.
     * Excludes itself if loaded.
     *
     * @param   string   $field  the field to check for uniqueness
     * @param   callable   $additional_filtering  Additional filtering callback
     * @return  bool     whatever the value is unique
     */
    public function unique_field_value($field, callable $additional_filtering = NULL)
    {
        $value = $this->get($field);

        // Skip check if no value present
        if ( !$value )
            return true;

        $orm = $this->model_factory();

        if ( $additional_filtering ) {
            call_user_func($additional_filtering, $orm);
        }

		$model = $orm
            ->where($this->object_name().'.'.$field, '=', $value)
            ->find();

        if ($this->loaded()) {
            return ( ! ($model->loaded() && $model->pk() != $this->pk()));
        }

        return !$model->loaded();
    }

    /**
     * Converts value of MySQL datetime column to PHP DateTime object
     *
     * @param string             $name
     * @param \DateTimeZone|NULL $tz
     *
     * @return \DateTime|null
     */
    protected function get_datetime_column_value($name, \DateTimeZone $tz = NULL)
    {
        $value = $this->get($name);

        if (!$value) {
            return NULL;
        }

        return $tz
            ? \DateTime::createFromFormat('Y-m-d H:i:s', $value, $tz)
            : \DateTime::createFromFormat('Y-m-d H:i:s', $value);
    }

    /**
     * Sets value of MySQL datetime column from PHP DateTime object
     *
     * @param string             $name
     * @param \DateTime          $value
     * @param \DateTimeZone|NULL $tz
     *
     * @return $this
     */
    protected function set_datetime_column_value($name, \DateTime $value, \DateTimeZone $tz = NULL)
    {
        if ($tz) {
            $value->setTimezone($tz);
        }

        return $this->set($name, $value->format('Y-m-d H:i:s'));
    }

    protected function filter_datetime_column_value($name, \DateTime $value, $operator, \DateTimeZone $tz = NULL)
    {
        if ($tz) {
            $value->setTimezone($tz);
        }

        return $this->where($name, $operator, $value->format('Y-m-d H:i:s'));
    }

    /**
     * @param int $pk
     * @param string|null $name
     * @return OrmInterface
     */
    public function model_factory($pk = NULL, $name = null)
    {
        return static::factory($name ?: $this->object_name(), $pk);
    }
}
