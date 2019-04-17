<?php
/**
 * @link http://www.heyanlong.com/
 * @copyright Copyright (c) 2015 heyanlong.com
 * @license http://www.heyanlong.com/license/
 */

namespace yii\redis;

use yii\db\Exception;
use yii\helpers\Inflector;

class ExtConnection extends \yii\redis\Connection
{

    const EVENT_AFTER_OPEN = 'afterOpen';

    public $master = [];
    
    public $slave = [];

    private $_socket_list = [];
    
    private $last_hostname = '';
    private $current_hostname = '';

    public function __sleep()
    {
        $this->close();

        return array_keys(get_object_vars($this));
    }

    public function getIsActiveEx($host_name)
    {
        if(!isset($this->_socket_list[$host_name]))
        {
            return false;
        }
        else
        {
            return $this->_socket_list[$host_name] != null;
        }
    }

    public function open(&$hostname = '', $key='')
    {
        if(empty($hostname) && empty($key))
        {
            throw new Exception("Redis error: hostname is ".$hostname." or key is " . $key . "\n");
        }
        
        //查看当前Key适合连接哪个端口的槽位
        $hostname = empty($hostname) ? $this->get_hostname($key) : $hostname;  
        
        if(!$this->getIsActiveEx($hostname))
        {
            $this->_socket_list[$hostname] = $this->connect($hostname);

            if(!empty($this->_socket_list[$hostname]))
            {
                $this->last_hostname = $hostname;
            }
            else
            {
                throw new Exception("Redis error: disconnect to " . $hostname . "\n");
            }
        }  
        
        return true;
    }

    public function close($hostname = '')
    {
        if(empty($hostname))
        {
            foreach ($this->_socket_list as $host_name=>$socket)
            {
                $this->close($host_name);
            }
        }
        else
        {
            if ($this->_socket_list[$hostname] !== null) {
                $this->executeCommand('QUIT',[],$hostname);
                stream_socket_shutdown($this->_socket_list[$hostname], STREAM_SHUT_RDWR);
                $this->_socket_list[$hostname] = null;
                unset($this->_socket_list[$hostname]);
            }
        }
    }

    protected function initConnection()
    {
        $this->trigger(self::EVENT_AFTER_OPEN);
    }

    public function __call($name, $params)
    {
        $redisCommand = strtoupper(Inflector::camel2words($name, false));
        if (in_array($redisCommand, $this->redisCommands)) {

            return $this->executeCommand($name, $params);

        } else {
            return parent::__call($name, $params);
        }
    }

    public function executeCommand($name, $params = [], $hostname = '')
    {
        $is_moved = false;
        $key = '';
        $is_empty_key = false;
        
        //只有当Moved发生时，才会准确传入Hostname
        if(empty($hostname))
        {
            //在集群Redis里，必须传入Key，以便区分使用哪个slot，否则调用效率低下
            if(!empty($params) && isset($params['key']))
            {
                $key = $params['key'];
                $params = $params['params'];
                $is_empty_key = isset($params['is_empty_key']) && $params['is_empty_key'] ? true : false;
                //key只是为了寻找到一个slot，否则multi执行时，如果多个key不在一个slot会报错
                if(!$is_empty_key)
                {
                    array_unshift($params, $key);
                }
            }
            else if(empty ($hostname))
            {
                throw new Exception("Redis error: executeCommand error \nparams exclude key in the array.");
            }
        }
        else
        {
            $is_moved = true;
        }
        
        //$hostname为空时，在open处理后会根据$key自动指定到正确的访问地址下
        $this->open($hostname,$key);
        
        //当hostname为空时，表示正常操作，否则当做Moved处理
        if($is_moved == false)
        {
            array_unshift($params, $name);
            $command = '*' . count($params) . "\r\n";

            foreach ($params as $arg) {
                $command .= '$' . mb_strlen($arg, '8bit') . "\r\n" . $arg . "\r\n";
            }   
            
            $repeat_exeute_data = array(
                "name"=>$name,
                "command"=>$command,
                "params"=>implode(' ', $params)
            );             
        }
        else
        {
            $command = $params['command'];
            
            $repeat_exeute_data = $params;
        }
                       
        \Yii::trace("Executing Redis Command: {$name}", __METHOD__);
        fwrite($this->_socket_list[$hostname], $command);
        
        return $this->parseResponse($repeat_exeute_data, $this->_socket_list[$hostname]);
    }

    private function parseResponse($command, $socket)
    {
        if (($line = fgets($socket)) === false) {
            throw new Exception("Failed to read from socket.\nRedis command was: " . $command["params"]);
        }
        
        $type = $line[0];
        $line = mb_substr($line, 1, -2, '8bit');
        switch ($type) {
            case '+': // Status reply
                if ($line === 'OK' || $line === 'PONG') {
                    return true;
                } else {
                    return $line;
                }
            case '-': // Error reply

                $moved = explode(' ', $line);
                
                if (isset($moved[0]) && $moved[0] == 'MOVED') {
                    $hostname = $moved[2];
                    $name = $command['name'];
                    //在主缓存宕机的时候，调用从缓存来处理
                    return $this->executeCommand($name, $command, $hostname);

                } else {                                             
                    throw new Exception("Redis error: " . $line . "\nRedis command was: " . $command['params']);
                }

            case ':': // Integer reply
                // no cast to int as it is in the range of a signed 64 bit integer
                return $line;
            case '$': // Bulk replies
                if ($line == '-1') {
                    return null;
                }
                $length = $line + 2;
                $data = '';
                while ($length > 0) {
                    if (($block = fread($socket, $length)) === false) {
                        throw new Exception("Failed to read from socket.\nRedis command was: " . $command['params']);
                    }
                    $data .= $block;
                    $length -= mb_strlen($block, '8bit');
                }
                return mb_substr($data, 0, -2, '8bit');
            case '*': // Multi-bulk replies
                $count = (int)$line;
                $data = [];
                for ($i = 0; $i < $count; $i++) {
                    $data[] = $this->parseResponse($command, $socket);
                }
                return $data;
            default:
                throw new Exception('Received illegal data from redis: ' . $line . "\nRedis command was: " . $command['params']);
        }
    }

    private function connect($node)
    {
        $socket = null;

        $connection = $node . ', database=' . $this->database;
        \Yii::trace('Opening redis DB connection: ' . $connection, __METHOD__);

        $socket = stream_socket_client(
            'tcp://' . $connection,
            $errorNumber,
            $errorDescription,
            $this->connectionTimeout ? $this->connectionTimeout : ini_get("default_socket_timeout")
        );
        
        if ($socket) {
            if ($this->dataTimeout !== null) {
                stream_set_timeout($socket, $timeout = (int)$this->dataTimeout, (int)(($this->dataTimeout - $timeout) * 1000000));              
            }

        } else {
            \Yii::error("Failed to open redis DB connection ($connection): $errorNumber - $errorDescription", __CLASS__);
            $message = YII_DEBUG ? "Failed to open redis DB connection ($connection): $errorNumber - $errorDescription" : 'Failed to open DB connection.';
            throw new Exception($message, $errorDescription, (int)$errorNumber);
        }

        return $socket;
    }
    
    private function get_hostname($key)
    {
        $hash_slot = $this->hash_slot($key);
        $host_name = '';
        
        foreach ($this->master as $key=>$value)
        {
            if(empty($host_name))
            {
                $host_name = $key;
            }
            
            if($hash_slot >= $value[0] && $hash_slot <= $value[1])
            {
                $host_name = $key;
                
                break;
            }
        }
        
        return $host_name;
    }
    
    private function hash_slot($key)
    {
        $s = strpos($key, '{');
		
        if($s !== false)
        {
            $e = strpos($key, '}', $s+1);
            if($e !== false && $e != ($s + 1))
            {
                $key = substr($key, $s+1,$e-1);
            }
        }
        
        return $this->redisCRC16($key) % 16384;
    }
    
    private function redisCRC16 (&$ptr){
        $crc_table=array(
	    0x0000,0x1021,0x2042,0x3063,0x4084,0x50a5,0x60c6,0x70e7,
	    0x8108,0x9129,0xa14a,0xb16b,0xc18c,0xd1ad,0xe1ce,0xf1ef,
	    0x1231,0x0210,0x3273,0x2252,0x52b5,0x4294,0x72f7,0x62d6,
	    0x9339,0x8318,0xb37b,0xa35a,0xd3bd,0xc39c,0xf3ff,0xe3de,
	    0x2462,0x3443,0x0420,0x1401,0x64e6,0x74c7,0x44a4,0x5485,
	    0xa56a,0xb54b,0x8528,0x9509,0xe5ee,0xf5cf,0xc5ac,0xd58d,
	    0x3653,0x2672,0x1611,0x0630,0x76d7,0x66f6,0x5695,0x46b4,
	    0xb75b,0xa77a,0x9719,0x8738,0xf7df,0xe7fe,0xd79d,0xc7bc,
	    0x48c4,0x58e5,0x6886,0x78a7,0x0840,0x1861,0x2802,0x3823,
	    0xc9cc,0xd9ed,0xe98e,0xf9af,0x8948,0x9969,0xa90a,0xb92b,
	    0x5af5,0x4ad4,0x7ab7,0x6a96,0x1a71,0x0a50,0x3a33,0x2a12,
	    0xdbfd,0xcbdc,0xfbbf,0xeb9e,0x9b79,0x8b58,0xbb3b,0xab1a,
	    0x6ca6,0x7c87,0x4ce4,0x5cc5,0x2c22,0x3c03,0x0c60,0x1c41,
	    0xedae,0xfd8f,0xcdec,0xddcd,0xad2a,0xbd0b,0x8d68,0x9d49,
	    0x7e97,0x6eb6,0x5ed5,0x4ef4,0x3e13,0x2e32,0x1e51,0x0e70,
	    0xff9f,0xefbe,0xdfdd,0xcffc,0xbf1b,0xaf3a,0x9f59,0x8f78,
	    0x9188,0x81a9,0xb1ca,0xa1eb,0xd10c,0xc12d,0xf14e,0xe16f,
	    0x1080,0x00a1,0x30c2,0x20e3,0x5004,0x4025,0x7046,0x6067,
	    0x83b9,0x9398,0xa3fb,0xb3da,0xc33d,0xd31c,0xe37f,0xf35e,
	    0x02b1,0x1290,0x22f3,0x32d2,0x4235,0x5214,0x6277,0x7256,
	    0xb5ea,0xa5cb,0x95a8,0x8589,0xf56e,0xe54f,0xd52c,0xc50d,
	    0x34e2,0x24c3,0x14a0,0x0481,0x7466,0x6447,0x5424,0x4405,
	    0xa7db,0xb7fa,0x8799,0x97b8,0xe75f,0xf77e,0xc71d,0xd73c,
	    0x26d3,0x36f2,0x0691,0x16b0,0x6657,0x7676,0x4615,0x5634,
	    0xd94c,0xc96d,0xf90e,0xe92f,0x99c8,0x89e9,0xb98a,0xa9ab,
	    0x5844,0x4865,0x7806,0x6827,0x18c0,0x08e1,0x3882,0x28a3,
	    0xcb7d,0xdb5c,0xeb3f,0xfb1e,0x8bf9,0x9bd8,0xabbb,0xbb9a,
	    0x4a75,0x5a54,0x6a37,0x7a16,0x0af1,0x1ad0,0x2ab3,0x3a92,
	    0xfd2e,0xed0f,0xdd6c,0xcd4d,0xbdaa,0xad8b,0x9de8,0x8dc9,
	    0x7c26,0x6c07,0x5c64,0x4c45,0x3ca2,0x2c83,0x1ce0,0x0cc1,
	    0xef1f,0xff3e,0xcf5d,0xdf7c,0xaf9b,0xbfba,0x8fd9,0x9ff8,
	    0x6e17,0x7e36,0x4e55,0x5e74,0x2e93,0x3eb2,0x0ed1,0x1ef0
        );
        $crc = 0x0000;
        for ($i = 0; $i < strlen($ptr); $i++)
            $crc =  $crc_table[(($crc>>8) ^ ord($ptr[$i]))] ^ (($crc<<8) & 0x00FFFF);
        return $crc;
    }    
}