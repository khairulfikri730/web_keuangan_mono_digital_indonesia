<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class CapitalImport implements ToArray, WithHeadingRow
{
    public function array(array $array)
    {
        // This is handled in the controller
    }
}
