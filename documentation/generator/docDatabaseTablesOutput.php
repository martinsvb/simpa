<?

namespace documentation\generator\docDatabaseTablesOutput;

use function documentation\deployment\database\databaseDeployment\{getDbConnection, getDatabaseTablesDetails};
use function documentation\generator\docDatabaseTables\getDatabaseTablesDocumentation;
use function documentation\generator\docView\printHeader;

use const documentation\generator\docDatabaseTables\TABLE_COLUMNS;

function generateDocDatabaseTablesOutput()
{
	$docDatabaseTables = getDatabaseTablesDocumentation();

	echo "<div class='docBodyItem hide' id='databaseBody'>";
	
	printHeader("Code tables", 4, thin: true, marginVal: ['mrgTop' => 10, 'mrgLeft' => 0]);

	foreach ($docDatabaseTables as $table => $columnsSettings) {
		printTableSettings('code', $table, $columnsSettings);
	}

	printHeader("Database tables", 4, thin: true, marginVal: ['mrgTop' => 10, 'mrgLeft' => 0]);
	
	$dbConnector = getDbConnection();

	[ 'tablesSettings' => $tablesSettings ] = getDatabaseTablesDetails($dbConnector);

	foreach ($tablesSettings as $table => $settings) {
		printTableSettings('database', $table, $settings);
	}
	
	echo "</div>";

	return $docDatabaseTables;
}

function printTableSettings(string $type, string $table, array $columnsSettings) {
	printHeader($table, 2, "$type-$table");
	echo "<div id='$type-$table-body' class='databaseTable mrgBottom-10 hide'>";
	echo "<table class='docTable'>";
	echo "<thead><tr>";
	foreach (TABLE_COLUMNS as $column) {
		echo "<th class='docTableHeader'>$column</th>";
	}
	echo "</tr></thead>";
	echo "<tbody>";
	foreach ($columnsSettings as $columns) {
		echo "<tr class='docTableRow'>";
			foreach ($columns as $columnValue) {
				echo "<td class='docTableCell'>$columnValue</td>";
			}
		echo "</tr>";
	}
	echo "</tbody>";
	echo "</table>";
	echo "</div>";
}
