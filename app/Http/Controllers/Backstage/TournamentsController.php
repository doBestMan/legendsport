<?php

namespace App\Http\Controllers\Backstage;

use JavaScript;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Backstage\Tournaments;
use App\Models\Backstage\Config;

class TournamentsController extends Controller
{
    public function index()
    {
        $tournaments = Tournaments::paginate(10);

        $numFirstItemPage = $tournaments->firstItem();

        return view('backstage.tournaments.index')
            ->with('tournaments', $tournaments)
            ->with('tournament', null)
            ->with('numFirstItemPage', $numFirstItemPage);
    }

    public function create()
    { 
        $config = Config::first();

        JavaScript::put([
            'commission' => $config->config['commission'],
        ]);

        return view('backstage.tournaments.create')
            ->with('tournaments', null)
            ->with('tournament', null)
            ->with('config', $config)
            ->with('numFirstItemPage', 0);
    }

    public function store(Request $request)
    {
        $this->validation($request);

        $tournament = new Tournaments;
        $tournament->name = $request->name;
        $tournament->type = $request->type;
        $tournament->prize_pool = $request->prize_pool;
        $tournament->players_limit = $request->players_limit;
        $tournament->buy_in = $request->buy_in;
        $tournament->chips = $request->chips;
        $tournament->commission = $request->commission;
        $tournament->late_register = $request->late_register;
        $tournament->late_register_rule = $request->late_register_rule;
        $tournament->state = $request->state;
        $tournament->prizes = $request->prizes;
        $tournament->save();

        return redirect()->route('tournaments.index');
    }

    public function show(Tournaments $tournament)
    {
        JavaScript::put([
            'buy_in' => $tournament->buy_in,
            'commission' => $tournament->commission,
            'lateRegister' => $tournament->late_register,
        ]);

        return view('backstage.tournaments.show')
            ->with('tournaments', null)
            ->with('tournament', $tournament)
            ->with('numFirstItemPage', 0);
    }

    public function edit(Tournaments $tournament)
    {
        JavaScript::put([
            'buy_in' => $tournament->buy_in,
            'commission' => $tournament->commission,
            'lateRegister' => $tournament->late_register,
        ]);

        return view('backstage.tournaments.edit')
            ->with('tournaments', null)
            ->with('tournament', $tournament)
            ->with('numFirstItemPage', 0);
    }

    public function update(Request $request, Tournaments $tournament)
    {
        $this->validation($request);

        $tournament->name = $request->name;
        $tournament->type = $request->type;
        $tournament->prize_pool = $request->prize_pool;
        $tournament->players_limit = $request->players_limit;
        $tournament->buy_in = $request->buy_in;
        $tournament->chips = $request->chips;
        $tournament->commission = $request->commission;
        $tournament->late_register = $request->late_register;
        $tournament->late_register_rule = $request->late_register_rule;
        $tournament->state = $request->state;
        $tournament->prizes = $request->prizes;
        $tournament->save();

        return redirect()->route('tournaments.index');
    }
    
    public function destroy(Tournaments $tournament)
    {
        $tournament->delete();
        
        return redirect()->route('tournaments.index');
    }
    
    private function validation(Request $request)
    {
        $request->validate([
            'name'=> 'required',
            'type'=> 'required',
            'players_limit'=> 'required',
            'buy_in'=> 'required',
            'chips'=> 'required',
            'commission'=> 'required',
            'late_register'=> 'required',
            'late_register_rule'=> 'required',
            'state'=> 'required',
            'prizes'=> 'required',
        ]);
    }
}