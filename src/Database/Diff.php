<?php

namespace DatabaseDiff\Database;

use Doctrine\DBAL\Schema\ColumnDiff;
use Doctrine\DBAL\Schema\Index;
use Doctrine\DBAL\Schema\TableDiff;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Class Diff
 * @package App\Lib\Database
 * 1.对比数据库表，只做新增不作删除
 * 2.对比表不同字段，只做新增修改不作删除
 */
class Diff
{

    protected $sourceManager;

    protected $manager;

    protected $sql = [];

    public function __construct($target_connection)
    {
        $this->sourceManager = new Manager(DB::getDefaultConnection());

        $this->manager = new Manager($target_connection);
    }

    public function getSourceManager()
    {
        return $this->sourceManager;
    }

    public function getManager()
    {
        return $this->manager;
    }

    protected function diffTable()
    {

        $oldTables = $this->sourceManager->getTables();
        $tables = $this->manager->getTables();
        $diffTables = array_diff($oldTables, $tables);
        foreach ($diffTables as $tableName) {
            $table = $this->sourceManager->getTable($tableName);
            $this->sql = array_merge($this->sql, $this->manager->getDoctrineSchemaManager()
                ->getDatabasePlatform()
                ->getCreateTableSQL($table));
        }
    }

    protected function diffTableColumn()
    {

        $tables = $this->sourceManager->getTables();
        foreach ($tables as $tableName) {
            $addColumns = [];
            $modifiedColumns = [];
            $addedIndexes = [];
            $table = $this->sourceManager->getTable($tableName);
            $columns = $table->getColumns();
            foreach ($columns as $column) {
                if ($this->manager->getTable($tableName)) {
                    if ($this->manager->getTable($tableName)->hasColumn($column->getName())) {
                        //对比修改字段
                        $alterColumn = $this->manager->getTable($tableName)->getColumn($column->getName());
                        if ($alterColumn->toArray() !== $column->toArray()) {
                            $modifiedColumns[] = new ColumnDiff($column->getName(), $column);
                        }
                    } else {
                        //对比新增字段
                        $addColumns[] = $column;
                    }
                    $addedIndexes = $this->diffTableIndex($tableName);
                }
            }
            $tableDiff = new TableDiff($tableName, $addColumns, $modifiedColumns,[],$addedIndexes);
            $this->sql = array_merge($this->sql, $this->manager->getDoctrineSchemaManager()
                ->getDatabasePlatform()
                ->getAlterTableSQL($tableDiff));
        }
    }
    
    protected function diffTableIndex($tableName){
        $oldIndexs = [];
        $table = $this->sourceManager->getTable($tableName);
        if($table) {
            foreach ($table->getIndexes() as $index) {
                $oldIndexs[] = $index->getName();
            }
        }
        $indexs = [];
        $table = $this->manager->getTable($tableName);
        if($table){
            foreach ($table->getIndexes() as $index){
                $indexs[] = $index->getName();
            }
        }
        $indexs = array_diff($oldIndexs, $indexs);
        $diffIndex = [];
        foreach ($indexs as $index){
           $diffIndex[] = $this->sourceManager->getTable($tableName)->getIndex($index);
        }
        return $diffIndex;
    }
    /**
     * 预览sql
     * @return array
     */
    public function preview()
    {
        $this->diffTable();
        $this->diffTableColumn();
        return $this->sql;
    }

    /**
     * 执行
     * @return bool
     */
    public function exec()
    {
        return $this->manager->execSql($this->sql);
    }
}
