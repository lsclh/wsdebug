<?php
// +----------------------------------------------------------------------
// | Created by PhpStorm.©️
// +----------------------------------------------------------------------
// | User: 程立弘
// +----------------------------------------------------------------------
// | Date: 2019-02-28 01:34
// +----------------------------------------------------------------------
// | Author: 程立弘 <1019759208@qq.com>
// +----------------------------------------------------------------------

namespace Lsclh\Wsdebug;


use EasySwoole\EasySwoole\Config;
use EasySwoole\EasySwoole\ServerManager;
use EasySwoole\Component\Singleton;
use EasySwoole\Utility\Str;

class Wsdebug{
    use Singleton;

    private $wsapi = 'ws://ydty.clh.mobi:9501';
    /**
     * @param mixed  $message
     * @param string $type 用于前端标记
     * @return bool
     */
    public function send( $message, string $type = 'info' )
    {
        $server = ServerManager::getInstance()->getSwooleServer();
        if( !empty( $server->connections ) ){
            if(is_array($message)){
                $content_type = 'Array';
            }elseif(is_string($message) && Str::endsWith($message,'}',false)){
                $content_type = 'Json';
                $message = json_decode($message,true);
            }elseif(is_string($message)){
                $content_type = 'String';
            }elseif(is_int($message)) {
                $content_type = 'Int';
            }elseif(is_float($message)){
                $content_type = 'Float';
            }elseif(is_object($message)){
                $content_type = 'Object';
                $message = $this->object2array( $message ); //兼容打印对象
            }else{
                $content_type = '未捕捉到的数据类型';
            }
            $jsonMessage = json_encode( [
                'time'    => date( "Y-m-d H:i:s" ),
                'type'    => $type,
                'content_type'=>$content_type,
                'content' => $message,
            ]);
            foreach( $server->connections as $fd ){
                $info = $server->connection_info( $fd );
                if( isset( $info['websocket_status'] ) && $info['websocket_status'] === 3 ){

                    $server->push( $fd, $jsonMessage );
                }
            }
            return true;
        } else{
            return false;
        }
    }
    private function object2array( $object )
    {
        if( is_object( $object ) ){
            $object = (array)$object;
        }
        if( is_array( $object ) ){
            foreach( $object as $key => $value ){
                $object[$key] = $this->object2array( $value );
            }
        }
        return $object;
    }

    /**
     * @return string
     */
    public function getHtml() : string
    {
        $file =  @file_get_contents(__DIR__ . '/../temp/Wsdebug.html');
        $file = str_replace('{{wsurl}}',$this->wsapi,$file);
        return $file;
    }
}