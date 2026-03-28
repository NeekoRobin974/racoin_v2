<?php

namespace tests;

use PHPUnit\Framework\TestCase;
use App\Model\Annonce;

class AnnonceTest extends TestCase {
    public function testAnnonceTableGood() {
        $annonce = new Annonce();
        
        $this->assertEquals('annonce', $annonce->getTable());
        $this->assertEquals('id_annonce', $annonce->getKeyName());
    }
}
