<?php
namespace Common\Model;
use Think\Log;
/**
* 抽象model
* @date: 2018-1-12 下午2:30:14
* @author: fayou
*/
Abstract class AbstractModel{
    
    public $objectHash='';
    
    Abstract protected function getTableName();
    
    protected function mappingField(){
        return array();
    }
    public static function getById($id){
        return self::findOne("id=".$id);
    }
    
    static public function find($parameters=null){
        $classname = self::getClassName();
        $ref = new \ReflectionClass($classname);
        $instance = $ref->newInstanceWithoutConstructor();
        $table = $instance->getTableName();
        
        if(!$parameters){
            $data = M($table)->find();
        }
        else{
            if(is_string($parameters)){
                $where = $parameters;
            }
            else{
                $where = array();
                foreach ($parameters as $key=>$item){
                    $where[$key] = $item;
                }
            }
            $data = M($table)->where($where)->select();
        }
        if(!$data){
            return false;
        }
        $mapping = $instance->mappingField();
        
        $properties = $ref->getProperties();
        
        $resultArray = array();
        foreach ($data as $poolItem){
            $class = new $classname();
            foreach ($properties as $item){
                $proName = $item->getName();
                if($proName == "objectHash"){
                    continue;
                }
                if(isset($mapping[$proName])){
                    $class->$proName = $poolItem[$mapping[$proName]];
                }
                else{
                    $class->$proName = $poolItem[$proName];
                }
            }
            $class->objectHash = $class->getHash();
            $resultArray[] = $class;
            unset($class);
        }
        return $resultArray;
    }
    
    static public function findOne($parameters=null){
        $classname = self::getClassName();
        $ref = new \ReflectionClass($classname);
        $instance = $ref->newInstanceWithoutConstructor();
        $table = $instance->getTableName();
        
        if(!$parameters){
            $data = M($table)->find();
        }
        else{
            if(is_string($parameters)){
                $where = $parameters;
            }
            else{
                $where = array();
                foreach ($parameters as $key=>$item){
                    $where[$key] = $item;
                }
            }
            $data = M($table)->where($where)->find();
        }
        if(!$data){
            return false;
        }
        $mapping = $instance->mappingField();
        $properties = $ref->getProperties();
        $class = new $classname();
        foreach ($properties as $item){
            $proName = $item->getName();
            if($proName == "objectHash"){
                continue;
            }
            if(isset($mapping[$proName])){
                $class->$proName = $data[$mapping[$proName]];
            }
            else{
                $class->$proName = $data[$proName];
            }
        }
        $class->objectHash = $class->getHash();
        return $class;
    }
    protected static function getClassName(){
        return get_called_class();
    }
    public function save(){
        $newHash = $this->getHash();
        if($newHash == $this->objectHash){
            return true;
        }
        $fields = get_object_vars($this);
        unset($fields['objectHash']);
        $mapping = $this->mappingField();
        if($mapping){
            foreach ($mapping as $key=>$value){
                if(isset($fields[$key])){
                    $middleValue = $fields[$key];
                    $fields[$value] = $middleValue;
                    unset($fields[$key]);
                }
            }
        }
        if(isset($fields['id'])){
            return M($this->getTableName())->save($fields);
        }
        else{
            return M($this->getTableName())->add($fields);
        }
    }
    protected function getHash(){
        $array = (array)$this;
        unset($array['objectHash']);
        $value = serialize($array);
        return md5($value);
    }
}
