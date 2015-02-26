<?php
/**
 * Rasa Framework
 *
 * @copyright Copyright (c) 2010-2015 Ross Smith II (http://smithii.com)
 * @license   MIT License (http://opensource.org/licenses/MIT)
 */

/**
 */
class Rasa_Logger
{
    /**
     */
    public function debug($s)
    {
        $s = print_r($s, true);
        $a = preg_split('/\n/', $s);
        foreach ($a as $l) {
            error_log('[DEBUG] ' . $l);
        }
    }

    /**
     */
    public function err($s)
    {
        $s = print_r($s, true);
        $a = preg_split('/\n/', $s);
        foreach ($a as $l) {
            error_log('[ERR] ' . $l);
        }
    }
}

# EOF
