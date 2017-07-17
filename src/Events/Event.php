<?php
/**
 * @author ueaner <ueaner@gmail.com>
 */
namespace Soli\Events;

use Closure;

/**
 * 事件原型
 */
class Event
{
    /**
     * 事件名称，当事件监听器为对象时，事件名称对应事件监听器中的方法名
     *
     * @var string
     */
    protected $name;

    /**
     * 事件来源
     *
     * @var object
     */
    protected $target;

    /**
     * 事件相关数据
     *
     * @var mixed
     */
    protected $data;

    /**
     * Whether no further event listeners should be triggered
     *
     * @var bool
     */
    protected $propagationStopped = false;

    /**
     * Event constructor.
     *
     * @param string $name
     * @param object $target
     * @param mixed $data
     * @throws \Exception
     */
    public function __construct($name, $target, $data = null)
    {
        if (!is_string($name) || !is_object($target)) {
            throw new \Exception('Invalid parameter type.');
        }

        $this->name = $name;
        $this->target = $target;

        if ($data !== null) {
            $this->data = $data;
        }
    }

    /**
     * 激活事件监听队列
     *
     * @param array $queue
     * @return mixed
     * @throws \Exception
     */
    public function fire($queue)
    {
        if (!is_array($queue)) {
            throw new \Exception('The queue is not valid');
        }

        // 事件监听队列中最后一个监听器的执行状态
        $status = null;

        foreach ($queue as $listener) {
            if ($this->isPropagationStopped()) {
                break;
            }

            if ($listener instanceof Closure) {
                // 调用闭包监听器
                $status = call_user_func_array($listener, [$this, $this->target, $this->data]);
            } elseif (method_exists($listener, $this->name)) {
                // 调用对象监听器
                $status = $listener->{$this->name}($this, $this->target, $this->data);
            }
        }

        return $status;
    }

    /**
     * Stops the propagation of the event to further event listeners.
     */
    public function stopPropagation()
    {
        $this->propagationStopped = true;
    }

    /**
     * Has this event indicated event propagation should stop?
     *
     * @return bool
     */
    public function isPropagationStopped()
    {
        return $this->propagationStopped;
    }
}
