<?php
declare(strict_types=1);

namespace DeRidderDenHertog\Laravel\Telescope;

final readonly class SoapResponse
{
    /** @throws CouldNotParseSoapResponse */
    public static function unwrap(string $xml): array
    {
        $openingTagPosition = strrpos($xml, $openingTag = '<RHDataServiceResult>');
        if ($openingTagPosition === false) {
            throw CouldNotParseSoapResponse::openingTagCouldNotBeFound();
        }

        $closingTagPosition = strrpos($xml, '</RHDataServiceResult>');
        if ($closingTagPosition === false) {
            throw CouldNotParseSoapResponse::closingTagCouldNotBeFound();
        }

        $json = substr($xml, $openingTagPosition + strlen($openingTag), $closingTagPosition - $openingTagPosition - strlen($openingTag));
        $json = base64_decode($json);
        $json = json_decode($json, true);

        return $json ?? throw CouldNotParseSoapResponse::unknownError();
    }

    private function __construct() {}
}
