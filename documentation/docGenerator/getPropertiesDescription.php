<?

/**
 *  Create description array from properties and comments
 */
function getPropertiesDescription(string $comment, array $props)
{
    $commentPieces = explode('@property', $comment);
    foreach($props as $prop => & $value) {
        if ($propMatch = preg_grep('/\$'.$prop.',/', $commentPieces)) {
            $commentKey = array_keys($propMatch)[0];
            $commentValue = trim(preg_replace('/\$'.$prop.','.chr(32).'/', '', $propMatch[$commentKey]));
            $value = $commentValue ? ["<i>" . $commentValue . "</i>", $value] : $value;
            unset($commentPieces[$commentKey]);
        }
    }
    
    return [
        'comment' => trim(join('\n', $commentPieces)),
        'properties' => array_filter($props)
    ];
}
