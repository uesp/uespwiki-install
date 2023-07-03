<?php

use Wikimedia\Rdbms\Database;
use Wikimedia\Rdbms\IDatabase;
use Wikimedia\Rdbms\ResultWrapper;

/**
 * Tries to send a popup message via Javascript.
 *
 * @param mixed $msg The message to send.
 *
 * @return void
 */
function RHalert($msg): void
{
	if (!RHisDev()) {
		return;
	}

	echo "<script>alert(\" $msg\")</script>";
}

/**
 * Returns the last query run along with the number of rows affected, if any.
 *
 * @param IDatabase $db
 * @param ResultWrapper|null $result
 *
 * @return string The text of the query and the result count or an empty string.
 *
 */
function RHformatQuery(IDatabase $db, ?ResultWrapper $result = null): string
{
	if (!RHisDev()) {
		return '';
	}

	$retval = $result ? $db->numRows($result) . ' rows returned.' : '';
	return $db->lastQuery() . "\n\n" . $retval;
}

/**
 * Returns the last query run along with the number of rows affected, if any.
 *
 * @param IDatabase $db
 * @param ResultWrapper|null $result
 *
 * @return string The text of the query and the result count or an empty string.
 *
 */
function RHformatQueryDbb(Database $db, ?ResultWrapper $result = null): string
{
	if (!RHisDev()) {
		return '';
	}

	$retval = $result ? $db->numRows($result) . ' rows returned.' : '';
	return $db->lastQuery() . "\n\n" . $retval;
}

/**
 * Logs text to the file provided in the PH_LOG_FILE define, along with the class and function it's executing from.
 *
 * @param string $text The text to add to the log.
 *
 * @return void
 *
 */
function RHlogFunctionText($text = ''): void
{
	if (!RHisDev()) {
		return;
	}

	$caller = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)[1];
	$method = $caller['function'];
	if (isset($caller['class'])) {
		$method = $caller['class'] . '::' . $method;
	}

	RHwriteFile($method, ': ', $text);
}

function RHlogTrace(int $limit = 0): string
{
	if (!RHisDev()) {
		return '';
	}

	$trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, $limit == 0 ? 0 : $limit + 1);
	if (count($trace) > 0) {
		unset($trace[0]);
	}

	$lines = [];
	foreach ($trace as $index => $line) {
		$lines[] =
			str_pad($line['class'], 20, ' ', STR_PAD_LEFT) .
			$line['type'] .
			str_pad($line['function'], 25) .
			' on line ' .
			str_pad($line['line'], 4, ' ', STR_PAD_LEFT) .
			' of file ' .
			$line['file'];
	}

	return implode("\n", $lines);
}

/**
 * Displays the provided message(s) on-screen.
 *
 * @param mixed ...$msgs
 *
 * @return void
 *
 */
function RHecho(...$msgs): void
{
	if (!RHisDev() || !$msgs) {
		return;
	}

	echo '<pre>';
	foreach ($msgs as $msg) {
		if (!is_null($msg)) {
			// Functions are separate for possible behaviour flags later on.
			// The double print_r is necessary here. The first converts it to something we can capture and run
			// htmlspecialchars on. The second one actually prints it.
			$msg = print_r($msg, true);
			$msg = htmlspecialchars($msg);
			print_r($msg);
		}
	}

	echo '</pre>';
}

/**
 * Displays the provided intro text and message(s) on-screen.
 *
 * @param mixed ...$msgs
 *
 * @return void
 *
 */
function RHshow($intro, ...$msgs): void
{
	RHecho($intro . ': ', ...$msgs);
}

function RHshowBacktrace(): void
{
	RHecho((new Exception())->getTraceAsString());
}

/**
 * Writes the provided text to the log file specified in PH_LOG_FILE.
 *
 * @param mixed ...$msgs What to log.
 *
 * @return void
 *
 */
function RHwriteFile(...$msgs): void
{
	if (!RHisDev()) {
		return;
	}

	$file = RHDebug::$phLogFile;
	$handle = fopen($file, 'a') or die("Cannot open file: $file");

	[$msec, $sec] = explode(' ', microtime());
	$msec = str_pad(round($msec, 2), 4, '0');
	fwrite($handle, '(' . date('Y-m-d H:i:s', $sec) . substr($msec, 1) . ') ');
	foreach ($msgs as $msg) {
		$msg2 = print_r($msg, true);
		fwrite($handle, $msg2);
	}

	fwrite($handle, "\n");
	fflush($handle);
	fclose($handle);
}

/**
 * Logs the provided text to the specified file.
 *
 * @param string $file The file to output to.
 * @param mixed ...$msgs What to log.
 *
 * @return void
 *
 */
function RHwriteAnyFile(string $file, ...$msgs): void
{
	if (!RHisDev()) {
		return;
	}

	$handle = fopen($file, 'a') or die("Cannot open file: $file");
	foreach ($msgs as $msg) {
		$msg2 = print_r($msg, true);
		fwrite($handle, $msg2);
	}

	fwrite($handle, "\n");
	fflush($handle);
	fclose($handle);
}

/**
 * Indicates if running on a development server.
 *
 * @return bool
 *
 */
function RHisDev(): bool
{
	if (php_sapi_name() == 'cli') {
		return true;
	}

	$server = $_SERVER['SERVER_NAME'] ?? null;
	return in_array($server, ['content3.uesp.net', 'dev.uesp.net', 'rob-centos']);
}

class RHDebug
{
	/**
	 * Where to log to for the global functions that need it.
	 */
	public static $phLogFile = 'ParserHelperLog.txt';

	public static function noop()
	{
		// This function exists only to force PHP to include the global functions, above.
	}
}
