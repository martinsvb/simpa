<?

include_once(__DIR__ . "./deploymentProcess.php");

use app\helpers\storage;

const DEPLOYMENT_BUTTONS = [
	'Process' => 'processDeployment'
];

const DEPLOYMENT_TABLE_COLUMNS = [
	'',
	'Title',
	'Description'
];

function generateDeploymentForm(string | null $hide, string | null $deploymentOperation, array $docDatabaseTables)
{
	$deploymentOperations = [
		'dbBackUp' => [
			'title' => 'Database back up',
			'description' => 'Back up database structure and data to CSV files.'
		],
		'dbSchemaUpdate' => [
			'title' => 'Database schema update',
			'description' => 'Update database tables schema from code configuration.'
		],
	];

	$ds = storage::getInstance();
	
	$form = null;
	
	foreach ($deploymentOperations as $operation => $configuration) {
		$form .= "<tr class='docTableRow'><td class='docTableCell'><input name='$operation' id='$operation' type='checkbox' />\n";
		$form .= "<td class='docTableCell'><label for='$operation' class=''>".$configuration['title']."</label>\n";
		$form .= "<td class='docTableCell'>".$configuration['description']."\n";
	}
	
	echo "<div class='docBodyItem $hide' id='deploymentBody'>";
	
	echo "<form name='dat' id='dat' action='' method='post'>\n";
	
	echo "<table class='docTable'>";
	echo "<thead><tr>";
	foreach (DEPLOYMENT_TABLE_COLUMNS as $column) {
		echo "<th class='docTableHeader'>$column</th>";
	}
	echo "</tr></thead>";
	echo "<tbody>$form</tbody>";
	echo "</table>";

	echo "<div class='buttonsWrapper'>\n";
	echo "<input name='button' type='button' value='Deployment id' class='button buttonInfo' onclick='setElUniqueId(\"deploymentId\")' />\n";
	echo "<input name='deploymentId' id='deploymentId' type='text' value='' readonly />\n";
	echo "</div>\n";

	echo "<div class='buttonsWrapper'>\n";
	echo "<input name='button' type='submit' value='Process' class='button buttonSubmit' />\n";
	echo "</div>\n";

	echo "</form>";
	
	deploymentProcess($deploymentOperation, $docDatabaseTables);

	echo "</div>";
}
