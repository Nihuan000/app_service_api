<?php

/*
 * This file is part of Swoft.
 * (c) Swoft <group@swoft.org>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

! defined('DS') && define('DS', DIRECTORY_SEPARATOR);
// App name
! defined('APP_NAME') && define('APP_NAME', 'appSwoft');
// Project base path
! defined('BASE_PATH') && define('BASE_PATH', dirname(__DIR__, 1));

// Register alias
$aliases = [
    '@root'       => BASE_PATH,
    '@env'        => '@root',
    '@app'        => '@root/app',
    '@res'        => '@root/resources',
    '@runtime'    => '@root/runtime',
    '@configs'    => '@root/config',
    '@resources'  => '@root/resources',
    '@beans'      => '@configs/beans',
    '@properties' => '@configs/properties',
    '@console'    => '@beans/console.php',
    '@commands'   => '@app/command',
    '@vendor'     => '@root/vendor',
];

##########################业务相关 START#######################
define('INDUSTRY_NEWS_TIME','task_last_time');//行业动态同步时间
define('LOCAL_PATH_URL','https://api.isoubu.com');
define('LOCAL_EXECUTION_PATH','/srv/execution/');

define('RECOMMEND_MODULE_NAME','recommend_list');
##########################业务相关 END#######################
\Swoft\App::setAliases($aliases);
