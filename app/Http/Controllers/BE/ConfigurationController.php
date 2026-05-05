<?php

namespace App\Http\Controllers\BE;

use App\Filters\ConfigurationFilter;
use App\Http\Controllers\Controller;
use App\Http\Requests\ConfigurationIndexRequest;
use App\Http\Requests\ConfigurationShowRequest;
use App\Http\Requests\ConfigurationUpdateRequesst;
use App\Http\Resources\ConfigurationResource;
use App\Models\Configuration;

class ConfigurationController extends Controller
{
    public function __construct(private ConfigurationFilter $configurationFilter)
    {
    }

    public function index(ConfigurationIndexRequest $request)
    {
        $configurations = Configuration::query();

        $configurations = $this->configurationFilter->apply($request, $request->size ?? 10, $configurations);

        return self::responsePaginated(ConfigurationResource::collection($configurations), $configurations);
    }

    public function update(ConfigurationUpdateRequesst $request, string $uuid)
    {
        $configuration = Configuration::findByUuid($uuid);
        
        $configuration->update([
            'value' => $request->value,
            'updated_at' => self::currentDateTime(),
        ]);
        
        return self::response(new ConfigurationResource($configuration));
    }

    public function show(ConfigurationShowRequest $request, string $uuid)
    {
        $configuration = Configuration::findByUuid($uuid);
        
        return self::response(new ConfigurationResource($configuration));
    }
}
