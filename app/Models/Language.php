<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Sushi\Sushi;

final class Language extends Model
{
    use Sushi;

    public function getRows(): array
    {
        if (! file_exists(database_path('/data/languages.json'))) {
            return [];
        }

        return json_decode(file_get_contents(database_path('/data/languages.json')), true)['languages'];
    }
}
