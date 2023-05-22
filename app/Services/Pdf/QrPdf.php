<?php

declare(strict_types=1);

namespace App\Services\Pdf;

use Ramsey\Uuid\Uuid;
use TCPDF;

/**
 * Custom TCPDF class to override footers
 *
 * @package App\Services\Pdf
 */
class QrPdf extends TCPDF
{
    /** @var string */
    protected $uuid;

    /**
     * @param string $uuid
     */
    public function setUuid(string $uuid): void
    {
        $this->uuid = $uuid;
    }

    /**
     * @return string
     */
    public function getUuid(): string
    {
        if ($this->uuid == null) {
            $this->uuid = (string)Uuid::uuid4();
        }

        return $this->uuid;
    }

    /**
     * @return void
     */
    public function Footer()
    {
        // Position at 15 mm from bottom
        $this->SetY(-15);

        $this->SetFont('helvetica', '', 8);
        $this->Cell(0, 10, $this->uuid, 0, 0, 'L', false, '', 0, false, 'T', 'M');

        $txt = 'Pagina ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages();
        $this->Cell(0, 10, $txt, 0, 0, 'R', false, '', 0, false, 'T', 'M');
    }
}
