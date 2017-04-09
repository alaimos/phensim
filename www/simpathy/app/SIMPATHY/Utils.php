<?php

namespace App\SIMPATHY;


use App\Exceptions\CommandException;

final class Utils
{
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
        } elseif (is_dir($path)) {
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
    public static function createDirectory(string $directory)
    {
        if (!file_exists($directory)) {
            @mkdir($directory, 0777, true);
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
        return self::getStorageDirectory($type) . DIRECTORY_SEPARATOR . self::makeKey(rand(), microtime(true));
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
     * @param string     $command
     * @param array|null $output
     *
     * @return boolean
     */
    public static function runCommand(string $command, array &$output = null): bool
    {
        $returnCode = -1;
        exec($command, $output, $returnCode);
        if ($returnCode != 0) {
            throw new CommandException($returnCode);
        }
        return true;
    }

    private static function recursiveFilter($objects): string
    {
        if (is_object($objects)) {
            if (method_exists($objects, 'getKey')) {
                return $objects->getKey();
            } else {
                return (string)$objects;
            }
        } elseif (is_array($objects)) {
            return '[' . implode(',', array_map(function ($k, $v) {
                    return $k . '=>' . self::recursiveFilter($v);
                }, array_keys($objects), $objects)) . ']';
        } elseif (is_resource($objects)) {
            return '';
        } else {
            return $objects;
        }
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
     * Compress and encode a big array for storing in the database
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
        if ($scientific && $number != 0 && abs($number) < pow(10, -$decimals)) {
            return sprintf('%.4e', $number);
        } else {
            return number_format($number, $decimals);
        }
    }

    /**
     * Map command exception to message
     *
     * @param string           $command
     * @param CommandException $e
     * @param array            $errorCodeMap
     *
     * @return boolean
     * @throws CommandException
     */
    public static function mapCommandException(string $command, CommandException $e, array $errorCodeMap = []): bool
    {
        $code = intval($e->getMessage());
        if (isset($errorCodeMap[$code])) {
            throw new CommandException($errorCodeMap[$code]);
        } else {
            throw new CommandException('Execution of command "' . $command . '" returned error code ' . $code . '.');
        }
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
        return intval(exec('wc -l ' . escapeshellarg($file)));
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
        if (!file_exists($file)) return false;
        $fp = @fopen($file, 'r');
        if (!$fp) return false;
        while (($line = fgets($fp)) !== false) {
            if (empty($line) || $line{0} == '#') continue;
            $fields = explode("\t", trim($line));
            $c = count($fields);
            if ($c == 1) continue;
            if ($c == 2 && !is_numeric($fields[1])) return false;
            if ($c > 2) return false;
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
        if (!file_exists($file)) return false;
        $fp = @fopen($file, 'r');
        if (!$fp) return false;
        while (($line = fgets($fp)) !== false) {
            if (empty($line) || $line{0} == '#') continue;
            $fields = explode("\t", trim($line));
            $c = count($fields);
            if ($c > 1) return false;
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
        if (!file_exists($file)) return false;
        $fp = @fopen($file, 'r');
        if (!$fp) return false;
        while (($line = fgets($fp)) !== false) {
            if (empty($line) || $line{0} == '#') continue;
            $fields = explode("\t", trim($line));
            $c = count($fields);
            if ($c == 1) continue;
            if ($c >= 2 && !is_numeric($fields[1])) return false;
            if ($c >= 3 && !is_numeric($fields[2])) return false;
            if ($c > 4) return false;
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
        if (!file_exists($file)) return false;
        $fp = @fopen($file, 'r');
        if (!$fp) return false;
        while (($line = fgets($fp)) !== false) {
            if (empty($line) || $line{0} == '#') continue;
            $fields = explode("\t", trim($line));
            $c = count($fields);
            if ($c != 9) return false;
        }
        @fclose($fp);
        return true;
    }

    /**
     * Checks if a file is a valid simpathy input file
     *
     * @param string        $file
     * @param callable|null $callback
     *
     * @return bool
     */
    public static function checkInputFile(string $file, callable $callback = null): bool
    {
        if (!file_exists($file)) return false;
        $fp = @fopen($file, 'r');
        if (!$fp) return false;
        $aType = [
            Launcher::OVEREXPRESSION  => true,
            Launcher::UNDEREXPRESSION => true,
            Launcher::BOTH            => true,
        ];
        while (($line = fgets($fp)) !== false) {
            if (empty($line) || $line{0} == '#') continue;
            $fields = explode("\t", trim($line));
            $c = count($fields);
            if ($c != 2) return false;
            $fields[1] = strtoupper($fields[1]);
            if (!isset($aType[$fields[1]])) return false;
            if ($callback !== null && is_callable($callback)) {
                call_user_func($callback, $fields);
            }
        }
        @fclose($fp);
        return true;
    }

    /**
     * Read simpathy input file
     *
     * @param string $file
     *
     * @return array
     */
    public static function readInputFile(string $file): array
    {
        $inputArray = [];
        $check = self::checkInputFile($file, function ($fields) use (&$inputArray) {
            $inputArray[$fields[0]] = $fields[1];
        });
        if (!$check) return null;
        return $inputArray;
    }


}