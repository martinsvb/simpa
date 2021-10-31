<?

namespace documentation\generator\docView;

function printHeader(
    string $text,
    int $size,
    int | string $id = 0,
    bool $thin = false,
    array $marginVal = []
) {
    $class = "header-$size";
    if ($id) {
        $class .= " icon-plus";
    }
    if ($thin) {
        $class .= " thin";
    }
    foreach ($marginVal as $mrgKey => $mrgValue) {
        $class .= " $mrgKey-$mrgValue";
    }
    $onClickHandler = $id ? "onclick='toggle(\"$id\")'" : '';
    $lastBreak = $size > 1 ? "<br />" : '';
    echo "<h$size id='$id' class='$class' $onClickHandler>$text</h$size>$lastBreak";
}

function printComment(string $text, int $level)
{
    echo "<pre class='textWrapper textWrapper-$level'><p class='comment colorGreen fontItalic'>$text</p></pre>";
}

function getMethodComment(array $methodComment, int $level)
{
    [ 'params' => $params, 'return' => $return ] = $methodComment;
    $formattedParams = count($params) ? join(",<br /><span class='mrgLeft-20' />", $params) : null;
    $newMethodComment = "()";
    if ($formattedParams) {
        $newMethodComment = "(<br /><span class='mrgLeft-20' />$formattedParams<br />)";
    }
    if ($return) {
        $newMethodComment .= ": $return";
    }

    return $newMethodComment;
}

function printProperty(
    string $property,
    string | array | null $description,
    int $level,
    int $descLevel,
    array $docDatabaseTables
) {
    $databaseTables = array_keys($docDatabaseTables);
    $formattedDescription = is_array($description)
        ? getDescription($property, $description, $databaseTables)
        : ($property === 'table' && in_array($description, $databaseTables)
           ? getLinkToDatabaseTable($description)
           : $description);
    echo "<pre class='textWrapper textWrapper-$level'><div class='propertiesTitle'>$property:</div><div class='properties properties-$descLevel'>$formattedDescription</div></pre>";
}

function getDescription(
    string $property,
    array $description,
    array $databaseTables,
    string $descriptionResult = ''
) {
    foreach($description as $descriptionKey => $descriptionItem) {
        if (is_array($descriptionItem)) {
            $descriptionResult .= getDescription($property, $descriptionItem, $databaseTables, '');
        }
        else {
            if ($property === 'table' && in_array($descriptionItem, $databaseTables)) {
                $descriptionResult .= getLinkToDatabaseTable($descriptionItem);
            }
            else {
                $descriptionResult .= (is_string($descriptionKey) && $descriptionItem
                    ? "$descriptionKey: $descriptionItem<br />"
                    : ($descriptionItem ? "$descriptionItem<br />" : ''));
            }
        }
    }
    
    return $descriptionResult;
}

function getLinkToDatabaseTable(string $descriptionItem)
{
    return "<span class='docOpenDatabaseTable' onClick='docOpenDatabaseTable(\"code-$descriptionItem\")'>$descriptionItem</span>";
}
