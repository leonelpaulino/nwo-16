<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;
use App\Model\BuildingUnits;

class BuildingUnitController extends BaseController
{
    protected $buildingUnitsModel;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(BuildingUnits $buildingUnits)
    {
        $this->buildingUnitsModel = $buildingUnits;
        //
    }

    public function index() {
        return 'Building unit index';
    }

    public function store() {
        return 'Creating Building unit';
    }

    public function update($id) {
        return 'Updating Building unit';
    }

    public function delete($id) {
        return 'deleting Building unit';
    }

}