<?php
declare(strict_types=1);

namespace core\service\app;

use core\base\BaseService;

class TemplateService extends BaseService
{

    protected string $table = 'app_layout_template';
    protected string $tableUniqueKey = 'template_id';

}