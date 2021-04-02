<?php
declare(strict_types=1);

namespace core\service\user;

use core\base\BaseService;
use think\exception\ValidateException;

class UserAddressService extends BaseService
{

    protected string $table = 'user_address';
    protected string $tableUniqueKey = 'address_id';

    protected function onBeforeExecute()
    {
        if (!($this->formData['user_id'] = $this->userId)) {
            throw new ValidateException('用户ID错误');
        }
        if ($this->formData['is_default'] ?? 0) $this->clearDefault();
    }


    // 获取列表
    public function getList(array $condition, int $page = 1, int $size = 10): ?array
    {
        $condition = array_merge($condition, ['ua.is_delete' => 0]);
        $field = 'ua.*, p.region_name as province_name, c.region_name as city_name';
        $field .= ', d.region_name as district_name, s.region_name as street_name';
        $instance = $this->db()->alias('ua')->where($condition)->field($field)
            ->leftJoin('region p', 'p.region_code = ua.province')
            ->leftJoin('region c', 'c.region_code = ua.city')
            ->leftJoin('region d', 'd.region_code = ua.district')
            ->leftJoin('region s', 's.region_code = ua.street');
        if ($page === 1 && $size === 1) return $instance->find();
        return $instance->order('ua.id', 'desc')->select()->toArray();
    }

    // 获取
    public function getByIdOrDefault($addressId = ''): ?array
    {
        $condition = [];
        $condition['ua.user_id'] = $this->getUserId();
        if ($addressId) {
            $condition['ua.address_id'] = $addressId;
        } else {
            $condition['ua.is_default'] = 1;
        }
        if ($addressId) $condition['ua.address_id'] = $addressId;
        $info = $this->getList($condition, 1, 1);
        if (null !== $info) $info['full_address'] = $this->getFullAddressField($info);
        return $info;
    }


    // 清楚默认地址
    public function clearDefault(): self
    {
        $condition = [];
        $condition['is_default'] = 1;
        $condition['user_id'] = $this->userId;
        $this->dbQuery()->where($condition)->update(['is_default' => 0]);
        return $this;
    }

    // 全部地址
    public static function getFullAddressField($addressInfo, $delimiter = ''): string
    {
        $fullAddress = [];
        $fullAddress[] = $addressInfo['province_name'];
        $fullAddress[] = $addressInfo['city_name'];
        $fullAddress[] = $addressInfo['district_name'];
        $fullAddress[] = $addressInfo['street_name'];
        $fullAddress[] = $addressInfo['address'];
        $fullAddress[] = $addressInfo['house_number'];
        return implode($delimiter, $fullAddress);
    }
}