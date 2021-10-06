<?

include_once(__DIR__ . DIRECTORY_SEPARATOR . "docCode.php");
include_once(__DIR__ . DIRECTORY_SEPARATOR . "docView.php");

function generateDocOutput(array $docDatabaseTables, string | null $hide)
{
	$docInfo = getDocumentation();
	
	echo "<div class='docBodyItem $hide' id='documentationBody'>";
	
	$id = 1;
	
	foreach ($docInfo as $module => $moduleArr) {
		$moduleId = "module-$id";
		printHeader($module, 2, $moduleId);
		echo "<div id='$moduleId-body' class='mrgBottom-10 hide'>";
		foreach ($moduleArr as $classArr) {
			$id++;
			$classNameId = "module-$id-className";
			printComment($classArr['comment'], 1);
			printHeader("Class: " . $classArr['className'], 3, "$classNameId");
			echo "<div id='$classNameId-body' class='classContent hide'>";
			if (array_key_exists('properties', $classArr)) {
				printHeader("Properties", 4, 0, true, ['mrgTop' => 5]);
				$descLevel = 0;
				foreach($classArr['properties'] as $property => $description) {
					$descLevel++;
					printProperty($property, $description, 2, $descLevel, $docDatabaseTables);
				}
			}
			printHeader("Methods", 4, 0, true, ['mrgTop' => 10]);
			foreach ($classArr['methods'] as $methodsArr) {
				if ($methodsArr['comment']['description']) {
					printComment($methodsArr['comment']['description'], 2);
				}
				$methodComment = "<span class='colorBlue'>" . $methodsArr['type'] . " function</span> 
				<span class='colorLightBrown'>" . $methodsArr['name'] . "</span>";
				if (count($methodsArr['comment']['params']) || $methodsArr['comment']['return']) {
					$methodComment .= getMethodComment($methodsArr['comment'], 2);
				}
				printHeader($methodComment, 4);
			}
			echo "</div>";
		}
		echo "</div>";
		$id++;
	}
	
	echo "</div>";
}
