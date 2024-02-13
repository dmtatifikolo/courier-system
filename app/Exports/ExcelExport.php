<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ExcelExport implements FromArray, WithHeadings
{
    protected $dataArray;
    protected $headerArray;

    public function headings(): array
    {
        return $this->headerArray;
    }

    public function __construct(array $dataArray, array $headerArray)
    {
        $this->dataArray = $dataArray;
        $this->headerArray = $headerArray;
    }

    public function array(): array
    {
        return $this->dataArray;
    }
}
