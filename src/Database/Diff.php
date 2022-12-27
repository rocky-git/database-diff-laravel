<?php

namespace DatabaseDiff\Database;
use Doctrine\DBAL\Schema\ColumnDiff;
use Doctrine\DBAL\Schema\TableDiff;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Class Diff
 * @package App\Lib\Database
   1.对比数据库表，只做新增不作删除
   2.对比表不同字段，只做新增修改不作删除
 */
class Diff
{

    protected $sourceManager;

    protected $manager;

    protected $sql = [];

    public function __construct($source_connection,$connection = null)
    {
        $this->sourceManager = new Manager($source_connection);

        $this->manager = new Manager($connection ??  DB::getDefaultConnection());
    }

    protected function diffTable(){

        $oldTables = $this->sourceManager->getTables();
        $tables = $this->manager->getTables();
        $diffTables =  array_diff($oldTables,$tables);
        foreach ($diffTables as $tableName){
            $table = $this->sourceManager->getTable($tableName);
            $this->sql = array_merge($this->sql,$this->manager->getDoctrineSchemaManager()
                ->getDatabasePlatform()
                ->getCreateTableSQL($table));
//            $this->manager->getDoctrineSchemaManager()->createTable($table);
        }
    }
    protected function diffTableColumn(){

        $tables = $this->sourceManager->getTables();
        foreach ($tables as $tableName){
            $addColumns = [];
            $modifiedColumns = [];
            $columns = $this->sourceManager->getTable($tableName)->getColumns();
            foreach ($columns as $column){
                if($this->manager->getTable($tableName)){
                    if($this->manager->getTable($tableName)->hasColumn($column->getName())){
                        //对比修改字段
                        $alterColumn = $this->manager->getTable($tableName)->getColumn($column->getName());
                        if($alterColumn->toArray() !== $column->toArray()){
                            $modifiedColumns[] = new ColumnDiff($column->getName(), $column);
                        }
                    }else{
                        //对比新增字段
                        $addColumns[] = $column;
                    }
                }
            }
            $tableDiff = new TableDiff($tableName,$addColumns,$modifiedColumns);
            $this->sql = array_merge($this->sql,$this->manager->getDoctrineSchemaManager()
                ->getDatabasePlatform()
                ->getAlterTableSQL($tableDiff));
//            $this->manager->getDoctrineSchemaManager()->alterTable($tableDiff);
        }
    }

    /**
     * 预览sql
     * @return array
     */
    public function preview(){
        $this->diffTable();
        $this->diffTableColumn();
        return $this->sql;
    }

    /**
     * 执行
     * @return bool
     */
    public function exec(){
        return $this->manager->execSql($this->sql);
    }
}
