<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;
use App\Model\Buildings;

class BuildingController extends BaseController
{
    protected $buildingsModel;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(Buildings $Buildings)
    {
        $this->buildingsModel = $Buildings;
        //
    }

    public function index() {
        return 'Building index';
    }

    public function store() {
        return 'Creating building';
    }

    public function update($id) {
        return 'Updating building';
    }

    public function delete($id) {
        return 'deleting building';
    }

}