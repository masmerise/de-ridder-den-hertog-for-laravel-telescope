<?php declare(strict_types=1);

namespace DeRidderDenHertog\Laravel\Telescope;

use Exception;

final class CouldNotParseSoapRequest extends Exception
{
    public static function closingTagCouldNotBeFound(): self
    {
        return new self('The closing tag </paramRequest> cannot be found in the SOAP request.');
    }

    public static function openingTagCouldNotBeFound(): self
    {
        return new self('The opening tag cannot be found in the SOAP request.');
    }

    public static function unknownError(): self
    {
        return new self('SOAP request could not be parsed due to an unknown error.');
    }
}
