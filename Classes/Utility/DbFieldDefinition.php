<?php

declare(strict_types=1);

namespace Typo3Api\Utility;

class DbFieldDefinition
{
    public static function getIntForNumberRange(int $low, int $high, int $default = 0): string
    {
        if ($default < $low) {
            $low = $default;
        }

        if ($default > $high) {
            $high = $default;
        }

        if ($low >= 0) {
            if ($high <= 255) {
                return "TINYINT(3) UNSIGNED DEFAULT '$default' NOT NULL";
            }

            if ($high <= 65535) {
                return "SMALLINT(5) UNSIGNED DEFAULT '$default' NOT NULL";
            }

            if ($high <= 16777215) {
                return "MEDIUMINT(8) UNSIGNED DEFAULT '$default' NOT NULL";
            }

            // the compare alone might already cause problems with 32bit php... might need to investigate
            if ($high <= 4294967295) {
                return "INT(10) UNSIGNED DEFAULT '$default' NOT NULL";
            }
        } else {
            if ($low >= -128 && $high <= 127) {
                return "TINYINT(4) DEFAULT '$default' NOT NULL";
            }

            if ($low >= -32768 && $high <= 32767) {
                return "SMALLINT(6) DEFAULT '$default' NOT NULL";
            }

            if ($low >= -8388608 && $high <= 8388607) {
                return "MEDIUMINT(8) DEFAULT '$default' NOT NULL";
            }

            if ($low >= -2147483648 && $high <= 2147483647) {
                return "INT(11) DEFAULT '$default' NOT NULL";
            }
        }

        // i leave bigint away because it'll cause all sorts of trouble with 32 vs 64 bit php and other quirks.

        throw new \LogicException("Can't find a fitting db type for range '$low' to '$high'.");
    }
}
