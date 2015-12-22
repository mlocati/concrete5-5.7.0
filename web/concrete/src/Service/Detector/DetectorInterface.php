<?php
namespace Concrete\Core\Service\Detector;

interface DetectorInterface
{

    /**
     * Determine whether this environment matches the expected service environment
     * @return bool
     */
    public function detect();

}
