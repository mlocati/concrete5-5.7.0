<?php
namespace Concrete\Core\Service\HTTP;

use Concrete\Core\Application\Application;
use Concrete\Core\Service\Configuration\GeneratorInterface;
use Concrete\Core\Service\Detector\DetectorInterface;
use Concrete\Core\Service\ServiceInterface;

class Nginx implements ServiceInterface
{

    /** @var string The class to use for our detector */
    protected $detector_class = 'Concrete\Core\Application\Service\Detector\HTTP\NginxDetector';

    /** @var string The class to use for our generator */
    protected $generator_class = 'Concrete\Core\Application\Service\Configuration\HTTP\NginxGenerator';

    /** @var DetectorInterface */
    protected $detector;

    /** @var Application */
    protected $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Get the human readable service name
     * @return string
     */
    public function getName()
    {
        return "NGINX";
    }

    /**
     * @return DetectorInterface
     */
    public function getDetector()
    {
        return $this->app->make($this->detector_class);
    }

    /**
     * Get the configuration generator instance
     * @return GeneratorInterface
     */
    public function getGenerator()
    {
        return $this->app->make($this->detector_class);
    }

}
