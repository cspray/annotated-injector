<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\Helper;

use php_user_filter;

final class StreamBuffer extends php_user_filter {

    private static string $buffer = '';

    public static function getBuffer() : string {
        return self::$buffer;
    }

    public static function clearBuffer() : void {
        self::$buffer = '';
    }

    public function filter($in, $out, &$consumed, bool $closing) : int {
        while ($bucket = stream_bucket_make_writeable($in)) {
            self::$buffer .= $bucket->data;
            $consumed += $bucket->datalen;
            stream_bucket_append($out, $bucket);
        }

        return PSFS_FEED_ME;
    }
}
