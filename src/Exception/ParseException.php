<?php

namespace Maximethebault\XmlParser\Exception;

class ParseException extends \Exception
{
    /**
     * Line at which the error happened
     *
     * @var int
     */
    private $_lineNumber;

    public function __construct($lineNumber, $message = '', $code = 0, $previous = null) {
        parent::__construct($message, $code, $previous);
        $this->_lineNumber = $lineNumber;
    }

    /**
     * Getter for $_lineNumber
     *
     * @return int
     *
     * @see $_lineNumber
     */
    public function getLineNumber() {
        return $this->_lineNumber;
    }
} 