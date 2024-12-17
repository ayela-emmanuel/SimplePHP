<?php

namespace Internal\Attributes;

#[\Attribute]
class AutoDocAttribute
{
    public string $comment;
    
    public function __construct(string $comment = "")
    {
        $this->comment = $comment;
    }
}
