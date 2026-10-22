<?php

namespace App\LegacyApi;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * The in-world entry points api.php, hud.php, sys.php and ans.php (port of the 2.0.x Switchboards).
 *
 * Routing, status codes and the response format are part of the frozen contract
 * (docs/lsl-contract.md): unknown endpoints get HTTP 501, everything else HTTP 200 with a JSON
 * object, and a request that ends with status false leaves no database changes behind.
 */
class Gateway
{
    /** entry => [endpoint namespace, booleans sent as "1"/"0", default module, default area] */
    private const ENTRIES = [
        'api' => ['Secondlifeapi', true, 'Home', 'DefaultView'],
        'hud' => ['Hudapi', true, 'Home', 'DefaultView'],
        'sys' => ['SystemApi', false, 'Home', 'DefaultView'],
        'ans' => ['Ans', false, 'Ans', 'Event'],
    ];

    public function api(Request $request, string $path = ''): Response
    {
        return $this->handle($request, 'api', $path);
    }

    public function hud(Request $request, string $path = ''): Response
    {
        return $this->handle($request, 'hud', $path);
    }

    public function sys(Request $request, string $path = ''): Response
    {
        return $this->handle($request, 'sys', $path);
    }

    public function ans(Request $request, string $path = ''): Response
    {
        return $this->handle($request, 'ans', $path);
    }

    private function handle(Request $request, string $entry, string $path): Response
    {
        [$namespace, $boolsAsStrings, $defaultModule, $defaultArea] = self::ENTRIES[$entry];
        $segments = array_pad(explode('/', trim($path, '/')), 4, '');
        [$module, $area, $page, $option] = $segments;

        $loadingModule = ucfirst(strtolower($module !== '' ? $module : $defaultModule));
        $loadingArea = ucfirst(strtolower(in_array($area, ['', '*'], true) ? $defaultArea : $area));
        $class = $this->findEndpoint($namespace, $loadingModule, $loadingArea, $page, $option, $defaultArea);
        if ($class === null) {
            return $this->respond([
                'status' => false,
                'message' => '['.$loadingModule.' | '.$loadingArea.' | '.$page.'] Unsupported',
            ], 501);
        }

        $output = new Output;
        $fields = $entry === 'ans' ? $request->query() : $request->post();
        DB::beginTransaction();
        try {
            /** @var Endpoint $endpoint */
            $endpoint = new $class(new Input($fields), $output, $request, ucfirst($module), ucfirst($area));
            $endpoint->boot();
            if ($endpoint->loadOk()) {
                $output->set('module', $loadingModule);
                $output->set('area', $loadingArea);
                $endpoint->process();
            }
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
        $output->succeeded() ? DB::commit() : DB::rollBack();

        return $this->respond($output->toArray($boolsAsStrings), 200);
    }

    /** class lookup order of the 2.0.x Switchboard */
    private function findEndpoint(string $namespace, string $module, string $area, string $page, string $option, string $defaultArea): ?string
    {
        $routes = [[$area], [], [$area, $page], [$area, $page, $option], [$defaultArea]];
        foreach ($routes as $route) {
            $parts = array_filter([$module, ...$route], fn ($part) => $part !== '');
            $class = __NAMESPACE__.'\\Endpoints\\'.$namespace.'\\'.implode('\\', $parts);
            if (preg_match('/^[A-Za-z0-9\\\\]+$/', $class) === 1 && class_exists($class) && is_subclass_of($class, Endpoint::class)) {
                return $class;
            }
        }

        return null;
    }

    /** @param array<string, mixed> $body */
    private function respond(array $body, int $status): Response
    {
        return response((string) json_encode($body, JSON_UNESCAPED_UNICODE), $status)
            ->header('Content-Type', 'text/html; charset=UTF-8');
    }
}
