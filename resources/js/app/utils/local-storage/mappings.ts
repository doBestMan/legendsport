import { BetTypeTab, TournamentInfoTab, PendingOdd, StorableWindow } from "../../types/window";

export const mapStorableWindow = (data: any): StorableWindow => ({
    id: data.id,
    selectedSportIds: data.selectedSportIds ?? [],
    selectedBetTypeTab: data.selectedBetTypeTab ?? BetTypeTab.Pending,
    selectedTournamentInfoTab: data.selectedTournamentInfoTab ?? TournamentInfoTab.Games,
    parlayWager: data.parlayWager,
    pendingOdds: (data.pendingOdds ?? []).map(mapPendingOdd),
});

export const mapPendingOdd = (data: any): PendingOdd => ({
    tournamentEventId: data.tournamentEventId,
    externalId: data.externalId ?? data.eventId,
    type: data.type,
    wager: data.wager,
});
