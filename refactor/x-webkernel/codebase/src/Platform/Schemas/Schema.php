<?php declare(strict_types=1);

namespace Webkernel\Platform\Schemas;

final class Schema
{
    /** @var list<array{name: string, label: string, type: string}> */
    private array $fields = [];

    /**
     * @param list<array{name: string, label?: string, type?: string}|string> $fields
     */
    public function fields(array $fields): self
    {
        $out = [];
        foreach ($fields as $field) {
            if (is_string($field)) {
                $out[] = ['name' => $field, 'label' => $field, 'type' => 'text'];
                continue;
            }
            if (! isset($field['name'])) {
                continue;
            }
            $out[] = [
                'name' => (string) $field['name'],
                'label' => (string) ($field['label'] ?? $field['name']),
                'type' => (string) ($field['type'] ?? 'text'),
            ];
        }
        $this->fields = $out;

        return $this;
    }

    /**
     * @return list<array{name: string, label: string, type: string}>
     */
    public function get_fields(): array
    {
        return $this->fields;
    }
}
