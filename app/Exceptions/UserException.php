<?php

declare(strict_types=1);

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class UserException extends HttpException
{
    /**
     * @return self
     */
    public static function alreadyExists(): self
    {
        return new self(Response::HTTP_CONFLICT, 'user already exists');
    }

    /**
     * @return self
     */
    public static function ampUploadFailure(string $message): self
    {
        return new self(Response::HTTP_INTERNAL_SERVER_ERROR, 'failed to upload CSV to AMP: ' . $message);
    }

    /**
     * @return self
     */
    public static function notFound(?string $uuid): self
    {
        return new self(Response::HTTP_NOT_FOUND, 'User not found: ' . $uuid);
    }

    /**
     * @return self
     */
    public static function noWriteAccess(): self
    {
        return new self(Response::HTTP_FORBIDDEN, 'write access is not allowed for readonly accounts');
    }
}
