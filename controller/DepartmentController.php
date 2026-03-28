<?php

namespace App\Controller;

use App\Model\Departement;

class DepartmentController {

    protected $departments = [];

    public function getAllDepartments() {
        return Departement::orderBy('nom_departement')->get()->toArray();
    }
}