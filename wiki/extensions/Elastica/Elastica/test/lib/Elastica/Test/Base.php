<?php

namespace Elastica\Test;

use Elastica\Client;

class Base extends \PHPUnit_Framework_TestCase
{
    protected function _getClient()
    {
        return new Client();
    }

    /**
     * @param  string         $name Index name
     * @param  bool           $delete Delete index if it exists
     * @return \Elastica\Index
     */
    protected function _createIndex($name = 'test', $delete = true)
    {
        $client = $this->_getClient();
        $index = $client->getIndex('elastica_' . $name);
        $index->create(array('index' => array('number_of_shards' => 1, 'number_of_replicas' => 0)), $delete);

        return $index;
    }
}
