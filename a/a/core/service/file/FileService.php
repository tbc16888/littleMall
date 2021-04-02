<?php
declare(strict_types=1);

namespace core\service\file;

use core\base\BaseService;

class FileService extends BaseService
{
    protected string $table = 'files';
    protected string $tableUniqueKey = 'file_id';

    // 通过Mime获取文件类型
    public static function getFileTypeByMime(string $mime): string
    {
        if (strpos($mime, 'image') !== false) return 'image';
        if (strpos($mime, 'zip') !== false) return 'zip';
        if (strpos($mime, 'x-7z-compressed') !== false) return 'zip';
        if (strpos($mime, 'video') !== false) return 'video';
        return '';
    }

    public static function getCover(array $file): string
    {
        if ($file['file_type'] === 'video') {
            return 'https://demo.careyshop.cn/admin/image/storage/video.png';
        }
        if ($file['file_type'] === 'directory') {
            return 'https://qzonestyle.gtimg.cn/qz-proj/wy-pc-v3/static/img/svg/doctype/icon-file-l.svg';
        }
        return $file['file_url'];
    }

    // 移动到指定目录
    public function moveToDirectory($fileId, $directoryId): int
    {
        return $this->db()->whereIn('file_id', $fileId)
            ->where('file_id', '<>', $directoryId)
            ->update(['directory' => $directoryId]);
    }

    // 目录树
    public function getDirectoryTree($parentFileId = ''): array
    {
        $condition = [];
        $condition['file_type'] = 'directory';
        $condition['directory'] = $parentFileId;
        $directoryList = $this->dbQuery($condition)->select()->toArray();
        foreach ($directoryList as &$file) {
            $file['value'] = $file['file_id'];
            $file['label'] = $file['file_title'];
            $children = $this->getDirectoryTree($file['value']);
            if (count($children)) $file['children'] = $children;
        }
        return $directoryList;
    }
}