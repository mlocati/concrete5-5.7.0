<?php
namespace Concrete\Core\Server\Server;

abstract class ServerBase
{
    /**
     * @var string
     */
    protected $handle;

    /**
     * @var bool
     */
    protected $isCurrent;

    protected function __construct($handle)
    {
        $this->handle = $handle;
    }

    /**
     * @return string
     */
    public function getHandle()
    {
        return $this->handle;
    }

    public function isCurrent()
    {
        if (!isset($this->isCurrent)) {
            $this->isCurrent = $this->getIsCurrent();
        }

        return $this->isCurrent;
    }

    /**
     * @return bool
     */
    abstract protected function getIsCurrent();
}
