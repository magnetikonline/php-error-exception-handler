# PHP error and exception handler
A very rudimentary error and exception handler for PHP 5.4 and above.

- When caught, error/exception message is echoed back along with an easy to read stack trace.
- New lines are converted to `<br />` for any [SAPI](http://www.php.net/php_sapi_name) type *other* than `cli`.
- Execution is simply halted afterwards - PHP automatically [stops execution](http://www.php.net/manual/en/function.set-exception-handler.php) after Exceptions handled by `set_exception_handler()` anyway.
- Future enhancements could include friendly error messages for end users in production and/or logging to file/datastore for caught errors. An exercise for another day, but for now this works well for my development.

## Example
```php
<?php
require('errorexceptionhandler.php');

function test() {

	throw new Exception('This is an exception');
	// trigger_error('This is an error');
}

test();
```

Output:

	Exception: This is an exception

	#0    Exception() at [/phperrorexceptionhandler/test.php:6]
	#1    test() at [/phperrorexceptionhandler/test.php:10]

## Further reading
- [set_error_handler()](http://www.php.net/manual/en/function.set-error-handler.php)
- [trigger_error()](http://www.php.net/trigger_error)
- [set_exception_handler()](http://www.php.net/manual/en/function.set-exception-handler.php)
- [Exception base class](http://www.php.net/manual/en/class.exception.php)
