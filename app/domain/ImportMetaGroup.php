<?php
/**
 * ImportMetaGroup.php of eve_abacus.
 * Date: 2023-03-06
 */

namespace App\domain;

use App\Models\MetaGrooup;

class ImportMetaGroup
{
    public static function import(string $filepath): void
    {
        MetaGrooup::query()->truncate();
        foreach (yaml_parse_file($filepath) as $groupId => $group) {
            $attributes = [
                'meta_group_id' => $groupId,
                'name_zh' => $group['nameID']['zh'],
                'name_en' => $group['nameID']['en'],
                'description' => $group['descriptionID']['zh'] ?? '',
            ];
            MetaGrooup::query()->create($attributes);
        }
    }
}
