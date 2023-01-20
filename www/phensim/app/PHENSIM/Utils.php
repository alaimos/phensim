<?php
/**
 * PHENSIM: Phenotype Simulator
 * @version 2.0.0.2
 * @author  Salvatore Alaimo, Ph.D.
 */

namespace App\PHENSIM;

use App\Exceptions\CommandException;
use App\Exceptions\FileSystemException;
use App\Exceptions\IgnoredException;
use JetBrains\PhpStorm\Pure;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Throwable;
use ZipArchive;

/**
 * All utilities without a specific place will be put here!
 * @package App\PHENSIM
 */
final class Utils
{

    /**
     * A placeholder for error codes that should be ignored
     */
    public const IGNORED_ERROR_CODE = '===IGNORED===';

    /**
     * A list of invalid characters for filenames
     */
    public const INVALID_CHARS = [
        '?',
        '[',
        ']',
        '/',
        '\\',
        '=',
        '<',
        '>',
        ':',
        ';',
        ',',
        "'",
        '"',
        '&',
        '$',
        '#',
        '*',
        '(',
        ')',
        '|',
        '~',
        '`',
        '!',
        '{',
        '}',
        '%',
        '+',
        "\0",
    ];

    /**
     * Replace all invalid characters from a filename with a "_" symbol
     *
     * @param  string  $name  The filename to sanitize
     *
     * @return string
     */
    public static function sanitizeFilename(string $name): string
    {
        return str_replace(self::INVALID_CHARS, '_', $name);
    }

    /**
     * Recursively delete files or directories
     *
     * @param  string  $path  a filesystem object to delete (file or directory)
     *
     * @return bool
     */
    public static function delete(string $path): bool
    {
        if (!file_exists($path)) {
            return false;
        }
        if (is_file($path)) {
            return unlink($path);
        }
        if (is_dir($path)) {
            $files = array_diff(scandir($path), ['.', '..']);
            foreach ($files as $file) {
                $absolutePath = $path.DIRECTORY_SEPARATOR.$file;
                if (is_file($absolutePath) || is_link($absolutePath)) {
                    unlink($absolutePath);
                } elseif (is_dir($absolutePath)) {
                    self::delete($absolutePath);
                }
            }

            return rmdir($path);
        }

        return false;
    }

    /**
     * Create a directory and set permissions
     *
     * @param  string  $directory
     *
     * @return void
     * @throws \App\Exceptions\FileSystemException
     */
    public static function createDirectory(string $directory): void
    {
        if (!file_exists($directory)) {
            if (!mkdir($directory, 0777, true) && !is_dir($directory)) {
                throw new FileSystemException(sprintf('Directory "%s" was not created', $directory));
            }
            if (!chmod($directory, 0777)) {
                throw new FileSystemException(
                    sprintf('Permissions to directory "%s" were not assigned correctly', $directory)
                );
            }
        }
    }

    /**
     * Returns the path of a folder inside the storage/app directory.
     * The directory will be created if it does not exist
     *
     * @param  string  $for  A name for the subfolder
     *
     * @return string
     * @throws \App\Exceptions\FileSystemException
     */
    public static function getStorageDirectory(string $for): string
    {
        $path = storage_path('app/'.$for);
        if (!file_exists($path)) {
            self::createDirectory($path);
        }

        return $path;
    }

    /**
     * Returns the path of a random file in a storage directory
     *
     * @param  string  $for
     * @param  string  $prefix
     *
     * @return string
     * @throws \App\Exceptions\FileSystemException
     */
    public static function storageFile(string $for, string $prefix = 'tmp_'): string
    {
        return self::getStorageDirectory($for).DIRECTORY_SEPARATOR.uniqid(self::sanitizeFilename($prefix), true);
    }

    /**
     * Returns the path of the temporary directory
     *
     * @return string
     * @throws \App\Exceptions\FileSystemException
     */
    public static function tempDir(): string
    {
        return self::getStorageDirectory('tmp');
    }

    /**
     * Create a random filename
     *
     * @param  string  $prefix
     * @param  string  $suffix
     *
     * @return string
     */
    public static function tempFilename(string $prefix = '', string $suffix = ''): string
    {
        return uniqid(self::sanitizeFilename($prefix), true).self::sanitizeFilename($suffix);
    }

    /**
     * Return the path of a temporary file in the temporary directory
     *
     * @param  string  $prefix
     * @param  string  $extension
     *
     * @return string
     * @throws \App\Exceptions\FileSystemException
     */
    public static function tempFile(string $prefix = '', string $extension = ''): string
    {
        return self::tempDir().DIRECTORY_SEPARATOR.self::tempFilename($prefix, $extension);
    }

    /**
     * Runs a shell command and checks for successful completion of execution
     *
     * @param  array  $command
     * @param  string|null  $cwd
     * @param  int|null  $timeout
     * @param  callable|null  $callback
     *
     * @return string|null
     * @throws \Symfony\Component\Process\Exception\ProcessFailedException
     */
    public static function runCommand(
        array $command,
        ?string $cwd = null,
        ?int $timeout = null,
        ?callable $callback = null
    ): ?string {
        $process = new Process($command, $cwd, null, null, $timeout);
        $process->run($callback);
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return $process->getOutput();
    }

    /**
     * Maps a ProcessFailedException to a more readable error message.
     *
     * @param  \Symfony\Component\Process\Exception\ProcessFailedException  $e
     * @param  array  $errorCodeMap
     *
     * @return \Throwable
     */
    public static function mapCommandException(ProcessFailedException $e, array $errorCodeMap = []): Throwable
    {
        $code = $e->getProcess()->getExitCode();
        if (isset($errorCodeMap[$code])) {
            if ($errorCodeMap[$code] !== self::IGNORED_ERROR_CODE) {
                return new IgnoredException($code, $code);
            }

            return new CommandException($errorCodeMap[$code], $code, $e);
        }

        return new CommandException($e->getMessage(), $code, $e);
    }

    /**
     * Format a float number using scientific notation if needed.
     * The user provides a number to format and the maximum number of decimal places.
     * If the provided number is too small the scientific notation will be used.
     *
     * @param  float  $number  A number
     * @param  int  $decimals  The maximum number of decimal places
     * @param  bool  $scientific  Is scientific notation enabled?
     *
     * @return string
     */
    #[Pure] public static function formatDouble(float $number, int $decimals = 4, bool $scientific = true): string
    {
        if ($scientific && $number !== 0 && abs($number) < (10 ** -$decimals)) {
            return sprintf('%.4e', $number);
        }

        return number_format($number, $decimals);
    }

    /**
     * Count the number of lines in a text file using "wc" shell utility for performance
     *
     * @param  string  $file
     *
     * @return int
     */
    public static function countLines(string $file): int
    {
        try {
            return (int)self::runCommand(['wc', '-l', $file]);
        } catch (CommandException) {
            return -1;
        }
    }

    /**
     * Checks if a file could contain node types.
     * A node types file is a TSV files where comments are denoted with a "#" symbol at the beginning of a row.
     * Each row must contain at most two field: a name (string) and an optional weight (number)
     *
     * @param  string  $file
     *
     * @return bool
     */
    public static function checkNodeTypeFile(string $file): bool
    {
        if (!file_exists($file)) {
            return false;
        }
        $fp = @fopen($file, 'rb');
        if (!$fp) {
            return false;
        }
        $result = true;
        while (($line = fgets($fp)) !== false) {
            if (empty($line) || str_starts_with($line, '#')) {
                continue;
            }
            $fields = str_getcsv($line, "\t");
            $c = count($fields);
            if ($c > 2 || ($c === 2 && !is_numeric($fields[1]))) {
                $result = false;
                break;
            }
        }
        @fclose($fp);

        return $result;
    }

    /**
     * Checks if a file could contain edge types.
     * An edge types file is a TSV files where comments are denoted with a "#" symbol at the beginning of a row.
     * Each row must contain a single string field.
     * I know a TSV file with a single field doesn't make sense but PHENSIM will use more than one fields when we will
     * introduce ML prediction.
     *
     * @param  string  $file
     *
     * @return bool
     */
    public static function checkEdgeTypeFile(string $file): bool
    {
        if (!file_exists($file)) {
            return false;
        }
        $fp = @fopen($file, 'rb');
        if (!$fp) {
            return false;
        }
        $result = true;
        while (($line = fgets($fp)) !== false) {
            if (empty($line) || str_starts_with($line, '#')) {
                continue;
            }
            if (substr_count($line, "\t") > 0) {
                $result = false;
                break;
            }
        }
        @fclose($fp);

        return $result;
    }

    /**
     * Checks if a file could contain edge subtypes
     * An edge subtypes file is a TSV files where comments are denoted with a "#" symbol at the beginning of a row.
     * Each row must contain at most three field: a name (string), an optional weight (number), and an optional
     * priority (number)
     *
     * @param  string  $file
     *
     * @return bool
     */
    public static function checkEdgeSubTypeFile(string $file): bool
    {
        if (!file_exists($file)) {
            return false;
        }
        $fp = @fopen($file, 'rb');
        if (!$fp) {
            return false;
        }
        $result = true;
        while (($line = fgets($fp)) !== false) {
            if (empty($line) || str_starts_with($line, '#')) {
                continue;
            }
            $fields = str_getcsv($line, "\t");
            $c = count($fields);
            if (
                $c > 3 ||
                ($c === 3 && !is_numeric($fields[2])) ||
                ($c >= 2 && !is_numeric($fields[1]))
            ) {
                $result = false;
                break;
            }
        }
        @fclose($fp);

        return $result;
    }

    /**
     * Checks if a file could contain an enrichment db
     * An enrichment file is a TSV files with 9 fields where comments are denoted with a "#" symbol at the beginning of
     * a row.
     *
     * @param  string  $file
     *
     * @return bool
     */
    public static function checkDbFile(string $file): bool
    {
        if (!file_exists($file)) {
            return false;
        }
        $fp = @fopen($file, 'rb');
        if (!$fp) {
            return false;
        }
        $result = true;
        while (($line = fgets($fp)) !== false) {
            if (empty($line) || str_starts_with($line, '#')) {
                continue;
            }
            // I must use the str_getcsv function to account for escaping otherwise I would have counted the number of "\t" in the row
            if (count(str_getcsv($line, "\t")) !== 9) {
                $result = false;
                break;
            }
        }
        @fclose($fp);

        return $result;
    }

    /**
     * Checks if a file is a valid phensim input file
     *
     * @param  string  $file
     *
     * @return bool
     */
    public static function checkInputFile(string $file): bool
    {
        if (!file_exists($file)) {
            return false;
        }
        $fp = @fopen($file, 'rb');
        if (!$fp) {
            return false;
        }
        $aType = [
            Launcher::OVEREXPRESSION  => true,
            Launcher::UNDEREXPRESSION => true,
        ];
        $result = true;
        while (($line = fgets($fp)) !== false) {
            if (empty($line) || str_starts_with($line, '#')) {
                continue;
            }
            $fields = str_getcsv($line, "\t");
            $c = count($fields);
            if (
                $c !== 2 ||
                !isset($aType[strtoupper($fields[1])])
            ) {
                $result = false;
                break;
            }
        }
        @fclose($fp);

        return $result;
    }

    /**
     * Checks if a file contains a list of elements
     *
     * @param  string  $file
     *
     * @return bool
     */
    public static function checkListFile(string $file): bool
    {
        if (!file_exists($file)) {
            return false;
        }
        $fp = @fopen($file, 'rb');
        if (!$fp) {
            return false;
        }
        $result = true;
        while (($line = fgets($fp)) !== false) {
            if (empty($line)) {
                $result = false;
                break;
            }
        }
        @fclose($fp);

        return $result;
    }

    /**
     * Create a zip archive
     *
     * @param  string  $filename
     * @param  array  $files
     *
     * @return bool
     */
    public static function createZipArchive(string $filename, array $files): bool
    {
        $zip = new ZipArchive();
        if ($zip->open($filename, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
            foreach ($files as $file) {
                if (file_exists($file)) {
                    $zip->addFile($file, basename($file));
                }
            }
            $zip->close();
        } else {
            return false;
        }

        return true;
    }

    public static function mixed2bool($value): bool
    {
        if (is_bool($value)) {
            return $value;
        }
        if (is_numeric($value)) {
            return (bool)$value;
        }
        return strtolower($value) === 'true';
    }

}
