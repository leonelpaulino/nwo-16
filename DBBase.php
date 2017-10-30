<?php

namespace Helpers\Db;

use Helpers\Traits\Errors;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Helpers\Contracts\Errors as ErrorsContract;

/**
 * DB Querying Tool
 *
 * These tools are used for grabbing information from the database.
 * All additional logic should go into the "Utils" namespace.
 *
 * @author  jbelelieu
 */
abstract class DbBase implements DbContract, ErrorsContract
{
    use Errors;

    protected $id;
    protected $payload;
    protected $connection;
    protected $currentConnection;
    protected $date;
    protected $table;
    protected $inputFilters;
    protected $forceData = [];
    protected $neverFromCache = false;

    protected $withMetaData = false;
    protected $metadataKey = null;
    protected $permittedMetaKeys = [];

    protected $remove;

    /**
     * Non md5 version of the cache key.
     * @var null
     */
    private $plainCacheKey = null;

    /**
     * md5 version of the cache key. What is actually
     * stored in redis.
     * @var null
     */
    protected $cacheKey = null;

    protected $cacheMethods = [
        'get',
        'list',
        'getMetadata',
    ];

    /**
     * These are the columns that
     * are going to be selected when
     * making the query
     *
     * @var array
     */
    protected $columns = ['*'];

    /**
     * What was the source of this data, db or cache?
     * Shows up in payload->data->source
     *
     * @var string
     */
    protected $source = 'db';

    /**
     * Does this model want us to use caching?
     *
     * @var bool
     */
    protected $performCache = true;

    /**
     * List of keys that are permitted for filtering in a list request.
     *
     *
     * @var array
     */
    protected $permittedFilters = [];

    /**
     * Name of the primary ID field on a table.
     *
     * @var string
     */
    protected $idField = 'id';

    /**
     * When inserting or updating, update timestamps?
     *
     * @var bool
     */
    protected $timestamps = false;

    /**
     * @var bool
     */
    protected $softDeletes = false;

    /**
     * When inserting, create an entity record?
     * $entityFields are the fields that will be appended, example
     * first_name, last_name.
     *
     * @var bool
     */
    protected $createEntity = false;

    /**
     *
     */
    protected $entityType = '';
    protected $entityName = ''; // ID or for example, unit number 10C
    protected $entityAdditions = [];

    /**
     * Stores filters requests that are applied at list run time
     * @var
     */
    protected $storeFilters = [];

    protected $fillable = [];


    /**
     * Holds the relations of the model
     *
     * @var array
     */
    protected $relations = [];

    /**
     * DbBase constructor.
     */
    public function __construct()
    {
        $this->payload = new \stdClass();

        $this->connection = app('db')->table($this->table);

        $this->date = new Carbon;

        $this->declare();
    }

    /**
     * @param $method
     * @param $args
     *
     * @return mixed
     */
    public function __call($method, $args)
    {
        // Set teh cache key.
        if (in_array($method, $this->cacheMethods)) {
            $callString = get_class($this) . '-' . $method . '-' . implode(',', array_flatten($args));

            if (! empty($this->storeFilters)) {
                $callString .= '-' . http_build_query($this->storeFilters);
            }

            $this->plainCacheKey = $callString;

            $this->cacheKey = md5($callString);
        }

        // Call the function.
        return call_user_func_array([$this, 'base' . ucfirst($method)], $args);
    }

    private function cacheListKey()
    {
        return md5(get_class($this) . '-list');
    }

    private function cacheGetKey($id)
    {
        return md5(get_class($this) . '-list-' . $id);
    }

    /**
     * For inserts, force a value.
     *
     * @param $key
     * @param $value
     *
     * @return $this
     */
    public function forceData($key, $value)
    {
        $this->forceData[$key] = $value;

        return $this;
    }

    public function neverFromCache()
    {
        $this->neverFromCache = true;

        return $this;
    }

    /**
     * Sets the columns that are going
     * to be selected in the query
     *
     * @param $columns
     *
     * @return $this
     */
    public function setColumns($columns)
    {
        $this->columns = $columns;

        return $this;
    }

    /**
     * @return array
     */
    public function getFillable()
    {
        return $this->fillable;
    }

    /**
     * Set filters to be applied later.
     *
     * @param array $filters
     *
     * @return $this
     */
    public function setFilters(array $filters)
    {
        if (empty($filters)) {
            return $this;
        }

        $this->storeFilters = normalizeFields($filters);

        return $this;
    }

    /**
     * Sets a filter key and its value
     * @param string $key
     * @param $value
     * @return $this
     */
    public function setFilter(string $key, $value)
    {
        $normalized = normalizeFields([$key => $value]);
        $this->storeFilters[$key] = $normalized[$key];
        return $this;
    }

    /**
     * Clears the stored filters
     * @return $this
     */
    public function unsetFilters()
    {
        $this->storeFilters = [];

        return $this;
    }

    /**
     * Clears a given filter
     * @param string $key
     * @return $this
     */
    public function unsetFilter(string $key)
    {
        if (isset($this->storeFilters[$key])) {
            unset($this->storeFilters[$key]);
        }
        return $this;
    }

    /**
     * Set permitted filters for a list query.
     *
     * @param array $filters
     */
    public function applyFilters(array $filters)
    {
        foreach ($filters as $key => $value) {
            if (empty($value)) {
                continue;
            }

            $functionName = ucwords(str_replace(['-', '_'], ' ', $key));
            $functionName = 'filter' . str_replace(' ', '', $functionName);

            if (method_exists($this, $functionName)) {
                $this->$functionName($value);
            }
        }
    }

    /**
     * Sets the entity name for this model
     *
     * @param $name
     *
     * @return $this
     */
    public function setEntityName($name)
    {
        $this->entityName = $name;

        return $this;
    }

    /**
     * Set the table.
     *
     * @deprecated No longer used. Was for shortcuts. Shortcuts are deprecated.
     *
     * @param $table
     *
     * @return $this
     */
    public function setTable($table)
    {
        $this->table = $table;

        return $this;
    }

    /**
     * Set a key in the payload and caches it, if applicable
     *
     * @param $key
     * @param $value
     * @param int $cacheLength Length of time to cache for,.
     *
     * @return DbBase
     */
    protected function setPayload($key, $value, $cacheLength = 0): self
    {
        $this->payload->{$key} = $value;

        // If we are making a request with filters, we can't
        // properly and predictably clear those cache keys
        // so we don't store it ever.
        if (! empty($this->cacheKey) && $cacheLength > 0 && ! $this->storeFilters) {
            // cache($this->cacheKey, $value, $cacheLength);
        }

        return $this;
    }

    /**
     * Eliminates all data from the payload
     */
    public function clearPayload()
    {
        $this->payload = new \stdClass();
    }

    /**
     * Get the payload result.
     *
     * @param string $format
     *
     * @return string|object
     */
    public function result($format = 'object')
    {
        $this->setPayload('source', $this->source);

        $this->setPayload('error', [
            'error' => $this->error,
            'errorMsg' => $this->errorMsg,
            'errorCode' => $this->errorCode,
        ]);

        switch (strtolower($format)) {
            case 'json':
                return json_encode($this->payload);
            default:
                return $this->payload;
        }
    }

    /**
     * Find a specific key in the result payload.
     *
     * @param $key
     *
     * @return bool|null
     */
    public function getResultKey($key)
    {
        return property_exists($this->payload, $key) ?? null;
    }

    /**
     * Set the ID we are working with. This is often done after a simple
     * get() request, and is then used to fluent chain together additional
     * calls.
     *
     * @param $id
     *
     * @return $this
     */
    public function setId($id)
    {
        if (is_object($id)) {
            return $this;
        } else {
            $this->id = $id;
        }

        return $this;
    }

    /**
     * Check if an ID has been set.
     *
     * @param $id
     *
     * @return mixed
     */
    protected function checkForId($id = null)
    {
        if (! empty($id)) {
            $this->setId($id);

            return $id;
        }

        return $this->id;
    }

    /**
     * Get a record.
     *
     * @param $inId
     *
     * @return mixed
     */
    protected function baseGet($inId)
    {
        $id = (! is_null($inId)) ? $inId : $this->id;

        if ($this->checkForCache()) {
            return $this;
        }

        try {
            $query = $this->connection
                ->where($this->table . '.id', '=', $id);

            if ($this->softDeletes) {
                $query->whereNull($this->table . '.deleted_at');
            }

            $data = $query
                ->select($this->columns)
                ->first();

            if ($data) {
                // Set the ID for fluent calls.
                $this->setId($data->id);

                // Remove keys we don't want in the payload and set payload.
                // $this->removals($data)
                $this->setPayload('data', $data, 10000);
            } else {
                $this->setPayload('data', []);

                $this->setError('Could not find item.');
            }

            if ($this->withMetaData) {
                $this->getMetadata($id);
            }
        } catch (\Throwable $e) {
            $this->setError($e->getMessage());
        }

        return $this;
    }

    /**
     * @param null $id
     * @param bool $doPayload Get commands = true / List commands = false
     *
     * @return array|$this
     */
    public function baseGetMetadata($id = null, $doPayload = true)
    {
        if (! $this->metadataKey) {
            return $this;
        }

        $customerId = $this->checkForId($id);

        if (! $customerId) {
            return $this;
        }

        $data = app('db')
            ->table($this->table . '_metadata')
            ->where($this->metadataKey, '=', $id)
            ->get();

        $meta = [];

        foreach ($data as $item) {
            $meta[$item->key] = $item->value;
        }

        // For GET requests. Lists won't work like this because the
        // metadata payload needs to be for each entry, not the
        // entire payload.
        if ($doPayload) {
            $this->setPayload('metadata', $meta, 10000);
        } else {
            // Because we also sometime return metadata
            // for list commands, we need all this if
            // logic stuff.
            // cache($this->cacheKey, $meta, 10080);

            return $meta;
        }

        return $this;
    }

    /**
     * Remove keys from the resulting array that we don't want people
     * seeing. Good example would be removing passwords.
     *
     * @param $data
     *
     * @return mixed
     */
    protected function removals($data)
    {
        // Todo: set up recursion for array within arrays.
        // Commented this out until that is in place.
        /*
        if (! empty($this->remove)) {
            foreach ($this->remove as $item) {
                if (is_array($data) && array_key_exists($item, $data)) {
                    unset($data[$item]);
                }

                if (is_object($data) && property_exists($data, $item)) {
                    unset($data->{$item});
                }
            }
        }
        */

        return $data;
    }

    /**
     * Check if rows exist
     *
     * @param null $inId
     * @param string $inFieldName
     *
     * @return bool
     */
    protected function baseExists($inId = null, $inFieldName = 'id')
    {
        $id = (! is_null($inId)) ? $inId : $this->id;

        try {
            $exists = $this->connection
                ->where($inFieldName, $id);

            if ($this->softDeletes) {
                $exists->whereNull('deleted_at');
            }

            $check = $exists->exists();
        } catch (\Throwable $e) {
            $this->setError($e->getMessage());

            return false;
        }

        return $check;
    }

    /**
     * Call this before doing a list/get.
     *
     * @return $this
     */
    public function metadata()
    {
        $this->withMetaData = true;

        return $this;
    }

    /**
     * @return bool
     */
    protected function checkForCache()
    {
        // Todo: reactivate once caching in place.
        return false;

        if ($this->neverFromCache) {
            Cache::forget($this->cacheKey);

            return false;
        }

        // For filters, we can't clear cache keys
        // effectively, so we must hit the db each time.
        if (Cache::has($this->cacheKey) && ! $this->storeFilters && ! $this->neverFromCache) {
            $this->source = 'cache';

            $this->setPayload('data', cacheGet($this->cacheKey));

            return true;
        }

        return false;
    }

    /**
     * Get a list of items.
     *
     * Uses Lumen's standard pagination tool.
     * @link    https://laravel.com/docs/5.5/pagination
     *
     * @param array|null $filters
     *
     * @return $this
     */
    protected function baseList(array $filters = null)
    {
        if ($filters) {
            $this->setFilters($filters);
        }

        if ($this->checkForCache()) {
            return $this;
        }

        $this->currentConnection = $this->connection;

        if ($this->softDeletes) {
            $this->currentConnection->whereNull($this->table . '.deleted_at');
        }

        if ($this->storeFilters) {
            $this->applyFilters($this->storeFilters);

            $this->setPayload('filters', $this->storeFilters);
        }

        $data = $this->currentConnection
            ->select($this->columns)
            ->paginate();

        if ($this->withMetaData) {
            $final = [];
            foreach ($data->items() as $item) {
                $add = [];
                $add['data'] = $item;
                $add['metadata'] = $this->getMetadata($item->id, false);
                $final[] = $add;
            }
            $data = $final;
        }

        $this->setPayload('data', $data, 10000);

        return $this;
    }

    /**
     * Update a record.
     *
     * Todo: set fillable options.
     *
     * @param array $data
     * @param null  $inId
     *
     * @return bool
     */
    public function baseUpdate(array $data, $inId = null)
    {
        $id = (is_null($inId)) ? $this->id : $inId;

        $this->fillable['entity_id'] = '';
        $this->fillable['updated_at'] = '';
        $this->fillable['deleted_at'] = '';

        // Auto-update timestamps?
        if ($this->timestamps && ! isset($data['updated_at'])) {
            $data['updated_at'] = $this->date::now();
        }

        $filter = empty($this->fillable)
            ? $data
            : array_intersect_key($data, $this->fillable);

        // Do the update!
        try {
            $updated = $this->connection
                ->where($this->idField, $id)
                ->update($filter);
        } catch (\Throwable $e) {
            $this->setError($e->getMessage());

            return false;
        }

        if (! $updated) {
            $this->setError();

            return false;
        }

        // Clear cache...
        Cache::forget($this->cacheListKey());
        Cache::forget($this->cacheGetKey($id));

        if (! empty($this->permittedMetaKeys)) {
            $this->processMetaData($id, $data);
        }

        return $updated;
    }

    /**
     * Create a record in the database.
     *
     * Todo: set fillable options.
     *
     * @param array $data
     * @param bool $allowIdOverwrite Allow us to force an ID. Useful for migrations, etc.
     *
     * @return mixed
     */
    public function baseInsert(array $data, $allowIdOverwrite = false)
    {
        // Add extras to fillable
        $this->fillable['entity_id'] = '';
        $this->fillable['created_at'] = '';
        $this->fillable['updated_at'] = '';

        if ($allowIdOverwrite) {
            $this->fillable['id'] = '';
        }

        // Timestamps
        if ($this->timestamps) {
            if (! isset($data['created_at'])) {
                $data['created_at'] = $this->date::now();
            }
            if (! isset($data['updated_at'])) {
                $data['updated_at'] = $this->date::now();
            }
        }

        $filter = empty($this->fillable)
            ? $data
            : array_intersect_key($data, $this->fillable);

        // Are we force feeding any data into this insert?
        if (! empty($this->forceData)) {
            $filter = array_merge($filter, $this->forceData);
        }

        // Do it!
        try {
            $id = $this->connection->insertGetId($filter);
        } catch (\Throwable $e) {
            $this->setError($e->getMessage());

            return false;
        }

        // Auto-create entity?
        // Must come after the insert as we need the $id.
        if ($this->createEntity) {
            $entAdditions = [];
            if ($this->entityAdditions) {
                foreach ($this->entityAdditions as $internalId => $relationship) {
                    if (! empty($relationship)) {
                        $exp = explode('.', $relationship);

                        if (sizeof($exp) == 3) {
                            $entityGet = app('db')
                                ->table($exp['0'])
                                ->select($exp['2'])
                                ->where($exp['1'], '=', $data[$internalId])
                                ->first();

                            $entAdditions[] = $entityGet->{$exp['2']} ?? null;
                        }
                    } else {
                        $entAdditions[] = $data[$internalId] ?? null;
                    }
                }
            }

            $entityId = entityId(
                $data[$this->entityName] ?? $id,
                $this->entityType ?? null,
                $entAdditions
            );

            $this->setPayload('entityId', $entityId);

            $this->baseUpdate([
                'entity_id' => $entityId
            ], $id);
        }

        if (empty($id)) {
            $this->setError();

            return false;
        }

        // Clear Cache
        Cache::forget($this->cacheListKey());

        // Set the insert ID in the payload
        $this->setPayload('insertId', $id);

        if (! empty($this->permittedMetaKeys)) {
            $this->processMetaData($id, $data);
        }

        return $id;
    }

    /**
     * If a model contains a metadata table, we can create/update
     * that metadata using this function. It is automatically called
     * by the baseInsert() and baseUpdate() methods if the method
     * finds a $permittedMetaKeys array in the model.
     *
     * @param       $id
     * @param array $data
     *
     * @return int
     */
    protected function processMetaData($id, array $data)
    {
        $potentialMetadata = array_diff_key($data, $this->fillable);

        $now = $this->date::now();

        $currentMeta = app('db')
            ->table($this->table . '_metadata')
            ->where($this->metadataKey, '=', $id)
            ->get();

        $currentMetaKeys = $metaDataToInsert = $payload = [];
        foreach ($currentMeta as $meta) {
            $currentMetaKeys[] = $meta->key;
        }

        foreach ($currentMeta as $meta) {
            // update only if meta value changed
            // less queries means more performance
            if (array_key_exists($meta->key, $potentialMetadata)
                && $potentialMetadata[$meta->key] !== $meta->value) {
                app('db')
                    ->table($this->table . '_metadata')
                    ->where('id', $meta->id)
                    ->update([
                        'value' => $potentialMetadata[$meta->key],
                        'updated_at' => $now
                    ]);

                $payload[$meta->key] = $potentialMetadata[$meta->key];
            }
        }

        // Now let's look for the new
        // metadata that is to be created
        foreach ($potentialMetadata as $key => $value) {
            // if the key is not stored but is valid, insert it
            if (!in_array($key, $currentMetaKeys) && in_array($key, $this->permittedMetaKeys)) {
                $metaDataToInsert[] = [
                    $this->metadataKey => $id,
                    'key' => $key,
                    'value' => $value,
                    'created_at' => $now,
                    'updated_at' => $now
                ];

                $payload[$key] = $value;
            }
        }

        if (! empty($metaDataToInsert)) {
            app('db')
                ->table($this->table . '_metadata')
                ->insert($metaDataToInsert);
        }

        $this->setPayload('metadata', $payload);

        return sizeof($payload);
    }

    /**
     * Delete an item from the database.
     *
     * @param null $inId
     * @param bool $forceDelete Whether to override soft deletes? Very rarely used.
     *
     * @return bool
     */
    public function baseDelete($inId = null, $forceDelete = false)
    {
        $id = (! is_null($inId)) ? $inId : $this->id;

        try {
            if ($forceDelete) {
                $soft = false;
            } elseif ($this->softDeletes) {
                $soft = true;
            } else {
                $soft = false;
            }

            if ($soft) {
                if ($this->permittedMetaKeys) {
                    app('db')
                        ->table($this->table . '_metadata')
                        ->where($this->metadataKey, '=', $id)
                        ->update(['deleted_at' => $this->date::now()]);
                }

                $this->connection
                    ->where($this->idField, $id)
                    ->update(['deleted_at' => $this->date::now()]);
            } else {
                if ($this->permittedMetaKeys) {
                    app('db')
                        ->table($this->table . '_metadata')
                        ->where($this->metadataKey, '=', $id)
                        ->delete();
                }

                $this->connection
                    ->where($this->idField, $id)
                    ->delete();
            }
        } catch (\Throwable $e) {
            $this->setError($e->getMessage());

            return false;
        }

        // Removing cache
        Cache::forget($this->cacheKey);

        return true;
    }

    /**
     * Returns the connection of
     * this model
     *
     * @return mixed
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * Delegates to a loading function
     * to load the relations given
     * by the relations array
     *
     * @param $relations
     *
     * @return $this
     */
    public function load($relations)
    {
        if (is_string($relations)) {
            $relations = [$relations];
        }

        foreach ($relations as $relation) {
            if (in_array($relation, $this->relations)) {
                $functionName = ucwords(str_replace(['-', '_'], ' ', $relation));
                $functionName = 'load' . str_replace(' ', '', $functionName);

                if (method_exists($this, $functionName)) {
                    $this->$functionName();
                }
            }
        }

        return $this;
    }

    abstract public function declare();
}