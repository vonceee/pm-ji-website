<?php

namespace Models;

class PaymentRefundsModel
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    
}