<?php

namespace Local\Util\Database;

use mysqli;
use mysqli_result;
use RuntimeException;

/**
 * Class Import
 * @package Local\Util\Database
 *
 * @since 12.12.2020
 */
class Import
{
    /**
     * @var mysqli $db Соединение с базой.
     */
    private $db;

    /**
     * @var int $start_time
     */
    private $start_time;

    /**
     * @var string $host
     */
    private $host;

    /**
     * @var string $username
     */
    private $username;

    /**
     * @var string $password
     */
    private $password;

    /**
     * @var string $database
     */
    private $database;

    /**
     * @var integer Максимальное время исполнения.
     */
    private $import_timeout = 180;

    /**
     * Import constructor.
     *
     * @param string $host     Хост.
     * @param string $database База данных.
     * @param string $username Логин.
     * @param string $password Пароль.
     */
    public function __construct(
        string $host,
        string $database,
        string $username,
        string $password
    ) {
        $this->host = $host;
        $this->database = $database;
        $this->username = $username;
        $this->password = $password;

        $this->start_time = time();
    }

    /**
     * Инициализировать подключение к серверу MySql.
     *
     * @return void
     * @throws RuntimeException
     */
    public function init()
    {
        $this->db = new mysqli($this->host, $this->username, $this->password);
        if ($this->db->connect_error) {
            throw new RuntimeException(
                sprintf(
                    'Error connecting MYSQL: %s',
                    $this->db->connect_error
                )
            );
        }
    }

    /**
     * Импортировать файл в базу.
     *
     * @param string   $file Файл.
     * @param int|null $offset Смещение.
     *
     * @return array|string[]
     *
     * @throws RuntimeException
     */
    public function importFile(string $file, $offset = null)
    {
        $handle = fopen($file, "rb");

        if (empty($handle)) {
            throw new RuntimeException('Cannot open database file '.$file);
        }

        $offset = empty($offset) ? 0 : $offset;
        $current_query = '';

        $this->initDatabase();

        $this_lines_count = $loop_iteration = 0;

        while (($line = fgets($handle)) !== false) {
            $loop_iteration++;
            if ($loop_iteration <= $offset) {
                continue;
            }

            $this_lines_count++;

            if (substr($line, 0, 2) == '--' || $line == '' || substr($line, 0, 3) == '/*!') {
                continue; // Skip it if it's a comment
            }

            $current_query .= $line;

            // If it does not have a semicolon at the end, then it's not the end of the query
            if (substr(trim($line), -1, 1) != ';') {
                continue;
            }

            if (!$this->db->query($current_query)) {
                echo 'Error performing query \'<strong>'.$current_query.'\': '.$this->db->error.'<br /><br />';
                $this->db->query("UNLOCK TABLES;");

                return ['status' => 'continue', 'offset' => $loop_iteration];
            }

            $current_query = $line = '';

            //check timeout after every 10 queries executed
            if ($this_lines_count <= 10) {
                continue;
            }

            $this_lines_count = 0;

            if (!$this->isTimedOut()) {
                continue;
            }

            $this->db->query("UNLOCK TABLES;");
            fclose($handle);

            return ['status' => 'continue', 'offset' => $loop_iteration];
        }

        $this->db->query("UNLOCK TABLES;");

        return ['status' => 'completed', 'msg' => 'Imported successfully!'];
    }

    /**
     * Пора делать следующую итерацию?
     *
     * @return boolean
     */
    public function isTimedOut(): bool
    {
        if ((time() - $this->start_time) >= $this->import_timeout) {
            return true;
        }

        return false;
    }

    /**
     * Дропнуть базу.
     *
     * @param string $dbName Название базы.
     *
     * @return void
     * @throws RuntimeException
     */
    public function dropDatabase(string $dbName): void
    {
        $this->query("DROP DATABASE IF EXISTS `".$dbName."`");
    }

    /**
     * Инициализировать базу. Если не существует - создать.
     *
     * @return void
     *
     * @throws RuntimeException
     */
    private function initDatabase(): void
    {
        $result = $this->db->select_db($this->database);

        if (!$result) {
            if (!$this->db->query("CREATE DATABASE IF NOT EXISTS `$this->database`;")) {
                throw new RuntimeException(
                    sprintf(
                        'Couldnt create database: : %s',
                        $this->db->error
                    )
                );
            }

            $this->db->select_db($this->database);

            return;
        }

        throw new RuntimeException(
            sprintf(
                'Error connection to database: %s',
                $this->db->connect_error
            )
        );
    }

    /**
     * Запрос к базе.
     *
     * @param string $query
     *
     * @return bool|mysqli_result
     * @throws RuntimeException
     */
    private function query(string $query)
    {
        $result = $this->db->query($query);

        if (!$result) {
            throw new RuntimeException(
                sprintf(
                    'Error query database: %s',
                    $this->db->error
                )
            );
        }

        return $result;
    }
}