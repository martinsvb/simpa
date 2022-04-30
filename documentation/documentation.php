<?

namespace documentation;

use function documentation\deployment\deploymentForm\generateDeploymentForm;
use function documentation\generator\docHeader\generateDocHeader;
use function documentation\generator\docApiOutput\generateApiOutput;
use function documentation\generator\docCodeOutput\generateDocOutput;
use function documentation\generator\docDatabaseTablesOutput\generateDocDatabaseTablesOutput;
use function documentation\logs\logsOutput\generateLogsOutput;

use const documentation\deployment\deploymentForm\DEPLOYMENT_BUTTONS;

header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate'); // HTTP 1.1.
header('Pragma: no-cache'); // HTTP 1.0.
header('Expires: 0'); // Proxies.

$deploymentOperation = isset($_POST['button']) && in_array($_POST['button'], array_keys(DEPLOYMENT_BUTTONS))
    ? DEPLOYMENT_BUTTONS[$_POST['button']]
    : null;

generateDocHeader($deploymentOperation ? 'deployment' : null);

generateApiOutput($deploymentOperation ? 'hide' : null);

$docDatabaseTables = generateDocDatabaseTablesOutput();

generateDocOutput($docDatabaseTables);

generateDeploymentForm($deploymentOperation ? null : 'hide', $deploymentOperation, $docDatabaseTables);

generateLogsOutput();
