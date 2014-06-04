# PHP error and exception handler
A very rudimentary error and exception handler for PHP 5.4 and above.

- When caught, error/exception message is echoed back along with an easy to read stack trace.
- New lines are converted to `<br />` for any [SAPI](http://www.php.net/php_sapi_name) type *other* than `cli`.
- Execution is halted afterwards - PHP [stops execution](http://www.php.net/manual/en/function.set-exception-handler.php) after exceptions handled by `set_exception_handler()` regardless.
- Ability to set a *user friendly* error message for HTTP requests.
- Optional logging of error messages to file and/or sending via email using the PHP [`mail()`](http://www.php.net/manual/en/function.mail.php) method.

## Example
```php
<?php
require('errorexceptionhandler.php');

// set a friendly HTTP user message, log file and email notification settings (all optional)
ErrorExceptionHandler::setHTTPUserMessage('There has been an error!');
ErrorExceptionHandler::setLogFilePath('/tmp/error.log');
ErrorExceptionHandler::setEmailSend(
	'notify@domain.com','error.report@domain.com',
	'Application error notification subject line'
);

function test() {

	throw new Exception('This is an exception');
	// trigger_error('This is an error');
}

test();
```

Output:

	Exception: This is an exception

	#0    Exception() at [/phperrorexceptionhandler/test.php:14]
	#1    test() at [/phperrorexceptionhandler/test.php:18]

## Further reading
- [set_error_handler()](http://www.php.net/manual/en/function.set-error-handler.php)
- [trigger_error()](http://www.php.net/trigger_error)
- [set_exception_handler()](http://www.php.net/manual/en/function.set-exception-handler.php)
- [Exception base class](http://www.php.net/manual/en/class.exception.php)
