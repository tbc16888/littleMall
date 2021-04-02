<?php
declare(strict_types=1);

namespace tbc\utils;

class Session
{
    public static int $expire = 2592000;
    public static string $sessionId = '';
    public static string $framework = 'think';

    //
    public static function getDbInstance()
    {
        return \think\facade\Db::name('session');
    }

    // 生成或设置 SESSION_ID
    public static function sessionId(string $sessionId = '', string $uuid = '')
    {
        if ($sessionId) {
            // 设置
            $condition = [];
            $condition['session_id'] = $sessionId;
            $sessionInfo = static::getDbInstance()->where($condition)->find();

            $rData = [];
            $rData['expire'] = time();
            $rData['session_id'] = self::$sessionId = $sessionId;
            $rData['uuid'] = $uuid;
            if (null === $sessionInfo) return static::getDbInstance()->insert($rData);
            if ($sessionInfo['expire'] < time() - self::$expire) $rData['data'] = '';
            return static::getDbInstance()->where($condition)->limit(1)->update($rData);
        } else {
            if (self::$sessionId) return self::$sessionId;
            // 生成
            $rData = [];
            $rData['expire'] = time();
            $rData['uuid'] = $uuid;
            $rData['session_id'] = self::$sessionId = md5(microtime(true) . session_create_id());
            static::getDbInstance()->insert($rData);
            return self::$sessionId;
        }
    }

    // 初始化
    public static function init(array $config = [])
    {
        self::start($config['id'] ?? '', $config['uuid'] ?? '');
    }

    // 启动 session
    public static function start(string $sessionId = '', string $uuid = '')
    {
        if ($uuid) self::getDbInstance()->where('uuid', $uuid)->delete();
        self::sessionId($sessionId, $uuid);
    }

    // 删除 session
    public static function delete($name)
    {
        self::set($name, null);
    }

    // 清除 session
    public static function clear(): int
    {
        return self::getDbInstance()->where('session_id', self::$sessionId)->delete();
    }

    // 设置值
    public static function set(string $name, $value): bool
    {

        if (self::$sessionId == '') self::start();
        $sessionInfo = self::getDbInstance()->where('session_id', self::$sessionId)
            ->where('expire', '>', time() - self::$expire)->find();
        if (null === $sessionInfo) return false;
        $data = json_decode($sessionInfo['data'], true);
        if (null === $value) unset($data[$name]); else $data[$name] = $value;
        $rData = [];
        $rData['data'] = json_encode($data);
        $rData['expire'] = time();
        self::getDbInstance()->where('session_id', self::$sessionId)->limit(1)->update($rData);
        return true;
    }

    // 获取值
    public static function get($name = ''): ?string
    {
        if (self::$sessionId === '') return null;
        $sessionInfo = self::getDbInstance()->where('session_id', self::$sessionId)
            ->where('expire', '>', time() - self::$expire)->find();
        if (null === $sessionInfo) return null;
        $data = json_decode($sessionInfo['data'], true);
        return $data[$name] ?? null;
    }
}