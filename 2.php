<?php

namespace Gateway;

use PDO;

class User
{
    const limit = 10;

    /**
     * @var PDO
     */
    protected static $instance = null;

    public function __construct()
    {
        if (is_null(self::$instance)) {
            try {
                $dsn = 'mysql:dbname=db;host=127.0.0.1';
                $user = 'dbuser';
                $password = 'dbpass';
                self::$instance = new PDO($dsn, $user, $password);
            } catch (PDOException $e) {
                throw new Exception ($e->getMessage());
            }
        }

        return self::$instance;
    }

    /**
     * Возвращает список пользователей старше заданного возраста.
     * @param int $ageFrom
     * @return array
     */
    public static function getAgeFrom(int $ageFrom, int $limit = self::limit): array
    {
        if (!$ageFrom) return [];
        $stmt = self::$instance->prepare("
            SELECT `id`, `name`, `lastName`, `from`, `age`, `settings` 
              FROM `Users` 
             WHERE `age` > '{$ageFrom}' 
             LIMIT " . $limit . ";
        ");
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $users = [];
        foreach ($rows as $row) {
            $settings = json_decode($row['settings']);
            $users[] = [
                'id' => $row['id'],
                'name' => $row['name'],
                'lastName' => $row['lastName'],
                'from' => $row['from'],
                'age' => $row['age'],
                'key' => $settings['key']
            ];
        }

        return $users;
    }

    /**
     * Возвращает пользователей по именам.
     * @param array $names
     * @return array
     */
    public static function getByName(array $names): array
    {
        $param = implode(',', array_fill(0, count($params), '?'));
        $stmt = self::$instance->prepare("
            SELECT `id`, `name`, `lastName`, `from`, `age` 
              FROM `Users` 
             WHERE `name` IN ({$param});
        ");
        $stmt->execute($names);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $users = [];
        foreach ($rows as $row) {
            $users[] = [
                'id' => $row['id'],
                'name' => $row['name'],
                'lastName' => $row['lastName'],
                'from' => $row['from'],
                'age' => $row['age']
            ];
        }

        return $users;
    }

    /**
     * Добавляет пользователя в базу данных.
     * @param string $name
     * @param string $lastName
     * @param int $age
     * @return string
     */
    public static function add(string $name, string $lastName, int $age): string
    {
        $sth = self::$instance->prepare("
            INSERT INTO `Users` (`name`, `lastName`, `age`) 
                 VALUES (:name, :age, :lastName)
        ");
        $sth->execute([
            ':name' => $name, 
            ':age' => $age, 
            ':lastName' => $lastName
        ]);

        return self::getInstance()->lastInsertId();
    }
}