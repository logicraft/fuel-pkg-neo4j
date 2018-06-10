<?php

/**
 * Fuel is a fast, lightweight, community driven PHP5 framework.
 *
 * @package    Fuel-Neo4j
 * @version    1.0
 * @author     Miura Daisuke
 * @link
 */

Autoloader::add_core_namespace('Neo4j');

Autoloader::add_classes(array(
    'Neo4j\\Neo4j'                  => __DIR__.'/classes/neo4j.php',
    'Neo4j\\Result'                 => __DIR__.'/classes/result.php',
    'Neo4j\\Neo4jException'         => __DIR__.'/classes/neo4j.php',
    'Neo4j\\Neo4jResultException'   => __DIR__.'/classes/result.php',
));

/* End of file bootstrap.php */