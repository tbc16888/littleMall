<?php
declare(strict_types=1);

namespace core\service\app;

use core\base\BaseService;

class VersionService extends BaseService
{

    protected string $table = 'app_version';
    protected string $tableUniqueKey = 'version_id';

    protected function onBeforeInsert()
    {
        if (!($this->formData['version_number']) ?? '') {
            $lastVersion = $this->getLastVersionNumber($this->formData['platform']);
            if ($lastVersion) $this->formData['version_number'] = $lastVersion + 1;
        }
    }

    // 获取最后的版本号
    public function getLastVersionNumber($platform)
    {
        return $this->dbQuery()->where('platform', $platform)
            ->max('version_number');
    }
}