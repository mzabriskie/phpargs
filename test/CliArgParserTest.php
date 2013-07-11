<?php

require_once(dirname(__FILE__) . '/../src/CliArgParser.php');

class CliArgParserTest extends TestCase {
    function testBooleanOptions() {
        $parser = new CliArgParser(array('script.php', '-a', '-b', 'abc', '-c'));

        $this->assertTrue($parser->hasOption('a'));
        $this->assertTrue($parser->hasOption('c'));
        $this->assertEquals('abc', $parser->getValue('b'));
    }

    function testNumberOptions() {
        $parser = new CliArgParser(array('script.php', '-a', '12345', '-b', '123.45'));

        $this->assertEquals(12345, $parser->getValue('a'));
        $this->assertEquals(123.45, $parser->getValue('b'));
    }

    function testStringOptions() {
        $parser = new CliArgParser(array('script.php', '-a', 'abcdefg', '-b', 'hijklmnop'));

        $this->assertEquals('abcdefg', $parser->getValue('a'));
        $this->assertEquals('hijklmnop', $parser->getValue('b'));
    }

    function testArrayOptions() {
        $parser = new CliArgParser(array('script.php', '-a', 'a', '-a', 'b'));

        $arr = $parser->getValue('a');
        $this->assertEquals(2, sizeof($arr));
        $this->assertEquals('a', $arr[0]);
        $this->assertEquals('b', $arr[1]);
    }

    function testMixedArrayOptions() {
        $parser = new CliArgParser(array('script.php', '-a', 'a', '-a', 'b', '-b', '--alpha=c'));

        $arr = $parser->getValue('a', 'alpha');
        $this->assertEquals(3, sizeof($arr));
        $this->assertEquals('a', $arr[0]);
        $this->assertEquals('b', $arr[1]);
        $this->assertEquals('c', $arr[2]);
    }

    function testInvalidArrayOptions() {
        $parser = new CliArgParser(array('script.php', '-a', 'a', '-a', '-b', '-a', 'c'));

        $arr = $parser->getValue('a');
        $this->assertEquals(2, sizeof($arr));
        $this->assertEquals('a', $arr[0]);
        $this->assertEquals('c', $arr[1]);
    }

    function testCombinedShortOptions() {
        $parser = new CliArgParser(array('script.php', '-fb', 'foo'));

        $this->assertTrue($parser->hasOption('f'));
        $this->assertTrue($parser->hasOption('b'));
    }

    function testMultipleOptions() {
        $parser = new CliArgParser(array('script.php', '-a', 'a', '--bravo=b'));

        $this->assertEquals('a', $parser->getValue('a', 'alpha'));
        $this->assertEquals('b', $parser->getValue('b', 'bravo'));
    }

    function testValueFromIndex() {
        $parser = new CliArgParser(array('script.php', 'foo', 'bar'));

        $this->assertEquals('foo', $parser->getValue(1));
        $this->assertEquals('bar', $parser->getValue(2));
        $this->assertEquals('foo', $parser->getValue(-2));
        $this->assertEquals('bar', $parser->getValue(-1));
    }

    function testValueFromInvalidIndex() {
        $parser = new CliArgParser(array('script.php', 'foo', 'bar'));
        $error = false;

        try {
            $parser->getValue(3);
        } catch (InvalidArgumentException $e) {
            $error = true;
        }

        $this->assertTrue($error);

        $error = false;

        try {
            $parser->getValue(-4);
        } catch (InvalidArgumentException $e) {
            $error = true;
        }

        $this->assertTrue($error);
    }

    function testExtraArguments() {
        $parser = new CliArgParser(array('script.php', '-f', 'foo', '-b', 'bar', '--', '--abc'));

        $this->assertEquals('foo', $parser->getValue('f'));
        $this->assertEquals('bar', $parser->getValue('b'));
        $this->assertEquals(null, $parser->getValue(null, 'abc'));
        $this->assertEquals(1, sizeof($parser->getExtraArgs()));
        $this->assertEquals(array('--abc'), $parser->getExtraArgs());
    }

}
