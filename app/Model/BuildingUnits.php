<?php
namespace App\Model;

class BuildingUnits extends DbBase {

    protected $table = 'buildings_units';

    protected $timestamps = true;

    protected $softDeletes = true;

    protected $createEntity = true;
    
    protected $fillable = [
        'buildings_units' => 'required|exists:buildings id',
        'unit_number' => 'required|integer',
        'entity_id' => 'nullable|integer',
        'bed' => 'required|integer',
        'baths' => 'required|integer',
        'unit_type' => 'required|integer'
    ];
        /**
     * Called automatically from the constructor
     */
    public function declare()
    {
        // ...
    }
}