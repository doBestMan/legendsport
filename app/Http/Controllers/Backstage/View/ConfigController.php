<?php
namespace App\Http\Controllers\Backstage\View;

use App\Betting\MultiProvider;
use App\Http\Controllers\Controller;
use App\Models\Config;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use JavaScript;

class ConfigController extends Controller
{
    private MultiProvider $bettingProvider;

    public function __construct(MultiProvider $bettingProvider)
    {
        $this->bettingProvider = $bettingProvider;
    }

    public function index()
    {
        //
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        //
    }

    public function show()
    {
        $config = Config::first();

        JavaScript::put([
            'commission' => $config->config['commission'],
            'chips' => $config->config['chips'],
            'keepCompleted' => $config->config['keep_completed'],
            'providers' => $config->config['providers'],
        ]);

        return view('backstage.config.show')->with('config', $config);
    }

    public function edit()
    {
        $config = Config::first();

        JavaScript::put([
            'commission' => $config->config['commission'],
            'chips' => $config->config['chips'],
            'keepCompleted' => $config->config['keep_completed'],
            'providers' => $config->config['providers'],
            'availableProviders' => $this->bettingProvider->getProviderMap(),
        ]);

        return view('backstage.config.edit')->with('config', $config);
    }

    public function update(Request $request)
    {
        $config = Config::first();

        $config->config = $request->config;
        $config->save();

        return new Response('', Response::HTTP_NO_CONTENT);
    }
}
