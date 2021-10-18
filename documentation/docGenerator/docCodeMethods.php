<?

include_once __DIR__ . DIRECTORY_SEPARATOR . "getFormattedComment.php";

function getMethodsDocumentation(array $methods, string $namespace, string $item)
{
    $methodsDocResult = [];

    foreach($methods as $m) {
        $methodsDocResult[] = ['name' => $m->name];
        $currMeth = count($methodsDocResult) - 1;

        $methodsDocResult[$currMeth]['type'] = $m->isPublic()
            ? "Public"
            : (
                $m->isPrivate()
                    ? "Private"
                    : ($m->isProtected() ? "Protected" : '')
            );

        $methodsDocResult[$currMeth]['comment'] = getFormattedMethodComment($m->getDocComment());

        if ($params = $m->getParameters()) {
            $methodsDocResult[$currMeth]['params'] = [];
            foreach($params as $p) {
                $methodsDocResult[$currMeth]['params'][] = $p->getName();
            }
        }
    }
    
    return $methodsDocResult;
}
