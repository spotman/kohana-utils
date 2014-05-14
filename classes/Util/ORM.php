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

    public function get_id()
    {
        return $this->pk();
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