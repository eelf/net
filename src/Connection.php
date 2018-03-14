<?php

namespace Net;

class Connection implements IStream {
    use TEvent;

    const
        CLOSING_WR = 1,
        CLOSING_WR_DONE = 2;
    const
        EVENT_DATA = 1,
        EVENT_CLOSE = 2;

    private $write_buf;
    private $stream;
    private $closing;
    /** @var Loop */
    private $loop;
    private $address;

    public static function connect($address, Loop $loop) {
        $stream = stream_socket_client($address, $errno, $errstr);
        $remote_address = stream_socket_get_name($stream, true);
        $Connection = new self($loop, $stream, $remote_address);
        $loop->addConnection($Connection);
        return $Connection;
    }

    public function __construct(Loop $loop, $stream, $address) {
        $this->loop = $loop;
        $this->stream = $stream;
        $this->address = $address;
    }

    public function getAddress() {
        return $this->address;
    }

    public function getStream() {
        return $this->stream;
    }

    public function wantWrite() {
        return $this->closing == self::CLOSING_WR || strlen($this->write_buf);
    }

    public function write($buf) {
        $this->write_buf .= $buf;
    }

    public function readyWrite() {
        if (strlen($this->write_buf)) {
            $wrote = fwrite($this->stream, $this->write_buf);
            $this->write_buf = substr($this->write_buf, $wrote);
        }
        if (!strlen($this->write_buf) && $this->closing == self::CLOSING_WR) {
            stream_socket_shutdown($this->stream, STREAM_SHUT_WR);
            $this->closing = self::CLOSING_WR_DONE;
        }
    }

    public function readyRead() {
        $buf = fread($this->stream, 8192);
        if ($buf === false || $buf === '') {
            $this->loop->removeConnection($this);
            fclose($this->stream);
            $this->emit(self::EVENT_CLOSE);
        } else if ($this->closing != self::CLOSING_WR && $this->closing != self::CLOSING_WR_DONE) {
            $this->emit(self::EVENT_DATA, $buf);
        }
    }

    public function close($data = null) {
        if ($data !== null) $this->write($data);
        $this->closing = self::CLOSING_WR;
    }
}
