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

if (!class_exists('CliArgParser', false)) {
class CliArgParser {
    private $args;
    private $extra;

    /**
     * Constructs a CliArgParser object using the array specified as the args
     *
     * @param array $args The $argv provided from php's cli
     */
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

    /**
     * Determine whether or not an option has been specified
     *
     * @param string|number $short The short option value (e.g., 'h', 'v', etc.)
     * @param string [$long] The long option value (e.g., 'help', 'version', etc.)
     * @return bool True if the option has been specified, otherwise false
     */
    function hasOption($short, $long = null) {
        // Look for explicit options
        if (!is_null($short) && in_array('-' . $short, $this->args)) return true;
        if (!is_null($long) && in_array('--' . $long, $this->args)) return true;

        for ($i=1; $i<sizeof($this->args); $i++) {
            // Look for joined short option (e.g., -rf)
            if (!is_null($short) && $this->args[$i]{1} != '-' && strpos($this->args[$i], $short) > -1) {
                return true;
            }
            // Look for long option with value (e.g., --foo=bar)
            else if ($this->checkLongOption($long, $i)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the value of an option if it has been specified
     *
     * <p>
     * Get value using either short or long options.
     *
     * Example:
     *  $ ./myscript -f input.txt
     *
     *  - OR -
     *
     *  $ ./myscript --file=input.txt
     *
     *  $parser = new CliArgParser($argv);
     *  $parser->getValue('f', 'file') -> returns 'input.txt'
     *
     * <p>
     * If <code>short</code> is a number <code>getValue</code> will treat it as the index of the value to retrieve.
     *
     * Example:
     *  $ ./myscript foo bar baz
     *
     *  $parser = new CliArgParser($argv);
     *  $parser->getValue(0) -> returns './myscript'
     *  $parser->getValue(1) -> returns 'foo'
     *  $parser->getValue(2) -> returns 'bar'
     *  $parser->getValue(3) -> returns 'baz'
     *
     * <p>
     * You can also use a negative value for <code>short</code>.
     *
     * Example:
     *  $ ./myscript -r -f -v input.txt output.txt
     *
     *  $parser = new CliArgParser($argv);
     *  $parser->getValue(-2) -> returns 'input.txt'
     *  $parser->getValue(-1) -> returns 'output.txt'
     *
     * <p>
     * Short options can be combined, or specified individually
     *
     * Example:
     *  $ ./myscript -r -f -v input.txt output.txt
     *  $ ./myscript -rfv input.txt output.txt
     *
     * <p>
     * Option values can be passed using short or long options
     *
     * Example:
     *  $ ./myscript -f input.txt
     *  $ ./myscript --file=input.txt
     *
     * @param string|number $short The short option value (e.g., 'n', 'f', etc.)
     * @param string [$long] The long option value (e.g., 'name', 'file', etc.)
     * @return string|null The value of the option if it has been specified, otherwise null
     */
    function getValue($short, $long = null) {
        if (is_numeric($short)) {
            if ($short >= 0) return $this->getArg($short);
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
                else if ($this->checkLongOption($long, $i)) {
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

    /**
     * Get any extra args specified after --
     *
     * Example:
     *  $ ./myscript --foo abc --bar 123 -- --baz
     *
     * <code>getExtraArgs<code> on the command above would return <code>array('--baz')</code>
     *
     * @return array The extra args passed to the script
     */
    function getExtraArgs() {
        return $this->extra;
    }

    private function getArg($index) {
        if ($index < 0 || $index >= sizeof($this->args)) throw new InvalidArgumentException();
        return $this->args[$index];
    }

    private function checkLongOption($long, $index) {
        return !is_null($long) && strpos($this->args[$index], '--' . $long . '=') === 0;
    }
}
}