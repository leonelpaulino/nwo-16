<?php
namespace App\Model;

class BuildingsMetadata extends DbBase {

    protected $table = 'buildings_metadata';

    protected $timestamps = true;

    protected $softDeletes = true;

    protected $createEntity = true;
    
    protected $fillable = [
        'buildings' => 'required|exists:buildings id',
        'key' => 'required|string',
        'value' => 'required|string'
    ];
        /**
     * Called automatically from the constructor
     */
    public function declare()
    {
        // ...
    }
}