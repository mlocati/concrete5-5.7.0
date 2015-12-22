<?php

namespace Concrete\Core\Service;

use Concrete\Core\Service\Configuration\GeneratorInterface;

interface ServiceInterface
{

    /**
     * Get the human readable service name
     * @return string
     */
    public function getName();

    /**
     * Get a detector instance to determine if this service is active
     * @return \Concrete\Core\Service\Detector\DetectorInterface
     */
    public function getDetector();

    /**
     * Get the configuration generator instance
     * @return GeneratorInterface
     */
    public function getGenerator();

}
