<?php

namespace Internal\Attributes;

#[\Attribute]
class RequestModelAttribute
{
    public string $model;
    
    public function __construct(string $model = "")
    {
        if($this->validateClassName($model)){
            $this->model = $model;
        }
    }

    public function validateClassName(string $className): bool
    {
        // Check if the class exists
        if (!class_exists($className)) {
            throw new \InvalidArgumentException("Class '{$className}' does not exist.");
        }
        return true;
    }
}
