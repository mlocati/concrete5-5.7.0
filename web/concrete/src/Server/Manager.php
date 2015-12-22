<?php
namespace Concrete\Core\Server;

use Core;

class Manager
{
    /**
     * @var Server\ServerBase[]
     */
    protected $servers = array();

    /**
     * @var Server\ServerBase|false|null
     */
    protected $current = false;

    /**
     * @param string $handle
     *
     * @return Server\ServerBase|null
     */
    public function get($handle)
    {
        return $this->has($handle) ? $this->servers[$handle] : null;
    }

    public function has($handle)
    {
        return isset($this->servers[$handle]);
    }

    /**
     * @return Server\ServerBase[]
     */
    public function getList()
    {
        return $this->servers;
    }

    /**
     * @param Server\ServerBase $server
     */
    public function setCurrent(Server\ServerBase $server)
    {
        $this->current = $server;
    }

    /**
     * @return Server\ServerBase|null
     */
    public function getCurrent()
    {
        if ($this->current === false) {
            if (Core::make('app')->isRunThroughCommandLineInterface()) {
                $this->current = null;
            } else {
                foreach ($this->servers as $server) {
                    /** @var Server\ServerBase $server */
                    if ($server->isCurrent()) {
                        $this->current = $server;
                        break;
                    }
                }
                if ($this->current === false) {
                    $this->current = null;
                }
            }
        }

        return $this->current;
    }

    /**
     * @param Server\ServerBase $server
     */
    public function register(Server\ServerBase $server)
    {
        $this->servers[$server->getHandle()] = $server;
    }
}
