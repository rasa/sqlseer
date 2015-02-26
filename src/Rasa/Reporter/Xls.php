<?php
/**
 * Rasa Framework
 *
 * @copyright Copyright (c) 2010-2015 Ross Smith II (http://smithii.com)
 * @license   MIT License (http://opensource.org/licenses/MIT)
 */

# #require_once 'Spreadsheet/Excel/Writer.php';

class Rasa_Reporter_Xls extends Rasa_Reporter_AbstractReporter
{
    /**
     * @var string
     */
    protected $contentType = 'application/vnd.ms-excel';

    /**
     * @var string
     */
    protected $extension = '.xls';

    /**
     * @var string
     */
    protected $filename = 'untitled.xls';

    /**
     * @var boolean
     */
    protected $format = true;

    /**
     * @var boolean
     */
    protected $freezepanes = true;

    /**
     * @var formats
     */
    protected $formats = array();

    /**
     * @var string
     */
    protected $sheetname = 'Sheet 1';

    /**
     * @var string
     */
    protected $tempfile = '';

    /**
     * @var boolean
     */
    protected $widths = true;

    /**
     * @return boolean
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * @param boolean $format
     * @return object $this
     */
    public function setFormat($format)
    {
        $this->format = $format;
        return $this;
    }

    /**
     * @return array
     */
    public function getFormats()
    {
        return $this->formats;
    }

    /**
     * @param array $formats
     * @return object $this
     */
    public function setFormats($formats)
    {
        $this->formats = $formats;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getFreezepanes()
    {
        return $this->freezepanes;
    }

    /**
     * @param boolean $freezepanes
     * @return object $this
     */
    public function setFreezepanes($freezepanes)
    {
        $this->freezepanes = $freezepanes;
        return $this;
    }

    /**
     * @return string
     */
    public function getSheetname()
    {
        return $this->sheetname;
    }

    /**
     * @param string $sheetname
     * @return object $this
     */
    public function setSheetname($sheetname)
    {
        $this->sheetname = $sheetname;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getTempfile()
    {
        return $this->tempfile;
    }

    /**
     * @param boolean $tempfile
     * @return object $this
     */
    public function setTempfile($tempfile)
    {
        $this->tempfile = $tempfile;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getWidths()
    {
        return $this->widths;
    }

    /**
     * @param boolean $widths
     * @return object $this
     */
    public function setWidths($widths)
    {
        $this->widths = $widths;
        return $this;
    }

    /**
     * @param object $worksheet
     * @param int $row
     * @param int $col
     * @param mixed $v
     * @param array $formats
     * @return int length of string
     */
    protected function write($worksheet, $row, $col, $v, $format)
    {
        $type = strtolower(gettype($v));
        switch ($type) {
            case 'integer':
            case 'double':
                if (is_numeric($v)) {
                    $worksheet->writeNumber($row, $col, $v, $format);
                } else {
                    $worksheet->writeString($row, $col, $v, $format);
                }
                break;
            case 'string':
                if (preg_match('~^(ftp|http|https)://(www\.)?(.*)~i', $v, $m)) {
                    $worksheet->writeUrl($row, $col, $v, $m[3]);
                } else {
                    #if (is_numeric($v)) {
                    # $format = $formats['integer'];
                    # $worksheet->writeNumber($row, $col, $v, $format);
                    #} else {
                    $worksheet->writeString($row, $col, $v, $format);
                    #}
                }
                break;
            case 'null':
                $v = '';
                break;
            default:
                $v = 'Unknown type: ' . $type;
                $worksheet->writeString($row, $col, $v, $format);
        }
        return strlen($v);
    }

    protected function writex($worksheet, $row, $col, $v, $format, $metadata)
    {
        static $date_formats = array(
            'MYSQLI_TYPE_DATE' => 'yyyy-mm-dd',
            'MYSQLI_TYPE_DATETIME' => 'yyyy-mm-dd hh:mm;ss',
            'MYSQLI_TYPE_NEWDATE' => 'yyyy-mm-dd',
            'MYSQLI_TYPE_TIME' => 'hh:mm:ss',
            'MYSQLI_TYPE_TIMESTAMP' => 'yyyy-mm-dd hh:mm;ss'
        );

        $type = strtolower(gettype($v));
        switch ($type) {
            case 'integer':
            case 'double':
            case 'string':
                if (strlen($v) > 0) {
                    if ($metadata->numeric) {
                        $v = (double) $v;
                        $worksheet->writeNumber($row, $col, $v, $format);
                    } else {
                        if (preg_match('~^(ftp|http|https)://(www\.)?(.*)~i', $v, $m)) {
                            $worksheet->writeUrl($row, $col, $v, $m[3], $format);
                        } else {
                            switch ($metadata->field_type) {
                                case 'MYSQLI_TYPE_DATETIME':
                                case 'MYSQLI_TYPE_NEWDATE':
                                case 'MYSQLI_TYPE_TIMESTAMP':
                                case 'MYSQLI_TYPE_DATE':
                                    $tz = date_default_timezone_get();
                                    date_default_timezone_set('UTC');
                                    $t = strtotime($v);
                                    date_default_timezone_set($tz);
                                    if ($t === false) {
                                        $worksheet->writeString($row, $col, $v, $format);
                                        break;
                                    }
                                    $t += 25569 * 86400;
                                    if ($t < 0) {
                                        $worksheet->writeString($row, $col, $v, $format);
                                        break;
                                    }
                                    $days    = intval($t / 86400);
                                    $seconds = $t - ($days * 86400);
                                    $v       = $days + ($seconds / 86400);
                                    $worksheet->writeNumber($row, $col, $v, $format);
                                    break;
                                case 'MYSQLI_TYPE_TIME':
                                    $tz = date_default_timezone_get();
                                    date_default_timezone_set('UTC');
                                    $t = strtotime($v);
                                    date_default_timezone_set($tz);
                                    if ($t === false) {
                                        $worksheet->writeString($row, $col, $v, $format);
                                        break;
                                    }
                                    $t += 25569 * 86400;
                                    if ($t < 0) {
                                        $worksheet->writeString($row, $col, $v, $format);
                                        break;
                                    }
                                    $days    = intval($t / 86400);
                                    $seconds = $t - ($days * 86400);
                                    $v       = $seconds / 86400;
                                    $worksheet->writeNumber($row, $col, $v, $format);
                                    break;
                                default:
                                    $worksheet->writeString($row, $col, $v, $format);
                                    break;
                            }
                        }
                    }
                }
                break;
            case 'null':
                $v = '';
                break;
            default:
                $v = 'Unknown type: ' . $type;
                $worksheet->writeString($row, $col, $v, $format);
        }
        return strlen($v);
    }

    /**
     * @param boolean $echo
     * @return boolean
     */
    public function export($echo = true)
    {
        static $date_formats = array(
            'MYSQLI_TYPE_DATE' => 'yyyy-mm-dd',
            'MYSQLI_TYPE_DATETIME' => 'yyyy-mm-dd hh:mm:ss',
            'MYSQLI_TYPE_NEWDATE' => 'yyyy-mm-dd',
            'MYSQLI_TYPE_TIME' => 'hh:mm:ss',
            'MYSQLI_TYPE_TIMESTAMP' => 'yyyy-mm-dd hh:mm:ss'
        );

        $fields = $this->getFieldCount();

        if (!$fields) {
            return false;
        }

        $addtitles   = $this->titles;
        $filename    = $this->filename;
        $freezepanes = $this->freezepanes;
        $formats     = $this->formats;
        $setwidths   = $this->widths;
        $sheetname   = $this->sheetname;

        $widths = array_fill(0, $fields, 0);

        $path = sys_get_temp_dir() . '/' . $filename;

        # quiet logs
        $old_error_reporting = error_reporting(error_reporting() & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);

        $workbook = new Spreadsheet_Excel_Writer($path);
        $workbook->setTempDir(sys_get_temp_dir());

        $formats       = array();
        $title_formats = array();
        foreach ($this->metadata as $metadata) {
            $format = $workbook->addFormat();
            $format->setAlign($metadata->align);
            $title_formats[] = $format;

            $format = $workbook->addFormat();
            $format->setAlign($metadata->align);
            while (true) {
                if ($metadata->format && $metadata->numeric) {
                    $s = '#,##0';
                    if ($metadata->places > 0) {
                        $s .= '.' . str_repeat('0', $metadata->places);
                    }
                    if ($metadata->field_type == 'MYSQLI_TYPE_YEAR') {
                        $s = '#0';
                    }
                    $format->setNumFormat($s);
                    break;
                }

                if (isset($date_formats[$metadata->field_type])) {
                    $format->setNumFormat($date_formats[$metadata->field_type]);
                    break;
                }

                break;
            }

            $formats[] = $format;
        }

        $worksheet = $workbook->addWorksheet($sheetname);
        $worksheet->setInputEncoding('UTF-8');

        $row = 0;
        $col = 0;

        if ($addtitles) {
            $field_names = $this->getFieldNames();
            foreach ($field_names as $field_name) {
                $strlen       = $this->write($worksheet, $row, $col, $field_name, $title_formats[$col]);
                $widths[$col] = max($widths[$col], $strlen);
                ++$col;
            }
            ++$row;
            $col = 0;
        }

        $freezepanes = $freezepanes && $addtitles
            ? array(1, 0, 1, 0)
            : array();

        if (count($freezepanes)) {
            $worksheet->freezePanes($freezepanes);
            $worksheet->setSelection($freezepanes[0], $freezepanes[1], $freezepanes[2], $freezepanes[3]);
        }

        $hasNumIndex = true;

        $row_count = $this->getRowCount();

        for ($row_num = 0; $row_num < $row_count; ++$row_num) {
            $the_row = $this->getRow($row_num);

            if ($hasNumIndex) {
                for ($i = 0; $i < $fields; ++$i) {
                    $v            = $the_row[$i];
                    #$strlen = $this->write($worksheet, $row, $col, $v, $formats);
                    $strlen       = $this->writex($worksheet, $row, $col, $v, $formats[$i], $this->metadata[$i]);
                    $widths[$col] = max($widths[$col], $strlen);
                    ++$col;
                }
            } else {
                foreach ($the_row as $k => $v) {
                    #$strlen = $this->write($worksheet, $row, $col, $v, $formats);
                    $strlen       = $this->writex($worksheet, $row, $col, $v, $formats[$i], $this->metadata[$i]);
                    $widths[$col] = max($widths[$col], $strlen);
                    ++$col;
                }
            }
            ++$row;
            $col = 0;
        }

        if ($setwidths) {
            for ($i = 0; $i < $fields; ++$i) {
                $w = 0.29 + $widths[$i];
                $worksheet->setColumn($i, $i, $w);
            }
        }

        $this->tempfile = $path;

        $rv = false;

        if (is_file($path)) {
            $rv = file_get_contents($path);
        }

        $workbook->close();

        error_reporting($old_error_reporting);

        if (is_file($path)) {
            $rv = file_get_contents($path);
            unlink($path);
        }

        if ($echo) {
            $workbook->send($filename);

            echo $rv;
        }

        return $rv;
    }
}

# EOF
