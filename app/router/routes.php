<?

namespace app\router;

use app\router\route;

class routes
{
    public function getRoutes(string $location, string | null $locationPattern)
    {
        $routesInfo = [];
        
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(
                $location,
                \RecursiveDirectoryIterator::SKIP_DOTS
            ),
            \RecursiveIteratorIterator::SELF_FIRST
        );
    
        $replacePath = preg_Replace(['/\/$/'], [null], $locationPattern ?? $location);
    
        foreach ($iterator as $fileinfo) {
            if (preg_match('/(__databaseTables)/', $fileinfo->getPath())) {
                continue;
            }
            
            if ($fileinfo->isFile() && $fileinfo->getExtension() === "php") {
                $path = mb_substr($fileinfo->getPathName(), 0, -4);
                $path = str_replace($replacePath, NULL, $path);
                
                $class = new \ReflectionClass($path);
                
                $className = mb_substr($fileinfo->getFilename(), 0, -4);
                if ($class->isAbstract()) {
                    $className .= " (Abstract)";
                }

                $namespace = $class->getNamespaceName();
    
                foreach ($class->getMethods() as $m) {
                    foreach (
                        $m->getAttributes(
                            route::class,
                            \ReflectionAttribute::IS_INSTANCEOF
                        ) as $attribute
                    ) {
                        $attr = $attribute->newInstance();
                        $routesInfo[$namespace][$className][$m->name] = [
                            'method' => $attr->method,
                            'path' => $path,
                            'pathname' => $attr->pathname,
                            'payload' => $attr->payload,
                        ];
                    }
                }
            }
        }
    
        return $routesInfo;
    }
}
