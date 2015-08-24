<?php
/**
 * Redis Sentinel客户端
 *
 * @category Redis
 * @package  Sentinel_Client
 * @author   Lancer He <lancer.he@gmail.com>
 * @version  1.0 
 */

namespace RedisSentinel;

use RedisSentinel\ConnectionTcpExecption;

class Client {
    protected $_socket;
    protected $_host;
    protected $_port;

    public function __construct($h, $p = 26379) {
        $this->_host = $h;
        $this->_port = $p;
    }

    public function __destruct() {
        if ($this->_socket) 
            $this->_close();
    }

    public function getHost() {
        return $this->_host;
    }

    public function getPort() {
        return $this->_port;
    }

    /*!
     * PING
     *
     * @retval boolean true 连通成功
     * @retval boolean false 连通失敗
     */
    public function ping() {
        $this->_connect();
        $this->_write('PING');
        $this->_write('QUIT');
        $data = $this->_get();
        $this->_close();
        return ($data === '+PONG');
    }

    /*!
     * SENTINEL masters
     *
     * @retval array
     * @code
     * array (
     *   [0]  => // master
     *     array(
     *       'name' => 'mymaster',
     *       'host' => 'localhost',
     *       'port' => 6379,
     *       ...
     *     ),
     *   ...
     * )
     * @endcode
     */
    public function masters() {
        $this->_connect();
        $this->_write('SENTINEL masters');
        $this->_write('QUIT');
        $data = $this->_extract($this->_get());
        $this->_close();
        return $data;
    }

    /*!
     * SENTINEL slaves
     *
     * @param [in] $master string
     * @retval array 
     * @code
     * array (
     *   [0]  =>
     *     array(
     *       'name' => 'mymaster',
     *       'host' => 'localhost',
     *       'port' => 6379,
     *       ...
     *     ),
     *   ...
     * )
     * @endcode
     */
    public function slaves($master) {
        $this->_connect();
        $this->_write('SENTINEL slaves ' . $master);
        $this->_write('QUIT');
        $data = $this->_extract($this->_get());
        $this->_close();
        return $data;
    }

    /*!
     * Sentinel 连接
     *
     * @retval boolean true  连接成功
     * @retval boolean false 连接失敗
     */
    protected function _connect() {
        $this->_socket = @fsockopen($this->_host, $this->_port, $errno, $errstr, 1);
        if ( ! $this->_socket ) {
            throw new ConnectionTcpExecption($errstr);
        }
    }

    /*!
     * Sentinel 关闭
     *
     * @retval boolean true  切断成功
     * @retval boolean false 切断失敗
     */
    protected function _close() {
        $ret = @fclose($this->_socket);
        $this->_socket = null;
        return $ret;
    }

    /*!
     * Sentinel 接受值
     *
     * @retval boolean true  有内容
     * @retval boolean false 无内容
     */
    protected function _receiving() {
        return !feof($this->_socket);
    }

    /*!
     * Sentinel 写
     *
     * @param [in] $c string 
     * @retval mixed integer 
     * @retval mixed boolean false 
     */
    protected function _write($c) {
        return fwrite($this->_socket, $c . "\r\n");
    }

    /*!
     * Sentinel
     *
     * @retval string 返却値
     */
    protected function _get() {
        $buf = '';
        while($this->_receiving()) {
            $buf .= fgets($this->_socket);
        }
        return rtrim($buf, "\r\n+OK\n");
    }

    /*!
     * 分解tcp
     *
     * @param [in] $data string 
     * @retval array
     */
    protected function _extract($data) {
        if (!$data) return array();
        $lines = explode("\r\n", $data);
        $is_root = $is_child = false;
        $c = count($lines);
        $results = $current = array();
        for ($i = 0; $i < $c; $i++) {
            $str = $lines[$i];
            $prefix = substr($str, 0, 1);
            if ($prefix === '*') {
                if (!$is_root) {
                    $is_root = true;
                    $current = array();
                    continue;
                } else if (!$is_child) {
                    $is_child = true;
                    continue;
                } else {
                    $is_root = $is_child = false;
                    $results[] = $current;
                    continue;
                }
            }
            $keylen = $lines[$i++];
            $key    = $lines[$i++];
            $vallen = $lines[$i++];
            $val    = $lines[$i++];
            $current[$key] = $val;
            --$i;
        }
        $results[] = $current;
        return $results;
    }
}