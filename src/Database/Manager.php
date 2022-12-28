<?php


namespace DatabaseDiff\Database;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Manager
{
    /**
     * @var \Illuminate\Database\Schema\Builder
     */
    protected $schemaBuilder;
    /**
     * @var \Doctrine\DBAL\Schema\AbstractSchemaManager
     */
    protected $doctrineSchemaManager;

    protected $listTables = [];
    protected $_conn;
    public function __construct($connection)
    {
        $this->_conn = DB::connection($connection);
        $this->schemaBuilder = $this->_conn->getSchemaBuilder();
        $this->doctrineSchemaManager = $this->schemaBuilder->getConnection()
            ->getDoctrineSchemaManager();
    }

    /**
     * 备份sql
     * @return array
     */
    public function backupSql(){
        $sql = [];
        $tables = $this->listTables();
        foreach ($tables as $table){
            $sql = array_merge($sql, $this->getDoctrineSchemaManager()
                ->getDatabasePlatform()
                ->getCreateTableSQL($table));
        }
        return $sql;
    }
    /**
     * 获取唯一标识
     * @return string
     */
    public function identifier(){
        $tables = $this->listTables();
        $data = [];
        foreach ($tables as $table){
            $columns = $table->getColumns();
            foreach ($columns as $column){
                $data[$table->getName()][$column->getName()] = $column->toArray();
            }

        }
        return substr(md5(serialize($data)),8,16);
    }

    public function listTables($cache = false){
        if(empty($this->listTables) || $cache){
            $this->listTables = [];
            foreach ($this->doctrineSchemaManager->listTables() as $table){
                $this->listTables[$table->getName()] = $table;
            }
        }
        return $this->listTables;
    }

    /**
     * @return \Doctrine\DBAL\Schema\AbstractSchemaManager
     */
    public function getDoctrineSchemaManager(){
        return $this->doctrineSchemaManager;
    }
    /**
     * 获取表
     * @param string $name 表名
     * @return \Doctrine\DBAL\Schema\Table
     */
    public function getTable(string $name){
        return $this->listTables()[$name] ?? null;
    }

    /**
     * 获取所有表名
     * @return array
     */
    public function getTables(){
        $tables = [];
        foreach ($this->listTables() as $table){
           $tables[] = $table->getName();
        }
        return $tables;
    }

    /**
     * 执行sql
     * @param array|string $sql
     * @return bool
     */
    public function execSql($sql){
        if(is_array($sql)){
            $sql = implode(';',$sql);
        }
        return $this->_conn->unprepared($sql);
    }
}
