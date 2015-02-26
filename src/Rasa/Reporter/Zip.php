<?php
/**
 * Rasa Framework
 *
 * @copyright Copyright (c) 2010-2015 Ross Smith II (http://smithii.com)
 * @license   MIT License (http://opensource.org/licenses/MIT)
 */

/**
 */
class Rasa_Reporter_Zip extends Rasa_Reporter_AbstractReporter
{
  /**
   * @var object
   */
  protected $_class = null;

  /**
   * @var string
   */
  protected $contentType = 'application/zip';

  /**
   * @var string
   */
  protected $extension = '.zip';

  /**
   * @var string
   */
  protected $file = '';

  /**
   * @var string
   */
  protected $filename = 'untitled.zip';

  /**
   * @return object
   */
  public function getClass()
  {
    return $this->_class;
  }

  /**
   * @param string $class
   * @return object $this
   */
  public function setClass($_class)
  {
    $this->_class = $_class;
    return $this;
  }

  /**
   * @return string
   */
  public function getFile()
  {
    return $this->file;
  }

  /**
   * @param string $file
   * @return object $this
   */
  public function setFile($file)
  {
    $this->file = $file;
    return $this;
  }

  /**
   * @param string $file
   * @return string
   */
  protected function getData()
  {
    $this->_class->setMetadata($this->metadata);
    $this->_class->setParams($this->params);
    $this->_class->setRows($this->rows);
    $this->_class->setTitle($this->title);

    return $this->_class->export(false);
  }

  /**
   * @param boolean $echo
   * @return boolean
   */
  public function export($echo = true)
  {
    $z = new ZipArchive();

    $data = $this->getData();

    $filename = $this->getFilename();
    $path = pathinfo($filename, PATHINFO_FILENAME) . $this->_class->getExtension();
    $zip = sys_get_temp_dir() . '/' . uniqid() . '-' . $filename;
    $rv = $z->open($zip, ZIPARCHIVE::CREATE | ZIPARCHIVE::OVERWRITE);
    $rv = $z->addFromString($path, $data);
    $rv = $z->close();
    $zipdata = file_get_contents($zip);

    if ($echo) {
      $this->sendHeaders();
      echo $zipdata;
    }

    return $zipdata;
  }
}

# EOF
