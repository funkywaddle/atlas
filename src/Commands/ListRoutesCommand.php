<?php

namespace Atlas\Commands;

use Atlas\Router\Router;

/**
 * Command to list all registered routes in a table or JSON format.
 */
class ListRoutesCommand
{
    /**
     * Executes the command.
     *
     * @param Router $router The router instance
     * @param array $argv CLI arguments
     * @return void
     */
    public function execute(Router $router, array $argv): void
    {
        $json = in_array('--json', $argv);
        $routes = $router->getRoutes();
        $output = [];

        foreach ($routes as $route) {
            $output[] = $route->toArray();
        }

        if ($json) {
            echo json_encode($output, JSON_PRETTY_PRINT) . PHP_EOL;
        } else {
            printf("%-10s | %-30s | %-20s | %-30s\n", "Method", "Path", "Name", "Handler");
            echo str_repeat("-", 100) . PHP_EOL;
            foreach ($output as $r) {
                printf(
                    "%-10s | %-30s | %-20s | %-30s\n",
                    $r['method'],
                    $r['path'],
                    $r['name'] ?? '',
                    is_string($r['handler']) ? $r['handler'] : 'Closure'
                );
            }
        }
    }
}
