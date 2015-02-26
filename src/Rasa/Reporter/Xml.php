<?php
/**
 * Rasa Framework
 *
 * @copyright Copyright (c) 2010-2015 Ross Smith II (http://smithii.com)
 * @license   MIT License (http://opensource.org/licenses/MIT)
 */

/**
 */
class Rasa_Reporter_Xml extends Rasa_Reporter_AbstractReporter
{
  /**
   * @var string
   */
  protected $contentType = 'text/xml';

  /**
   * @var string
   */
  protected $extension = '.xml';

  /**
   * @var string
   */
  protected $filename = 'untitled.xml';

  /**
   * @var string
   */
  protected $sql = '';

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
    $this->sql = $sql;
    return $this;
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

    $this->sendHeaders();

    $sql = htmlspecialchars($this->sql, ENT_QUOTES);

    $rv = <<<EOT
<?xml version="1.0"?>
<resultset statement="$sql" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
EOT;

    $names = $this->getFieldNames();

    for ($j = 0; $j < $fields; ++$j) {
          $names[$j] = htmlspecialchars($names[$j], ENT_QUOTES);
    }

    $rows = $this->getRowCount();

    for ($i = 0; $i < $rows; ++$i) {
      $rv .= "\t<row>\n";
      $row = $this->getRow($i);

      for ($j = 0; $j < $fields; ++$j) {
            $rv .= sprintf("\t\t<field name='%s'>%s</field>\n", $names[$j], htmlspecialchars($row[$j], ENT_QUOTES));
      }
      $rv .=  "\t</row>\n";
    }

    $rv .=  "</resultset>\n";

    if ($echo) {
      echo $rv;
    }

    return $rv;
  }
}

# EOF
