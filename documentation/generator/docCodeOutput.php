<?

namespace documentation\generator\docCodeOutput;

use app\helpers\storage;

use function documentation\generator\docCode\getDocumentation;
use function documentation\generator\docView\{getMethodComment, printHeader, printComment, printProperty};

function generateDocOutput(array $docDatabaseTables)
{
	$ds = storage::getInstance();
	
	$docInfo = getDocumentation();

	echo "<div class='docBodyItem hide' id='documentationBody'>";

	$id = 1;
	
	foreach ($docInfo as $module => $moduleArr) {
		$moduleId = str_replace('\\', '', $module);
		printHeader($module, 2, $moduleId);
		echo "<div id='$moduleId-body' class='module mrgBottom-10 hide'>";

		foreach ($moduleArr as $classArr) {
			$id++;
			printComment($classArr['comment'], 1);
			$className = $classArr['className'];
			$classNameId = "$moduleId-$className";
			if (isset($classArr['parent']) && $classArr['parent']) {
				$className .= " extends " . $classArr['parent'];
			}
			printHeader("Class: $className", 3, $classNameId);
			echo "<div id='$classNameId-body' class='classContent hide'>";

			if (array_key_exists('properties', $classArr)) {
				printHeader("Properties", 4, 0, true, ['mrgTop' => 10]);
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
