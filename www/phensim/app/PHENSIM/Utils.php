<?php

namespace App\PHENSIM;


use App\Exceptions\CommandException;
use App\Exceptions\IgnoredException;
use App\Exceptions\ProcessingJobException;
use App\Models\Organism;
use RuntimeException;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Throwable;

final class Utils
{

    public const IGNORED_ERROR_CODE = '===IGNORED===';

    /**
     * Delete a file or a directory
     *
     * @param string $path something to delete
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
                static::delete($path . DIRECTORY_SEPARATOR . $file);
            }

            return rmdir($path);
        }

        return false;
    }

    /**
     * Create a directory and set chmod
     *
     * @param string $directory
     *
     * @return void
     */
    public static function createDirectory(string $directory): void
    {
        if (!file_exists($directory)) {
            if (!mkdir($directory, 0777, true) && !is_dir($directory)) {
                throw new RuntimeException(sprintf('Directory "%s" was not created', $directory));
            }
            @chmod($directory, 0777);
        }
    }

    /**
     * Returns the path of a storage directory
     *
     * @param string $type
     *
     * @return string
     */
    public static function getStorageDirectory(string $type): string
    {
        $path = storage_path('app/' . $type);
        if (!file_exists($path)) {
            static::createDirectory($path);
        }

        return $path;
    }

    /**
     * Returns the path of a random file in a storage directory
     *
     * @param string $type
     *
     * @return string
     */
    public static function storageFile(string $type): string
    {
        return self::getStorageDirectory($type) . DIRECTORY_SEPARATOR . self::makeKey(mt_rand(), microtime(true));
    }

    /**
     * Returns the path of the temporary files directory
     *
     * @return string
     */
    public static function tempDir(): string
    {
        $dirName = storage_path('tmp');
        if (!file_exists($dirName)) {
            static::createDirectory($dirName);
        }

        return $dirName;
    }

    /**
     * Return the name of a temporary file
     *
     * @param string $prefix
     * @param string $extension
     *
     * @return string
     */
    public static function tempFilename(string $prefix = '', string $extension = ''): string
    {
        $filename = $prefix . self::makeKey($prefix, microtime(true));
        if (!empty($extension)) {
            $filename .= '.' . ltrim($extension, '.');
        }

        return $filename;
    }

    /**
     * Return the path of a temporary file name in the temporary files directory
     *
     * @param string $prefix
     * @param string $extension
     *
     * @return string
     */
    public static function tempFile(string $prefix = '', string $extension = ''): string
    {
        return self::tempDir() . DIRECTORY_SEPARATOR . self::tempFilename($prefix, $extension);
    }

    /**
     * Runs a shell command and checks for successful completion of execution
     *
     * @param array         $command
     * @param string|null   $cwd
     * @param int|null      $timeout
     * @param callable|null $callback
     *
     * @return string|null
     * @throws \Symfony\Component\Process\Exception\ProcessFailedException
     */
    public static function runCommand(array $command, ?string $cwd = null, ?int $timeout = null, ?callable $callback = null): ?string
    {
        $process = new Process($command, $cwd, null, null, $timeout);
        $process->run($callback);
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return $process->getOutput();
    }

    /**
     * Map command exception to message
     *
     * @param \Symfony\Component\Process\Exception\ProcessFailedException $e
     * @param array                                                       $errorCodeMap
     *
     * @return \App\Exceptions\CommandException|\App\Exceptions\IgnoredException
     */
    public static function mapCommandException(ProcessFailedException $e, array $errorCodeMap = []): Throwable
    {
        $code = $e->getProcess()->getExitCode();
        if (isset($errorCodeMap[$code])) {
            if ($errorCodeMap[$code] === self::IGNORED_ERROR_CODE) {
                return new IgnoredException($code, $code);
            }

            return new CommandException($errorCodeMap[$code], $code, $e);
        }

        return new CommandException($e->getMessage(), $code, $e);
    }

    private static function recursiveFilter($objects): string
    {
        if (is_object($objects)) {
            if (method_exists($objects, 'getKey')) {
                return $objects->getKey();
            }

            return (string)$objects;
        }

        if (is_array($objects)) {
            return '[' . implode(
                    ',',
                    array_map(
                        static function ($k, $v) {
                            return $k . '=>' . Utils::recursiveFilter($v);
                        },
                        array_keys($objects),
                        $objects
                    )
                ) . ']';
        }

        if (is_resource($objects)) {
            return '';
        }

        return $objects;
    }

    /**
     * Generate an unique key starting from a set of objects
     *
     * @param mixed ...
     *
     * @return string
     */
    public static function makeKey(/*...*/): string
    {
        return md5(self::recursiveFilter(func_get_args()));
    }

    /**
     * Compress and encode a big array for storage in the database
     *
     * @param array $array
     *
     * @return string
     */
    public static function compressArray(array $array): string
    {
        return base64_encode(gzcompress(serialize($array), 9));
    }

    /**
     * Decode and expand a big array from the database
     *
     * @param string $string
     *
     * @return array
     */
    public static function uncompressArray(string $string): array
    {
        /** @noinspection UnserializeExploitsInspection */
        return (array)unserialize(gzuncompress(base64_decode($string)));
    }

    /**
     * Format a float number using scientific notation if needed
     *
     * @param float $number
     * @param int   $decimals
     * @param bool  $scientific
     *
     * @return string
     */
    public static function formatDouble(float $number, int $decimals = 4, bool $scientific = true): string
    {
        if ($scientific && $number !== 0 && abs($number) < (10 ** -$decimals)) {
            return sprintf('%.4e', $number);
        }

        return number_format($number, $decimals);
    }

    /**
     * Count the number of lines in a text file
     *
     * @param string $file
     *
     * @return integer
     */
    public static function countLines(string $file): int
    {
        try {
            return (int)self::runCommand(['wc', '-l', $file]);
        } catch (CommandException $e) {
            return -1;
        }
    }

    /**
     * Checks if a file could contain node types
     *
     * @param string $file
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
        while (($line = fgets($fp)) !== false) {
            if (empty($line) || strpos($line, '#') === 0) {
                continue;
            }
            $fields = explode("\t", trim($line));
            $c = count($fields);
            if ($c === 1) {
                continue;
            }
            if ($c === 2 && !is_numeric($fields[1])) {
                return false;
            }
            if ($c > 2) {
                return false;
            }
        }
        @fclose($fp);

        return true;
    }

    /**
     * Checks if a file could contain edge types
     *
     * @param string $file
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
        while (($line = fgets($fp)) !== false) {
            if (empty($line) || strpos($line, '#') === 0) {
                continue;
            }
            $fields = explode("\t", trim($line));
            $c = count($fields);
            if ($c > 1) {
                return false;
            }
        }
        @fclose($fp);

        return true;
    }

    /**
     * Checks if a file could contain edge subtypes
     *
     * @param string $file
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
        while (($line = fgets($fp)) !== false) {
            if (empty($line) || strpos($line, '#') === 0) {
                continue;
            }
            $fields = explode("\t", trim($line));
            $c = count($fields);
            if ($c === 1) {
                continue;
            }
            if ($c >= 2 && !is_numeric($fields[1])) {
                return false;
            }
            if ($c >= 3 && !is_numeric($fields[2])) {
                return false;
            }
            if ($c > 4) {
                return false;
            }
        }
        @fclose($fp);

        return true;
    }

    /**
     * Checks if a file could contain an enrichment db
     *
     * @param string $file
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
        while (($line = fgets($fp)) !== false) {
            if (empty($line) || strpos($line, '#') === 0) {
                continue;
            }
            $fields = explode("\t", trim($line));
            $c = count($fields);
            if ($c !== 9) {
                return false;
            }
        }
        @fclose($fp);

        return true;
    }

    /**
     * Checks if a file is a valid phensim input file
     *
     * @param string        $file
     * @param callable|null $callback
     *
     * @return bool
     */
    public static function checkInputFile(string $file, callable $callback = null): bool
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
            Launcher::BOTH            => true,
        ];
        while (($line = fgets($fp)) !== false) {
            if (empty($line) || strpos($line, '#') === 0) {
                continue;
            }
            $fields = explode("\t", trim($line));
            $c = count($fields);
            if ($c !== 2) {
                return false;
            }
            $fields[1] = strtoupper($fields[1]);
            if (!isset($aType[$fields[1]])) {
                return false;
            }
            if ($callback !== null && is_callable($callback)) {
                $callback($fields);
            }
        }
        @fclose($fp);

        return true;
    }

    /**
     * Read phensim input file
     *
     * @param string $file
     *
     * @return array|null
     */
    public static function readInputFile(string $file): ?array
    {
        $inputArray = [];
        $check = self::checkInputFile(
            $file,
            static function ($fields) use (&$inputArray) {
                $inputArray[$fields[0]] = $fields[1];
            }
        );
        if (!$check) {
            return null;
        }

        return $inputArray;
    }

    /**
     * Checks if a file is a valid phensim input file
     *
     * @param array $data
     *
     * @return bool
     */
    public static function checkSimulationParameters(array $data): bool
    {
        if (empty($data)) {
            return false;
        }
        $aType = [
            Launcher::OVEREXPRESSION  => true,
            Launcher::UNDEREXPRESSION => true,
            Launcher::BOTH            => true,
        ];
        foreach ($data as $gene => $type) {
            if (!isset($aType[$type])) {
                return false;
            }
        }

        return true;
    }


}
