<?

use app\helpers\storage;

include_once __DIR__ . DIRECTORY_SEPARATOR . "docCodeMethods.php";
include_once __DIR__ . DIRECTORY_SEPARATOR . "getFormattedComment.php";
include_once __DIR__ . DIRECTORY_SEPARATOR . "getPropertiesDescription.php";

const NO_PROCESS = [
    "appData.php",
    "index.php",
    "mailHelpers.php",
    "start.php",
];

function getDocumentation()
{
    try {

        $ds = storage::getInstance();

        return array_merge(
            loadDocumentationInfo($ds->simpaLocation, null),
            loadDocumentationInfo(
                $ds->apiModules,
                str_replace("modules" . DIRECTORY_SEPARATOR, NULL, $ds->apiModules)
            ),
        );
    }
    catch (\Exception $e) {
        printArr($e);
    }
}

function loadDocumentationInfo(string $location, string | null $locationPattern)
{
    $docInfo = [];
    
    $iterator = new \RecursiveIteratorIterator(
        new \RecursiveDirectoryIterator(
            $location,
            \RecursiveDirectoryIterator::SKIP_DOTS
        ),
        \RecursiveIteratorIterator::SELF_FIRST
    );

    $replacePath = preg_Replace(['/\/$/'], [null], $locationPattern ?? $location);

    foreach ($iterator as $fileinfo) {
        if (
            in_array($fileinfo->getBasename(), NO_PROCESS) ||
            preg_match('/(jwt|documentation|__databaseTables)/', $fileinfo->getPath())
        ) {
            continue;
        }
        
        if ($fileinfo->isFile() && $fileinfo->getExtension() === "php") {
            $path = mb_substr($fileinfo->getPathName(), 0, -4);
            $path = str_replace($replacePath, NULL, $path);
            
            $class = new ReflectionClass($path);
            
            $parent = $class->getParentClass();
            $className = mb_substr($fileinfo->getFilename(), 0, -4);
            if ($class->isAbstract()) {
                $className .= " (Abstract)";
            }
            
            $namespace = $class->getNamespaceName();
            $docInfo[$namespace][] = ['className' => $className];
            $item = count($docInfo[$namespace]) - 1;
            if ($parent) {
                $docInfo[$namespace][$item]['parent'] = $parent->getName();
            }
            
            $docInfo[$namespace][$item]['comment'] = getFormattedClassComment($class->getDocComment());

            if ($props = $class->getDefaultProperties()) {
                [ 'comment' => $comment, 'properties' => $properties ] = getPropertiesDescription(
                    $docInfo[$namespace][$item]['comment'],
                    $props
                );
                $docInfo[$namespace][$item]['comment'] = $comment;
                $docInfo[$namespace][$item]['properties'] = $properties['instance'] ?? $properties;
            }
            
            if ($methods = $class->getMethods()) {
                $docInfo[$namespace][$item]['methods'] = getMethodsDocumentation(
                    $methods,
                    $namespace,
                    $item
                );
            }
        }
    }

    return $docInfo;
}
