<?php

namespace Atlas\Router;

use Atlas\Config\Config;
use Atlas\Exception\MissingConfigurationException;

/**
 * Handles module discovery and route loading.
 */
class ModuleLoader
{
    /**
     * Constructs a new ModuleLoader instance.
     *
     * @param Config $config The configuration object
     * @param Router|RouteGroup $target The target to register routes to
     */
    public function __construct(
        private readonly Config $config,
        private readonly Router|RouteGroup $target
    ) {
    }

    /**
     * Loads routes for a given module or modules.
     *
     * @param string|array $identifier The module identifier or array of identifiers
     * @param string|null $prefix Optional URI prefix for the module
     * @return void
     * @throws MissingConfigurationException if modules_path is not configured
     */
    public function load(string|array $identifier, string|null $prefix = null): void
    {
        $identifier = is_string($identifier) ? [$identifier] : $identifier;
        $modulesPath = $this->config->getModulesPath();
        $routesFile = $this->config->getRoutesFile();

        if ($modulesPath === null) {
            throw new MissingConfigurationException(
                'modules_path configuration is required to use module() method'
            );
        }

        $moduleName = $identifier[0] ?? '';

        foreach ((array)$modulesPath as $basePath) {
            $modulePath = $basePath . '/' . $moduleName . '/' . $routesFile;

            if (file_exists($modulePath)) {
                $this->loadModuleRoutes($modulePath, $prefix, $moduleName);
            }
        }
    }

    /**
     * Loads route definitions from a file and registers them.
     *
     * @param string $routesFile The path to the routes file
     * @param string|null $prefix Optional URI prefix
     * @param string|null $moduleName Optional module name
     * @return void
     */
    private function loadModuleRoutes(string $routesFile, string|null $prefix = null, string|null $moduleName = null): void
    {
        $moduleRoutes = require $routesFile;

        $options = [];
        if ($prefix) {
            $options['prefix'] = $prefix;
        }

        $group = $this->target->group($options);

        foreach ($moduleRoutes as $routeData) {
            if (!isset($routeData['method'], $routeData['path'], $routeData['handler'])) {
                continue;
            }

            $route = $group->registerCustomRoute(
                $routeData['method'],
                $routeData['path'],
                $routeData['handler'],
                $routeData['name'] ?? null,
                $routeData['middleware'] ?? [],
                $routeData['validation'] ?? [],
                $routeData['defaults'] ?? []
            );

            if ($moduleName) {
                $route->setModule($moduleName);
            }
        }
    }
}
