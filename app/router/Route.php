<?

namespace app\router;

#[\Attribute]
class Route
{
    public function __construct(
        public string $method,
        public string $pathname,
        public array $payload,
    ) {}
}
