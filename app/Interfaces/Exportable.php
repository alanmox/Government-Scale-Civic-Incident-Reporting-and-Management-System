<?php declare(strict_types=1);
namespace App\Interfaces;

/** Classes implementing this can export their data. */
interface Exportable
{
    public function toPdf(): string;
    public function toExcel(): string;
    public function toCsv(): string;
}
