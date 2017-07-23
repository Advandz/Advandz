<?php
/**
 * Allow file upload and storage for files added to the filesystem of the app.
 *
 * @package Advandz
 * @subpackage Advandz.components.upload
 * @copyright Copyright (c) 2016-2017 Advandz, LLC. All Rights Reserved.
 * @license https://opensource.org/licenses/MIT The MIT License (MIT)
 * @author The Advandz Team <team@advandz.com>
 */

namespace Advandz\Component;

use Exception;
use Advandz\Core\Configure;

class Upload
{
    /**
     * @var int Maximum upload size in bytes (Default: 5MB)
     */
    private $file_size = 5120000;

    /**
     * @var string The upload directory
     */
    private $upload_dir = 'uploads';

    /**
     * Constructs a new Upload object.
     */
    public function __construct()
    {
        // Fetch default upload folder
        $upload_dir = Configure::get('Upload.upload_dir');

        if (!empty($upload_dir)) {
            $this->upload_dir = ROOTWEBDIR . trim($upload_dir, DS) . DS;
        } else {
            $this->upload_dir = ROOTWEBDIR . $this->upload_dir . DS;
        }
    }

    /**
     * Get all the posted (uploaded) files to the server.
     *
     * @return array An array containing all the posted files to the server
     */
    public function getFiles()
    {
        $files       = $_FILES;
        $files_array = [];

        foreach ($files as $key => $file) {
            if (is_array($file) && is_array($file['name'])) {
                foreach ($file['name'] as $file_key => $value) {
                    if (!empty($file['name'][$file_key]) && !empty($file['tmp_name'][$file_key])) {
                        $files_array[$key][] = [
                            'name'     => $file['name'][$file_key],
                            'type'     => $file['type'][$file_key],
                            'tmp_name' => $file['tmp_name'][$file_key],
                            'error'    => $file['error'][$file_key],
                            'size'     => $file['size'][$file_key]
                        ];
                    }
                }
            } else {
                $files_array = $files;
            }
        }

        return $files_array;
    }

    /**
     * Writes a file to the uploads directory from the given set files.
     *
     * @param  array     $file        An array of file information
     * @param  int       $permissions The permission value in octets, null to default to user permissions
     * @param  bool      $overwrite   Whether or not to overwrite the file if it already exists
     * @param  bool      $hash_name   Use the SHA-256 sum of the file, as file's name
     * @throws Exception If the file can't be writed
     * @return string    The path to the uploaded file
     */
    public function saveFile($file, $permissions = 0644, $overwrite = false, $hash_name = false)
    {
        if (!empty($file['tmp_name'])) {
            // Check if exits the upload directory, If not exists then create it
            if (!is_dir($this->upload_dir)) {
                try {
                    mkdir($this->upload_dir);
                } catch (Exception $e) {
                    throw new Exception('Failed to create the uploads directory');
                }
            }

            // Validate the file size
            if ($file['size'] > $this->file_size) {
                throw new Exception('The uploaded file exceeds the ' . $this->file_size . ' limit size');
            }

            // Build full upload path
            if ($hash_name) {
                $file_extension = $this->getExtension($file['name']);
                $file_hash      = $this->hash($file['tmp_name']);
                $path           = $this->upload_dir . $file_hash . $file_extension;
            } else {
                $path = $this->upload_dir . $file['name'];
            }

            // Write the file to the upload directory
            if (!file_exists($path) || ($overwrite && file_exists($path))) {
                // Move and chmod the file to the upload directory
                $transfer    = move_uploaded_file($file['tmp_name'], $path);
                $permissions = chmod($path, $permissions);

                return $path;
            } else {
                throw new Exception('Another file with the same name exists in the uploads directory');
            }
        } else {
            throw new Exception('The given file is invalid');
        }
    }

    /**
     * Get the maximum upload size.
     *
     * @return int The maximum upload size in bytes
     */
    public function getMaxSize()
    {
        return $this->file_size;
    }

    /**
     * Set the maximum upload size.
     *
     * @param  int       $max_size The maximum upload size in bytes
     * @throws Exception When an invalid maximum size is given
     */
    public function setMaxSize($max_size)
    {
        if (is_int($max_size) && !empty($max_size)) {
            $this->file_size = $max_size;
        } else {
            throw new Exception('Invalid maximum size, Maximum size must be a non-zero integer value');
        }
    }

    /**
     * Get the upload directory.
     *
     * @return string The upload directory
     */
    public function getUploadDir()
    {
        return $this->upload_dir;
    }

    /**
     * Set the upload directory.
     *
     * @param  string    $dir The new upload directory
     * @throws Exception When an invalid maximum size is given
     */
    public function setUploadDir($dir)
    {
        $this->upload_dir = $dir;
    }

    /**
     * Calculates the hash sum of a file.
     *
     * @param  string    $file The full path of the file
     * @throws Exception If the file not exits
     * @return string    The SHA-256 hash of the file
     */
    public function hash($file)
    {
        if (file_exists($file)) {
            return hash_file('sha256', $file);
        } else {
            throw new Exception('The given file not exists or has been moved');
        }
    }

    /**
     * Get the extension of a file.
     *
     * @param  string $file_name The name of the file
     * @return string The file extension
     */
    private function getExtension($file_name)
    {
        return '.' . explode('.', $file_name, 2)[1];
    }
}
