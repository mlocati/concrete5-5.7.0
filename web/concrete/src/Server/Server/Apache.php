<?php
namespace Concrete\Core\Server\Server;

class Apache extends ServerBase
{
    /**
     * @var array
     */
    protected $options;
    /**
     * @param string $handle
     */
    public function __construct($handle, array $options)
    {
        parent::__construct($handle);
        $this->options = $options;
    }

    /**
     * @return bool
     */
    protected function getIsCurrent()
    {
        $result = false;
        if ($result === false && isset($_SERVER['SERVER_SOFTWARE'])) {
            if (preg_match('/Apache\/'.preg_quote($this->options['version'], '/').'\b/i', $_SERVER['SERVER_SOFTWARE'])) {
                $result = true;
            }
        }
        if ($result === false && function_exists('apache_get_version')) {
            $av = @apache_get_version();
            if (is_string($av) && preg_match('/Apache\/\d*\.\d*/i', $av)) {
                if (preg_match('/Apache\/'.preg_quote($this->options['version'], '/').'\b/i', $av)) {
                    $result = true;
                }
            }
        }
        if ($result === false) {
            ob_start();
            phpinfo(INFO_MODULES);
            $info = ob_get_contents();
            ob_end_clean();
            if (preg_match('/Apache\/'.preg_quote($this->options['version'], '/').'\b/i', $info)) {
                $result = true;
            }
        }

        return $result;
    }
}
