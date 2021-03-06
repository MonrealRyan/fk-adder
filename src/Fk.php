<?php

namespace FkAdder;

use Illuminate\Support\Facades\Config;

class Fk
{
    /**
     * Table blueprint.
     *
     * @var \Illuminate\Database\Schema\Blueprint
     */
    protected $table;

    /**
     * Array of foreign keys declaration.
     *
     * @var array
     */
    public static $foreignKeys = [];

    /**
     * Constructor.
     *
     * @param \Illuminate\Database\Schema\Blueprint $table
     */
    public function __construct($table)
    {
        $this->table = $table;
    }

    /**
     * Instantiate.
     *
     * @param  \Illuminate\Database\Schema\Blueprint $table
     * @return static
     */
    public static function make($table)
    {
        return new static($table);
    }

    /**
     * Add a foreign key to table and defer its foreign key creation.
     *
     * @param string $fk
     * @param string $column
     * @param string $keyName
     * @param string $onDelete
     * @param string $onUpdate
     * @return \Illuminate\Support\Fluent
     */
    public function add($fk, $column = null, $keyName = null, $onDelete = null, $onUpdate = null)
    {
        $baseFk = $this->baseFk($fk);

        $column = $column ?: $baseFk->defaultColumn();

        static::$foreignKeys[] = [
            'column'          => $column,
            'key_name'        => $keyName,
            'table'           => $this->table->getTable(),
            'reference_table' => $baseFk->referenceTable(),
            'primary_key'     => $baseFk->primaryKey,
            'on_delete'       => $onDelete ?: $baseFk->onDelete,
            'on_update'       => $onUpdate ?: $baseFk->onUpdate,
        ];

        return $baseFk->createFkColumn($column);
    }

    /**
     * Return the baseFk for foreign key column creation.
     *
     * @param  string $fk
     * @return \FkAdder\ColumnCreator
     */
    public function baseFk($fk)
    {
        if (!is_null(BaseFk::getFkDatatype($fk))) {
            return new BaseFk($this->table, $fk);
        }

        $class = Config::get('fk_adder.fk_namespace').'\\'.studly_case($fk);

        return new $class($this->table);
    }

    /**
     * Alias of add(). Older version uses addFk(). Recommended to use add().
     * Add a foreign key to table and defer its foreign key creation.
     *
     * @deprecated
     * @param string $fk
     * @param string $column
     * @param string $keyName
     * @param string $onDelete
     * @param string $onUpdate
     * @return \Illuminate\Support\Fluent
     */
    public function addFk($fk, $column = null, $keyName = null, $onDelete = null, $onUpdate = null)
    {
        return $this->add($fk, $column, $keyName, $onDelete, $onUpdate);
    }
}
