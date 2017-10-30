<?php
namespace App\Model;

class Locations extends DbBase {

    protected $table = 'locations';

    protected $timestamps = true;

    protected $softDeletes = true;

    protected $createEntity = true;
    
    protected $fillable = [
        'street_address' => 'required|string',
        'address_line_two' => 'nullable|string',
        'city' => 'required|string',
        'postal_code' => 'required|string',
        'state' => 'required|string',
        'country' => 'required|string',
        'google_places_id' => 'required|integer',
        'coordinates' => 'nullable|string',
        'operating_region_id' => 'nullable|integer',
    ];
        /**
     * Called automatically from the constructor
     */
    public function declare()
    {
        // ...
    }
}