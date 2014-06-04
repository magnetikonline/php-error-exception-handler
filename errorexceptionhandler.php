<?php
class ErrorExceptionHandler {

	const EMAIL_MESSAGE_LINE_LENGTH = 70;

	private static $HTTPUserMessage = false;
	private static $logFilePath = false;
	private static $emailFrom = false;
	private static $emailTo;
	private static $emailSubject;


	public static function error($errorNumber,$message) {

		// build error message and halt execution
		static::buildMessage(
			'Error',$message,
			debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)
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

	public static function setHTTPUserMessage($message) {

		static::$HTTPUserMessage = $message;
	}

	public static function setLogFilePath($path) {

		static::$logFilePath = $path;
	}

	public static function setEmailSend($from,$to,$subject) {

		static::$emailFrom = $from;
		static::$emailTo = $to;
		static::$emailSubject = $subject;
	}

	private static function buildMessage($type,$message,array $stackTraceList) {

		// build error message and backtrace
		$message =
			$type . ': ' . $message . "\n\n" .
			static::buildMessageBacktrace($stackTraceList) .
			static::buildMessageHTTPRequest() .
			"\n";

		// log message to disk and/or email send
		$messageDateTime = 'Date: ' . date('Y-m-d H:i:s') . "\n";
		static::logMessage($messageDateTime . $message);
		static::sendMessageAsEmail($messageDateTime . $message);

		// emit message to CLI/browser
		$message = "\n\n" . $message . "\n\n";

		if (PHP_SAPI == 'cli') {
			echo($message);

		} else {
			// HTTP request
			echo(nl2br(
				(static::$HTTPUserMessage === false)
					? $message
					: static::$HTTPUserMessage
			));
		}
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

		// request URI (empty entries for line breaks)
		$messageList = ['','','Request URI: ' . $_SERVER['REQUEST_URI']];

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

		return implode("\n",$messageList);
	}

	private static function logMessage($message) {

		if (static::$logFilePath === false) return;

		// open log file and obtain lock
		$fh = fopen(static::$logFilePath,'a');

		if (flock($fh,LOCK_EX)) {
			// got the file lock - write message and release lock
			fwrite($fh,$message . "\n\n");
			flock($fh,LOCK_UN);
		}

		fclose($fh);
	}

	private static function sendMessageAsEmail($message) {

		if (static::$emailFrom === false) return;

		mail(
			static::$emailTo,
			static::$emailSubject,
			wordwrap(
				str_replace("\r\n","\n",$message),
				self::EMAIL_MESSAGE_LINE_LENGTH
			),
			'From: ' . static::$emailFrom,
			'-f <' . static::$emailFrom . '>'
		);
	}
}


// register error and exception handlers
set_error_handler('ErrorExceptionHandler::error');
set_exception_handler('ErrorExceptionHandler::exception');
