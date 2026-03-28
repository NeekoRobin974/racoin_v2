<?php

namespace tests;

use PHPUnit\Framework\TestCase;
use App\Model\Departement;

class DepartementTest extends TestCase {
    public function testDepartementTableGood() {
        $departement = new Departement();
        
        $this->assertEquals('departement', $departement->getTable());
        $this->assertEquals('id_departement', $departement->getKeyName());
        $this->assertFalse($departement->timestamps);
    }
}
