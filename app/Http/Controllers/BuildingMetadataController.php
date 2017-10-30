<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;
use App\Model\BuildingsMetadata;

class BuildingMetadataController extends BaseController
{
    protected $buildingsMetadataModel;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(BuildingsMetadata $buildingsMetadata)
    {
        $this->buildingsMetadataModel = $buildingsMetadata;
        //
    }

    public function index() {
        return 'Building Metadata index';
    }

    public function store() {
        return 'Creating Building Metadata';
    }

    public function update($id) {
        return 'Updating Building Metadata';
    }

    public function delete($id) {
        return 'deleting Building Metadata';
    }

}