<?php

if (!function_exists('\bcmul')) {
    printf("PHP bc module is needed\n");
    exit;
}

if (!isset($argv[1])) {
    printf("Usage: %s file\n", $argv[0]);
    exit;
}

$file = $argv[1];
if (!is_file($file)) {
    printf("'%s' is not a file\n");
    exit;
}

$privateKeys = array_map('trim', file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES));

$outFile = __DIR__.'/out.csv';
$fpOut = fopen($outFile, 'wb') or die(sprintf("Unable to open '%s' for writing\n", $outFile));

$start = microtime(true);
foreach ($privateKeys as $privateKey) {
    $wif = PrivateKey2Wif::convert($privateKey);
    fputcsv($fpOut, [$privateKey, $wif]);
}
$end = microtime(true);

printf("%d private keys converted in %.3f seconds\n", count($privateKeys), $end - $start);

class PrivateKey2Wif {
    
    public static function convert($privateKey) {
        $prefixedPrivateKey = 'a9'.$privateKey;
        $firstPassSha256 = strtoupper(hash('sha256', hex2bin($prefixedPrivateKey)));
        $secondPassSha256 = strtoupper(hash('sha256', hex2bin($firstPassSha256)));
        $checksumString = substr($secondPassSha256, 0, 8);
        $checksummedPrivateKey = $prefixedPrivateKey.$checksumString;

        $service = new BCMathService();
        return $service->encode(hex2bin($checksummedPrivateKey));
    }
}
    
// From : https://github.com/stephen-hill/base58php
class BCMathService
{
    protected $alphabet;

    protected $base;

    public function __construct($alphabet = null)
    {
        // Handle null alphabet
        if (is_null($alphabet) === true) {
            $alphabet = '123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz';
        }

        // Type validation
        if (is_string($alphabet) === false) {
            throw new InvalidArgumentException('Argument $alphabet must be a string.');
        }

        // The alphabet must contain 58 characters
        if (strlen($alphabet) !== 58) {
            throw new InvalidArgumentException('Argument $alphabet must contain 58 characters.');
        }

        $this->alphabet = $alphabet;
        $this->base = strlen($alphabet);
    }
    /**
     * Encode a string into base58.
     *
     * @param  string $string The string you wish to encode.
     * @since Release v1.1.0
     * @return string The Base58 encoded string.
     */
    public function encode($string)
    {
        // Type validation
        if (is_string($string) === false) {
            throw new InvalidArgumentException('Argument $string must be a string.');
        }

        // If the string is empty, then the encoded string is obviously empty
        if (strlen($string) === 0) {
            return '';
        }

        // Strings in PHP are essentially 8-bit byte arrays
        // so lets convert the string into a PHP array
        $bytes = array_values(unpack('C*', $string));

        // Now we need to convert the byte array into an arbitrary-precision decimal
        // We basically do this by performing a base256 to base10 conversion
        $decimal = $bytes[0];

        for ($i = 1, $l = count($bytes); $i < $l; $i++) {
            $decimal = bcmul($decimal, 256);
            $decimal = bcadd($decimal, $bytes[$i]);
        }

        // This loop now performs base 10 to base 58 conversion
        // The remainder or modulo on each loop becomes a base 58 character
        $output = '';
        while ($decimal >= $this->base) {
            $div = bcdiv($decimal, $this->base, 0);
            $mod = bcmod($decimal, $this->base);
            $output .= $this->alphabet[$mod];
            $decimal = $div;
        }

        // If there's still a remainder, append it
        if ($decimal > 0) {
            $output .= $this->alphabet[$decimal];
        }

        // Now we need to reverse the encoded data
        $output = strrev($output);

        // Now we need to add leading zeros
        foreach ($bytes as $byte) {
            if ($byte === 0) {
                $output = $this->alphabet[0] . $output;
                continue;
            }
            break;
        }

        return (string) $output;
    }
}