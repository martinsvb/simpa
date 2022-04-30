<!DOCTYPE html>

<html lang="cs">

<head>

<meta charset="utf-8">
<meta name="viewport" content="width=device-width, minimumscale=1.0, maximum-scale=1.0" />
    
<title>API Documentation</title>

<link rel="stylesheet" type="text/css" href="/vendor/martinsvb/simpa/documentation/css/docStyles.css" />
<link rel="stylesheet" type="text/css" href="/vendor/martinsvb/simpa/documentation/css/docStylesForms.css" />
<link rel="stylesheet" type="text/css" href="/vendor/martinsvb/simpa/documentation/css/docStylesHeader.css" />
<link rel="stylesheet" type="text/css" href="/vendor/martinsvb/simpa/documentation/css/docStylesTable.css" />

<script src="/vendor/martinsvb/simpa/documentation/callApi.js" language="JavaScript" type="text/javascript"></script>
<script src="/vendor/martinsvb/simpa/documentation/clearResponse.js" language="JavaScript" type="text/javascript"></script>
<script src="/vendor/martinsvb/simpa/documentation/docScripts.js" language="JavaScript" type="text/javascript"></script>
<script src="/vendor/martinsvb/simpa/documentation/uniqueId.js" language="JavaScript" type="text/javascript"></script>

</head>

<body class="docBody">

<?

ini_set('session.save_path', realpath($_SERVER['DOCUMENT_ROOT'] . '/tmp'));

session_start();

include_once(__DIR__ . '/user/login.php');
include_once(__DIR__ . '/user/register.php');

[ 'docUsers' => $docUsers ] = parse_ini_file ($ds->apiSettings . "users.ini", true);

if (isset($_GET['user']) && $_GET['user'] === 'logout') {
    unset($_SESSION['user']);
    header('Location: /?process=documentation');
    exit;
}

if (isset($_SESSION['user']) && in_array($_SESSION['user'], array_keys($docUsers))) {
    include_once(__DIR__ . '/documentation.php');
}
else if (
    !count($docUsers) ||
    isset($_POST['button']) && ($_POST['button'] === 'registration' || $_POST['button'] === 'register')
) {
    registerUser($docUsers);
}
else {
    loginForm($docUsers);
}

?>

</body>

</html>
