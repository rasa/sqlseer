<?php
/**
 * Rasa Framework
 *
 * @copyright Copyright (c) 2010-2015 Ross Smith II (http://smithii.com)
 * @license   MIT License (http://opensource.org/licenses/MIT)
 */

/**
 */
class Rasa_Reporter_Txt extends Rasa_Reporter_AbstractReporter
{
    /**
     * @var string
     */
    protected $contentType = 'text/plain';

    /**
     * @var string
     */
    protected $extension = '.txt';

    /**
     * @var string
     */
    protected $filename = 'untitled.txt';

    /**
     * @param boolean $echo
     * @return boolean
     */
    public function export($echo = true)
    {
        $fields = $this->getFieldCount();

        if (!$fields) {
            return false;
        }

        $rv = '';

        if ($this->titles) {
            $field_names = $this->getFieldNames();
            $rv .= join("\t", $field_names) . "\r\n";
        }

        $rows = $this->getRowCount();

        for ($i = 0; $i < $rows; ++$i) {
            $row = $this->getRow($i);
            $rv .= join("\t", $row) . "\r\n";
        }

        if ($echo) {
            $this->sendHeaders();
            echo $rv;
        }

        return $rv;
    }
}

# EOF
