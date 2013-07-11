phpargs
=======

CLI Argument Parser for PHP

## Example

```php
#!/usr/bin/php
<?php

require 'CliArgParser.php';

function help() {
	echo 'Usage: example [options] <input> <output>' . PHP_EOL . PHP_EOL .
			'Options:' . PHP_EOL .
			'	-r					recursively process files within <input>' . PHP_EOL .
			'	-f					attempt to process without confirmation' . PHP_EOL .
			'	-v					be verbose while processing' . PHP_EOL .
			'	-h, --help			dislay this help and exit' . PHP_EOL;
}

function main($argv, $argc) {
	$parser = new CliArgParser($argv);

	if ($argc == 0 || $parser->hasOption('h', 'help')) {
		help();
		return 0;
	}
	
	$verbose = $parser->hasOption('v');
	$recurse = $parser->hasOption('r');
	$confirm = !$parser->hasOption('f');
	$input = $parser->getValue(-2);
	$output = $parser->getValue(-1);
	
	/*...*/
}

exit(main($argv, $argc));

```

## API

- hasOption($short[, $long])
- getValue($short[, $long])
- getExtraArgs