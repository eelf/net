<?php

namespace Net;

trait TEvent {
    /** @var Callable[] */
    private $listeners = [];

    public function on($event, $cb) {
        $this->listeners[$event][] = $cb;
    }

    public function emit($event, &...$data) {
        if (!isset($this->listeners[$event])) return;
        foreach ($this->listeners[$event] as $cb) {
            $cb(...$data);
        }
    }
}
