<?php declare(strict_types=1);

namespace DeRidderDenHertog\Laravel\Telescope;

use Exception;

final class CouldNotParseSoapResponse extends Exception
{
    public static function closingTagCouldNotBeFound(): self
    {
        return new self('The closing tag </RHDataServiceResult> cannot be found in the SOAP response.');
    }

    public static function openingTagCouldNotBeFound(): self
    {
        return new self('The opening tag <RHDataServiceResult> cannot be found in the SOAP response.');
    }

    public static function unknownError(): self
    {
        return new self('SOAP response could not be parsed due to an unknown error.');
    }
}
