<?php
namespace App\Http\Controllers\App\Api;

use App\Domain\BetItem;
use App\Domain\BetPlacementException;
use App\Domain\User;
use App\Http\Controllers\Controller;
use App\Models\Tournament;
use App\Models\TournamentEvent;
use App\Tournament\Enums\PendingOddType;
use App\Tournament\Events\TournamentUpdate;
use App\User\MeUpdate;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManager;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class TournamentBetParlayController extends Controller
{
    public function post(
        Tournament $tournament,
        Request $request,
        Dispatcher $dispatcher,
        EntityManager $entityManager
    ) {
        $request->validate([
            'pending_odds' => ['required', 'array', 'min:2'],
            'pending_odds.*.event_id' => [
                'required',
                'numeric',
                Rule::exists(TournamentEvent::table(), "id"),
            ],
            'pending_odds.*.type' => [
                'required',
                Rule::in(array_values(PendingOddType::toArray())),
            ],
            'wager' => ['required', 'numeric', 'min:100'],
        ]);

        $entityManager->beginTransaction();
        /** @var User $user */
        $user = $entityManager->find(User::class, $request->user()->id);
        /** @var \App\Domain\Tournament $tournamentEntity */
        $tournamentEntity = $entityManager->find(\App\Domain\Tournament::class, $tournament->id, LockMode::PESSIMISTIC_WRITE);
        $tournamentPlayer = $user->getTournamentPlayer($tournamentEntity);
        $betItems = [];

        try {
            if ($tournamentPlayer === null) {
                throw BetPlacementException::notRegistered();
            }

            foreach ($request->request->get('pending_odds') as $pendingWager) {
                $tournamentEvent = $tournamentEntity->getEvent($pendingWager['event_id']);
                if ($tournamentEvent === null) {
                    throw BetPlacementException::invalidEvent();
                }

                $betItems[] = new BetItem($pendingWager['type'], $tournamentEvent);
            }

            $tournamentEntity->placeParlayBet($tournamentPlayer, (int) $request->get('wager'), ...$betItems);
        } catch (BetPlacementException $e) {
            $entityManager->rollback();
            return new JsonResponse(
                [
                    'message' => $e->getMessage(),
                ],
                Response::HTTP_BAD_REQUEST,
            );
        }

        $entityManager->flush();
        $entityManager->commit();

        $dispatcher->dispatch(new TournamentUpdate($tournament));
        $dispatcher->dispatch(new MeUpdate($request->user()));

        return [];
    }
}
