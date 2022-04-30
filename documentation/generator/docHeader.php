<?

namespace documentation\generator\docHeader;

use function documentation\generator\docView\printHeader;

function generateDocHeader(string | null $selectedDocHeaderId)
{
	echo "<div class='docHeaderWrapper'>\n<div class='docHeader'>\n";
	
	$documentationSelected = (!$selectedDocHeaderId || $selectedDocHeaderId === 'api')
		? "docHeaderItemSelected"
		: "";

	echo "<div class='docHeaderItem $documentationSelected' id='api' onClick='docHeaderClicked(this.id)'>";
	printHeader("API", 1);
	echo "</div>";

	echo "<div class='docHeaderItem' id='documentation' onClick='docHeaderClicked(this.id)'>";
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

	echo "</div>\n";

	echo "<div class='userWrapper'><span class='userName'>User: " . $_SESSION['user'] . " | </span><a class='logoutLink' href='/?process=documentation&user=logout'>Logout</a></div>\n";

	echo "</div>\n";
}
