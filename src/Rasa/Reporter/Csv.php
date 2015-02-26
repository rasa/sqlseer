<?php
/**
 * Rasa Framework
 *
 * @copyright Copyright (c) 2010-2015 Ross Smith II (http://smithii.com)
 * @license   MIT License (http://opensource.org/licenses/MIT)
 */

/**
 */
class Rasa_Reporter_Csv extends Rasa_Reporter_AbstractReporter
{
    /**
     * @var string
     */
    protected $contentType = 'text/comma-separated-values';

    /**
     * @var string
     */
    protected $extension = '.csv';

    /**
     * @var string
     */
    protected $filename = 'untitled.csv';

    /**
     * @param string $s
     * @return string
     */
    protected function fixcell($s)
    {
        $s = str_replace('"', '""', $s);
        if (preg_match('/,/', $s)) {
            $s = '"' . $s . '"';
        }
        return $s;
    }

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
            $col         = 0;
            foreach ($field_names as $field_name) {
                if ($col++) {
                    $rv .= ',';
                }
                $rv .= $this->fixcell($field_name);
            }
            $rv .= "\r\n";
        }

        $rows = $this->getRowCount();

        for ($i = 0; $i < $rows; ++$i) {
            $row = $this->getRow($i);

            for ($j = 0; $j < $fields; ++$j) {
                if ($j) {
                    $rv .= ',';
                }
                $rv .= $this->fixcell($row[$j]);
            }
            $rv .= "\r\n";
        }

        if ($echo) {
            $this->sendHeaders();
            echo $rv;
        }

        return $rv;
    }
}

# EOF
