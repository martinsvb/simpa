<?

include_once(__DIR__ . DIRECTORY_SEPARATOR . "docView.php");

function generateDocHeader(string | null $selectedDocHeaderId)
{
	echo "<div class='docHeader'>";
	
	$documentationSelected = (!$selectedDocHeaderId || $selectedDocHeaderId === 'documentation')
		? "docHeaderItemSelected"
		: "";
	echo "<div class='docHeaderItem $documentationSelected' id='documentation' onClick='docHeaderClicked(this.id)'>";
	printHeader("Documentation", 1);
	echo "</div>";
	
	echo "<div class='docHeaderItem' id='database' onClick='docHeaderClicked(this.id)'>";
	printHeader("Database", 1);
	echo "</div>";
	
	$deploymentSelected = $selectedDocHeaderId === 'deployment' ? "docHeaderItemSelected" : "";
	echo "<div class='docHeaderItem $deploymentSelected' id='deployment' onClick='docHeaderClicked(this.id)'>";
	printHeader("Deployment", 1);
	echo "</div>";
	
	echo "<div class='docHeaderItem' id='logs' onClick='docHeaderClicked(this.id)'>";
	printHeader("Logs", 1);
	echo "</div>";

	echo "</div>";
}