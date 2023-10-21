<?php

declare(strict_types=1);

namespace Platine\App\Helper;

use Platine\App\Exception\AppUploadException;
use Platine\App\Model\Entity\File;
use Platine\App\Model\Repository\FileRepository;
use Platine\Config\Config;
use Platine\Filesystem\Filesystem;
use Platine\Http\UploadedFile;
use Platine\Upload\File\UploadFileInfo;
use Platine\Upload\Storage\FileSystem as UploadFileSystem;
use Platine\Upload\Upload;
use Platine\Upload\Validator\Rule\Extension;
use Platine\Upload\Validator\Rule\MimeType;
use Platine\Upload\Validator\Rule\Required;
use Platine\Upload\Validator\Rule\Size;
use Platine\Upload\Validator\RuleInterface;

/**
 * @class FileHelper
 * @package Platine\App\Helper
 * @template T
 */
class FileHelper
{
    /**
     * The FileRepository instance
     * @var FileRepository
     */
    protected FileRepository $fileRepository;

    /**
     * The application configuration
     * @var Config<T>
     */
    protected Config $config;

    /**
     * The File system instance
     * @var Filesystem
     */
    protected Filesystem $filesystem;

    /**
     * Create new instance
     * @param FileRepository $fileRepository
     * @param Config<T> $config
     * @param Filesystem $filesystem
     */
    public function __construct(
        FileRepository $fileRepository,
        Config $config,
        Filesystem $filesystem
    ) {
        $this->fileRepository = $fileRepository;
        $this->config = $config;
        $this->filesystem = $filesystem;
    }

    /**
     * Whether the file is uploaded
     * @param string $name
     * @return bool
     */
    public function isUploaded(string $name): bool
    {
        $uploadedFiles = UploadedFile::createFromGlobals();
        $uploadFile = $uploadedFiles[$name] ?? null;

        return $uploadFile instanceof UploadedFile
                && $uploadFile->getClientMediaType() !== null;
    }

    /**
     * Delete the given file
     * @param File $file
     * @param string|null $folder
     * @param bool $useEnterprise whether to use real enterprise
     * @return bool
     */
    public function delete(File $file, ?string $folder = null, bool $useEnterprise = true): bool
    {
        $configPath = $this->getEnterprisePath($this->config->get('platform.data_attachment_path'), $useEnterprise);
        $path = $configPath;
        if ($folder !== null) {
            $path .= DIRECTORY_SEPARATOR . $folder;
        }

        $filepath = sprintf('%s/%s', $path, $file->name);
        $handle = $this->filesystem->get($filepath);
        if ($handle === null) {
            return true; //no need to delete if file does not exist
        }

        $handle->delete();

        return $this->fileRepository->delete($file);
    }

    /**
     * Delete the given upload public image file
     * @param File $file
     * @param bool $useEnterprise whether to use real enterprise
     * @return bool
     */
    public function deleteUploadPublicImage(File $file, bool $useEnterprise = true): bool
    {
        return $this->processDeleteUploadImage($file, 'platform.public_images_path', $useEnterprise);
    }

    /**
     * Delete the given upload image file
     * @param File $file
     * @param bool $useEnterprise whether to use real enterprise
     * @return bool
     */
    public function deleteUploadImage(File $file, bool $useEnterprise = true): bool
    {
        return $this->processDeleteUploadImage($file, 'platform.data_image_path', $useEnterprise);
    }

    /**
     * Whether the given file
     * @param File $file
     * @param string|null $folder
     * @param bool $useEnterprise whether to use real enterprise
     * @return bool
     */
    public function exists(File $file, ?string $folder = null, bool $useEnterprise = true): bool
    {
        $configPath = $this->getEnterprisePath(
            $this->config->get('platform.data_attachment_path'),
            $useEnterprise
        );

        $path = $configPath;
        if ($folder !== null) {
            $path .= DIRECTORY_SEPARATOR . $folder;
        }

        $filepath = sprintf('%s/%s', $path, $file->name);
        $handle = $this->filesystem->get($filepath);

        return $handle !== null && $handle->exists();
    }


    /**
     * Upload an image
     * @param string $name
     * @param string $note
     * @param bool $useEnterprise whether to use real enterprise
     * @return File
     * @throws AppUploadException
     */
    public function uploadImage(string $name, string $note, bool $useEnterprise = true): ?File
    {
        return $this->doUploadImage($name, $note, 'platform.data_image_path', $useEnterprise);
    }

    /**
     * Upload an public image
     * @param string $name
     * @param string $note
     * @param bool $useEnterprise whether to use real enterprise
     * @return File
     * @throws AppUploadException
     */
    public function uploadPublicImage(string $name, string $note, bool $useEnterprise = true): ?File
    {
        return $this->doUploadImage($name, $note, 'platform.public_images_path', $useEnterprise);
    }

    /**
     * Upload an attachment
     * @param string $name
     * @param string $note
     * @param null|string $folder
     * @param bool $useEnterprise whether to use real enterprise
     * @return File
     * @throws AppUploadException
     */
    public function uploadAttachment(
        string $name,
        string $note,
        ?string $folder = null,
        bool $useEnterprise = true
    ): File {
        $configPath = $this->getEnterprisePath($this->config->get('platform.data_attachment_path'), $useEnterprise);
        $path = $configPath;

        if ($folder !== null) {
            $directory = $this->filesystem->directory($configPath);
            $path .= DIRECTORY_SEPARATOR . $folder;
            // Create the folder if it does not exist
            if ($this->filesystem->directory($path)->exists() === false) {
                $directory->create($folder, 0775, true);
            }
        }

        return $this->doUpload(
            $name,
            [
                new Required(),
                new Extension([
                    'png',
                    'gif',
                    'jpg',
                    'jpeg',
                    'csv',
                    'txt',
                    'docx',
                    'doc',
                    'pdf',
                    'xls',
                    'xlsx',
                    'pptx',
                    'ppt',
                    'zip',
                ]),
                new Size('2M'),
            ],
            $path,
            $note
        );
    }

    /**
     * Delete the given upload image file
     * @param File $file
     * @param string $cfgPath
     * @param bool $useEnterprise whether to use real enterprise
     * @return bool
     */
    public function processDeleteUploadImage(File $file, string $cfgPath, bool $useEnterprise = true): bool
    {
        $configPath = $this->getEnterprisePath($this->config->get($cfgPath), $useEnterprise);
        $path = $configPath;

        $filepath = sprintf('%s/%s', $path, $file->name);
        $handle = $this->filesystem->get($filepath);
        if ($handle === null) {
            return true; //no need to delete if file does not exist
        }

        $handle->delete();

        return $this->fileRepository->delete($file);
    }

    /**
     * Upload an image
     * @param string $name
     * @param string $note
     * @param string $path
     * @param bool $useEnterprise whether to use real enterprise
     * @return File
     * @throws AppUploadException
     */
    protected function doUploadImage(string $name, string $note, string $path, bool $useEnterprise = true): ?File
    {
        $configPath = $this->getEnterprisePath($this->config->get($path), $useEnterprise);
        return $this->doUpload(
            $name,
            [
                new Size('1MB'),
                new Extension(['png', 'gif', 'jpg', 'jpeg']),
                new MimeType([
                    'image/png',
                    'image/jpg',
                    'image/gif',
                    'image/jpeg',
                ])
            ],
            $configPath,
            $note
        );
    }

    /**
     *
     * @param string $name
     * @param RuleInterface[] $rules
     * @param string $path
     * @param string $note
     * @return File
     */
    protected function doUpload(string $name, array $rules, string $path, string $note): File
    {
        $uploadedFiles = UploadedFile::createFromGlobals();
        /** @var UploadedFile $uploadFile */
        $uploadFile = $uploadedFiles[$name] ?? [];

        $upload = new Upload(
            $name,
            new UploadFileSystem(
                $path,
                true
            ),
            null,
            [$name => $uploadFile]
        );

        $upload->setFilename(md5(uniqid() . time()));

        $upload->addValidations($rules);

        if (!$upload->isUploaded()) {
            throw new AppUploadException('The file to upload is empty');
        }

        $isUploaded = $upload->process();
        if ($isUploaded === false) {
            $errors = $upload->getErrors();
            throw new AppUploadException($errors[0]);
        }

        /** @var UploadFileInfo $info */
        $info = $upload->getInfo();

        $file = $this->fileRepository->create([
            'name' => $info->getFullName(),
            'real_name' => $uploadFile->getClientFilename(),
            'type' => $info->getMimeType(),
            'size' => $info->getSize(),
            'revision' => 0,
            'note' => $note,
        ]);

        $this->fileRepository->save($file);


        return $file;
    }

    /**
     * Return the path with the enterprise suffix
     * @param string $configPath
     * @param bool $useEnterprise whether to use real enterprise
     * @return string
     */
    public function getEnterprisePath(string $configPath, bool $useEnterprise = true): string
    {
        $enterpriseId = '0';
        if ($useEnterprise) {
            $enterpriseId = '';
        }
        $path = sprintf(
            '%s%s%s',
            $configPath,
            DIRECTORY_SEPARATOR,
            $enterpriseId
        );

        $directory = $this->filesystem->directory($configPath);

        // Create the folder if it does not exist
        if ($this->filesystem->directory($path)->exists() === false) {
            $directory->create($enterpriseId, 0775, true);

            // if it's public path add index file to prevent directory listing
            $this->filesystem->directory($path)->createFile('index.html', 'Access denied');
        }

        return $path;
    }
}
