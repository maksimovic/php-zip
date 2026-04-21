<?php

declare(strict_types=1);

/*
 * This file is part of the nelexa/zip package.
 * (c) Ne-Lexa <https://github.com/Ne-Lexa/php-zip>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpZip\Util;

use PhpZip\Exception\ZipException;

/**
 * Thin wrappers around PHP binary-packing built-ins that turn the documented
 * `false` return into a typed {@see ZipException}. Used everywhere the library
 * would otherwise feed a `false` into a `string`- or `array`-typed operand.
 *
 * @internal
 */
final class PackUtil
{
    /**
     * Callers in this library only ever use int-producing unpack formats
     * (`C`, `V`, `v`, `P`, `c`), so the declared element type is narrowed
     * to `int` for convenience at call sites.
     *
     * @throws ZipException if unpack cannot produce a result for this format/buffer
     *
     * @return array<array-key, int>
     */
    public static function unpackOrFail(string $format, string $buffer): array
    {
        /** @var array<array-key, int>|false $result */
        $result = unpack($format, $buffer);

        if ($result === false) {
            throw new ZipException(sprintf('Failed to unpack binary data with format "%s"', $format));
        }

        return $result;
    }

    /**
     * @param int|float|string ...$values
     *
     * @throws ZipException if pack cannot produce a result for this format
     */
    public static function packOrFail(string $format, ...$values): string
    {
        $result = pack($format, ...$values);

        /** @psalm-suppress TypeDoesNotContainType pack return type varies between psalm 8.1/8.5 stubs */
        if ($result === false) {
            throw new ZipException(sprintf('Failed to pack binary data with format "%s"', $format));
        }

        return $result;
    }

    /**
     * @throws ZipException if substr would return false (only possible with invalid offset semantics)
     */
    public static function substrOrFail(string $buffer, int $offset, ?int $length = null): string
    {
        $result = $length === null ? substr($buffer, $offset) : substr($buffer, $offset, $length);

        if ($result === false) {
            throw new ZipException(sprintf('Failed to slice buffer at offset %d', $offset));
        }

        return $result;
    }

    /**
     * @param resource $stream
     *
     * @throws ZipException on read failure
     */
    public static function freadOrFail($stream, int $length): string
    {
        $result = fread($stream, $length);

        if ($result === false) {
            throw new ZipException(sprintf('Failed to read %d bytes from stream', $length));
        }

        return $result;
    }

    /**
     * @param resource $stream
     *
     * @throws ZipException on ftell failure
     */
    public static function ftellOrFail($stream): int
    {
        $result = ftell($stream);

        if ($result === false) {
            throw new ZipException('Failed to read stream position (ftell)');
        }

        return $result;
    }
}
