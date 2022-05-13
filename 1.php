<?php

namespace Manager;

class User extends \Gateway\User
{
    /**
     * Добавляет пользователей в базу данных.
     * @param $users
     * @return array
     */
    public function addUsers($users): array
    {
        $ids = [];
        self::$instance->beginTransaction();
        foreach ($users as $user) {
            try {
                self::$instance->add($user['name'], $user['lastName'], $user['age']);
                self::$instance->lastInsertId();
            } catch (Exception $e) {
                self::$instance->rollBack();
                return [];
            }
        }
        self::$instance->commit();
        return $ids;
    }
}