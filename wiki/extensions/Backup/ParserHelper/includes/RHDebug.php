<?php

use Wikimedia\Rdbms\Database;
use Wikimedia\Rdbms\IDatabase;
use Wikimedia\Rdbms\ResultWrapper;

class RHDebug
{
	private static $writeFilePath;

	/**
	 * Tries to send a popup message via Javascript.
	 *
	 * @param mixed $msg The message to send.
	 *
	 * @return void
	 */
	public static function alert($msg): void
	{
		if (!RHDebug::isDev()) {
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
	public static function formatQuery(IDatabase $db, ?ResultWrapper $result = null): string
	{
		if (!RHDebug::isDev()) {
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
	public static function formatQueryDbb(Database $db, ?ResultWrapper $result = null): string
	{
		if (!RHDebug::isDev()) {
			return '';
		}

		$retval = $result ? $db->numRows($result) . ' rows returned.' : '';
		return $db->lastQuery() . "\n\n" . $retval;
	}

	public static function getStackTrace()
	{
		return (new Exception())->getTraceAsString();
	}

	/**
	 * Logs text to the file provided in the PH_LOG_FILE define, along with the class and function it's executing from.
	 *
	 * @param string $text The text to add to the log.
	 *
	 * @return void
	 *
	 */
	public static function logFunctionText($text = ''): void
	{
		if (!RHDebug::isDev()) {
			return;
		}

		$caller = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)[1];
		$method = $caller['function'];
		if (isset($caller['class'])) {
			$method = $caller['class'] . '::' . $method;
		}

		RHDebug::writeFile($method, ': ', $text);
	}

	/**
	 * Displays the provided message(s) on-screen.
	 *
	 * @param mixed ...$msgs
	 *
	 * @return void
	 *
	 */
	public static function echo(...$msgs): void
	{
		if (!RHDebug::isDev() || !$msgs) {
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
	public static function show($intro, ...$msgs): void
	{
		RHDebug::echo($intro . ': ', ...$msgs);
	}

	public static function showBacktrace(): void
	{
		RHDebug::echo((new Exception())->getTraceAsString());
	}

	/**
	 * Writes the provided text to the log file specified in PH_LOG_FILE.
	 *
	 * @param mixed ...$msgs What to log.
	 *
	 * @return void
	 *
	 */
	public static function writeFile(...$msgs): void
	{
		if (!RHDebug::isDev()) {
			return;
		}

		if (!isset(self::$writeFilePath)) {
			$server = $_SERVER['SERVER_NAME'] ?? gethostname() ?? null;
			$backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
			$dir = dirname($backtrace[count($backtrace) - 1]['file']);
			$mdir = basename($dir);
			if ($mdir === 'maintenance') {
				$dir = dirname($dir);
			}

			self::$writeFilePath = $server === 'rob-centos'
				? "$dir/RHLog.txt"
				: '/home/robinhood/RHLog.txt';
		}

		$file = self::$writeFilePath;
		$handle = fopen($file, 'a') or die("Cannot open file: {$file}");

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
	public static function writeAnyFile(string $file, ...$msgs): void
	{
		if (!RHDebug::isDev()) {
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
	public static function isDev(): bool
	{
		if (php_sapi_name() == 'cli') {
			return true;
		}

		$server = $_SERVER['SERVER_NAME'] ?? gethostname() ?? null;
		return in_array($server, [
			'content3.starfieldwiki.net',
			'content3.uesp.net',
			'dev.starfieldwiki.net',
			'dev.uesp.net',
			'rob-centos'
		]);
	}
}
