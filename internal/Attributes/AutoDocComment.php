<?php

namespace Internal\Attributes;

#[\Attribute]
class AutoDocComment
{
    public string $comment;
    
    public function __construct(string $comment = "")
    {
        $this->comment = $comment;
    }
}
