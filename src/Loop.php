<?php

namespace Net;

class Loop {
    /** @var IStream[] */
    private $connections = [];

    public function addConnection(IStream $connection) {
        $stream = $connection->getStream();
        $stream_id = "$stream";
        $this->connections[$stream_id] = $connection;
    }

    public function removeConnection(IStream $connection) {
        $stream = $connection->getStream();
        $stream_id = "$stream";
        unset($this->connections[$stream_id]);
    }

    /**
     * @throws \Exception
     */
    public function run() {
        while (true) {
            if (!$this->connections) return;
            $reads = $writes = $excepts = [];
            foreach ($this->connections as $stream_id => $connection) {
                $stream = $connection->getStream();
                $stream_id = "$stream";
                $reads[$stream_id] = $stream;
                if ($connection->wantWrite()) $writes[$stream_id] = $stream;
            }
            $selected = stream_select($reads, $writes, $excepts, null);
            if ($selected === false) {
                throw new \Exception("select failed");
            }

            foreach ($reads as $stream) {
                $id = "$stream";
                $this->connections[$id]->readyRead();
            }

            foreach ($writes as $stream) {
                $id = "$stream";
                if (isset($this->connections[$id])) $this->connections[$id]->readyWrite();
            }
        }
    }
}
