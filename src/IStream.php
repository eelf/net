<?php

namespace Net;

interface IStream {

    public function getStream();

    /**
     * Stream wants to write
     * @return bool
     */
    public function wantWrite();

    /**
     * Called when stream is ready to make one read
     */
    public function readyRead();

    /**
     * Called when stream is ready to make one write
     */
    public function readyWrite();

    /**
     * Local address for listener and remote address for connection
     */
    public function getAddress();
}
