<!DOCTYPE html>

<html lang="cs">

<head>

<meta charset="utf-8">
<meta name="viewport" content="width=device-width, minimumscale=1.0, maximum-scale=1.0" />
    
<title>API Documentation</title>

<link rel="stylesheet" type="text/css" href="./css/docStyles.css" />
<link rel="stylesheet" type="text/css" href="./css/docStylesForms.css" />
<link rel="stylesheet" type="text/css" href="./css/docStylesHeader.css" />
<link rel="stylesheet" type="text/css" href="./css/docStylesTable.css" />

<script src="./docScripts.js" language="JavaScript" type="text/javascript"></script>
<script src="./uniqueId.js" language="JavaScript" type="text/javascript"></script>

</head>

<body class="docBody">

<?

header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate'); // HTTP 1.1.
header('Pragma: no-cache'); // HTTP 1.0.
header('Expires: 0'); // Proxies.

mb_internal_encoding("UTF-8");

include_once("../app/helpers/storage.php");
include_once("./docGenerator/docHeader.php");
include_once("./docGenerator/docCodeOutput.php");
include_once("./docGenerator/docDatabaseTablesOutput.php");
include_once("./deployment/deploymentForm.php");
include_once("./logs/logsOutput.php");

include_once(__DIR__ . DIRECTORY_SEPARATOR . "../../api_settings/start.php");

new start();

$deploymentOperation = isset($_POST['button']) ? DEPLOYMENT_BUTTONS[$_POST['button']] : null;

generateDocHeader($deploymentOperation ? 'deployment' : null);

$docDatabaseTables = generateDocDatabaseTablesOutput();

generateDocOutput($docDatabaseTables, $deploymentOperation ? 'hide' : null);

generateDeploymentForm($deploymentOperation ? null : 'hide', $deploymentOperation, $docDatabaseTables);

generateLogsOutput();

?>

</body>

</html>
