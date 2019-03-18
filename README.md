#Wsdebug  程立弘适配 v3
###(原版来自韩博文easyswoole v2版)

>1.通过自定义路由 Router 添加输出页面
```php
<?php
namespace App\HttpController;

use Lsclh\Wsdebug\Wsdebug;
use EasySwoole\Http\AbstractInterface\AbstractRouter;
use FastRoute\RouteCollector;
use EasySwoole\http\Request;
use EasySwoole\http\Response;

/**
 * 注册自定义路由
 * Class Router
 * @package App\HttpController
 */
class Router extends AbstractRouter
{

    function initialize(RouteCollector $routeCollector)
    {
        //访问WebSocket 调试bug的
        $routeCollector->get( '/wsdebug', function( Request $request, Response $response ){
            // 输出调试工具的html
            $res = WsDebug::getInstance()->getHtml();

            if($res){
                $response->write($res);
            }else{
                $response->withHeader('Content-type', 'text/html;charset=UTF-8');
                $response->write('error');
            }
            $response->end();
        } );
    }

}
```

>2.任意地方通过 send() 发送到debug页面
```php
Wsdebug::getInstance()->send('数组 字串 对象均可','类型默认info');
```