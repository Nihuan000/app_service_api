<?php
/**
 * This file is part of Swoft.
 *
 * @link https://swoft.org
 * @document https://doc.swoft.org
 * @contact group@swoft.org
 * @license https://github.com/swoft-cloud/swoft/blob/master/LICENSE
 */

namespace App\Middlewares;

use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Swoft\Bean\Annotation\Bean;
use Swoft\Http\Message\Middleware\MiddlewareInterface;

/**
 * Class ActionVerifyMiddleware - Custom middleware
 * @Bean()
 * @package App\Middlewares
 */
class ActionVerifyMiddleware implements MiddlewareInterface
{
    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Server\RequestHandlerInterface $handler
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \InvalidArgumentException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // before request handle
        $auth = true;
        $request_params = isset($request['post']) ? $request['post'] : [];
        if(empty($request_params)){
            $auth = false;
        }else{
            $sign_key = '';
            $sign = isset($request_params['sign']) ? $request_params['sign'] : '';
            unset($request_params['sign']);
            foreach ($request_params as $sub_key => $item) {
                $sign_key .= $item;
            }
            $secret_key = md5($sign_key);
            if($sign == '' || $sign != $secret_key){
                $auth = false;
            }
        }

        if(!$auth){
            return response()->withStatus(401);
        }
        $response = $handler->handle($request);

        // after request handle

        return $response->withAddedHeader('User-Middleware', 'success');
    }
}
