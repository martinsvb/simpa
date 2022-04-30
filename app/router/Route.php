<?

namespace app\router;

#[\Attribute]
/**
 *  API Route description attribute
 */
class Route
{
    /**
     *  @param string $method, HTTP method
     *  @param string $endpoint, API endpoint
     */
    public function __construct(
        public string $method,
        public string $endpoint,
    ) {}
}
