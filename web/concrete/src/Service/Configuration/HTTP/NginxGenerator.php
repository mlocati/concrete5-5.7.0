<?php

namespace Concrete\Core\Service\Configuration\HTTP;

use Concrete\Core\Application\Application;
use Concrete\Core\Service\Configuration\GeneratorInterface;

class NginxGenerator implements GeneratorInterface
{

    /** @var Application */
    protected $app;

    public function __construct(Application $application)
    {
        $this->app = $application;
    }

    /**
     * @return string
     */
    public function generate()
    {
        if ($this->app['config']['concrete.seo.url_rewriting']) {
            return "Uhh.. nginx config goes here :)";
        } else {
            return "";
        }

    }

}
