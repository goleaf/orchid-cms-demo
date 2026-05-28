<?php

namespace App\Enums;

enum ReportExportFormat: string
{
    case Csv = 'csv';
    case Json = 'json';
}
