import Vue from "vue";
import axios from "axios";
import { setup } from "../utils/setup.js";
import ModalDelete from "../components/ModalDelete";
import ModalAvailableEventList from "../molecules/tournament/ModalAvailableEventList";
import SelectedEventList from "../molecules/tournament/SelectedEventList";
import ActionButton from "../components/ActionButton";
import TournamentForm from "../molecules/tournament/TournamentForm";
import notificationStore from "../stores/notificationStore";
import FullLoader from "../components/FullLoader";
import loaderStore from "../stores/loaderStore";
import sportStore from "../stores/sportStore";

setup();

sportStore.load().catch(console.error);

new Vue({
    el: "#main",

    components: {
        ActionButton,
        FullLoader,
        ModalAvailableEventList,
        ModalDelete,
        SelectedEventList,
        TournamentForm,
    },

    data: {
        name: "",
        playersLimit: "",
        buyIn: 0,
        commission: 0,
        chips: 0,
        lateRegister: "",
        interval: "",
        lateRegisterValue: "",
        prizePool: "",
        prizePoolValue: "",
        prizes: "",
        state: "",
        selectedEvents: [],
        timeFrame: "",
        errors: {},

        isModalAvailableEventListVisible: false,
        modalDeleteId: null,
        modalDeleteDescription: null,
    },

    created() {
        this.name = phpVars.name;
        this.buyIn = phpVars.buyIn;
        this.selectedEvents = phpVars.apiSelectedSports || [];

        if (this.chips != "" || phpVars.config == "") {
            this.chips = phpVars.chips;
            this.commission = phpVars.commission;
        } else {
            this.commission = phpVars.config["commission"];
            this.chips = phpVars.config["chips"];
        }

        this.lateRegister = phpVars.lateRegister;
        this.interval = phpVars.interval;
        this.lateRegisterValue = phpVars.value;
        this.prizePool = phpVars.prizePool;
        this.prizePoolValue = phpVars.prizePoolValue;
        this.prizes = phpVars.prizes;
        this.playersLimit = phpVars.playersLimit;
        this.state = phpVars.state;
        this.timeFrame = phpVars.timeFrame;
    },

    mounted() {
        notificationStore.loadAndShow();
    },

    computed: {
        tournamentId() {
            return location.pathname.toString().split("/")[2];
        },
    },

    methods: {
        showModalAvailableEventList() {
            this.isModalAvailableEventListVisible = true;
        },

        includeEvent(event) {
            if (this.selectedEvents.every(item => item.ID !== event.ID)) {
                this.selectedEvents.push(event);
            }
        },

        removeEvent(event) {
            this.selectedEvents = [
                ...this.selectedEvents.filter(
                    selected =>
                        selected.HomeTeam !== event.HomeTeam ||
                        selected.AwayTeam !== event.AwayTeam ||
                        selected.Sport !== event.Sport,
                ),
            ];
        },

        openDeleteModal(id, description) {
            this.modalDeleteId = id;
            this.modalDeleteDescription = description;
        },

        closeDeleteModal() {
            this.modalDeleteId = null;
            this.modalDeleteDescription = null;
        },

        async deleteTournament(tournamentId) {
            loaderStore.show();
            try {
                await axios.delete(`/tournaments/${tournamentId}`);
                this.closeDeleteModal();
                notificationStore.info("Tournament's been deleted.");
                window.location = "/tournaments";
            } finally {
                loaderStore.hide();
            }
        },

        async createTournament() {
            var eventsType = this.selectedEvents.map(event => event.Sport);

            loaderStore.show();

            try {
                const response = await axios.post("/tournaments", {
                    ApiData: this.selectedEvents,
                    name: this.name,
                    type: eventsType,
                    players_limit: this.playersLimit,
                    buy_in: this.buyIn,
                    chips: this.chips,
                    commission: this.commission,
                    late_register: this.playersLimit == "Unlimited" ? this.lateRegister : "",
                    late_register_rule: {
                        interval: this.playersLimit == "Unlimited" ? this.interval : "",
                        value: this.playersLimit == "Unlimited" ? this.lateRegisterValue : "",
                    },
                    prize_pool: {
                        type: this.prizePool || "",
                        fixed_value: this.prizePoolValue || "",
                    },
                    prizes: {
                        type: this.prizes || "",
                    },
                    state: this.state,
                    time_frame: this.timeFrame,
                });

                notificationStore.info("New tournament's been created.");
                window.location = "/tournaments";
            } catch (e) {
                this.errors = e.response.data.errors;
                notificationStore.errorSync(e.response.data.message);
            } finally {
                loaderStore.hide();
            }
        },

        async updateTournament() {
            var eventsType = this.selectedEvents.map(event => event.Sport);

            loaderStore.show();

            try {
                await axios.patch(`/tournaments/${this.tournamentId}`, {
                    ApiData: this.selectedEvents,
                    name: this.name,
                    type: eventsType,
                    players_limit: this.playersLimit,
                    buy_in: this.buyIn,
                    chips: this.chips,
                    commission: this.commission,
                    late_register: this.playersLimit == "Unlimited" ? this.lateRegister : "",
                    late_register_rule: {
                        interval: this.playersLimit == "Unlimited" ? this.interval : "",
                        value: this.playersLimit == "Unlimited" ? this.lateRegisterValue : "",
                    },
                    prize_pool: {
                        type: this.prizePool || "",
                        fixed_value: this.prizePoolValue || "",
                    },
                    prizes: {
                        type: this.prizes || "",
                    },
                    state: this.state,
                    time_frame: this.timeFrame,
                });

                notificationStore.info("Tournament's been updated.");
                window.location = "/tournaments";
            } catch (e) {
                this.errors = e.response.data.errors;
                notificationStore.errorSync(e.response.data.message);
            } finally {
                loaderStore.hide();
            }
        },
    },
});