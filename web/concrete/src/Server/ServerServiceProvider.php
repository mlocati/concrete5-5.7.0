<?php
namespace Concrete\Core\Server;

use Core;
use Config;

class ServerServiceProvider extends \Concrete\Core\Foundation\Service\Provider
{
    public function register()
    {
        Core::bindShared('server/manager', function () {
            $manager = Core::make('\\Concrete\\Core\\Server\\Manager');
            /* @var Manager $manager */
            foreach (Config::get('servers') as $handle => $config) {
                $instanceClassName = $config['class'];
                unset($config['class']);
                $instance = new $instanceClassName($handle, $config);
                $manager->register($instance);
            }

            return $manager;
        });
    }
}
