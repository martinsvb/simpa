<?

include_once(__DIR__ . DIRECTORY_SEPARATOR . "logsData.php");

function generateLogsOutput()
{
	[ $exceptions, $errorLog ] = getLogsData();
	
	echo "<div class='docBodyItem hide' id='logsBody'>";

	printExceptions("Exceptions", $exceptions);

	printErrorLogs("Error log", $errorLog);

	echo "</div>";
}

function printLogs(string $type, string $logBody) {
	printHeader($type, 2, "$type");
	echo "<div id='$type-body' class='mrgBottom-10 hide'>$logBody</div>";
}

function printExceptions(string $type, array $logData) {

	$exceptionLog = "<table class='docTable'>";
	$exceptionLog .= "<thead><tr>";
	foreach (LOGS_COLUMNS as $column) {
		$exceptionLog .= "<th class='docTableHeader'>$column</th>";
	}
	$exceptionLog .= "</tr></thead>";
	$exceptionLog .= "<tbody>";
	foreach ($logData as $log) {
		$exceptionLog .= "<tr class='docTableRow'>";
			foreach ($log as $logValueIndex => $logValue) {
				$logDisplayValue = $logValueIndex ? $logValue : implode('<br />', explode('T', $logValue));
				$exceptionLog .= "<td class='docTableCell'>$logDisplayValue</td>";
			}
			$exceptionLog .= "</tr>";
	}
	$exceptionLog .= "</tbody>";
	$exceptionLog .= "</table>";

	printLogs($type, $exceptionLog);
}

function printErrorLogs(string $type, string | null $logData) {

	printLogs($type, "<pre>" . implode('<br />', explode('\n', $logData)) . "</pre>");
}
