<?php
class ErrorExceptionHandler {

	public static function error($errorNumber,$message) {

		// build error message and halt execution
		static::buildMessage(
			'Error',$message,
			array_slice(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS),1)
		);

		exit();
	}

	public static function exception(Exception $exception) {

		// build exception message - execution will halt at this point
		static::buildMessage(
			'Exception',$exception->getMessage(),
			// build complete exception stack trace
			array_merge(
				[[
					'function' => get_class($exception),
					'file' => $exception->getFile(),
					'line' => $exception->getLine()
				]],
				$exception->getTrace()
			)
		);
	}

	private static function buildMessage($type,$message,array $stackTraceList) {

		// build error message and backtrace
		$message =
			sprintf("\n\n%s: %s\n\n",$type,$message) .
			static::buildMessageBacktrace($stackTraceList) .
			static::buildMessageHTTPRequest() .
			"\n\n";

		// output, with <br /> if not in CLI mode
		echo((PHP_SAPI == 'cli') ? $message : nl2br($message));
	}

	private static function buildMessageBacktrace(array $stackTraceList) {

		// determine number of characters to cut from the start of file paths
		$baseApplicationDirLength = strlen(dirname(__DIR__));
		$messageList = [];
		$stackCount = 0;

		foreach ($stackTraceList as $stackTraceItem) {
			// add message line
			$fileName = (isset($stackTraceItem['file']))
				? substr($stackTraceItem['file'],$baseApplicationDirLength) // strip redundant start of file path
				: false;

			$lineNumber = (isset($stackTraceItem['line']))
				? $stackTraceItem['line']
				: false;

			$messageList[] = sprintf(
				'#%s %s%s()' . ((($fileName !== false) && ($lineNumber !== false)) ? ' at [%s:%d]' : ''),
				str_pad($stackCount++,4,' '), // stack trace item number, padded
				(isset($stackTraceItem['type'])) ? $stackTraceItem['class'] . $stackTraceItem['type'] : '', // calling class details
				$stackTraceItem['function'],
				$fileName,$lineNumber
			);
		}

		return implode("\n",$messageList);
	}

	private static function buildMessageHTTPRequest() {

		// no work if CLI mode
		if (PHP_SAPI == 'cli') return '';

		// request URI
		$messageList = ['Request URI: ' . $_SERVER['REQUEST_URI']];

		// POST data
		if ($_POST) {
			array_push($messageList,'','POST data:');
			foreach ($_POST as $key => $value) {
				$messageList[] = sprintf('\'%s\' => %s',$key,$value);
			}
		}

		// SESSION data
		if (isset($_SESSION) && $_SESSION) {
			array_push($messageList,'','SESSION data:');
			foreach ($_SESSION as $key => $value) {
				$messageList[] = sprintf('\'%s\' => %s',$key,$value);
			}
		}

		return "\n\n" . implode("\n",$messageList);
	}
}


// register error and exception handlers
set_error_handler('ErrorExceptionHandler::error');
set_exception_handler('ErrorExceptionHandler::exception');
