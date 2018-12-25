<?php
/**
 * This file is part of Swoft.
 *
 * @link https://swoft.org
 * @document https://doc.swoft.org
 * @contact group@swoft.org
 * @license https://github.com/swoft-cloud/swoft/blob/master/LICENSE
 */

namespace App\Commands;

use App\Models\Logic\UserLogic;
use Swoft\App;
use Swoft\Console\Bean\Annotation\Command;
use Swoft\Console\Bean\Annotation\Mapping;
use Swoft\Console\Input\Input;
use Swoft\Console\Output\Output;
use Swoft\Core\Coroutine;
use Swoft\Log\Log;
use Swoft\Task\Task;

/**
 * Test command
 *
 * @Command(coroutine=false)
 */
class TestCommand
{
    /**
     * this test command
     *
     * @Usage
     * test:{command} [arguments] [options]
     *
     * @Options
     * -o,--o this is command option
     *
     * @Arguments
     * arg this is argument
     *
     * @Example
     * php swoft test:test arg=stelin -o opt
     *
     * @param Input  $input
     * @param Output $output
     *
     * @Mapping("test2")
     */
    public function test(Input $input, Output $output)
    {
        App::error('this is eror');
        App::trace('this is trace');
        Coroutine::create(function (){
            App::error('this is eror child');
            App::trace('this is trace child');
        });

        var_dump('test', $input, $output, Coroutine::id(),Coroutine::tid());
    }


    /**
     * this task command
     *
     * @Usage
     * test:{command} [arguments] [options]
     *
     * @Options
     * -o,--o this is command option
     *
     * @Arguments
     * arg this is argument
     *
     * @Example
     * php swoft test:task
     *
     * @Mapping()
     */
    public function task()
    {
        $result = Task::deliver('sync', 'console', ['console']);
        var_dump($result);
    }
}