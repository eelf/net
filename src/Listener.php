<?php

namespace Net;

class Listener implements IStream {
    use TEvent;
    const
        EVENT_CONNECTION = 1;

    private $stream;
    /** @var Loop */
    private $loop;
    private $address;

    public static function listen($address, Loop $loop) {
        $stream = stream_socket_server($address, $errno, $errstr, STREAM_SERVER_BIND | STREAM_SERVER_LISTEN);
        $Listener = new self($loop, $stream);
        $Listener->address = stream_socket_get_name($stream, false);
        $loop->addConnection($Listener);
        return $Listener;
    }

    public function getAddress() {
        return $this->address;
    }

    public function __construct(Loop $loop, $stream) {
        $this->loop = $loop;
        $this->stream = $stream;
    }

    public function getStream() {
        return $this->stream;
    }

    public function wantWrite() {
        return false;
    }

    public function readyRead() {
        $client = stream_socket_accept($this->stream, null, $peer);
        $Connection = new Connection($this->loop, $client, $peer);
        $this->loop->addConnection($Connection);
        $this->emit(self::EVENT_CONNECTION, $Connection);
    }

    public function readyWrite() {
    }
}
