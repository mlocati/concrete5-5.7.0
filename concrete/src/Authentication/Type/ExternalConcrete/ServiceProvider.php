<?php

namespace Concrete\Core\Authentication\Type\ExternalConcrete;

use Concrete\Core\Foundation\Service\Provider;
use OAuth\ServiceFactory;
use OAuth\UserData\ExtractorFactory;

class ServiceProvider extends Provider
{
    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Foundation\Service\Provider::register()
     */
    public function register()
    {
        $this->app->extend('oauth/factory/extractor', static function (ExtractorFactory $factory) {
            $factory->addExtractorMapping(ExternalConcreteService::class, Extractor::class);

            return $factory;
        });

        $this->app->extend('oauth/factory/service', static function (ServiceFactory $factory) {
            return $factory->registerService('external_concrete', ExternalConcreteService::class);
        });
    }
}
