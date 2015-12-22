<?php
namespace Concrete\Core\Server\Server;

class Nginx extends ServerBase
{
    /**
     * @param string $handle
     */
    public function __construct($handle, $options)
    {
        parent::__construct($handle);
    }

    /**
     * @return bool
     */
    protected function getIsCurrent()
    {
        $result = false;
        if ($result === false && isset($_SERVER['SERVER_SOFTWARE'])) {
            if (preg_match('/\bnginx\//i', $_SERVER['SERVER_SOFTWARE'])) {
                $result = true;
            }
        }
        if ($result === false && function_exists('apache_get_version')) {
            $av = @apache_get_version();
            if (is_string($av) && preg_match('/nginx\//i', $av)) {
                if (preg_match('/\bnginx\//i', $av)) {
                    $result = true;
                }
            }
        }
        if ($result === false) {
            ob_start();
            phpinfo(INFO_MODULES);
            $info = ob_get_contents();
            ob_end_clean();
            if (preg_match('/\bnginx\//i', $info)) {
                $result = true;
            }
        }

        return $result;
    }
}
