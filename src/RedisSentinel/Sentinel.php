<?php
/**
 * 连接Redis Sentinel
 *
 * @category Redis
 * @package  Sentinel_Client
 * @author   Lancer He <lancer.he@gmail.com>
 * @version  1.0 
 */

namespace RedisSentinel;

use RedisSentinel\Client;
use RedisSentinel\ConnectionTcpExecption;
use RedisSentinel\ConnectionFailureExecption;

class Sentinel {

    /**
     * 通过TCP连接上Sentinel
     */
    protected static $_connected = false;

    protected $_output_file = '/tmp/sentinel.log';

    protected $_master_name = '';

    protected $_master = array();

    protected $_slaves = array();

    protected $_Clients = array();

    /**
     * 初始化需要连接的Master
     */
    public function __construct($master_name) {
        $this->_master_name = $master_name;
    }

    protected function _connect() {
        if ( self::$_connected ) {
            return;
        }
        $this->_connectEachIfNotConnected();
        self::$_connected = true;
    }

    protected function _connectEachIfNotConnected() {
        foreach ($this->_Clients as $Client) {
            try {
                $this->_masters = $Client->masters();
                $this->_slaves  = $Client->slaves($this->_master_name);
                return;
            } catch (ConnectionTcpExecption $e) {
                $this->_writeOutputException($Client, $e);
            }
        }
        throw new ConnectionFailureExecption();
    }

    public function setOutputFile($output_file) {
        $this->_output_file = $output_file;
    }

    protected function _writeOutputException($Client, $e) {
        $output = "[". date('Y-m-d H:i:s'). "] " . $Client->getHost() . ":" . $Client->getPort() . " " . $e->getMessage() . PHP_EOL;
        file_put_contents($this->_output_file, $output, FILE_APPEND);
    }

    public function add($Client) {
        $this->_Clients[] = $Client;
    }

    public function getMaster() {
        $this->_connect();
        $masters = array();
        foreach ($this->_masters as $master) {
            $masters[$master['name']] = $master;
        }
        return $masters[$this->_master_name];
    }

    public function getSlaves() {
        $this->_connect();
        $slaves = array();
        foreach($this->_slaves as $slave) 
            if($slave['flags'] == 'slave') $slaves[] = $slave;
        return $slaves;
    }

    public function getSlave() {
        $slaves = $this->getSlaves();
        $idx = rand(0, count($slaves) - 1);
        return $slaves[$idx];
    }
}