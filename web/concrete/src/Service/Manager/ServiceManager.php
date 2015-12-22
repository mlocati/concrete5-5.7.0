<?php
namespace Concrete\Core\Service\Manager;

use Concrete\Core\Application\Application;
use Concrete\Core\Service\ServiceInterface;

class ServiceManager implements ManagerInterface
{

    /**
     * @var Application
     */
    protected $app;

    /**
     * @var array
     */
    protected $extensions = array();

    /**
     * @var ServiceInterface[]
     */
    protected $services = array();

    /**
     * Manager constructor.
     * @param \Concrete\Core\Application\Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Add an extension to this manager
     * @param string $handle
     * @param string|callable|ServiceInterface $abstract
     */
    public function extend($handle, $abstract)
    {
        $this->extensions[$handle] = $abstract;
    }

    /**
     * An array of handles that have been added with `->extend`
     * @return string[]
     */
    public function getExtensions()
    {
        return array_keys($this->extensions);
    }

    /**
     * Does this handle exist?
     * This method MUST return true for anything added with `->extend`
     * @param $handle
     * @return bool
     */
    public function has($handle)
    {
        return isset($this->extensions[$handle]);
    }

    /**
     * Get the driver for this handle
     * @param $handle
     * @return ServiceInterface|null
     */
    public function getService($handle)
    {
        if ($this->has($handle)) {
            if (isset($this->services[$handle])) {
                return $this->services[$handle];
            } elseif ($abstract = array_get($this->extensions, $handle)) {
                if ($service = $this->buildService($abstract)) {
                    $this->services[$handle] = $service;
                    return $service;
                } else {
                    throw new \RuntimeException('Invalid service binding.');
                }
            }
        }
    }

    /**
     * Build a service from an abstract
     * @param string|callable|ServiceInterface $abstract
     * @return ServiceInterface|null
     */
    private function buildService($abstract)
    {
        $resolved = null;

        if (is_string($abstract)) {
            // If it's a string, throw it at the IoC container
            $resolved = $this->app->make($abstract);
        } elseif (is_callable($abstract)) {
            // If it's a callable, lets call it with the application and $this
            $resolved = $abstract($this->app, $this);
        } elseif ($abstract instanceof ServiceInterface) {
            // If it's a serviceinterface, it's already resolved.
            $resolved = $abstract;
        }

        return $resolved;
    }

    /**
     * Loops through the bound services and returns the ones that are active
     * @return ServiceInterface[]
     */
    public function getActiveServices()
    {
        $active = array();
        foreach ($this->getExtensions() as $handle) {
            $service = $this->getService($handle);
            if ($service->getDetector()->detect()) {
                $active[] = $service;
            }
        }

        return $active;
    }

}
