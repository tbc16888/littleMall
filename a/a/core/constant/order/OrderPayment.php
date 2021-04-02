<?php
declare(strict_types=1);

namespace core\constant\order;

class OrderPayment
{
    const WAIT_PAY = 0;
    const OFFLINE_PAY = 1;
    const ALIPAY_APP = 2;
    const WECHAT_MINI_PROGRAM = 3;
}