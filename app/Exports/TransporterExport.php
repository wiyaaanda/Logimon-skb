<?php

namespace App\Exports;

use App\Models\Transporter;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TransporterExport implements FromCollection, WithHeadings
{
    protected $template = false;

    public function __construct($template = false)
    {
        $this->template = $template;
    }

    public function collection()
    {
        if ($this->template) {
            return collect([['nama_transporter', 'no_telp_transporter', 'tempat_transporter', 'email_transporter', 'pic_transporter']]);
        }

        return Transporter::select('nama_transporter', 'no_telp_transporter', 'tempat_transporter', 'email_transporter', 'pic_transporter')->get();
    }

    public function headings(): array
    {
        return ['nama_transporter', 'no_telp_transporter', 'tempat_transporter', 'email_transporter', 'pic_transporter'];
    }
}
