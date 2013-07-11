<?php

/*

Copyright (c) 2013 by Matt Zabriskie

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.

 */

class CliArgParser {
    private $args;
    private $extra;

    function __construct($args) {
        $this->args = array();
        $this->extra = array();

        $source = &$this->args;
        for ($i=0; $i<sizeof($args); $i++) {
            // Any args that come after -- are considered extra args
            if ($args[$i] == '--') {
                $source = &$this->extra;
                continue;
            }
            $source[] = $args[$i];
        }
    }

    function hasOption($short, $long = null) {
        // Look for explicit options
        if (!is_null($short) && in_array('-' . $short, $this->args)) return true;
        if (!is_null($long) && in_array('--' . $long, $this->args)) return true;

        // Look for joined short option (e.g., -rf)
        for ($i=0; $i<sizeof($this->args); $i++) {
            if ($this->args[$i]{1} != '-' && strpos($this->args[$i], $short) > -1) {
                return true;
            }
        }

        return false;
    }

    function getValue($short, $long = null) {
        if (is_numeric($short)) {
            if ($short > 0) return $this->getArg($short);
            else return $this->getArg(sizeof($this->args) + $short);
        } else {
            $value = array();

            // Look for value(s) of option
            for ($i=0; $i<sizeof($this->args); $i++) {
                // Check short option using format -o value
                if (!is_null($short) && $this->args[$i] == ('-' . $short) && $this->args[$i+1]{0} != '-') {
                    $value[] = $this->args[++$i];
                }
                // Check long option using format --option=value
                else if (!is_null($long) && strpos($this->args[$i], '--' . $long) === 0) {
                    $parts = preg_split('/=/', $this->args[$i]);
                    if (sizeof($parts) > 1) {
                        $value[] = $parts[1];
                    }
                }
            }

            if (sizeof($value) > 0) {
                return sizeof($value) == 1 ? $value[0] : $value;
            }
        }

        return null;
    }

    function getExtraArgs() {
        return $this->extra;
    }

    private function getArg($index) {
        if ($index < 0 || $index >= sizeof($this->args)) throw new InvalidArgumentException();
        return $this->args[$index];
    }
}