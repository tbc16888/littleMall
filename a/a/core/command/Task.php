<?php
declare(strict_types=1);

namespace core\command;

use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;
use Workerman\Worker;
use Workerman\Crontab\Crontab;

class Task extends Command
{
    // 任务列表
    protected array $taskList = [
        'order.cancel' => \core\task\order\OrderCancelTask::class,
        'order.complete' => \core\task\order\OrderCompleteTask::class,
        'coupon.user.expire' => \core\task\coupon\UserCouponExpire::class
    ];

    protected array $workerList = [

    ];

    protected function configure()
    {
        // 指令配置
        $this->setName('task')
            ->addArgument('status', Argument::REQUIRED, 'start/stop/reload/status/connections')
            ->setDescription('start/stop/reload/status run system task');
    }

    protected function execute(Input $input, Output $output): ?int
    {
//        Worker::$daemonize = true;
        $this->start();
        return null;
    }


    public function start()
    {
        Worker::$pidFile = app()->getRootPath() . 'task.pid';
        foreach ($this->taskList as $name => $task) {
            $worker = new Worker();
            $worker->onWorkerStart = [new $task(), 'handle'];
            $worker->name = $name;
            $this->workerList[$name] = $task;
        }
        if (count($this->workerList)) Worker::runAll();
    }
}