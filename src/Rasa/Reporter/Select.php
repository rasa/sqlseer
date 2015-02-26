<?php
/**
 * Rasa Framework
 *
 * @copyright Copyright (c) 2010-2015 Ross Smith II (http://smithii.com)
 * @license   MIT License (http://opensource.org/licenses/MIT)
 */

/**
 */
class Rasa_Reporter_Select
{
    /**
     * @var string
     */
    protected $filename = '';

    /**
     * @var string
     */
    protected $format = '';

    /**
     * @var array
     */
    protected $metadata = array();

    /**
     * @var array
     */
    protected $params = array();

    /**
     * @var array
     */
    protected $rows = array();

    /**
     * @var string
     */
    protected $sql = '';

    /**
     * @var string
     */
    protected $title = '';

    /**
     * @var array
     */
    protected $urls = array();

    /**
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * @param string $filename
     * @return object $this
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;
        return $this;
    }

    /**
     * @return string
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * @param string $format
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
    public function getMetadata()
    {
        return $this->metadata;
    }

    /**
     * @param array $metadata
     * @return object $this
     */
    public function setMetadata($metadata)
    {
        $this->metadata = $metadata;
        return $this;
    }

    /**
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * @param string
     * @return object $this
     */
    public function setParams($params)
    {
        $this->params = $params;
        return $this;
    }

    /**
     * @return array
     */
    public function getRows()
    {
        return $this->rows;
    }

    /**
     * @param array $rows
     * @return object $this
     */
    public function setRows($rows)
    {
        $this->rows = $rows;
        return $this;
    }

    /**
     * @return string
     */
    public function getSql()
    {
        return $this->sql;
    }

    /**
     * @param string $sql
     * @return object $this
     */
    public function setSql($sql)
    {
        $sql       = preg_replace('/"/', "'", $sql);
        $this->sql = $sql;
        return $this;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return object $this
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return array
     */
    public function getUrls()
    {
        return $this->urls;
    }

    /**
     * @param array $urls
     * @return object $this
     */
    public function setUrls($urls)
    {
        $this->urls = $urls;
        return $this;
    }

    /**
     * @param array $rows
     * @param string $format
     * @param string $filename
     * @param string $title
     * @return void
     */
    public function __construct($rows = array(), $format = '', $filename = '', $title = '')
    {
        if ($rows) {
            $this->setRows($rows);
        }

        if ($format) {
            $this->setFormat($format);
        }

        if ($filename) {
            $this->setFilename($filename);
        }

        if ($title) {
            $this->setTitle($title);
        }
    }

    /**
     * @param string $name
     * @param string $selected
     * @return string
     */
    static public function getSelectHtml($name = '', $selected = '')
    {
        if (!$name) {
            $name = 'format';
        }

        if ($selected === null) {
            if (isset($this->params[$name])) {
                $selected = $this->params[$name];
            }
        }

        static $options = array(
            '' => '--Save As--',
            'csv' => 'Comma Separated Values (*.csv)',
            'xls' => 'Excel Spreadsheet (*.xls)',
            'html' => 'HTML (*.html)',
            'txt' => 'Tab Separated Values (*.txt)',
            'xml' => 'XML (*.xml)',
            'zip' => 'Zip (of xls) (*.zip)'
        );

        #$rv = sprintf("<select name='%s' id='%s'>\n", $name, $name);
        $rv = '';
        foreach ($options as $k => $v) {
            $x = strcasecmp($selected, $k) == 0 ? ' selected="selected"' : '';
            $rv .= sprintf("\t<option value='%s'%s>%s</option>\n", $k, $x, $v);
        }
        #$rv .= "</select>\n";

        return $rv;
    }

    /**
     * @return void
     */
    public function export()
    {
        $format = strtolower($this->format);

        switch ($format) {
            case 'csv':
                $x = new Rasa_Reporter_Csv();
                break;

            case 'html':
                $x = new Rasa_Reporter_Html();
                break;

            case 'txt':
                $x = new Rasa_Reporter_Txt();
                break;

            case 'xls':
                $x = new Rasa_Reporter_Xls();
                break;

            case 'xml':
                $x = new Rasa_Reporter_Xml();
                $x->setSql($this->sql);
                break;

            case 'zip':
                $x     = new Rasa_Reporter_Zip();
                $class = new Rasa_Reporter_Xls();
                #$this->filename = pathinfo($this->filename, PATHINFO_FILENAME) . $class->getExtension() . $x->getExtension();
                #$class->setFilename(pathinfo($this->filename, PATHINFO_FILENAME) . $class->getExtension());
                $x->setClass($class);
                break;

            default:
                return false;
        }

        $x->setFilename($this->filename);
        $x->setMetadata($this->metadata);
        $x->setParams($this->params);
        $x->setRows($this->rows);
        $x->setTitle($this->title);
        $x->setUrls($this->urls);

        $rv = $x->export();
        exit(0);
    }
}

# EOF
