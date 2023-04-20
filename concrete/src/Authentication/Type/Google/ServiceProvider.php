<?php

namespace Concrete\Core\Authentication\Type\Google;

use Concrete\Core\Foundation\Service\Provider;
use OAuth\OAuth2\Service\Google as GoogleService;
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
            $factory->addExtractorMapping(GoogleService::class, Extractor\Google::class);

            return $factory;
        });
    }
}
