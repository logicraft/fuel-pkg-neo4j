<?php

namespace Neo4j;

use Everyman\Neo4j\Client
  , Everyman\Neo4j\Cypher\Query;

class Neo4jException extends \FuelException {}
class Neo4j {

    protected $client;
    protected $result;


    /**
     * インスタンスの生成
     *
     * @param  string $name confing name
     * @return \Neo4j\Neo4j
     * @access public static
     */
    public static function client($name = null) {
        return new static($name);
    }


    /**
     * construct
     *
     * @param  string $name       confing name
     * @return object
     * @access protected
     */
    protected function __construct($name = null) {
        \Config::load('neo4j', true);

        if ( $name === null ) {
            $name = \Config::get('neo4j.active');
        }

        $config = \Config::get('neo4j.'.$name, []);
        $client = new Client($config['hostname'], $config['port']);
        $client->getTransport()->setAuth($config['username'], $config['password']);

        if ( $config['ssl'] ) {
            $client->getTransport()->useHttps();
        }

        $this->client   = $client;
    }


    /**
     * Test connection to server
     *
     * @return array
     * @access public
     */
    public function server_info() {
        return  $this->client->getServerInfo();
    }


    /**
     * Cypher Queryの実行結果取得
     *
     * @param  string $cypher
     * @param  array  $parm
     * @return \Neo4j\Result
     * @access public
     */
    public function query( $cypher, array $parm = [] ) {
        return  Result::query($this->client, $cypher, $parm);
    }


    /**
     * Cypher Queryの実行
     *
     * @param  string $cypher
     * @param  array  $parm
     * @return \Everyman\Neo4j\Cypher\Query
     * @access public
     */
    public function cypher( $cypher, array $parm = [] ) {
        return  new Query($this->client, $cypher, $parm);
    }


    /**
     * Create a new node object
     *
     * @param  array $parm
     * @return \Everyman\Neo4j\Node
     * @access public
     */
    public function makeNode(array $parm = [] ) {
        return  $this->client->makeNode($parm);
    }


    /**
     * Retrieve a Label object for the given name
     *
     * @param  string $label
     * @return \Everyman\Neo4j\Label
     * @access public
     */
    public function makeLabel( $label ) {
        return  $this->client->makeLabel($label);
    }


    /**
     * トランザクション開始
     *
     * @return \Everyman\Neo4j\Transaction
     * @access public
     */
    public function begin() {
        return  $this->client->beginTransaction();
    }


    /**
     * トランザクション実行
     *
     * @param  array $cyphers
     * @return boolean
     * @throws Neo4jException
     */
    public function transaction(array $cyphers ) {
        if ( empty( $cyphers ) ) {
            return  false;
        }

        foreach ( $cyphers as &$value ) {
            $value  = is_array( $value )
                        ? $this->cypher($value[0], $value[1])
                        : $this->cypher($value);
        }

        try {
            $transaction    = $this->begin();
            $transaction->addStatements( $cyphers );
            $transaction->commit();
        } catch (Exception $e) {
            if ( isset( $transaction ) ) {
                $transaction->rollback();
            }
            throw new Neo4jException($e->getMessage(), $e->getCode(). $e->getPrevious());
        }
        return  true;
    }


}
