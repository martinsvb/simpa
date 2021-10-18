<?

use app\helpers\files;
use app\helpers\storage;
use app\exception\excep;

const SALT = 'kjhfkjsalkdjakfhlksajf';

const EXISTS_MESSAGE = 'User already exists, please fill another user name.';

const REQUIRED_MESSAGE = 'Please, fill required fields.';

function getUserIdentity(string $userName, string $password)
{
    $id = hash('sha1', strtolower($userName) . SALT);

    return [
        $id,
        hash('sha512', $password . strtolower($id) . SALT),
    ];
}

function registerUser(array $docUsers)
{
	$registerSubmitted = isset($_POST['button']) && $_POST['button'] === 'register';
    if ($registerSubmitted && $_POST['userName'] && $_POST['password']) {
        [ 'userName' => $userName ] = $_POST;
        if (in_array($userName, array_keys($docUsers))) {
            getRegisterForm($docUsers, EXISTS_MESSAGE, $userName);
        }
        else {
            [ $id, $password ] = getUserIdentity($userName, $_POST['password']);
            $docUsers[$userName] = $password;
            $docUsersValue = "[docUsers]\n";
            foreach ($docUsers as $user => $userPassword) {
                $docUsersValue .= "$user = $userPassword\n";
            }
    
            $ds = storage::getInstance();
            $excep = new excep();
            $files = new files($ds, $excep);
    
            $files->openFile(
                $ds->apiSettings . "users.ini",
                $files->openingMode['writeOnly']
            )->write($docUsersValue);
    
            loginForm($docUsers, "User $userName created, you can login now.");
        }
    }
    else {
        getRegisterForm($docUsers, $registerSubmitted ? REQUIRED_MESSAGE : null);
    }
}

function getRegisterForm(
    array $docUsers,
    string | null $message = null,
    string | null $userName = null,
) {
    $error = $message === REQUIRED_MESSAGE ? 'error' : '';
    
    echo "<div class='loginFormWrapper'>\n
    
    <div>\n
    
    <h2 class='loginFormHeader'>User registration</h2>
    " . ($message ? "<p>$message</p>" : '') . "
    
    <form class='loginForm' name='registration' action='' method='post'>\n

    " . userControls(error: $error, userName: $userName) . "

    <div class='buttonsWrapper buttonsWrapperEnd'>\n
    <input name='button' type='submit' value='register' class='button buttonSubmit' />\n
    </div>\n

    </form>\n

    </div>
    
    </div>";
}
