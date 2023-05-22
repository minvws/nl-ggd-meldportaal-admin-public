<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AbstractUser;
use App\Services\Pdf\QrPdf;

/**
 * Generates a credentials PDF for given user
 * @package App\Services
 */
class PdfService
{
    public function __construct(
        protected string $appUrl
    ) {
    }

    public function generate(AbstractUser $user, string $password): string
    {
        $pdf = new QrPdf(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Meldportaal Gebruikers beheer');


        $fontPath = base_path('resources/font') . '/Roboto-Regular.ttf';
        $pdf->AddFont('roboto', '', base_path('resources/font/roboto'), '');
        $pdf->SetFont('roboto', '', 14, '', false);

        $pdf->setPrintHeader(false);

        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetAutoPageBreak(true, PDF_MARGIN_BOTTOM);

        if (!is_null($user->uuid ?? null)) {
            $pdf->setUuid($user->uuid ?? '');
        }

        $style = array(
            'position' => '',
            'align' => 'C',
            'stretch' => false,
            'fitwidth' => true,
            'cellfitalign' => '',
            'border' => false,
            'hpadding' => 'auto',
            'vpadding' => 'auto',
            'fgcolor' => array(0,0,0),
            'bgcolor' => false, //array(255,255,255),
            'text' => false,
            'font' => 'freeserif',
            'fontsize' => 8,
            'stretchtext' => 4
        );

        // Page 1: credentials

        $pdf->AddPage();

        $pdf->SetXY(0, 30);
        $pdf->Write(0, 'Scan deze QR-code in een authenticator app', '', false, 'C', true, 0, false, false, 0);

        $pdf->write2DBarcode($user->twoFactorQrCodeUrl(), 'QRCODE,H', 60, 40, 75, 75, $style, 'N');


        $pdf->SetXY(0, 130);
        $pdf->Write(0, 'Email: ' . $user->email, '', false, 'C', true, 0, false, false, 0);

        $pdf->SetXY(0, 140);
        $pdf->Write(0, 'Wachtwoord: ' . $password, '', false, 'C', true, 0, false, false, 0);

        $pdf->SetXY(0, 160);
        $pdf->Write(0, 'Website: ' . $this->appUrl, '', false, 'C', true, 0, false, false, 0);

        return $pdf->Output($user->uuid ?? '' . '.pdf', 'S');
    }

    protected function printAddress(QrPdf $pdf, AbstractUser $user): void
    {
        $lh = 7;
        $xoff = 34;
        $yoff = 32;

        $data = [
            'location' => '',
            'street' => '',
            'housenr' => '',
            'zipcode' => '',
            'city' => '',
            'country' => '',
            'phone' => '',
        ];
        $data = array_merge($data, $user->address ?? []);

        $pdf->SetXY($xoff, $yoff + (0 * $lh));
        $pdf->Write(0, $user->name ?? '', '', false, 'L', true, 0, false, false, 0);
        $pdf->SetXY($xoff, $yoff + (1 * $lh));
        $pdf->Write(0, $data['location'] ?? '', '', false, 'L', true, 0, false, false, 0);
        $pdf->SetXY($xoff, $yoff + (2 * $lh));
        $pdf->Write(0, $data['street'] . " " . $data['housenr'], '', false, 'L', true, 0, false, false, 0);
        $pdf->SetXY($xoff, $yoff + (3 * $lh));
        $pdf->Write(0, $data['zipcode'] . " " . $data['city'], '', false, 'L', true, 0, false, false, 0);
        $pdf->SetXY($xoff, $yoff + (4 * $lh));
        $pdf->Write(0, $data['country'], '', false, 'L', true, 0, false, false, 0);
    }
}
