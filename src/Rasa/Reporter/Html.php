<?php
/**
 * Rasa Framework
 *
 * @copyright Copyright (c) 2010-2015 Ross Smith II (http://smithii.com)
 * @license   MIT License (http://opensource.org/licenses/MIT)
 */

/**
 */
class Rasa_Reporter_Html extends Rasa_Reporter_AbstractReporter
{
    /**
     * @var int
     */
    const MAX_PLACES = 6;

    /**
     * @var string
     */
    protected $contentType = 'text/html';

    /**
     * @var string
     */
    protected $extension = '.html';

    /**
     * @var string
     */
    protected $filename = 'untitled.html';

    /**
     * @var boolean
     */
    protected $links = false;

    /**
     * @var boolean
     */
    protected $totals = false;

    /**
     * @return boolean
     */
    public function getLinks()
    {
        return $this->links;
    }

    /**
     * @param boolean $links
     * @return object $this
     */
    public function setLinks($links)
    {
        $this->links = $links;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getTotals()
    {
        return $this->totals;
    }

    /**
     * @param boolean $totals
     * @return object $this
     */
    public function setTotals($totals)
    {
        $this->totals = $totals;
        return $this;
    }

    /**
     * @todo fixme refactor this soon
     *
     * @param boolean $addUrls
     * @return string
     */
    public function getTable()
    {
        $htmlspecialchars_options = ENT_QUOTES;
        if (defined('ENT_SUBSTITUTE')) {
            $htmlspecialchars_options |= constant('ENT_SUBSTITUTE');
        }
        #if (defined('ENT_HTML5')) {
        # $htmlspecialchars_options |= constant('ENT_HTML5');
        #}

        $links = $this->links;

        $fields = $this->getFieldCount();

        if ($fields <= 0) {
            return '';
        }

        $rows = $this->getRowCount();

        $names = $this->getFieldNames();

        $numeric = array_fill(0, $fields, 0);
        $places  = array_fill(0, $fields, 0);
        $format  = array_fill(0, $fields, false);

        for ($j = 0; $j < $fields; ++$j) {
            $numeric[$j] = $this->metadata[$j]->numeric;
            if ($numeric[$j]) {
                $places[$j] = $this->metadata[$j]->places;
                /*
                if ($this->metadata[$j]->decimals > 0) {
                $places[$j] = $this->metadata[$j]->decimals > $this->metadata[$j]->max_length
                ? $this->metadata[$j]->max_length
                : $this->metadata[$j]->decimals;
                $places[$j] = min($places[$j], self::MAX_PLACES);
                #  123456789012345678
                #0.992699999999999250

                }
                */
            }

        }

        for ($j = 0; $j < $fields; ++$j) {
            $format[$j] = $this->metadata[$j]->format;
        }

        for ($j = 0; $j < $fields; ++$j) {
            $align[$j] = $this->metadata[$j]->alignment == 'right' ? 'r' : '';
        }

        $thead = "<thead>\n\t<tr>\n";

        if ($this->getOption('row_column')) {
            $class = ' class="r"';
            $thead .= sprintf("\t\t<th%s>%s</th>\n", $class, '#');
        }

        for ($j = 0; $j < $fields; ++$j) {
            $class = $align[$j];
            $c     = preg_replace('/_+/', ' ', $names[$j]);
            $c     = ucwords($c);
            $c     = htmlspecialchars($c, $htmlspecialchars_options);

            $c = rtrim($c, '_ ');

            $orgname = 'calculated field';

            if (isset($this->metadata[$j]->orgtable) && $this->metadata[$j]->orgtable > '') {
                $orgname = $this->metadata[$j]->orgtable . '.' . $this->metadata[$j]->orgname;
                if (isset($this->metadata[$j]->db)) {
                    $orgname = $this->metadata[$j]->db . '.' . $this->metadata[$j]->db;
                }
            }

            $orgname = 'source: ' . $orgname;

            $_title = $orgname ? sprintf(' title="%s"', $orgname) : '';

            #$_title = '';

            if (!$links || preg_match('/^(#|row)$/', $names[$j])) {
                $class = $class ? sprintf(' class="%s"', $class) : '';
                $thead .= sprintf("\t\t<th%s><span%s>%s</span></th>\n", $class, $_title, $c);
            } else {
                # @todo fixme replace $_SERVER['REQUEST_URI'] with passed param
                $parts = parse_url($_SERVER['REQUEST_URI']);
                if (isset($parts['query'])) {
                    parse_str($parts['query'], $query);
                    unset($query['orderby']);
                    unset($query['desc']);
                    #unset($query['go']);
                } else {
                    $query = array();
                }
                $query['orderby'] = $names[$j];
                if (isset($this->params['orderby'])
                  && ($this->params['orderby'] == $names[$j])
                  && !isset($this->params['desc'])) {
                    # @todo parameterize?
                    $query['desc'] = 1;
                }
                $url = $parts['path'] . '?' . http_build_query($query);

                $prearrow  = '';
                $postarrow = '';
                if (isset($this->params['orderby'])
                  && ($this->params['orderby'] == $names[$j])) {
                    $arrow = isset($this->params['desc'])
                      && Rasa_Reporter::is($this->params['desc'])
                        ? '^'
                        : 'v';

                    $arrow = sprintf('<span class="smaller">%s</span>', $arrow);

                    if ($class > '') {
                        $prearrow = $arrow . '&nbsp;';
                    } else {
                        $postarrow = '&nbsp;' . $arrow;
                    }
                    $class .= ' b';
                }
                $class = $class ? sprintf(' class="%s"', $class) : '';
                $thead .= sprintf(
                    "\t\t<th%s>%s<a href=\"%s\"%s>%s</a>%s</th>\n",
                    $class,
                    $prearrow,
                    $url,
                    $_title,
                    $c,
                    $postarrow
                );
            }
        }
        $thead .= "\t</tr>\n";

        $thead .= "</thead>\n";

        $tbody = "<tbody>\n";

        $placeholder = "Use '*' to match zero, or more, characters.
Use '?' to match any single character.
You may prefix search terms with
'>', '>=', '<', '<=', '<>', or '~' (sounds like).
Use commas to separate multiple search terms.
";
        $placeholder = htmlspecialchars($placeholder);

        if ($links) {
            $tbody .= "\t<tr>\n";

            if ($this->getOption('row_column')) {
                $class = ' class="r"';
                $tbody .= sprintf("\t\t<td%s>%s</td>\n", $class, '&nbsp;');
            }

            for ($j = 0; $j < $fields; ++$j) {
                $class      = $align[$j] ? sprintf(' class="%s"', $align[$j]) : '';
                $fieldName  = $names[$j];
                $value      = isset($this->params['f'][$fieldName])
                    ? htmlspecialchars($this->params['f'][$fieldName], $htmlspecialchars_options)
                    : '';
                $xfieldName = htmlspecialchars($fieldName);

                $classx = 'width100';

                $tbody .= sprintf("\t\t<td%s><input type='text' id='f%s' name='f[%s]' value='%s' class=\"%s\" title=\"%s\"/></td>\n", $class, $xfieldName, $xfieldName, $value, $classx, $placeholder);
            }
            $tbody .= "\t</tr>\n";
        }

        $actualRows = $this->totals ? max($rows - 1, 0) : $rows;

        $urls = $this->getUrls();

        static $classes = array(0 => '', 1 => ' class="e"');

        $offset = $this->getOffset() + 1;

        for ($i = 0; $i < $actualRows; ++$i) {
            $class = $classes[$i % 2];
            $tbody .= sprintf("\t<tr%s>\n", $class);
            $row = $this->getRow($i);

            if ($this->getOption('row_column')) {
                $class  = ' class="r"';
                $row_id = $offset + $i;
                $id     = sprintf('R%d', $row_id);
                $tbody .= sprintf(
                    "\t\t<td id='%s'%s><a href='#%s'/>%s</td>\n",
                    $id,
                    $class,
                    $id,
                    $row_id
                );
            }

            for ($j = 0; $j < $fields; ++$j) {
                if (!array_key_exists($j, $row)) {
                    continue;
                }
                $c = $row[$j];
                #$c = bin2hex($c);
                # @todo do total row last!

                if ($c) {
                    foreach ($urls as $k => $v) {
                        if (preg_match($k, $names[$j])) {
                            $c = sprintf($v, $c);
                        }
                    }

                    # @todo(rasa) add support for new TLDs
                    if (preg_match("/^[a-z0-9!#\$%&'\*\+\/=\?\^_`\{\|\}~\-]+(?:\.[a-z0-9!#\$%&'\*\+\/=\?^_`{\|}~\-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+(?:[A-Z0-9\-]{2,63})$/i", $c)) {
                        $text = $c;
                        $c    = 'mailto:' . $c;
                        $c    = sprintf("<a target='_blank' href=\"%s\">%s</a>",
                            $c,
                            htmlspecialchars($text,
                            $htmlspecialchars_options)
                        );
                    } elseif (preg_match('~^(ftp|http|https)://(www\.)?(.*)~i', $c, $m)) {
                        if ($c == $row[$j]) {
                            $text  = $m[3];
                            $title = strlen($text) > 80 ? substr($text, 0, 80) . '...' : $text;
                        } else {
                            $text  = $row[$j];
                            $title = $row[$j];
                        }
                        $c = sprintf("<a target='_blank' href=\"%s\" title='%s'>%s</a>",
                            $c,
                            htmlspecialchars($text,
                            $htmlspecialchars_options),
                            htmlspecialchars($title,
                            $htmlspecialchars_options)
                        );
                    } elseif ($format[$j] && is_numeric($c)
                            && ($this->metadata[$j]->field_type <> 'MYSQLI_TYPE_YEAR')) {
                        $c = number_format($c, $places[$j]);
                    } else {
                        #$c = bin2hex($c);
                        #$c = htmlentities($c, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5);
                        $c = htmlspecialchars($c, $htmlspecialchars_options);
                    }
                }
                $class = $align[$j] ? sprintf(' class="%s"', $align[$j]) : '';
                $tbody .= sprintf("\t\t<td%s>%s</td>\n", $class, $c);
            }
            $tbody .= "\t</tr>\n";
        }

        if ($this->totals) {
            $class = ' class="eee"';
            $tbody .= sprintf("\t<tr%s>\n", $class);
            $row = $this->getRow($actualRows);

            if ($this->getOption('row_column')) {
                $class = ' class="r"';
                $tbody .= sprintf("\t\t<td%s>%s</td>\n", $class, 'Totals');
            }

            for ($j = 0; $j < $fields; ++$j) {
                if (!array_key_exists($j, $row)) {
                    continue;
                }
                $c = $row[$j];

                if (strlen($c) > 0) {
                    $c = $format[$j]
                      && is_numeric($c)
                      && ($this->metadata[$j]->field_type <> 'MYSQLI_TYPE_YEAR')
                        ? number_format($c, $places[$j])
                        : htmlspecialchars($c, $htmlspecialchars_options);
                }

                $class = $align[$j] ? sprintf(' class="%s"', $align[$j]) : '';
                $tbody .= sprintf("\t\t<td%s>%s</td>\n", $class, $c);
            }
            $tbody .= "\t</tr>\n";
        }

        $tbody .= "</tbody>\n";

        $table = $thead . $tbody;

        #$table .= sprintf("<i style='font-size:smaller'>%s rows.</i>&nbsp;", number_format($actualRows, 0));
        #$c = date('M. jS, Y \a\t g:i:sa T');
        #$table .= sprintf("<i style='font-size:smaller'>Generated at %s.</i> ", $c);

        return $table;
    }

    /**
     * @param boolean $echo
     * @return boolean
     */
    public function export($echo = true)
    {
        $title = htmlspecialchars($this->title, ENT_QUOTES);

        # @todo fixme replace $_SERVER['DOCUMENT_ROOT'] with passed param

        $style = file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/default.css');

        $table = $this->getTable();
        $rv    = <<<EOT
<!DOCTYPE html>
<html>
<head>
<title>$title</title>
<meta name="viewport" content="width=device-width, initial-scale=1"/>
<style>
/*<![CDATA[*/
$style
/*]]>*/
</style>
</head>
<body>
<h2>$title</h2>
<table>
$table
</table>
</body>
</html>
EOT;
        if ($echo) {
            $this->sendHeaders();

            echo $rv;
        }

        return $rv;
    }
}

# EOF
