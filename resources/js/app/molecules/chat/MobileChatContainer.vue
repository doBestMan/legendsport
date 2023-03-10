<template>
    <div class="layout__content__sidebar__chat layout__content__sidebar__chat--active">
        <div class="d--only--mobile b--dark-3">
            <div class="container">
                <div class="paging m--b--0">
                    <div class="paging__item">
                        <i
                            class="icon icon--left icon--large icon--color--light-1 m--r--4"
                            @click="handleChatExpand"
                        ></i>
                        <div class="paging__item__title">Chat</div>
                    </div>
                </div>
            </div>
        </div>

        <div
            class="layout__content__sidebar__chat__container layout__content__sidebar__chat__container--border"
        >
            <div class="layout__content__sidebar__chat__container__messages">
                <template v-for="message in messages">
                    <IncomingMessage
                        v-if="isIncoming(message)"
                        :key="message.id"
                        :timestamp="message.timestamp"
                        :user="message.userName"
                        :message="message.message"
                        :participant="message.isParticipant"
                    />

                    <OutcomingMessage v-else :key="message.id" :message="message.message" />
                </template>
            </div>

            <form
                v-if="canSendMessages"
                class="layout__content__sidebar__chat__container__input"
                @submit.prevent="sendMessage"
            >
                <div class="form">
                    <div class="form__control">
                        <input
                            class="input"
                            placeholder="Send Message..."
                            v-model="text"
                            required
                        />
                    </div>
                </div>
                <button class="button button--small button--yellow m--l--4 f--1">SEND</button>
            </form>
        </div>
    </div>
</template>

<script lang="ts">
import Vue, { PropType } from "vue";
import { ChatMessage } from "../../types/chat";
import IncomingMessage from "./IncomingMessage.vue";
import OutcomingMessage from "./OutcomingMessage.vue";

export default Vue.extend({
    name: "MobileChatContainer",
    components: { IncomingMessage, OutcomingMessage },

    props: {
        messages: Array as PropType<ChatMessage[]>,
    },

    data() {
        return {
            text: "",
        };
    },

    computed: {
        canSendMessages(): boolean {
            return !!this.$stock.state.user.user;
        },
    },

    methods: {
        sendMessage() {
            this.$emit("sendMessage", this.text);
            this.text = "";
        },

        isIncoming(message: ChatMessage): boolean {
            const user = this.$stock.state.user.user;
            return !user || message.userId !== user.id;
        },

        handleChatExpand() {
            this.$emit("handleChatExpand");
        },
    },
});
</script>
