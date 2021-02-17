<?php
namespace BetaKiller\Utils\Kohana\ORM;

use BetaKiller\Exception;
use BetaKiller\Utils\Kohana\ORM;
use ORM_Validation_Exception;
use Validation;

interface OrmInterface extends OrmQueryBuilderInterface
{
    // Core methods

    /**
     * Reload column definitions.
     *
     * @chainable
     *
     * @param boolean $force Force reloading
     *
     * @return  ORM
     */
    public function reload_columns($force = false);

    /**
     * Unloads the current object and clears the status.
     *
     * @chainable
     * @return ORM
     */
    public function clear();

    /**
     * Reloads the current object from the database.
     *
     * @chainable
     * @return ORM
     */
    public function reload();

    /**
     * Checks if object data is set.
     *
     * @param string $column Column name
     *
     * @return boolean
     */
    public function __isset($column);

    /**
     * Unsets object data.
     *
     * @param string $column Column name
     *
     * @return void
     */
    public function __unset($column);

    /**
     * Displays the primary key of a model when it is converted to a string.
     *
     * @return string
     */
    public function __toString();

    /**
     * Allows serialization of only the object data and state, to prevent
     * "stale" objects being unserialized, which also requires less memory.
     *
     * @return string
     */
    public function serialize();

    /**
     * Check whether the model data has been modified.
     * If $field is specified, checks whether that field was modified.
     *
     * @param string $field field to check for changes
     *
     * @return  bool  Whether or not the field has changed
     */
    public function changed($field = null);

    /**
     * Prepares the database connection and reloads the object.
     *
     * @param string $data String for unserialization
     *
     * @return  void
     */
    public function unserialize($data);

    /**
     * Handles retrieval of all model values, relationships, and metadata.
     * [!!] This should not be overridden.
     *
     * @param string $column Column name
     *
     * @return  mixed
     */
    public function __get($column);

    /**
     * Handles getting of column
     * Override this method to add custom get behavior
     *
     * @param string $column Column name
     *
     * @return mixed|OrmInterface
     * @throws Exception
     */
    public function get($column);

    /**
     * Base set method.
     * [!!] This should not be overridden.
     *
     * @param string $column Column name
     * @param mixed  $value  Column value
     *
     * @return void
     */
    public function __set($column, $value);

    /**
     * Handles setting of columns
     * Override this method to add custom set behavior
     *
     * @param string $column Column name
     * @param mixed  $value  Column value
     *
     * @return $this
     * @throws Exception
     */
    public function set($column, $value);

    /**
     * Set values from an array with support for one-one relationships.  This method should be used
     * for loading in post data, etc.
     *
     * @param array $values   Array of column => val
     * @param array $expected Array of keys to take from $values
     *
     * @return $this
     */
    public function values(array $values, array $expected = null);

    /**
     * Returns the values of this object as an array, including any related one-one
     * models that have already been loaded using with()
     *
     * @return array
     */
    public function as_array();

    /**
     * Binds another one-to-one object to this model.  One-to-one objects
     * can be nested using 'object1:object2' syntax
     *
     * @param string $target_path Target model to bind to
     *
     * @return $this
     */
    public function with($target_path);


    /**
     * Rule definitions for validation
     *
     * @return array
     */
    public function rules();

    /**
     * Filter definitions for validation
     *
     * @return array
     */
    public function filters();

    /**
     * Label definitions for validation
     *
     * @return array
     */
    public function labels();

    /**
     * Validates the current model's data
     *
     * @param Validation $extra_validation Validation object
     *
     * @return ORM
     * @throws ORM_Validation_Exception
     */
    public function check(Validation $extra_validation = null);

    /**
     * Insert a new object to the database
     *
     * @param Validation $validation Validation object
     *
     * @return $this
     * @throws Exception
     */
    public function create(Validation $validation = null);

    /**
     * Updates a single record or multiple records
     *
     * @chainable
     *
     * @param Validation $validation Validation object
     *
     * @return $this
     * @throws \ORM_Validation_Exception
     * @throws \Kohana_Exception
     */
    public function update(Validation $validation = null);

    /**
     * Updates or Creates the record depending on loaded()
     *
     * @chainable
     *
     * @param Validation $validation Validation object
     *
     * @return $this
     * @throws \ORM_Validation_Exception
     */
    public function save(Validation $validation = null);

    /**
     * Deletes a single record while ignoring relationships.
     *
     * @chainable
     * @return $this
     * @throws \Kohana_Exception
     */
    public function delete();

    /**
     * Tests if this object has a relationship to a different model,
     * or an array of different models. When providing far keys, the number
     * of relations must equal the number of keys.
     *
     *
     *     // Check if $model has the login role
     *     $model->has('roles', ORM::factory('role', array('name' => 'login')));
     *     // Check for the login role if you know the roles.id is 5
     *     $model->has('roles', 5);
     *     // Check for all of the following roles
     *     $model->has('roles', array(1, 2, 3, 4));
     *     // Check if $model has any roles
     *     $model->has('roles')
     *
     * @param string $alias    Alias of the has_many "through" relationship
     * @param mixed  $far_keys Related model, primary key, or an array of primary keys
     *
     * @return boolean
     */
    public function has($alias, $far_keys = null);

    /**
     * Tests if this object has a relationship to a different model,
     * or an array of different models. When providing far keys, this function
     * only checks that at least one of the relationships is satisfied.
     *
     *     // Check if $model has the login role
     *     $model->has('roles', ORM::factory('role', array('name' => 'login')));
     *     // Check for the login role if you know the roles.id is 5
     *     $model->has('roles', 5);
     *     // Check for any of the following roles
     *     $model->has('roles', array(1, 2, 3, 4));
     *     // Check if $model has any roles
     *     $model->has('roles')
     *
     * @param string $alias    Alias of the has_many "through" relationship
     * @param mixed  $far_keys Related model, primary key, or an array of primary keys
     *
     * @return boolean
     */
    public function has_any($alias, $far_keys = null);

    /**
     * Returns the number of relationships
     *
     *     // Counts the number of times the login role is attached to $model
     *     $model->has('roles', ORM::factory('role', array('name' => 'login')));
     *     // Counts the number of times role 5 is attached to $model
     *     $model->has('roles', 5);
     *     // Counts the number of times any of roles 1, 2, 3, or 4 are attached to
     *     // $model
     *     $model->has('roles', array(1, 2, 3, 4));
     *     // Counts the number roles attached to $model
     *     $model->has('roles')
     *
     * @param string $alias    Alias of the has_many "through" relationship
     * @param mixed  $far_keys Related model, primary key, or an array of primary keys
     *
     * @return integer
     */
    public function count_relations($alias, $far_keys = null);

    /**
     * Adds a new relationship to between this model and another.
     *
     *     // Add the login role using a model instance
     *     $model->add('roles', ORM::factory('role', array('name' => 'login')));
     *     // Add the login role if you know the roles.id is 5
     *     $model->add('roles', 5);
     *     // Add multiple roles (for example, from checkboxes on a form)
     *     $model->add('roles', array(1, 2, 3, 4));
     *
     * @param string $alias    Alias of the has_many "through" relationship
     * @param mixed  $far_keys Related model, primary key, or an array of primary keys
     *
     * @return $this
     */
    public function add($alias, $far_keys);

    /**
     * Removes a relationship between this model and another.
     *
     *     // Remove a role using a model instance
     *     $model->remove('roles', ORM::factory('role', array('name' => 'login')));
     *     // Remove the role knowing the primary key
     *     $model->remove('roles', 5);
     *     // Remove multiple roles (for example, from checkboxes on a form)
     *     $model->remove('roles', array(1, 2, 3, 4));
     *     // Remove all related roles
     *     $model->remove('roles');
     *
     * @param string $alias    Alias of the has_many "through" relationship
     * @param mixed  $far_keys Related model, primary key, or an array of primary keys
     *
     * @return ORM
     */
    public function remove($alias, $far_keys = null);

    /**
     * Count the number of records in the table.
     *
     * @return integer
     */
    public function count_all();

    /**
     * Proxy method to Database list_columns.
     *
     * @return array
     */
    public function list_columns();

    /**
     * Returns the value of the primary key
     *
     * @return mixed Primary key
     */
    public function pk();

    /**
     * Returns last executed query
     *
     * @return string
     */
    public function last_query();

    /**
     * @return string
     */
    public function object_name();

    /**
     * @return string
     */
    public function object_plural();

    /**
     * @return bool
     */
    public function loaded();

    /**
     * @return bool
     */
    public function saved();

    /**
     * @return string
     */
    public function primary_key();

    /**
     * @return string
     */
    public function table_name();

    /**
     * @return array
     */
    public function table_columns();

    /**
     * @return array
     */
    public function original_values();

    /**
     * @return string
     */
    public function created_column();

    /**
     * @return string
     */
    public function updated_column();

    /**
     * @return Validation
     */
    public function validation();

    /**
     * @return array
     */
    public function object();

    /**
     * @return string
     */
    public function errors_filename();


    /**
     * Set the value of a parameter in the query.
     *
     * @param string $param parameter key to replace
     * @param mixed  $value value to use
     *
     * @return  $this
     */
    public function param($param, $value);

    /**
     * Checks whether a column value is unique.
     * Excludes itself if loaded.
     *
     * @param string $field the field to check for uniqueness
     * @param mixed  $value the value to check for uniqueness
     *
     * @return  bool     whteher the value is unique
     */
    public function unique($field, $value);


    // Extended methods

    public function belongs_to(array $config = null);

    public function has_one(array $config = null);

    public function has_many(array $config = null);

    public function load_with(array $config = null);

    /**
     * @param \BetaKiller\Utils\Kohana\ORM\OrmInterface $model
     *
     * @return bool
     */
    public function isEqualTo(OrmInterface $model): bool;

    /**
     * @return $this[]|OrmInterface[]
     * @throws \Kohana_Exception
     */
    public function get_all(): array;

    public function get_id();

    /**
     * @param int $value
     *
     * @return $this
     */
    public function set_id($value);

    /**
     * @param int  $id
     * @param bool $allow_missing
     *
     * @return $this
     * @throws \Kohana_Exception
     */
    public function get_by_id($id, ?bool $allow_missing = null);

    /**
     * @return $this
     */
    public function group_by_primary_key();

    /**
     * @return string
     */
    public function object_primary_key();

    /**
     * @param string $column
     *
     * @return string
     */
    public function object_column($column);

    /**
     * @param string $id
     *
     * @return $this
     */
    public function filter_primary_key(string $id);

    /**
     * @return $this
     */
    public function randomize();

    /**
     * @param string    $name
     * @param array     $sequence
     *
     * @param bool|null $unknownFirst
     *
     * @return $this
     */
    public function order_by_field_sequence(string $name, array $sequence, bool $unknownFirst = null);

    /**
     * Returns TRUE if column exists in database
     *
     * @param string $name
     *
     * @return bool
     */
    public function has_column($name);

    /**
     * Связывает элементы алиаса (с указанными первичными ключами) с текущей моделью
     *
     * @param string $alias
     * @param array  $far_keys
     *
     * @return $this
     */
    public function link_related($alias, array $far_keys);

    /**
     * Отвязывает элементы алиаса (с указанными первичными ключами) от текущей модели
     *
     * @param string     $alias
     * @param array|NULL $far_keys
     *
     * @return $this
     */
    public function unlink_related($alias, array $far_keys = null);

    /**
     * @param                                           $relation_name
     * @param \BetaKiller\Utils\Kohana\ORM\OrmInterface $model
     * @param bool|null                                 $or
     *
     * @return $this
     */
    public function filter_related($relation_name, OrmInterface $model, bool $or = null);

    /**
     * @param                                             $relation_name
     * @param \BetaKiller\Utils\Kohana\ORM\OrmInterface[] $models
     *
     * @return $this
     */
    public function filter_related_multiple($relation_name, array $models);

    /**
     * @param string      $relation_alias
     * @param string      $table_alias
     *
     * @param string|null $type
     *
     * @return $this
     */
    public function join_related(string $relation_alias, string $table_alias = null, string $type = null);

    /**
     * @param bool|null $buildSelect
     *
     * @return string
     */
    public function compile(bool $buildSelect = null): string;

    /**
     * Compile current query as a subquery and make COUNT(*) with from it
     *
     * @return integer
     */
    public function compile_as_subquery_and_count_all();

    /**
     * Get field alias for COUNT(N) expression
     *
     * @return string
     */
    public function get_sql_counter_alias();

    /**
     * Get field alias for GROUP_CONCAT(N) expression
     *
     * @param string $field
     *
     * @return string
     */
    public function get_sql_column_group_concat_alias($field);

    /**
     * Delete all objects in the associated table. This does NOT destroy
     * relationships that have been created with other objects.
     *
     * @chainable
     * @return  ORM
     */
    public function delete_all();

    /**
     * Get field alias for CONCAT(N) expression
     *
     * @param string $field
     *
     * @return string
     */
    public function get_sql_column_concat_alias($field);

    /**
     * @param array $ids
     * @param bool  $not_in
     *
     * @return $this|\ORM|static
     */
    public function filter_ids(array $ids, $not_in = false);

    /**
     * Checks whether a column value is unique.
     * Excludes itself if loaded.
     *
     * @param string   $field                the field to check for uniqueness
     * @param callable $additional_filtering Additional filtering callback
     *
     * @return  bool     whatever the value is unique
     */
    public function unique_field_value($field, callable $additional_filtering = null);

    /**
     * @param int         $pk
     * @param string|null $name
     *
     * @return $this
     */
    public function model_factory($pk = null, $name = null);

    /**
     * @param string $term          Search term for
     * @param array  $search_fields Array of fields to search in
     * @param bool   $as_key_label_pairs
     *
     * @return string[]
     */
    public function autocomplete(string $term, array $search_fields, ?bool $as_key_label_pairs = null): array;

    /**
     * @param string $term           String to search for
     * @param array  $search_columns Columns to search where
     *
     * @return $this
     */
    public function search_query($term, array $search_columns);

    /**
     * @param string             $name
     * @param \DateTimeImmutable $value
     * @param string             $operator
     *
     * @return $this
     */
    public function filter_datetime_column_value(string $name, \DateTimeImmutable $value, string $operator);

    /**
     * @param string             $name
     * @param \DateTimeImmutable $from
     * @param \DateTimeImmutable $to
     *
     * @return $this
     */
    public function filter_datetime_column_value_between(
        string $name,
        \DateTimeImmutable $from,
        \DateTimeImmutable $to
    );

    /**
     * @param string             $name
     * @param \DateTimeImmutable $value
     * @param string             $operator
     *
     * @return $this
     */
    public function filter_date_column_value(string $name, \DateTimeImmutable $value, string $operator);

    /**
     * @param string             $name
     * @param \DateTimeImmutable $from
     * @param \DateTimeImmutable $to
     *
     * @return $this
     */
    public function filter_date_column_value_between(string $name, \DateTimeImmutable $from, \DateTimeImmutable $to);
}
