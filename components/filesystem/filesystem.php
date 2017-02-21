<?php
/**
 * Allow file upload and storage for files added to the filesystem of the app.
 *
 * @package Advandz
 * @subpackage Advandz.components.upload
 * @copyright Copyright (c) 2012-2017 CyanDark, Inc. All Rights Reserved.
 * @license https://opensource.org/licenses/MIT The MIT License (MIT)
 * @author The Advandz Team <team@advandz.com>
 */

namespace Advandz\Component;

use Advandz\Helper\Arrayment;

class Filesystem
{
    /**
     * Read the file specified in the path and returns it as a string.
     *
     * @param  string $file The full path of the file to read
     * @return string The file contents
     */
    public function readFile($file)
    {
        try {
            return file_get_contents($file);
        } catch (Exception $e) {
            throw new \Exception('The file '.$file.' can\'t be readed, Permission denied');
        }
    }

    /**
     * Save data to the file specified in the path and creates a new file if not exists.
     *
     * @param  string    $file      The full path of the file to save
     * @param  mixed     $data      The permission value in octets, null to default to user permissions
     * @param  bool      $overwrite True to overwrite if the file exists
     * @return bool      True if the file has been saved successfully
     * @throws Exception If the file can't be writed
     */
    public function saveFile($file, $data, $overwrite = false)
    {
        if (($overwrite && file_exists($file)) || !file_exists($file)) {
            try {
                return @file_put_contents($file, $data);
            } catch (Exception $e) {
                throw new \Exception('The file '.$file.' can\'t be written, Permission denied');
            }
        } else {
            throw new \Exception('The file '.$file.' already exists');
        }
    }

    /**
     * Delete a file.
     *
     * @param  string    $file The full path of the file to delete
     * @return bool      True if the file has been deleted, false if not exists
     * @throws Exception If the file can't be deleted
     */
    public function deleteFile($file)
    {
        try {
            return unlink($file);
        } catch (Exception $e) {
            throw new \Exception('The file '.$file.' can\'t be deleted, Permission denied');
        }
    }

    /**
     * Check if is a valid file.
     *
     * @param  string $file The full path of the file
     * @return bool   True if is a valid file
     */
    public function isFile($file)
    {
        return is_file($file);
    }

    /**
     * Get the information of a file.
     *
     * @param  string $file The full path of the file
     * @return array  An array containing the informetion about the file
     */
    public function getInfoFile($file)
    {
        if (file_exists($file)) {
            $fileinfo = [
                'name'       => basename($file),
                'path'       => $file,
                'size'       => filesize($file),
                'date'       => filemtime($file),
                'readable'   => is_readable($file),
                'executable' => is_executable($file),
                'fileperms'  => fileperms($file)
            ];

            return $fileinfo;
        }

        return false;
    }

    /**
     * Read the files and folders of a directory.
     *
     * @param  string $dir       The full path of the directory
     * @param  bool   $flat      True to return a flat array, Otherwise will be returned a matrix
     * @param  bool   $recursive True to scan de directory recursively
     * @return array  An array containing the directory structure
     */
    public function readDir($dir, $flat = true, $recursive = false)
    {
        if (is_dir($dir)) {
            $content = array_values(array_diff(scandir($dir), ['.', '..']));

            if ($recursive || $flat) {
                foreach ($content as $key => $entry) {
                    $path = DS.trim($dir, DS).DS.$entry;

                    if (is_dir($path)) {
                        $path = $path.DS;
                    }

                    if ($flat) {
                        $content[$key] = $path;
                    }

                    if (is_dir($path) && $recursive && !$flat) {
                        $content[$key] = [$entry => $this->readDir($path, true)];
                    } elseif (is_dir($path) && $recursive && $flat) {
                        $content[$path] = $path;
                        $content[$key]  = [$entry => $this->readDir($path, true)];
                    }
                }
            }

            if ($flat) {
                $arrayment = new Arrayment();
                $content   = $arrayment->collapse($content, true);
            }

            return $content;
        }

        return [];
    }

    /**
     * Creates a new directory.
     *
     * @param  string    $dir         The full path of the new directory
     * @param  int       $permissions The permissions of the directory (Ignored in Windows)
     * @return bool      True if the directory has been created successfully
     * @throws Exception If the directory can't be created
     */
    public function createDir($dir, $permissions = 0777)
    {
        try {
            return mkdir($dir, $permissions, true);
        } catch (Exception $e) {
            throw new \Exception('The directory '.$dir.' can\'t be created, Permission denied');
        }
    }

    /**
     * Deletes a directory.
     *
     * @param  string    $dir       The full path of the directory
     * @param  bool      $recursive True to delete all the containing files and folders
     * @return bool      True if the directory has been deleted successfully
     * @throws Exception If the directory can't be deleted
     */
    public function deleteDir($dir, $recursive = true)
    {
        if ($recursive) {
            $entries = $this->readDir($dir, true, true);

            foreach ($entries as $entry) {
                if (is_file($entry)) {
                    unlink($entry);
                } elseif (is_dir($entry)) {
                    rmdir($entry);
                }
            }

            return rmdir($dir);
        } else {
            try {
                return rmdir($dir);
            } catch (Exception $e) {
                throw new \Exception('The directory '.$dir.' can\'t be deleted, Permission denied');
            }
        }
    }

    /**
     * Check if is a valid directory.
     *
     * @param  string $dir The full path of the directory
     * @return bool   True if is a valid directory
     */
    public function isDir($dir)
    {
        try {
            return is_dir($dir);
        } catch (Exception $e) {
            throw new \Exception('The directory '.$dir.' can\'t be readed, Permission denied');
        }
    }
}
