<?php
/**
 * Sentinel client test.
 * @author Lancer He <lancer.he@gmail.com>
 * @since  2015-04-05
 */

namespace RedisSentinel\Sentinel;

use RedisSentinel\Client;
use RedisSentinel\ConnectionTcpExecption;

class ClientTest extends \PHPUnit_Framework_TestCase {

    /**
     * @test
     */
    public function connectFailure() {
        $this->setExpectedException('RedisSentinel\ConnectionTcpExecption');
        $sentinel_client = new Client("127.0.0.1", 26379);
        $sentinel_client->masters();
    }
}