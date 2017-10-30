<?php
namespace App\Model;

class Buildings extends DbBase {

    protected $table = 'buildings';

    protected $entityType = 'buildings';

    protected $timestamps = true;

    protected $softDeletes = true;

    protected $fillable = [
        'location_id' => 'required|exists:location id',
        'buildings_developers_id' => 'required|exists:buildings_developers id',
        'operating_region_id' => 'nullable|integer',
        'name' => 'required|string',
        'status' => 'required|integer',
        'logo_url' => 'nullable|string',
        'entity_id' => 'nullable|integer'
    ];

    public function declare()
    {
        // ...
    }
}