<template>
    <section class="layout__content__sidebar b--dark-3 layout__content__sidebar--left">
        <ChatContainer :messages="chatMessages" @sendMessage="sendMessage" />
        <div class="layout__content__sidebar__games">
            <TournamentInfo :tournament="tournament" />
            <div class="layout__content__sidebar__seperator" />
            <InfoDetailSection :tournament="tournament" :window="window" />
        </div>
    </section>
</template>

<script lang="ts">
import Vue, { PropType } from "vue";
import { Window } from "../../../types/window";
import { Tournament } from "../../../types/tournament";
import ChatContainer from "../../chat/ChatContainer.vue";
import InfoDetailSection from "./InfoDetailSection.vue";
import TournamentRankTable from "../../general/TournamentRankTable.vue";
import { ChatMessage } from "../../../types/chat";
import { NewChatMessage } from "../../../utils/websockets/NewChatMessage";
import { User } from "../../../../general/types/user";
import RegisterNowButton from "../../../components/RegisterNowButton.vue";
import TournamentInfo from "../../general/TournamentInfo.vue";

export default Vue.extend({
    name: "InfoSection",
    components: {
        InfoDetailSection,
        RegisterNowButton,
        TournamentInfo,
        TournamentRankTable,
        ChatContainer,
    },

    props: {
        window: Object as PropType<Window>,
    },

    computed: {
        tournament(): Tournament {
            return this.window.tournament;
        },

        user(): User | null {
            return this.$stock.state.user.user;
        },

        chatMessages(): ChatMessage[] {
            const userIds = new Set(this.tournament.players.map(player => player.userId));

            return this.$stock.state.chat.messages
                .filter(message => message.tournamentId === this.tournament.id)
                .map(message => ({
                    ...message,
                    isParticipant: userIds.has(message.userId),
                }));
        },
    },

    methods: {
        sendMessage(message: string) {
            this.$echo.sendEvent(new NewChatMessage(this.tournament.id, message), this.user?.token);
        },
    },
});
</script>
