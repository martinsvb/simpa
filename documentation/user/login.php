<?

function loginForm(array $docUsers, string | null $message = null)
{
	$loginSubmitted = isset($_POST['button']) && $_POST['button'] === 'login';
    if ($loginSubmitted && $_POST['userName'] && $_POST['password']) {
        [ 'userName' => $userName ] = $_POST;
        [ $id, $password ] = getUserIdentity($userName, $_POST['password']);
        if (isset($docUsers[$userName]) && $password === $docUsers[$userName]) {
            $_SESSION['user'] = $userName;
            header('Location: /?process=documentation');
            exit;
        }
        else {
            getLoginForm($docUsers, "Access denied for user $userName.", $userName);
        }
    }
    else {
        getLoginForm($docUsers, $loginSubmitted ? REQUIRED_MESSAGE : $message);
    }
}

function getLoginForm(
    array $docUsers,
    string | null $message = null,
    string | null $userName = null,
) {
    $disabled = !count($docUsers) ? 'disabled' : '';

    $error = $message === REQUIRED_MESSAGE ? 'error' : '';

    echo "<div class='loginFormWrapper'>\n

    <div>\n

    <h2 class='loginFormHeader'>User login</h2>
    " . ($message ? "<p>$message</p>" : '') . "
    
    <form class='loginForm' name='login' action='' method='post'>\n

    " . userControls($disabled, $error, $userName) . "

    <div class='buttonsWrapper buttonsWrapperEnd'>\n
    <input name='button' type='submit' value='login' class='button buttonSubmit' $disabled />\n
    <input name='button' type='submit' value='registration' class='button buttonInfo' />\n
    </div>\n

    </form>\n

    </div>
    
    </div>";
}

function userControls(
    bool | null $disabled = null,
    string | null $error = null,
    string | null $userName = null,
) {
    return "    
        <label class='label required' for='userName'>User name</label>\n
        <input class='$error' name='userName' id='userName' type='text' value='$userName' $disabled /><br /><br />\n
        <label class='label required' for='password'>Password</label>\n
        <input class='$error' name='password' id='password' type='password' value='' $disabled /><br /><br />\n
    ";
}
