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
        // Fetch default encryption key
        $upload_dir = \Configure::get('Upload.upload_dir');

        if (!empty($upload_dir)) {
            $this->upload_dir = $upload_dir;
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
            if (is_array($file)) {
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
                if (!empty($file['name'][$key]) && !empty($file['tmp_name'][$key])) {
                    $files_array[] = [
                        'name'     => $file['name'][$key],
                        'type'     => $file['type'][$key],
                        'tmp_name' => $file['tmp_name'][$key],
                        'error'    => $file['error'][$key],
                        'size'     => $file['size'][$key]
                    ];
                }
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
     * @return string    The path to the uploaded file
     * @throws Exception If the file can't be writed
     */
    public function saveFile($file, $permissions = 0644, $overwrite = false, $hash_name = false)
    {
        // Full path to the upload directory
        $path = ROOTWEBDIR.$this->upload_dir;

        // Check if exits the upload directory, If not exists then create it
        if (!is_dir($path)) {
            try {
                mkdir($path);
            } catch (Exception $e) {
                throw new \Exception('Failed to create the uploads directory');
            }
        }

        // Validate the file size
        if ($file['size'] > $this->file_size) {
            throw new \Exception('The uploaded file exceeds the '.$this->file_size.' limit size');
        }

        // Write the file to the upload directory
        if (!file_exists($path.$file['name']) || ($overwrite && file_exists($path.$file['name']))) {
            if ($hash_name) {
                $file_extension = $this->getExtension($file['name']);
                $file_hash      = $this->hash($file['tmp_name']);
                $new_path       = $path.$file_hash.$file_extension;
            } else {
                $new_path = $path.$file['name'];
            }

            // Move and chmod the file to the upload directory
            $transfer    = move_uploaded_file($file['tmp_name'], $new_path);
            $permissions = chmod($new_path, $permissions);

            return $new_path;
        } else {
            throw new \Exception('Another file with the same name exists in the uploads directory');
        }
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
            throw new \Exception('Invalid maximum size, Maximum size must be a non-zero integer value');
        }
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
     * @return string    The SHA-256 hash of the file
     * @throws Exception If the file not exits
     */
    public function hash($file)
    {
        if (file_exists($file)) {
            return hash_file('sha256', $file);
        } else {
            throw new \Exception('The given file not exists or has been moved');
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
        return '.'.explode('.', $file_name, 2)[1];
    }
}
