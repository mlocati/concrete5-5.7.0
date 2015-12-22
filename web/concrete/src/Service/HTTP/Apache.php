<?php
namespace Concrete\Core\Service\HTTP;

use Concrete\Core\Application\Application;
use Concrete\Core\Service\Configuration\GeneratorInterface;
use Concrete\Core\Service\Detector\DetectorInterface;
use Concrete\Core\Service\ServiceInterface;

class Apache implements ServiceInterface
{

    /** @var string The class to use for our detector */
    protected $detector_class = 'Concrete\Core\Service\Detector\HTTP\ApacheDetector';

    /** @var string The class to use for our generator */
    protected $generator_class = 'Concrete\Core\Service\Configuration\HTTP\ApacheGenerator';

    /** @var DetectorInterface */
    protected $detector;

    /** @var Application */
    protected $app;

    /** @var string */
    protected $version;

    /**
     * Apache constructor.
     * @param string $version
     * @param \Concrete\Core\Application\Application $app
     */
    public function __construct($version, Application $app)
    {
        $this->version = $version;
        $this->app = $app;
    }

    /**
     * Get the human readable service name
     * @return string
     */
    public function getName()
    {
        return "Apache {$this->version}";
    }

    /**
     * @return DetectorInterface
     */
    public function getDetector()
    {
        return $this->app->make($this->detector_class, array($this->version));
    }

    /**
     * Get the configuration generator instance
     * @return GeneratorInterface
     */
    public function getGenerator()
    {
        return $this->app->make($this->generator_class, array($this->version));
    }

}
