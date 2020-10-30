<?php

class Database{
    private $pdo_obj;

    private $DB_HOST = "HOST";
    private $DB_USERNAME = "USERNAME"
    private $DB_PASSWORD = "PASSWORD";
    private $DB_DATABASE = DATABASE";

    public function __construct(){
        try{
            $options = [
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ];

            $dsn = "mysql:host={$this->DB_HOST};dbname={$this->DB_DATABASE}";
            $this->pdo_obj = new PDO($dsn, $this->DB_USERNAME, $this->DB_PASSWORD, $options);

        }
        catch(Exception $e){
            error_log($e->getMessage());
        }
    }   

    public function insert($model): ?int{
        try{
            $insert_id = null;

            $this->pdo_obj->beginTransaction();

            $statement = $this->pdo_obj->prepare($model::QUERY);

            foreach($model as $property => $value){
                $statement->bindValue(":{$property}", $value);
            }

            if($statement->execute()){
                $insert_id = $this->pdo_obj->lastInsertId();
                $this->pdo_obj->commit();
            }

            return $insert_id;
        }
        catch(Exception $e){
            $this->pdo_obj->rollback();
            error_log($e->getMessage()."in query: ".$model::QUERY);
            return null;
        }
    }

    public function getWikiRecordId($table, $wiki_id): ?int{
        try{
            $statement = $this->pdo_obj->prepare("SELECT id FROM {$table} WHERE wiki_id = :wiki_id"); 
            $statement->bindValue(":wiki_id", $wiki_id);               
            $result = $statement->execute();
            
            return $result == false ? null : $statement->fetch()['id'];
        }
        catch(Exception $e){
            error_log($e->getMessage());
            return null;                
        }
    }
}