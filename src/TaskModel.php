<?php
$connection_db = include '../src/connection.php';

class TaskModel
{
    /**
     * @var $connection PDO
     */
    public static $connection;

    public static function setDb(PDO $connection)
    {
        self::$connection = $connection;
    }

    public function __construct()
    {
    }

    /**
     * @return array
     */
    public function getTasks( $sort , $order, $offset)
    {
        $qs = "select * from tasks  order by {$sort} {$order}  limit 3 offset {$offset}";
        $st = self::$connection->prepare($qs);
        $st->execute();
        return $st->fetchAll();
    }

    /**
     * @param $status int | boolean
     * @param $id int
     * @return int
     */
    public function updateStatus($status, $id)
    {

        $status = (boolean)$status ? 1 : 0;
        $st = self::$connection->prepare("update tasks set done=? where id=?");
        $st->execute([$status, $id]);
        return $st->rowCount();
    }

    /**
     * @param $text string
     * @param $id int
     * @return int
     */
    public function updateText($text, $id)
    {
        $query_string = "update tasks set edited = if (text =? and edited = 0, 0, 1), text =?  where id =?";
        $st = self::$connection->prepare($query_string);
        $st->execute([$text, $text, (int)$id]);
        $st = self::$connection->prepare('select * from tasks where id=?');
        $st->execute([$id]);
        return $st->fetch();
    }

    public function find($value, $column = 'id')
    {
        if (!in_array($column, ['id', 'email', 'name'])) return null;
        $q_string = "select * from tasks where {$column}=?";
        $st = self::$connection->prepare($q_string);
        $st->execute([$value]);
        return $st->fetch();
    }

    public function create($task)
    {
        $query_string = "insert into tasks (name, email,text) values (:name,:email,:text)";
        $st = self::$connection->prepare($query_string);
        $st->execute(['name' => $task->name, 'email' => $task->email, 'text' => htmlentities($task->text)]);
        return self::$connection->lastInsertId();
    }


    public function count()
    {
        $st = self::$connection->query('select count(*) as count from tasks');
        return (int)($st->fetchAll()[0]['count']);
    }

}

TaskModel::setDb($connection_db);