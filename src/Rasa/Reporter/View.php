<?php
/**
 * Rasa Framework
 *
 * @copyright Copyright (c) 2010-2015 Ross Smith II (http://smithii.com)
 * @license   MIT License (http://opensource.org/licenses/MIT)
 */

/**
 */
class Rasa_Reporter_View
{
    /**
     */
    public function __construct($view = null)
    {
        $this->__view = $view;
    }

    /**
     * @return string
     */
    public function getView()
    {
        return $this->__view;
    }

    /**
     * @param string $view
     * @return object $this
     */
    public function setView($view)
    {
        $this->__view = $view;
        return $this;
    }

    /**
     */
    public function render()
    {
        return include($this->__view);
    }
}

# EOF
