<?php
declare(strict_types=1);

namespace core\service\basic;

use core\base\BaseService;
use core\exception\BusinessException;

class MobileCodeService extends BaseService
{

    protected string $table = 'validate_mobile_code';
    protected string $tableUniqueKey = 'code_id';

    protected int $mobileCode = 0;

    // 发送验证码
    public function send(string $mobile, string $action, int $code): self
    {
        $condition = [];
        $condition['mobile'] = $mobile;
        $condition['action'] = $action;
        $codeInfo = $this->db()->where($condition)
            ->where('send_time', '>', time() - 10)
            ->order('id', 'desc')->find();
        if (null !== $codeInfo) throw new BusinessException('操作频繁');
        $rData = $condition;
        $rData['code'] = $code;
        $rData['send_time'] = time();
        $rData['expire_time'] = time() + 60 * 10;
        $this->setFormData($rData)->addOrUpdate();
        return $this;
    }

    // 校验验证码
    public function verify(string $mobile, $code, string $action): self
    {
        $condition = [];
        $condition['mobile'] = $mobile;
        $condition['action'] = $action;
        $dbCode = $this->db()->where($condition)
            ->where('expire_time', '>=', time())
            ->order('id', 'desc')->value('code');
        if (null === $dbCode) throw new BusinessException('验证码不存在', 1);
        if ($code != $dbCode) throw new BusinessException('验证码不正确', 1);
        return $this;
    }
}