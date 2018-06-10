<?php

namespace Neo4j;

use Everyman\Neo4j\Cypher\Query;

class Neo4jResultException extends \FuelException {}
class Result {

    protected $result;
    protected $columns;


    /**
     * Cypher Queryの実行
     *
     * @param  \Everyman\Neo4j\Client $client
     * @param  string $cypher
     * @param  array  $parm
     * @return \Neo4j\Result
     */
    public static function query(\Everyman\Neo4j\Client $client, $cypher, array $parm = []) {
        try {
            $static = new static();
            $static->result     = (new Query($client, $cypher, $parm))->getResultSet();
            $static->columns    = array_flip( $static->result->getColumns() );
        } catch (\Exception $e) {
            throw new Neo4jResultException($e->getMessage(), $e->getCode(). $e->getPrevious());
        }

        return  $static;
    }


    /**
     * Cypher Queryの結果の取得
     *
     * @return \Everyman\Neo4j\Query\ResultSet
     * @access public
     */
    public function result() {
        return  $this->result;
    }


    /**
     * レコード数の取得
     *
     * @return integer
     * @access public
     */
    public function count() {
        return  $this->result->count();
    }


    public function current() {
        return  $this->result->current();
    }


    public function next() {
        $this->result->next();
        return  $this->current();
    }


    public function rewind() {
        $this->result->rewind();
        return  $this->current();
    }


    /**
     * 登録IDの取得
     *
     * @param  string $column
     * @return array
     * @access public
     */
    public function id($column) {
        $result = [];
        foreach ($this->result as $k => $row) {
            $result[]   = $row[$column]->getId();
        }
        return  $result;
    }


    /**
     * 属性の取得
     *
     * @param  string  $column
     * @param  mixed   $data
     * @param  string  $key
     * @param  boolean $delete
     * @return array
     * @throws Neo4jResultException
     * @access public
     */
    public function propertie($column, $data = null, $key = null, $delete = false) {
        if ( !isset( $this->columns[$column] )) {
            throw new Neo4jResultException('Column does not exist.');
        }

        $result = [];
        if( !$this->count() ) {
            return  $result;
        }

        foreach ($this->result as $k => $row) {
            $tmp    = [];
            switch ( gettype( $data ) ) {
                case 'string':
                    $tmp    = !is_object( $row[$column] ) ? $row[$column] : $row[$column]->getProperty( $data );
                    break;
                case 'array':
                    foreach ($data as $value) {
                        $tmp[$value]    = !is_object( $row[$column] ) ? $row[$column] : $row[$column]->getProperty( $value );
                    }
                    break;
                default:
                    $tmp    = !is_object( $row[$column] ) ? $row[$column] : $row[$column]->getProperties();
            }
            if ( $key ) {
                $k  = $row[$column]->getProperty( $key );
                if ( $delete ) {
                    unset( $tmp[$key] );
                    if ( 1 === count( $tmp ) ) {
                        $tmp    = current( $tmp );
                    }
                }
            }
            $result[$k] = $tmp;
        }
        $this->rewind();

        return  $result;
    }


    /**
     * 全属性の取得
     *
     * @param  boolean $merge
     * @return array
     * @access public
     */
    public function properties($merge = false) {
        $result = [];
        $keys   = array_keys($this->columns);

        foreach ($this->result as $row) {
            $value  = [];
            foreach ($keys as $column) {
                if ($merge) {
                    $value  += !is_object( $row[$column] ) ? [$column => $row[$column]] : $row[$column]->getProperties();
                }
                else {
                    $value[$column] = !is_object( $row[$column] ) ? $row[$column] : $row[$column]->getProperties();
                }
            }
            $result[]   = $value;
        }
        $this->rewind();

        return  $result;
    }


}