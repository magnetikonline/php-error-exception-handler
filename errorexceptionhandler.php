<?php
class ErrorExceptionHandler {

	public static function error($errorNumber,$message) {

		// skip first item, will be the call to static::error()
		$stackTraceList = debug_backtrace();
		array_shift($stackTraceList);

		// build message and halt execution
		static::buildMessage('Error',$message,$stackTraceList);
		exit();
	}

	public static function exception(Exception $exception) {

		// build complete exception stack trace
		$stackTraceList = array_merge(
			[[
				'function' => 'Exception',
				'file' => $exception->getFile(),
				'line' => $exception->getLine()
			]],
			$exception->getTrace()
		);

		// build message - execution will halt at this point
		static::buildMessage('Exception',$exception->getMessage(),$stackTraceList);
	}

	private static function buildMessage($type,$message,array $stackTraceList) {

		// build error message and backtrace
		$message =
			sprintf("\n\n%s: %s\n\n",$type,$message) .
			static::buildMessageBacktrace($stackTraceList) .
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
			$messageList[] = sprintf(
				'#%s %s%s() at [%s:%d]',
				str_pad($stackCount++,4,' '), // stack trace item number, padded
				(isset($stackTraceItem['type'])) ? $stackTraceItem['class'] . $stackTraceItem['type'] : '', // calling class details
				$stackTraceItem['function'],
				substr($stackTraceItem['file'],$baseApplicationDirLength), // strip redundant start of file path
				$stackTraceItem['line'] // line number
			);
		}

		return implode("\n",$messageList);
	}
}


// register error and exception handlers
set_error_handler('ErrorExceptionHandler::error');
set_exception_handler('ErrorExceptionHandler::exception');
