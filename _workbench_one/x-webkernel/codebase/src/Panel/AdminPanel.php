<?php declare(strict_types=1);

namespace Webkernel\Panel;

final readonly class AdminPanel
{
    /**
     * @param 'platform'|'module' $scope
     */
    public function __construct(
        public string $id,
        public string $scope,
        public ?string $module_name = null,
        /** @var list<string> */
        public array $clusters = [],
    ) {
    }
}
