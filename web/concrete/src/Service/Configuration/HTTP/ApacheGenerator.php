<?php

namespace Concrete\Core\Service\Configuration\HTTP;

use Concrete\Core\Application\Application;
use Concrete\Core\Service\Configuration\GeneratorInterface;

class ApacheGenerator implements GeneratorInterface
{

    /** @var string Apache version */
    protected $version;

    /** @var Application */
    protected $app;

    public function __construct($version, Application $application)
    {
        $this->version = $version;
        $this->app = $application;
    }

    /**
     * @return string
     */
    public function generate()
    {
        if ($this->app['config']['concrete.seo.url_rewriting']) {
            return "
# concrete5 pretty urls
<IfModule mod_rewrite.c>
RewriteEngine On
RewriteBase /
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME}/index.html !-f
RewriteCond %{REQUEST_FILENAME}/index.php !-f
RewriteRule . index.php [L]
</IfModule>
# end concrete5 pretty urls
";
        } else {
            return "";
        }

    }

}
