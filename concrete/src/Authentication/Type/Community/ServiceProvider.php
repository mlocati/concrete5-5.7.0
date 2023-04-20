<?php

namespace Concrete\Core\Authentication\Type\Community;

use Concrete\Core\Foundation\Service\Provider;
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
            $factory->addExtractorMapping(Service\Community::class, Extractor\Community::class);

            return $factory;
        });
    }
}
