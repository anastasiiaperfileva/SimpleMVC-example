<?php
namespace application\models;

use ItForFree\SimpleMVC\MVC\Model;
/**
 * Класс для обработки пользователей
 */
class UserModel extends Model
{
    // Свойства
    /**
    * @var string логин пользователя
    */
    public $login = null;
    
    public ?int $id = null;

    /**
    * @var string пароль пользователя
    */
    public $pass = null;
    
    /**
    * @var string роль пользователя
    */
    public $role = null;
    
    public $email = null; 
    
    public $timestamp = null;

    public $lastEdited = null;
    
    /**
     * @var string Критерий сортировки строк таблицы
     */
    public string $orderBy = "login ASC";
    
    /**
     *  @var string название таблицы
     */
    public string $tableName = 'users';
    
    public $salt = null;
    



    public function insert()
    {
        $sql = "INSERT INTO $this->tableName (timestamp, login, salt, pass, role, email, lastEdited) VALUES (:timestamp, :login, :salt, :pass, :role, :email, :lastEdited)"; 
        $st = $this->pdo->prepare ( $sql );
        $st->bindValue( ":timestamp", (new \DateTime('NOW'))->format('Y-m-d H:i:s'), \PDO::PARAM_STR);
        $st->bindValue( ":login", $this->login, \PDO::PARAM_STR );
        
        $st->bindValue( ":lastEdited", $currentDateTime, \PDO::PARAM_STR);
        $st->bindValue( ":login", $this->login, \PDO::PARAM_STR );

        //Хеширование пароля
        $this->salt = rand(0,1000000);
        $st->bindValue( ":salt", $this->salt, \PDO::PARAM_STR );
//        \DebugPrinter::debug($this->salt);
        
        $this->pass .= $this->salt;
        $hashPass = password_hash($this->pass, PASSWORD_BCRYPT);
//        \DebugPrinter::debug($hashPass);
        $st->bindValue( ":pass", $hashPass, \PDO::PARAM_STR );
        
        $st->bindValue( ":role", $this->role, \PDO::PARAM_STR );
        $st->bindValue( ":email", $this->email, \PDO::PARAM_STR );
        $st->execute();
        $this->id = $this->pdo->lastInsertId();
    }
    
    public function update()
    {
        $sql = "UPDATE $this->tableName SET timestamp=:timestamp, login=:login, role=:role,pass=:pass,salt=:salt,email=:email, lastEdited=:lastEdited  WHERE id = :id";  
        $st = $this->pdo->prepare ( $sql );
        
        $st = $this->pdo->prepare($sql);
        $st->bindValue(":timestamp", (new \DateTime('NOW'))->format('Y-m-d H:i:s'), \PDO::PARAM_STMT);
        $st->bindValue(":login", $this->login, \PDO::PARAM_STR);
        $st->bindValue(":role", $this->role, \PDO::PARAM_STR); 
        $st->bindValue(":email", $this->email, \PDO::PARAM_STR);
        $st->bindValue(":pass", $this->pass, \PDO::PARAM_STR);
        $st->bindValue(":salt", $this->salt, \PDO::PARAM_STR);

        if ($this->lastEdited) {
            $st->bindValue(":lastEdited", $this->lastEdited, \PDO::PARAM_STR);
        } else {
            $st->bindValue(":lastEdited", (new \DateTime('NOW'))->format('Y-m-d H:i:s'), \PDO::PARAM_STR);
        }
        $st->bindValue(":id", $this->id, \PDO::PARAM_INT);
        $st->execute(); 
    }

    public function updateLastEdited($id, $dateTime = null){
        if (!$dateTime){
             $dateTime = (new \DateTime('NOW'))->format('Y-m-d H:i:s');
        }
         $sql = "UPDATE $this->tableName SET lastEdited = :lastEdited WHERE id = :id";
        $st = $this->pdo->prepare($sql);
        $st->bindValue(":lastEdited", $dateTime, \PDO::PARAM_STR);
        $st->bindValue(":id", $id, \PDO::PARAM_INT);
        $st->execute();
    }
    
    /**
     * Вернёт id пользователя
     * 
     * @return ?int
     */
    public function getId()
    {
        if ($this->userName !== 'guest'){
            $sql = "SELECT id FROM users where login = :userName";
            $st = $this->pdo->prepare($sql); 
            $st -> bindValue( ":userName", $this->userName, \PDO::PARAM_STR );
            $st -> execute();
            $row = $st->fetch();
            return $row['id']; 
        } else  {
            return null;
        }  
    }
    
    /**
     * Проверка логина и пароля пользователя.
     */
    public function getAuthData($login): ?array {
	$sql = "SELECT salt, pass FROM users WHERE login = :login";
	$st = $this->pdo->prepare($sql);
	$st->bindValue(":login", $login, \PDO::PARAM_STR);
	$st->execute();
	$authData = $st->fetch();
	return $authData ? $authData : null;
    }
    
    /**
     * Проверяем активность пользователя.
     */
    public function getRole($login): array {
	$sql = "SELECT role FROM users WHERE login = :login";
	$st = $this->pdo->prepare($sql);
	$st->bindValue(":login", $login, \PDO::PARAM_STR);
	$st->execute();	
	return $st->fetch();
    }
    public function rules()
    {
        return [
            ['login', 'required', 'message' => 'Введите логин'],
            ['email', 'required', 'message' => 'Введите email'],
            ['email', 'email', 'message' => 'Введите корректный email'],
            ['pass', 'safe'], // Пароль необязательный
            ['role', 'required', 'message' => 'Выберите роль']
        ];
    }

}