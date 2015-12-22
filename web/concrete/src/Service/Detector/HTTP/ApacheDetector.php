<?php

namespace Concrete\Core\Service\Detector\HTTP;

use Concrete\Core\Service\Detector\DetectorInterface;

class ApacheDetector implements DetectorInterface
{

    /**
     * @var string
     */
    protected $version;

    /**
     * ApacheDetector constructor.
     * @param $version The version
     */
    public function __construct($version)
    {
        $this->version = $version;
    }

    /**
     * Determine whether this environment matches the expected service environment
     * @return bool
     *
     * @todo remove `$_SERVER` superglobal references
     */
    public function detect()
    {
        $result = false;
        if (!$result && isset($_SERVER['SERVER_SOFTWARE'])) {
            $result = $this->detectFromServer($_SERVER);
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
     * @param $server
     * @return bool
     */
    private function detectFromServer($server)
    {
        return !!preg_match('/Apache\/'.preg_quote($this->version, '/').'\b/i', $server['SERVER_SOFTWARE']);
    }

    /**
     * Detect using SPL apache_get_version()
     * @return bool
     */
    private function detectFromSPL()
    {
        $av = @apache_get_version();
        if (is_string($av) && preg_match('/Apache\/\d*\.\d*/i', $av)) {
            if (preg_match('/Apache\/'.preg_quote($this->version, '/').'\b/i', $av)) {
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

        return !!preg_match('/Apache\/'.preg_quote($this->version, '/').'\b/i', $info);
    }


}
