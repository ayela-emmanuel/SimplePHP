<?php
namespace App\Models\Data;

class ApiResponseModel{
    public function __construct(
        public bool $Result = false,
        public string $Message = "",
        public ?object $Data = null
        ){}
}
