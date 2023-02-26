<?php
/**
 * ImportType.php of eve_abacus.
 * Date: 2023-02-24
 */

namespace App\domain;

use App\Models\Type;

class ImportType
{
    public static function import(string $filepath): void
    {
        $item = yaml_parse_file($filepath);
        foreach ($item as $typeId => $type) {
            $attributes = ['type_id' => $typeId];
            $values = [
                'type_id' => $typeId,
                'group_id' => $type['groupID'],
                'graphic_id' => $type['graphicID'] ?? 0,
                'name_zh' => $type['name']['zh'] ?? '',
                'name_en' => $type['name']['en'],
                'volume' => $type['volume'] ?? 0,
                'published' => $type['published'] ? 1 : 0,
            ];
            Type::query()->updateOrCreate($attributes, $values);
        }
    }
}
