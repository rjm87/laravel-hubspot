<?php

namespace STS\HubSpot\Api;

use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\ForwardsCalls;
use Illuminate\Support\Traits\Macroable;
use STS\HubSpot\Api\Concerns\HasAssociations;
use STS\HubSpot\Facades\HubSpot;

abstract class Model
{
    use ForwardsCalls, Macroable, HasAssociations;

    protected string $type;

    protected array $payload = [];
    protected array $properties = [];

    protected array $schema = [
        'id'                    => 'int',
        'properties'            => 'array',
        'propertiesWithHistory' => 'array',
        'associations'          => 'array',
        'createdAt'             => 'datetime',
        'updatedAt'             => 'datetime',
        'archived'              => 'bool',
        'archivedAt'            => 'datetime',
    ];

    protected array $endpoints = [
        "create"       => "/v3/objects/{type}",
        "read"         => "/v3/objects/{type}/{id}",
        "batchRead"    => "/v3/objects/{type}/batch/read",
        "update"       => "/v3/objects/{type}/{id}",
        "search"       => "/v3/objects/{type}/search",
        "associate"    => "/v3/objects/{type}/{id}/associations/{association}/{associationId}/{associationType}",
        "associations" => "/v3/objects/{type}/{id}/associations/{association}",
    ];

    public function __construct(array $properties = [])
    {
        $this->fill($properties);
    }

    public static function hydrate(array $payload = []): static
    {
        $instance = new static;
        $instance->init($payload);

        return $instance;
    }

    public function init(array $payload = []): static
    {
        $this->payload = $payload;
        $this->fill(Arr::get($payload, 'properties', []));

        return $this;
    }

    public function fill(array $properties): static
    {
        $this->properties = array_merge($this->properties, $properties);

        return $this;
    }

    public function type(): string
    {
        return $this->type;
    }

    protected function endpoints(): array
    {
        return $this->endpoints;
    }

    public function endpoint($key, $fill = []): string
    {
        $fill['type'] = $this->type;

        if(Arr::has($this->payload, 'id')) {
            $fill['id'] = $this->id;
        }

        return str_replace(
            array_map(fn($key) => "{" . $key . "}", array_keys($fill)),
            array_values($fill),
            $this->endpoints()[$key]
        );
    }

    public static function create(array $properties = []): static
    {
        return static::hydrate(
            static::query()->create($properties)
        );
    }

    public function update(array $properties = []): static
    {
        return $this->fill($properties)->save();
    }

    public function save(): static
    {
        return $this->init(
            $this->builder()->update(
                $this->getDirty()
            )
        );
    }

    public function builder(): Builder
    {
        return app(Builder::class)->for($this);
    }

    public function get($key, $default = null): mixed
    {
        return $this->cast(
            Arr::get($this->payload, $key, $default),
            Arr::get($this->schema, $key, 'string')
        );
    }

    public function getDirty(): array
    {
        $dirty = [];
        $original = Arr::get($this->payload, 'properties', []);

        foreach ($this->properties as $key => $value) {
            if (! array_key_exists($key, $original) || $original[$key] !== $value) {
                $dirty[$key] = $value;
            }
        }

        return $dirty;
    }

    protected function cast($value, $type = "string"): mixed
    {
        return match ($type) {
            'int'      => (int)$value,
            'datetime' => Carbon::parse($value),
            'array'    => (array)$value,
            'string'   => (string)$value,
            default    => $value
        };
    }

    public function __get($key)
    {
        if (HubSpot::isType($key)) {
            return $this->getAssociations($key);
        }

        if (HubSpot::isType(Str::plural($key))) {
            return $this->getAssociations(Str::plural($key))->first();
        }

        if (array_key_exists($key, $this->payload)) {
            return $this->get($key);
        }

        return Arr::get($this->properties, $key);
    }

    public function __set($key, $value)
    {
        $this->properties[$key] = $value;
    }

    public function __isset($key)
    {
        return isset($this->properties[$key]);
    }

    public static function __callStatic($method, $parameters)
    {
        return (new static)->$method(...$parameters);
    }

    public function __call($method, $parameters)
    {
        if (HubSpot::isType($method)) {
            return $this->associations($method);
        }

        return $this->forwardCallTo($this->builder(), $method, $parameters);
    }
}