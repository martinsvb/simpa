<?

namespace documentation\generator\docCodeOutput;

use app\helpers\storage;
use app\router\routes;

use function documentation\generator\docCode\getDocumentation;
use function documentation\generator\docView\{getMethodComment, printHeader, printComment, printProperty};

function generateDocOutput(array $docDatabaseTables, string | null $hide)
{
	$ds = storage::getInstance();
	
	$docInfo = getDocumentation();

	$routes = new routes();

	$routesInfo = $routes->getRoutes(
		$ds->apiModules,
		str_replace("modules" . DIRECTORY_SEPARATOR, NULL, $ds->apiModules)
	);
	
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
			if (isset($routesInfo[$module][$classArr['className']])) {
				printHeader("Routes", 4, 0, true, ['mrgTop' => 5]);
				$descLevel = 0;
				foreach($routesInfo[$module][$classArr['className']] as $classMethod => $route) {
					$descLevel++;
					['pathname' => $pathname, 'payload' => $payload] = $route;
					$routeLink = "<a href='$pathname' target='_blank'>$pathname</a>";
					$routePayload = implode('<br />', $payload);
					printProperty($route['method'], $routeLink . "<br/>$routePayload", 2, $descLevel, []);
				}
			}
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
				if (isset($methodsArr['pathname'])) {
					echo "<a href='".$methodsArr['pathname']."' target='_blank'>test</a>";
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
