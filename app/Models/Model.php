<?php


namespace App\Models;


use Exception;
use PDO;

abstract class Model extends PDO
{
    private static $pdo;

    /**
     * Get the table name for the extending class.
     *
     * @return string
     */
    abstract protected static function getTableName(): string;

    /**
     * Connect to the database.
     *
     * @return PDO
     */
    private static function connection(): PDO
    {
        if (!isset(self::$pdo)) {
            $driver = config('DB_ADAPTER');
            $host = config('DB_HOST');
            $port = config('DB_PORT') ?? 3306;
            $dbName = config('DB_NAME');

            self::$pdo = new PDO(
                "$driver:host=$host;port=$port;dbname=$dbName",
                config('DB_USER_NAME'),
                config('DB_PASSWORD')
            );
        }

        return self::$pdo;
    }

    /**
     * Insert data into the specified table and return it's ID.
     *
     * @param array $data
     *
     * @return ?int
     */
    public static function create(array $data): ?int
    {
        // Get the table name from the extending class
        $tableName = static::getTableName();

        // Build the SQL statement
        $columns = implode(', ', array_keys($data));
        $values = implode(', ', array_fill(0, count($data), '?'));
        $sql = "INSERT INTO {$tableName} ($columns) VALUES ($values)";

        // Prepare and execute the statement
        $statement = self::connection()->prepare($sql);

        // Bind values
        $i = 1;
        foreach ($data as $value) {
            $statement->bindValue($i++, $value);
        }

        // Execute
        $success = $statement->execute();

        if ($success) {
            // Return the ID of the last inserted row
            return (int) self::connection()->lastInsertId();
        }

        return null;
    }
}