<?php
namespace App\Services;

use App\Models\Tournament;
use App\Models\TournamentPlayer;
use App\Models\User;
use App\Tournament\NotEnoughBalanceException;
use Illuminate\Database\DatabaseManager;

class TournamentPlayerService
{
    private DatabaseManager $databaseManager;

    public function __construct(DatabaseManager $databaseManager)
    {
        $this->databaseManager = $databaseManager;
    }

    /**
     * @param Tournament $tournament
     * @param User $user
     * @return TournamentPlayer
     * @throws NotEnoughBalanceException
     */
    public function register(Tournament $tournament, User $user): TournamentPlayer
    {
        return $this->databaseManager->transaction(function () use ($user, $tournament) {
            $player = TournamentPlayer::where([
                "tournament_id" => $tournament->id,
                "user_id" => $user->id,
            ])->first();

            if (!$player) {
                $user->refresh();

                $player = new TournamentPlayer();
                $player->tournament_id = $tournament->id;
                $player->user_id = $user->id;
                $player->chips = $tournament->chips;
                $player->save();

                $cost = $tournament->commission + $tournament->buy_in;
                if ($user->balance < $cost) {
                    throw new NotEnoughBalanceException();
                }

                $user->balance -= $cost;
                $user->save();
            }

            return $player;
        });
    }
}
