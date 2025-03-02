<template>
    <div>
        <v-navigation-drawer
            width="400"
            v-model="open"
            location="right"
            permanent
        >
            <v-card flat class="rounded-0">
                <!-- Fixed title at the top -->
                <v-card-title
                    class="d-flex align-center pe-2 bg-secondary sticky-title"
                >
                    <country-flag
                        :country="
                            loading ? 'XX' : data.country_iso_code ?? 'XX'
                        "
                        class="flag"
                        left
                        v-if="!loading"
                    />
                    {{ data.mmsi }}
                    <v-spacer></v-spacer>

                    <!-- Copy link with mmsi or imo -->
                    <v-btn
                        icon
                        variant="tonal"
                        size="small"
                        @click="copyLink"
                        title="Copy link"
                        class="mr-2"
                    >
                        <v-icon>mdi-link</v-icon>
                    </v-btn>

                    <!-- Close Dialog-->
                    <v-btn icon variant="tonal" size="small" @click="close">
                        <v-icon>mdi-close</v-icon>
                    </v-btn>
                </v-card-title>

                <!-- Scrollable content -->
                <v-card-text class="scrollable-content">
                    <v-skeleton-loader
                        class="mx-auto"
                        type="image, article"
                        v-if="loading"
                    ></v-skeleton-loader>

                    <v-avatar
                        rounded="0"
                        class="ma-0"
                        color="#ccc"
                        style="width: 100%; height: 200px"
                        v-if="!loading"
                    >
                        <v-img
                            cover
                            :src="`https://photos.marinetraffic.com/ais/showphoto.aspx?mmsi=${data.mmsi}`"
                        >
                            <template v-slot:error>
                                <v-img src="/images/placeholder.png"></v-img>
                            </template>
                        </v-img>
                    </v-avatar>

                    <v-btn
                        flat
                        @click="centerOnMap"
                        color="primary"
                        block
                        size="large"
                        rounded="0"
                        v-if="!loading"
                    >
                        Center on Map
                    </v-btn>

                    <v-table v-if="!loading">
                        <tbody class="details-table">
                            <template
                                v-for="(value, key) in shipDetails"
                                :key="key"
                            >
                                <tr
                                    v-if="
                                        data[key] !== null &&
                                        data[key] !== undefined
                                    "
                                >
                                    <td class="font-weight-black">
                                        {{ value.label }}
                                    </td>
                                    <td>{{ formatValue(key, data[key]) }}</td>
                                </tr>
                            </template>
                        </tbody>
                    </v-table>
                </v-card-text>
            </v-card>
        </v-navigation-drawer>

        <!-- Snackbar for copy link notification -->
        <v-snackbar
            v-model="snackbar"
            timeout="3000"
            color="black"
            location="top"
        >
            <v-icon left>mdi-check</v-icon>
            Link copied to clipboard
        </v-snackbar>
    </div>
</template>

<script lang="js">
export default {
    props: ["ship"],
    data() {
        return {
            open: false,
            loading: false,
            data: {},
            snackbar: false, // for the snackbar state
            // Ship details mapping
            shipDetails: {
                name: { label: "Name" },
                mmsi: { label: "MMSI" },
                imo: { label: "IMO" },
                callsign: { label: "Call Sign" },
                country_name: { label: "Country" },
                cargo_type_name: { label: "Cargo Type" },
                cargo_category_name: { label: "Cargo Category" },
                dim_a: { label: "Length", unit: "m" },
                dim_b: { label: "Beam", unit: "m" },
                destination: { label: "Destination" },
                sog: { label: "Speed Over Ground", unit: "knots" },
                cog: { label: "Course Over Ground", unit: "°" },
                hdg: { label: "Heading", unit: "°" },
                eta: { label: "ETA", isDate: true },
                predicted_eta: { label: "Predicted ETA", isDate: true },
                last_updated: { label: "Last Updated", isDate: true },
            },
        };
    },

    watch: {
        ship: {
            handler() {
                if (this.ship?.mmsi) this.fetchShipDetails();
            },
            immediate: true,
        },
    },

    methods: {
        fetchShipDetails() {
            this.open = true;
            this.loading = true;
            
            this.updateUrlBasedOnShip(this.ship);
            
            fetch(`/api/ships/details/${this.ship.mmsi}`)
                .then((response) => response.json())
                .then((data) => {
                    this.data = data;
                })
                .catch(() => {
                    this.data = {};
                    this.open = false;
                })
                .finally(() => {
                    this.loading = false;
                });
        },

        close() {
            this.open = false;
            this.updateUrlBasedOnShip({});
            this.$store.commit("setSelectedShip", null);
        },

        // Format values based on unit type or date format
        formatValue(key, value) {
            if (this.shipDetails[key]?.isDate) {
                return value ? new Date(value).toLocaleString() : "-";
            }
            if (this.shipDetails[key]?.unit) {
                return `${value} ${this.shipDetails[key].unit}`;
            }
            return value;
        },

        // Center the map on the selected ship
        centerOnMap() {
            //Emit the centerOnMap event with the ship data
            this.$emit("centerMapOnShip", this.data);
        },

        // Copy the link to the ship details
        copyLink() {
            const url = `${window.location.origin}/mmsi/${this.data.mmsi}`;
            navigator.clipboard.writeText(url);
            this.snackbar = true; // Show the snackbar notification
        },

        updateUrlBasedOnShip(ship) {
            const { mmsi, imo } = ship;

            if (mmsi || imo) {
                const shipId = mmsi || imo; // Use MMSI if available, otherwise IMO
                
                // Use Inertia to visit the new URL
                this.$inertia.visit(`/mmsi/${shipId}`, {
                    replace: true, // Substitui a entrada no histórico
                    preserveState: true, // Mantém o estado da página atual
                });
            } else {
                // Use Inertia to visit the new URL
                this.$inertia.visit("/", {
                    replace: true, // Substitui a entrada no histórico
                    preserveState: true, // Mantém o estado da página atual
                });
            }
        },
    },
};
</script>

<style scoped>
.scrollable-content {
    max-height: calc(100vh - 140px);
    overflow-y: auto;
    padding: 2px;
}

.sticky-title {
    position: sticky;
    top: 0;
    z-index: 1;
    color: white;
}

.flag {
    border: 1px solid #fff !important;
    margin-top: -12px !important;
    border-radius: 5px !important;
    margin-right: -5px !important;
    float: left !important;
}
</style>
