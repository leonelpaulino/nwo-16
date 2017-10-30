<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;
use App\Model\Locations;

class LocationController extends BaseController
{
    protected $LocationModel;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(Locations $Location)
    {
        $this->LocationModel = $Location;
        //
    }

    public function index() {
        return 'Locations index';
    }

    public function store() {
        return 'Creating Locations';
    }

    public function update($id) {
        return 'Updating Locations';
    }

    public function delete($id) {
        return 'deleting Locations';
    }

}