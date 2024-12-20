<?php
namespace BetaKiller\Utils\Kohana;

use BetaKiller\Utils\Kohana\ORM\OrmInterface;
use Database_Expression;
use Database_Result;
use DateTimeImmutable;
use DateTimeZone;
use LogicException;

class ORM extends \Kohana_ORM implements OrmInterface
{
    public const FORMAT_DATE     = 'Y-m-d';
    public const FORMAT_DATETIME = 'Y-m-d H:i:s';
    public const REL_SEP         = ':';
    public const COL_SEP         = '.';

    private $joinedRelated = [];

    public static function col(string $t, string $c): string
    {
        return $t.self::COL_SEP.$c;
    }

    public static function rel(...$items): string
    {
        return implode(self::REL_SEP, $items);
    }

    public function belongs_to(array $config = null): array
    {
        if ($config) {
            $this->_belongs_to = array_merge($this->_belongs_to, $config);
        }

        return parent::belongs_to();
    }

    public function has_one(array $config = null): array
    {
        if ($config) {
            $this->_has_one = array_merge($this->_has_one, $config);
        }

        return parent::has_one();
    }

    public function has_many(array $config = null): array
    {
        if ($config) {
            $this->_has_many = array_merge($this->_has_many, $config);
        }

        return parent::has_many();
    }

    public function load_with(array $config = null): array
    {
        if ($config) {
            $this->_load_with = array_merge($this->_load_with, $config);
        }

        return parent::load_with();
    }

    public function list_columns()
    {
        $cache_key = $this->table_name().':list_columns()';
        $columns   = \Kohana::cache($cache_key);

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
    public function isEqualTo(OrmInterface $model): bool
    {
        return ($this->table_name() === $model->table_name())
            && $this->pk() && $model->pk()
            && ($this->pk() === $model->pk());
    }

    /**
     * @return $this[]|OrmInterface[]
     * @throws \Kohana_Exception
     */
    public function get_all(): array
    {
        return $this->find_all()->as_array();
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
     * @return OrmInterface|mixed
     * @throws \Kohana_Exception
     */
    public function get_by_id($id, ?bool $allow_missing = null)
    {
        $model = $this->filter_primary_key($id)->find();

        $model = $model->loaded() ? $model : null;

        if (!$allow_missing && !$model) {
            throw new \Kohana_Exception('Model with id :id does not exists', [':id' => $id]);
        }

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
    public function object_primary_key(): string
    {
        return $this->object_column($this->primary_key());
    }

    /**
     * @param string $column
     *
     * @return string
     */
    public function object_column($column): string
    {
        return $this->object_name().'.'.$column;
    }

    /**
     * @param string $id
     *
     * @return $this
     */
    public function filter_primary_key(string $id)
    {
        return $this->where($this->object_primary_key(), '=', $id);
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
     * @param bool|null $unknownFirst
     *
     * @return $this|OrmInterface
     */
    public function order_by_field_sequence(string $name, array $sequence, bool $unknownFirst = null)
    {
        // Mysql marks unknown as "zero"
        $direction = $unknownFirst ? 'asc' : 'desc';
        $values    = $unknownFirst ? $sequence : \array_reverse($sequence);

        return $this->order_by(\DB::expr('FIELD('.$name.', "'.implode('", "', $values).'")'), $direction);
    }

    /**
     * Enables the query to be cached for a specified amount of time.
     * Cache lifetime is taken from config file called "clt.php" with structure <table name> => <seconds>
     *
     * @param string|integer $lifetime number of seconds to cache or cache lifetime config key
     *
     * @return  $this
     * @uses    Kohana::$cache_life
     */
    public function cached($lifetime = null)
    {
        // Do nothing if caching is not enabled
        if (!\Kohana::$caching) {
            return $this;
        }

        if (!is_int($lifetime)) {
            $key = $this->table_name();

            if (is_string($lifetime)) {
                $key .= '.'.$lifetime;
            }

            $group    = \Kohana::$config->load('clt');
            $lifetime = $group ? $group->get($key, 60) : 60;
        }

        parent::cached($lifetime);

        return $this;
    }

    /**
     * Returns TRUE if column exists in database
     *
     * @param string $name
     *
     * @return bool
     */
    public function has_column($name): bool
    {
        $columns = $this->list_columns();

        return isset($columns[$name]);
    }

    /**
     * Связывает элементы алиаса (с указанными первичными ключами) с текущей моделью
     *
     * @param string $alias
     * @param array  $far_keys
     *
     * @return $this
     */
    public function link_related($alias, array $far_keys)
    {
        return $this->_update_related_alias_foreign_key_field($alias, $far_keys, $this->pk());
    }

    /**
     * Отвязывает элементы алиаса (с указанными первичными ключами) от текущей модели
     *
     * @param string     $alias
     * @param array|NULL $far_keys
     *
     * @return $this
     */
    public function unlink_related($alias, array $far_keys = null)
    {
        // Если элементы не указаны
        if (!$far_keys) {
            // Попробуем достать их из алиаса
            $relation      = $this->_get_relation($alias);
            $relation_data = $relation->find_all()->as_array($relation->primary_key());
            $far_keys      = array_keys($relation_data);
        }

        // Если нет связанных элементов, то и делать нечего
        if (!$far_keys) {
            return $this;
        }

        return $this->_update_related_alias_foreign_key_field($alias, $far_keys, null);
    }

    /**
     * Проставляет полю внешнего ключа у алиаса значение первичного ключа текущей модели
     *
     * @param string   $alias
     * @param array    $far_keys
     * @param int|NULL $value
     *
     * @return $this
     */
    protected function _update_related_alias_foreign_key_field($alias, array $far_keys, $value)
    {
        $relation = $this->_get_relation($alias);

        $query = \DB::update($relation->table_name())
            ->set([$this->_has_many[$alias]['foreign_key'] => $value])
            ->where($relation->primary_key(), 'IN', $far_keys);

        $query->execute($this->_db);

        return $this;
    }

    /**
     * @param string $alias
     *
     * @return ORM
     */
    protected function _get_relation($alias)
    {
        return $this->get($alias);
    }

    /**
     * @param                                           $relation_name
     * @param \BetaKiller\Utils\Kohana\ORM\OrmInterface $model
     * @param bool|null                                 $or
     *
     * @return $this
     * @throws \Kohana_Exception
     */
    public function filter_related($relation_name, OrmInterface $model, bool $or = null)
    {
        $col = $relation_name.self::COL_SEP.$model->primary_key();
        $id  = $model->pk();

        $this->join_related($relation_name);

        if ($or) {
            $this->or_where($col, '=', $id);
        } else {
            $this->and_where($col, '=', $id);
        }

        return $this;
    }

    /**
     * @param                                             $relation_name
     * @param \BetaKiller\Utils\Kohana\ORM\OrmInterface[] $models
     *
     * @return $this
     */
    public function filter_related_multiple($relation_name, array $models)
    {
        $first = \reset($models);

        return $this
            ->join_related($relation_name)
            ->where($relation_name.self::COL_SEP.$first->primary_key(), 'IN', $models);
    }

    /**
     * Clears query builder.  Passing FALSE is useful to keep the existing
     * query conditions for another query.
     *
     * @param bool $next Pass FALSE to avoid resetting on the next call
     *
     * @return ORM
     */
    public function reset($next = true)
    {
        $this->joinedRelated = [];

        return parent::reset($next);
    }


    /**
     * @param string      $relation_alias
     * @param string      $table_alias
     *
     * @param string|null $type
     *
     * @return $this|OrmInterface
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \Kohana_Exception
     */
    public function join_related(string $relation_alias, string $table_alias = null, string $type = null)
    {
        $key = $relation_alias.$table_alias;

        // Prevent duplicate joins
        if (isset($this->joinedRelated[$key])) {
            return $this;
        }

        // Already joined via "load_with" (proceed join if table alias is defined)
        if (in_array($key, $this->_load_with, true) && !$this->isToManyAlias($relation_alias)) {
            return $this;
        }

        $joinAlias = $table_alias ?: $relation_alias;

        $this->joinedRelated[$key] = true;

        if (isset($this->_belongs_to[$relation_alias])) {
            $model       = $this->_related($relation_alias);
            $foreign_key = $this->_belongs_to[$relation_alias]['foreign_key'];

            $this->join_on_model(
                $model, $model->primary_key(), $this->object_column($foreign_key), $joinAlias
            );
        } elseif (isset($this->_has_one[$relation_alias])) {
            $model       = $this->_related($relation_alias);
            $foreign_key = $this->_has_one[$relation_alias]['foreign_key'];

            $this->join_on_model(
                $model, $model->object_column($foreign_key), $this->primary_key(), $joinAlias
            );
        } elseif (isset($this->_has_many[$relation_alias])) {
            $model = \ORM::factory($this->_has_many[$relation_alias]['model']);

            if (isset($this->_has_many[$relation_alias]['through'])) {
                // Grab has_many "through" relationship table
                $through_table       = $this->_has_many[$relation_alias]['through'];
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
                        $joinAlias
                    );
            } else {
                // Simple has_many relationship, search where target model's foreign key is this model's primary key
                return $this->join_on(
                    $model->table_name(),
                    $this->_has_many[$relation_alias]['foreign_key'],
                    $this->object_name().'.'.$this->primary_key(),
                    $joinAlias
                );
            }
        } else {
            throw new \Kohana_Exception('The related alias ":property" does not exist in the :class class', [
                ':property' => $relation_alias,
                ':class'    => get_class($this),
            ]);
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
     * @param             $table_name
     * @param             $table_key
     * @param             $equal_key
     * @param string|NULL $table_alias
     * @param string|NULL $join_type
     *
     * @return $this
     */
    protected function join_on($table_name, $table_key, $equal_key, $table_alias = null, $join_type = null)
    {
        if (!$table_alias) {
            $table_alias = $table_name;
        }

        return $this
            ->join([$table_name, $table_alias], $join_type ?: 'LEFT')
            ->on($table_alias.'.'.$table_key, '=', $equal_key);
    }

    protected function join_on_model(ORM $model, $on_left, $on_right, $table_alias = null, string $type = null)
    {
        return $this->join_on(
            $model->table_name(),
            $on_left,
            $on_right,
            $table_alias ?: $model->object_name(),
            $type
        );
    }

    /**
     * @param string $term           String to search for
     * @param array  $search_columns Columns to search where
     *
     * @return $this
     */
    public function search_query($term, array $search_columns)
    {
        if ($term) {
            // Keep term lowercase to simplify search on case-sensitive encodings
            $term = mb_strtolower($term);

            // Split into words
            $words = explode(' ', $term);

            if ($words === false) {
                throw new \InvalidArgumentException();
            }

            // Search for full term too
            if (count($words) > 1) {
                $words[] = $term;
            }

            // Make AND for every word
            foreach ($words as $word) {
                $this->and_having_open();

                foreach ($search_columns as $search_column) {
                    if (\is_string($search_column)) {
                        $search_column = $this->_db->quote_column($search_column);
                    }

                    $this->or_having(
                        \DB::expr(sprintf('LOWER(%s)', $search_column)),
                        'LIKE',
                        '%'.$word.'%'
                    );
                }

                $this->and_having_close();
            }
        }

        return $this->cached('search');
    }

    public function autocomplete(string $term, array $search_fields, ?bool $as_key_label_pairs = null): array
    {
        $as_key_label_pairs = $as_key_label_pairs ?? false;

        /** @var ORM[] $results */
        $results = $this->search_query($term, $search_fields)->find_all();

        $output = [];

        foreach ($results as $item) {
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
    protected function autocomplete_formatter(): array
    {
        return [
            'id'   => $this->autocomplete_formatter_key(),
            'text' => $this->autocomplete_formatter_label(),
        ];
    }

    /**
     * Returns "key" for autocomplete formatter
     *
     * @return int|string
     */
    protected function autocomplete_formatter_key(): string
    {
        return (string)$this->get_id();
    }

    /**
     * Returns "label" for autocomplete formatter
     *
     * @return string
     * @throws \Kohana_Exception
     */
    protected function autocomplete_formatter_label(): string
    {
        throw new \Kohana_Exception('Implement :class::autocomplete_formatter_label() method',
            [':class' => get_class($this)]);
    }

    /**
     * Creates custom SELECT query from current db builder queue
     *
     * @param array|null $columns
     * @param bool|null  $useLoadWith
     */
    public function custom_select(array $columns = null, bool $useLoadWith = null): static
    {
        if ($useLoadWith) {
            $this->use_load_with();
        }

        $this->_build(\Database::SELECT);

        $this->_db_builder
            ->select_array($columns ?? [])
            ->from([$this->_table_name, $this->_object_name]);

        return $this;
    }

    /**
     * @return \Database_Result|int
     */
    public function execute_query(): Database_Result|int
    {
        return $this->_db_builder->execute($this->_db);
    }

    public function compile(): string
    {
        return $this->_db_builder->compile($this->_db);
    }

    /**
     * Use all _load_with relations in current SELECT query
     *
     * @return $this
     */
    protected function use_load_with(): static
    {
        foreach ($this->_load_with as $alias) {
            // Skip *-to-many aliases
            if ($this->isToManyAlias($alias)) {
                continue;
            }

            // Bind auto relationships
            $this->with($alias);
        }

        return $this;
    }

    protected function select_all_columns()
    {
        $this->_db_builder->select_array($this->_build_select());

        return $this;
    }

    public function compile_as_subquery(): Database_Expression
    {
        return \DB::expr('('.$this->compile().')');
    }

    /**
     * Compile current query as a subquery and make COUNT(*) with from it
     *
     * @return integer
     */
    public function compile_as_subquery_and_count_all(): int
    {
        // Required for complex selects with GROUP BY and WHERE on related entities
        // Select all columns
        $this->custom_select($this->_build_select(), true);

        $sql = 'SELECT COUNT(*) AS total FROM ('.$this->compile().') AS x';

        $query = \DB::query(\Database::SELECT, $sql);

        $result = $query->execute($this->_db);

        return (int)$result->get('total');
    }

    /**
     * Count the number of records in the table.
     *
     * @return integer
     */
    public function count_all()
    {
        // Allow counting complex queries with GROUP BY
        return $this->compile_as_subquery_and_count_all();
    }

    /**
     * Get field alias for COUNT(N) expression
     *
     * @return string
     */
    public function get_sql_counter_alias(): string
    {
        return $this->object_name().'_counter';
    }

    /**
     * Get field alias for GROUP_CONCAT(N) expression
     *
     * @param string $field
     *
     * @return string
     */
    public function get_sql_column_group_concat_alias($field): string
    {
        return $this->object_name().'_'.$field.'_group_concat';
    }

    /**
     * Get field alias for CONCAT(N) expression
     *
     * @param string $field
     *
     * @return string
     */
    public function get_sql_column_concat_alias($field): string
    {
        return $this->object_name().'_'.$field.'_concat';
    }

    /**
     * @param $columns
     *
     * @return $this
     */
    protected function select_array($columns)
    {
        $this->_db_builder->select_array($columns);

        return $this;
    }


    /**
     * Delete all objects in the associated table. This does NOT destroy
     * relationships that have been created with other objects.
     *
     * @chainable
     * @return  int
     * @throws \BetaKiller\Exception
     */
    public function delete_all(): int
    {
        $this->_build(\Database::DELETE);

        return $this->_db_builder->execute($this->_db);
    }

    /**
     * @param array $ids
     * @param bool  $not_in
     *
     * @return $this|\ORM|static
     */
    public function filter_ids(array $ids, $not_in = null)
    {
        return $this->where($this->object_primary_key(), ($not_in ?? false) ? 'NOT IN' : 'IN', $ids);
    }

    /**
     * Checks whether a column value is unique.
     * Excludes itself if loaded.
     *
     * @param string   $field                the field to check for uniqueness
     * @param callable $additional_filtering Additional filtering callback
     *
     * @return  bool     whatever the value is unique
     */
    public function unique_field_value($field, callable $additional_filtering = null): bool
    {
        $value = $this->get($field);

        // Skip check if no value present
        if (!$value) {
            return true;
        }

        $orm = $this->model_factory();

        if ($additional_filtering) {
            $additional_filtering($orm);
        }

        $model = $orm
            ->where($this->object_name().'.'.$field, '=', $value)
            ->find();

        if ($this->loaded()) {
            return (!($model->loaded() && $model->pk() !== $this->pk()));
        }

        return !$model->loaded();
    }

    /**
     * Converts value of MySQL datetime column to PHP DateTime object
     *
     * @param string             $name
     * @param \DateTimeZone|NULL $tz
     *
     * @return \DateTimeImmutable|null
     */
    protected function get_datetime_column_value($name, \DateTimeZone $tz = null): ?DateTimeImmutable
    {
        $value = $this->get($name);

        if (!$value) {
            return null;
        }

        $date = DateTimeImmutable::createFromFormat(self::FORMAT_DATETIME, $value, static::getDbTimeZone());

        if ($date === false) {
            throw new LogicException(
                sprintf('Incorrect datetime format "%s" in %s.%s', $value, $this->table_name(), $name)
            );
        }

        return $tz ? $date->setTimezone($tz) : $date;
    }

    /**
     * Sets value of MySQL datetime column from PHP DateTime object
     *
     * @param string             $name
     * @param \DateTimeImmutable $value
     *
     * @return $this
     */
    protected function set_datetime_column_value(string $name, DateTimeImmutable $value)
    {
        return $this->set($name, $this->formatDateTime($value));
    }

    public function filter_datetime_column_value(
        Database_Expression|string $name,
        DateTimeImmutable          $value,
        string                     $operator,
        bool                       $or = null
    ) {
        return $or
            ? $this->or_where($name, $operator, $this->formatDateTime($value))
            : $this->and_where($name, $operator, $this->formatDateTime($value));
    }

    public function filter_datetime_column_value_between(string $name, DateTimeImmutable $from, DateTimeImmutable $to)
    {
        return $this->where($name, 'BETWEEN', [$this->formatDateTime($from), $this->formatDateTime($to)]);
    }

    /**
     * Converts value of MySQL date column to PHP DateTime object
     *
     * @param string             $name
     * @param \DateTimeZone|NULL $tz
     *
     * @return \DateTimeImmutable|null
     */
    protected function get_date_column_value($name, DateTimeZone $tz = null): ?DateTimeImmutable
    {
        $value = $this->get($name);

        if (!$value) {
            return null;
        }

        $date = DateTimeImmutable::createFromFormat(self::FORMAT_DATE, $value, static::getDbTimeZone());

        if ($date === false) {
            throw new LogicException(
                sprintf('Incorrect date format "%s" in %s.%s', $value, $this->table_name(), $name)
            );
        }

        if ($tz) {
            $date = $date->setTimezone($tz);
        }

        return $date->setTime(0, 0, 0);
    }

    /**
     * Sets value of MySQL date column from PHP DateTime object
     *
     * @param string             $name
     * @param \DateTimeImmutable $value
     *
     * @return $this
     */
    protected function set_date_column_value(string $name, DateTimeImmutable $value)
    {
        return $this->set($name, $this->formatDate($value));
    }

    public static function getDbTimeZone(): DateTimeZone
    {
        // Assume DB timezone is equal to PHP timezone
        $tz = \getenv('DATABASE_TIMEZONE') ?: \date_default_timezone_get();

        if (!$tz) {
            throw new LogicException('Missing default timezone for PHP');
        }

        return new DateTimeZone($tz);
    }

    public function filter_date_column_value(string $name, DateTimeImmutable $value, string $operator)
    {
        return $this->where($name, $operator, $this->formatDate($value));
    }

    public function filter_date_column_value_between(string $name, DateTimeImmutable $from, DateTimeImmutable $to)
    {
        return $this->where($name, 'BETWEEN', [$this->formatDate($from), $this->formatDate($to)]);
    }

    /**
     * @param int         $pk
     * @param string|null $name
     *
     * @return OrmInterface|mixed
     */
    public function model_factory($pk = null, $name = null)
    {
        return static::factory($name ?: $this->object_name(), $pk);
    }

    /**
     * @param array $columns
     */
    protected function serialize_columns(array $columns)
    {
        $this->_serialize_columns = $columns;
    }

    private function formatDateTime(DateTimeImmutable $dateTime): string
    {
        return $dateTime->setTimezone(static::getDbTimeZone())->format(self::FORMAT_DATETIME);
    }

    private function formatDate(DateTimeImmutable $date): string
    {
        return $date->setTimezone(static::getDbTimeZone())->format(self::FORMAT_DATE);
    }
}
