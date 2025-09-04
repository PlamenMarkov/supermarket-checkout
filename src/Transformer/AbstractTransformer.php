<?php

namespace App\Transformer;

abstract class AbstractTransformer
{
    public function collection(array $collection, bool $includeChildren = true): array
    {
        $out = [];
        foreach ($collection as $o) {
            $out[] = $this->toArray($o, $includeChildren);
        }

        return $out;
    }
}