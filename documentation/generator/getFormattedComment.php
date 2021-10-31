<?

namespace documentation\generator\getFormattedComment;

/**
 *  Format class comment to representation form
 *
 *  @param string $comment
 */
function getFormattedClassComment(string $comment)
{
    return preg_replace('/'.chr(32).'+/', ' ', trim(preg_replace('/(\/\*+|\*\/+|\*+)/', '', $comment)));
}

/**
 *  Process method comment data
 *
 *  @param string $comment
 */
function getFormattedMethodComment(string $comment)
{
    $commentParts = explode('*', $comment);
    $commentDescription = [];
    $commentParams = [];
    $commentReturn = '';
    foreach($commentParts as $commentItem => $commentValue) {
        if (preg_match('/@param/', $commentValue)) {
            array_push($commentParams, trim(preg_replace('/@param/', '', $commentValue)));
        }
        else if (preg_match('/@return/', $commentValue)) {
            $commentReturn = trim(preg_replace('/@return/', '', $commentValue));
        }
        else if (strlen($commentValue) > 1 && ord($commentValue) !== 13) {
            array_push($commentDescription, trim($commentValue));
        }
    }

    return [
        'description' => trim(join('<br />', $commentDescription)),
        'params' => $commentParams,
        'return' => $commentReturn
    ];
}
