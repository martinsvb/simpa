<?

namespace documentation\generator\docApiOutput;

use app\helpers\storage;
use app\router\routes;

use function documentation\generator\docView\{
	getLinkToController, getMethodComment, printHeader, printComment, printProperty
};

function generateApiOutput(string | null $hide)
{
	$ds = storage::getInstance();

	$routes = new routes();

	$routesInfo = $routes->getRoutes(
		$ds->apiModules,
		str_replace("modules" . DIRECTORY_SEPARATOR, NULL, $ds->apiModules)
	);

	echo "<div class='docBodyItem $hide' id='apiBody'>";

	foreach ($routesInfo as $module => $controllers) {
		foreach ($controllers as $controller => $routes) {
			printHeader($controller, 2, $controller);
			echo "<div id='$controller-body' class='mrgBottom-10 hide'>";
			printHeader(getLinkToController($module, $controller), 3);
			foreach ($routes as $controllerMethod => $route) {
				[
					'method' => $method,
					'path' => $path,
					'endpoint' => $endpoint,
					'payload' => $payload
				] = $route;

				$payloadValue = count($payload)
					? str_replace([",", "{", "}"], [",\n", "{\n", "\n}"], json_encode($payload))
					: "";

				$responseId = "$controller-$controllerMethod";
				$routeTest = "<input class='availableWidth' name='endpoint' id='$responseId-endpoint' title='endpoint' type='text' value='$endpoint' /><br />\n";
				if ($method !== 'GET') {
					$routeTest .= "Payload<br/>\n<textarea class='availableWidth' name='payload' title='payload' id='$responseId-payload' rows='8'>$payloadValue</textarea><br />\n";
				}
				$routeTest .= "<button class='button buttonSubmit' id='$responseId-button' type='button' onClick='callApi(\"$endpoint\", \"$method\", \"$responseId\")'>test</button><br />\n";
				$routeTest .= "<span class='hide' id='$responseId-wrapper'>Response <button class='button buttonDanger' id='$responseId-buttonClear' type='button' onClick='clearResponse(\"$responseId\")'>clear</button> <pre class='docApiResponse' id='$responseId'></pre></span>";

				printProperty($method, $routeTest, 1, 0, []);
			}
			echo "</div>";
		}
	}

	echo "</div>";
}
