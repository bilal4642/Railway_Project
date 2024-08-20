<?php
class Database
{
  private $pdo;
  private $stmt;
  private $error;
  private $host = DB_HOST;
  private $dbname = DB_DBNAME;
  private $username = DB_USERNAME;
  private $password = DB_PASSWORD;

  public function __construct(){
    try {
      $this->pdo = new PDO(
        "mysql:host=$this->host;dbname=$this->dbname",
        $this->username,
        $this->password,
        [
          PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
          PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
      }
         catch (PDOException $e) {
      $this->error = $e->getMessage();
      die("DataBase Connection Failed" . $this->error);
      //throw $th;
    }
  }

  public function query($sql)
  {
    $this->stmt = $this->pdo->prepare($sql);
    return $this->stmt;
  }

  public function bind($param, $value, $type = null)
  {
    if (is_null($type)) {
      switch (true) {
        case is_int($value):
          $type = PDO::PARAM_INT;
          break;
        case is_bool($value):
          $type = PDO::PARAM_BOOL;
          break;
        case is_null($value):
          $type = PDO::PARAM_NULL;
          break;
        default:
          $type = PDO::PARAM_STR;
          break;
      }
    }
    $this->stmt->bindParam($param, $value, $type);
  }
  public function execute(){
     return $this->stmt->execute();
  }
  public function fetchAll(){
    $this->execute();
    return $this->stmt->fetchAll();
  }
  public function fetch(){
    $this->execute();
    return $this->stmt->fetch();
  }
  public function rowCount(){
    return $this->stmt->rowCount();
  }
}
