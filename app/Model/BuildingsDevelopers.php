<?php
namespace App\Model;

class BuildingsDevelopers extends DbBase {

    protected $table = 'buildings_developers';

    protected $timestamps = true;

    protected $softDeletes = true;

    protected $createEntity = true;
    
    protected $fillable = [
        'name' => 'required|string',
        'logo_url' => 'nullable|string',
        'entity_id' => 'nullable|integer',
    ];
        /**
     * Called automatically from the constructor
     */
    public function declare()
    {
        // ...
    }
}