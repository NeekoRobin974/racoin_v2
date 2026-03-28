<?php

namespace tests;

use PHPUnit\Framework\TestCase;
use App\Model\Annonceur;

class AnnonceurTest extends TestCase {
    public function testAnnonceurTableGood() {
        $annonceur = new Annonceur();
        
        $this->assertEquals('annonceur', $annonceur->getTable());
        $this->assertEquals('id_annonceur', $annonceur->getKeyName());
        $this->assertFalse($annonceur->timestamps);
    }
}
