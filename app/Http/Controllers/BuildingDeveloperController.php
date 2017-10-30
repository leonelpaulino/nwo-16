<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;
use App\Model\BuildingsDevelopers;

class BuildingDeveloperController extends BaseController
{
    protected $buildingsDevelopersModel;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(BuildingsDevelopers $buildingsDevelopers)
    {
        $this->buildingsDevelopersModel = $buildingsDevelopers;
        //
    }

    public function index() {
        return 'Building developers index';
    }

    public function store() {
        return 'Creating Building developers';
    }

    public function update($id) {
        return 'Updating Building developers';
    }

    public function delete($id) {
        return 'deleting Building developers';
    }

}