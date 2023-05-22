<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Exceptions\UserException;
use App\Models\AbstractUser;
use App\Services\PdfService;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Response;
use TCPDI;

abstract class AbstractPdfController extends BaseController
{
    protected PdfService $pdfService;

    /** @var class-string<AbstractUser>|string */
    protected string $userClass = "";
    protected string $route = "";

    /**
     * PdfController constructor.
     */
    public function __construct(PdfService $pdfService)
    {
        $this->pdfService = $pdfService;
    }

    /**
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     * @throws \Throwable
     */
    public function batchDownload(Request $request): Response
    {
        /** @var array|null $uuids */
        $uuids = $request->request->get('uuids');
        if (is_null($uuids)) {
            $uuids = [];
        }

        // Generate PDF
        $mainPdf = new TCPDI(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $mainPdf->setPrintHeader(false);
        $mainPdf->setPrintFooter(false);

        foreach ($uuids as $uuid) {
            // Skip if user or credentials are not found
            $user = $this->userClass::whereUuid($uuid)->first();
            if (!$user || !$user->credentials) {
                continue;
            }

            $pdfFile = $this->pdfService->generate($user, $user->credentials()->password);

            // Import all pages from the pdf into the main pdf
            $pageCount = $mainPdf->setSourceData($pdfFile);
            for ($i = 1; $i <= $pageCount; $i++) {
                $idx = $mainPdf->importPage($i);
                $mainPdf->addPage();
                $mainPdf->useTemplate($idx);
            }
        }

        // Set all users in this batch to "downloaded"
        DB::transaction(function () use ($uuids) {
            foreach ($uuids as $uuid) {
                /** @var AbstractUser|null $user */
                $user = $this->userClass::whereUuid($uuid)->first();
                if (!is_null($user)) {
                    $user->downloaded_at = now();
                    $user->save();
                }
            }
        });

        // And finally, return the batch PDF
        return new Response($mainPdf->getPDFData(), Response::HTTP_OK, [
            'Content-Type' => 'application/pdf',
            'Content-Length' => strlen($mainPdf->getPDFData()),
            'Content-Disposition' => HeaderUtils::makeDisposition('attachment', 'batch-' . time() . '.pdf')
        ]);
    }

    public function download(string $uuid): Response
    {
        $user = $this->userClass::whereUuid($uuid)->first();
        if (is_null($user) || is_null($user->uuid) || is_null($user->credentials)) {
            throw UserException::notFound($user?->uuid);
        }

        // Set PDF status to downloaded
        $user->downloaded_at = now();
        $user->save();

        $pdfFile = $this->pdfService->generate($user, $user->credentials->password);

        return new Response($pdfFile, Response::HTTP_OK, [
            'Content-Type' => 'application/pdf',
            'Content-Length' => strlen($pdfFile),
            'Content-Disposition' => HeaderUtils::makeDisposition('attachment', $user->uuid . '.pdf')
        ]);
    }
}
