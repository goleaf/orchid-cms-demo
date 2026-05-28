<?php

namespace App\Enums;

enum ReportExportFormat: string
{
    case Csv = 'comma_separated_values';
    case SpreadsheetPlaceholder = 'spreadsheet_placeholder';
    case Json = 'json';
    case LegacyCsv = 'csv';

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return [
            self::Csv->value,
            self::SpreadsheetPlaceholder->value,
            self::Json->value,
        ];
    }

    public function extension(): string
    {
        return match ($this) {
            self::Csv, self::LegacyCsv => 'csv',
            self::SpreadsheetPlaceholder => 'xlsx',
            self::Json => 'json',
        };
    }
}
