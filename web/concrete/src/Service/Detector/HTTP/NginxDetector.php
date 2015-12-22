<?php

namespace Concrete\Core\Service\Detector\HTTP;

use Concrete\Core\Http\Request;
use Concrete\Core\Service\Detector\DetectorInterface;
use Symfony\Component\HttpFoundation\ServerBag;

class NginxDetector implements DetectorInterface
{

    protected $request;

    /**
     * ApacheDetector constructor.
     * @param \Concrete\Core\Http\Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Determine whether this environment matches the expected service environment
     * @return bool
     */
    public function detect()
    {
        $result = false;
        if (!$result && $this->request->server->has('SERVER_SOFTWARE')) {
            $result = $this->detectFromServer($this->request->server);
        }
        if (!$result && function_exists('apache_get_version')) {
            $result = $this->detectFromSPL();
        }
        if (!$result) {
            $result = $this->detectFromPHPInfo();
        }

        return $result;
    }

    /**
     * Detect using the superglobal server array
     * @param ServerBag $server
     * @return bool
     */
    private function detectFromServer(ServerBag $server)
    {
        return !!preg_match('/\bnginx\//i', $server->get('SERVER_SOFTWARE'));
    }

    /**
     * Detect using SPL apache_get_version()
     * @return bool
     */
    private function detectFromSPL()
    {
        $av = @apache_get_version();
        if (is_string($av) && preg_match('/nginx\//i', $av)) {
            if (preg_match('/\bnginx\//i', $av)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Detect using PHPInfo
     * @return bool
     */
    private function detectFromPHPInfo()
    {
        ob_start();
        phpinfo(INFO_MODULES);
        $info = ob_get_contents();
        ob_end_clean();

        return !!preg_match('/\bnginx\//i', $info);
    }

}
