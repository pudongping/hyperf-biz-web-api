<?php
/**
 * 文件系统
 *
 * Created by PhpStorm
 * User: Alex
 * Date: 2022-12-12 15:37
 * E-mail: <276558492@qq.com>
 */
declare(strict_types=1);

namespace App\Helper;

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\Filesystem\FilesystemFactory;
use Hyperf\Stringable\Str;

class FileSystemHelper
{

    public const STORAGE_LOCAL = 'local';
    public const STORAGE_FTP = 'ftp';
    public const STORAGE_MEMORY = 'memory';
    public const STORAGE_S3 = 's3';
    public const STORAGE_MINIO = 'minio';
    public const STORAGE_OSS = 'oss';
    public const STORAGE_QINIU = 'qiniu';
    public const STORAGE_COS = 'cos';


    public function __construct(
        protected RequestInterface  $request,
        protected FilesystemFactory $filesystemFactory
    ) {
    }

    /**
     * 上传文件
     * 注意：不可上传同文件路径同文件名称文件
     *
     * @param string $fileType 文件类型，方便上传者自行归类
     * @param string $folder 文件上传文件夹
     * @param string $storage 上传使用的驱动
     * @param bool $isRandomFileName 是否开启随机生成文件名（为 false 时，则使用原始文件名）
     * @param string $formDataKey 上传时，form-data 表单中的字段 key 名称
     * @return string 文件网址相对路径
     */
    public function upload(
        string $fileType = 'images',
        string $folder = '/uploader/',
        string $storage = self::STORAGE_OSS,
        bool   $isRandomFileName = true,
        string $formDataKey = 'file'
    ): string {
        // 上传过程
        $file = $this->request->file($formDataKey);
        $stream = fopen($file->getRealPath(), 'r+');
        $suffix = $file->getExtension();  // 文件后缀

        $fileName = $file->getClientFilename();
        if ($isRandomFileName) {
            // eg: "20221210182125-vGEtfif8QhlM3Tey.jpg"
            $fileName = date('YmdHis') . '-' . Str::random() . '.' . $suffix;
        }

        $location = $folder . $fileType . '/' . $fileName;  // eg：`/uploader/images/20221210182125-vGEtfif8QhlM3Tey.jpg`
        $this->writeStream($location, $stream, [], $storage);

        if (is_resource($stream)) {
            fclose($stream);
        }

        return $location;
    }

    /**
     * @param string $location eg: `both of "/uploader/images/20221210182125vGEtfif8QhlM3Tey.jpg" or "uploader/images/20221210182125vGEtfif8QhlM3Tey.jpg" is ok`
     * @param string $storage
     * @return bool
     * @throws \League\Flysystem\FilesystemException
     */
    public function fileExists(string $location, string $storage = self::STORAGE_OSS): bool
    {
        return $this->filesystemFactory->get($storage)->fileExists($location);
    }

    public function write(string $location, string $contents, array $config = [], string $storage = self::STORAGE_OSS): void
    {
        $this->filesystemFactory->get($storage)->write($location, $contents, $config);
    }

    public function writeStream(string $location, $contents, array $config = [], string $storage = self::STORAGE_OSS): void
    {
        $this->filesystemFactory->get($storage)->writeStream($location, $contents, $config);
    }

    public function read(string $location, string $storage = self::STORAGE_OSS): string
    {
        return $this->filesystemFactory->get($storage)->read($location);
    }

    public function readStream(string $location, string $storage = self::STORAGE_OSS): mixed
    {
        return $this->filesystemFactory->get($storage)->readStream($location);
    }

    /**
     * @param string $location eg: `both of "uploader/file/202212121225507AByOduPGDGsEs3q.jpg" or "/uploader/file/202212121225507AByOduPGDGsEs3q.jpg" is ok`
     * @param string $storage
     * @return void
     * @throws \League\Flysystem\FilesystemException
     */
    public function delete(string $location, string $storage = self::STORAGE_OSS): void
    {
        $this->filesystemFactory->get($storage)->delete($location);
    }

    /**
     * @param string $location eg: `both of "/uploader/images" or "uploader/images" is ok`
     * @param string $storage
     * @return void
     * @throws \League\Flysystem\FilesystemException
     */
    public function deleteDirectory(string $location, string $storage = self::STORAGE_OSS): void
    {
        $this->filesystemFactory->get($storage)->deleteDirectory($location);
    }

    public function createDirectory(string $location, array $config = [], string $storage = self::STORAGE_OSS): void
    {
        $this->filesystemFactory->get($storage)->createDirectory($location, $config);
    }

    /**
     * @param string $location eg: `both of "/uploader/images" or "uploader/images" is ok`
     * @param bool $deep
     * @param string $storage
     * @return \League\Flysystem\DirectoryListing
     * @throws \League\Flysystem\FilesystemException
     */
    public function listContents(string $location, bool $deep = false, string $storage = self::STORAGE_OSS): \League\Flysystem\DirectoryListing
    {
        return $this->filesystemFactory->get($storage)->listContents($location, $deep);
    }

    public function move(string $source, string $destination, array $config = [], string $storage = self::STORAGE_OSS): void
    {
        $this->filesystemFactory->get($storage)->move($source, $destination, $config);
    }

    /**
     * @param string $source eg: "/uploader/images/20221210182125vGEtfif8QhlM3Tey.jpg"
     * @param string $destination eg: "/demo/image/foo-bar.jpg"
     * @param array $config
     * @param string $storage
     * @return void
     * @throws \League\Flysystem\FilesystemException
     */
    public function copy(string $source, string $destination, array $config = [], string $storage = self::STORAGE_OSS): void
    {
        $this->filesystemFactory->get($storage)->copy($source, $destination, $config);
    }

    public function lastModified(string $path, string $storage = self::STORAGE_OSS): int
    {
        return $this->filesystemFactory->get($storage)->lastModified($path);
    }

    /**
     * @param string $path eg: `/uploader/images/20221210182125vGEtfif8QhlM3Tey.jpg`
     * @param string $storage
     * @return int eg: 593919
     * @throws \League\Flysystem\FilesystemException
     */
    public function fileSize(string $path, string $storage = self::STORAGE_OSS): int
    {
        return $this->filesystemFactory->get($storage)->fileSize($path);
    }

    /**
     * @param string $path eg: `/uploader/images/20221210182125vGEtfif8QhlM3Tey.jpg`
     * @param string $storage
     * @return string eg: "image/jpeg"
     * @throws \League\Flysystem\FilesystemException
     */
    public function mimeType(string $path, string $storage = self::STORAGE_OSS): string
    {
        return $this->filesystemFactory->get($storage)->mimeType($path);
    }

}