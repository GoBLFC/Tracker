<?php
/**
 * This file is part of the TelegramBot package.
 *
 * (c) Avtandil Kikabidze aka LONGMAN <akalongman@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Longman\TelegramBot;

use Longman\TelegramBot\Exception\TelegramException;

/**
 * Class BotanDB
 */
class BotanDB extends DB
{
    /**
     * Initilize botan shortener table
     */
    public static function initializeBotanDb()
    {
        if (!defined('TB_BOTAN_SHORTENER')) {
            define('TB_BOTAN_SHORTENER', self::$table_prefix . 'botan_shortener');
        }
    }

    /**
     * Select cached shortened URL from the database
     *
     * @param  $user_id
     * @param  $url
     *
     * @throws TelegramException
     *
     * @return bool|string
     */
    public static function selectShortUrl($user_id, $url)
    {
        if (!self::isDbConnected()) {
            return false;
        }

        try {
            $sth = self::$pdo->prepare('SELECT * FROM `' . TB_BOTAN_SHORTENER . '`
                WHERE `user_id` = :user_id AND `url` = :url
                ');

            $sth->bindParam(':user_id', $user_id, \PDO::PARAM_INT);
            $sth->bindParam(':url', $url, \PDO::PARAM_INT);
            $sth->execute();

            $results = $sth->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            throw new TelegramException($e->getMessage());
        }

        return $results;
    }

    /**
     * Insert shortened URL into the database
     *
     * @param  $user_id
     * @param  $url
     * @param  $short_url
     *
     * @throws TelegramException
     *
     * @return bool
     */
    public static function insertShortUrl($user_id, $url, $short_url)
    {
        if (!self::isDbConnected()) {
            return false;
        }

        try {
            $sth = self::$pdo->prepare('INSERT INTO `' . TB_BOTAN_SHORTENER . '`
                (
                `user_id`, `url`, `short_url`, `created_at`
                )
                VALUES (
                :user_id, :url, :short_url, :date
                )
                ');

            $created_at = self::getTimestamp();

            $sth->bindParam(':user_id', $user_id);
            $sth->bindParam(':url', $url);
            $sth->bindParam(':short_url', $short_url);
            $sth->bindParam(':date', $created_at);

            $status = $sth->execute();
        } catch (\Exception $e) {
            throw new TelegramException($e->getMessage());
        }

        return $status;
    }
}
