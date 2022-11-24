<script setup>
import { computed, onMounted, ref } from 'vue';
import { Bar, Line } from 'vue-chartjs'
import Box from './Box.vue';
import 'chart.js/auto';

const stats = ref(null);
const isDark = computed(() =>localStorage.theme === 'dark');

stats.value = {"latest":{"solved":151,"dnf":4,"average":43.39568874172188,"median":42.782,"sd":6.947079274974999},"times":[{"average":66.34116176470589,"session":"19/10/2022"},{"average":62.183445783132534,"session":"21/10/2022"},{"average":57.54790243902439,"session":"25/10/2022"},{"average":63.1845,"session":"26/10/2022"},{"average":58.25394366197182,"session":"27/10/2022"},{"average":53.87482926829268,"session":"28/10/2022"},{"average":49.869,"session":"29/10/2022"},{"average":52.569365384615395,"session":"30/10/2022"},{"average":53.82112280701754,"session":"31/10/2022"},{"average":50.18428,"session":"02/11/2022"},{"average":47.90504022988506,"session":"03/11/2022"},{"average":48.503296296296305,"session":"04/11/2022"},{"average":47.91488888888888,"session":"05/11/2022"},{"average":47.769098434004505,"session":"06/11/2022"},{"average":51.233000000000004,"session":"07/11/2022"},{"average":46.68068,"session":"08/11/2022"},{"average":48.11078499999998,"session":"09/11/2022"},{"average":46.265030508474595,"session":"10/11/2022"},{"average":47.267497382198925,"session":"11/11/2022"},{"average":49.021457286432124,"session":"12/11/2022"},{"average":54.16211111111112,"session":"14/11/2022"},{"average":49.82663953488375,"session":"16/11/2022"},{"average":47.733879365079375,"session":"17/11/2022"},{"average":46.84081690140848,"session":"18/11/2022"},{"average":44.71164795918366,"session":"19/11/2022"},{"average":49.641,"session":"20/11/2022"},{"average":44.10811284046694,"session":"21/11/2022"},{"average":42.83474380165289,"session":"22/11/2022"},{"average":43.95092279411767,"session":"23/11/2022"},{"average":43.39568874172188,"session":"24/11/2022"}],"solves":[{"solves":68,"session":"19/10/2022"},{"solves":166,"session":"21/10/2022"},{"solves":41,"session":"25/10/2022"},{"solves":8,"session":"26/10/2022"},{"solves":71,"session":"27/10/2022"},{"solves":41,"session":"28/10/2022"},{"solves":33,"session":"29/10/2022"},{"solves":52,"session":"30/10/2022"},{"solves":57,"session":"31/10/2022"},{"solves":75,"session":"02/11/2022"},{"solves":174,"session":"03/11/2022"},{"solves":162,"session":"04/11/2022"},{"solves":45,"session":"05/11/2022"},{"solves":447,"session":"06/11/2022"},{"solves":16,"session":"07/11/2022"},{"solves":25,"session":"08/11/2022"},{"solves":200,"session":"09/11/2022"},{"solves":295,"session":"10/11/2022"},{"solves":191,"session":"11/11/2022"},{"solves":199,"session":"12/11/2022"},{"solves":9,"session":"14/11/2022"},{"solves":258,"session":"16/11/2022"},{"solves":315,"session":"17/11/2022"},{"solves":213,"session":"18/11/2022"},{"solves":196,"session":"19/11/2022"},{"solves":3,"session":"20/11/2022"},{"solves":257,"session":"21/11/2022"},{"solves":242,"session":"22/11/2022"},{"solves":272,"session":"23/11/2022"},{"solves":151,"session":"24/11/2022"}]};

onMounted(async () => {
    return;
    fetch('/api/cube')
        .then((response) => response.json())
        .then((data) => (stats.value = data) && console.log(stats.value));
});

const solveTimeData = computed(() => {
    return {
        labels: stats.value.times.map(time => time.session),
        datasets: [
            {
                label: 'Average solve time (sec)', 
                data: stats.value.times.map(session => session.average),
                tension: .5,
                borderColor: isDark.value ? 'rgb(109 40 217)' : 'rgb(55 65 81)'
            },
        ]
    }
})

const solvesData = computed(() => {
    return {
        labels: stats.value.times.map(time => time.session),
        datasets: [
            {
                label: 'Solves during the session', 
                data: stats.value.solves.map(session => session.solves),
                tension: .5,
                borderColor: isDark.value ? 'rgb(109 40 217)' : 'rgb(55 65 81)'
            },
        ]
    }
})

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false, 
    layout: {
        padding: {
            bottom: 30
        }
    },
    scales: {
        x: {
            ticks: {
                color: isDark.value ? 'rgb(243 244 246)' : 'rgb(0, 0, 0)',
            },
            grid: {
                color: isDark.value ? 'rgb(71 85 105)' : 'rgb(226 232 240)',
            },
        },
        y: {
            ticks: {
                color: isDark.value ? 'rgb(243 244 246)' : 'rgb(0, 0, 0)',
            },
            grid: {
                color: isDark.value ? 'rgb(71 85 105)' : 'rgb(226 232 240)',
            },
        },
    },
    plugins: {
        legend: {
            display: false,
        }
    }
};
</script>

<template>
    <div class="mt-6">
        <h2 class="text-2xl font-bold">
            My recent session
        </h2>

        <div class="mt-4 grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <Box :loading="stats === null">
                <template v-if="stats">
                    <h3 class="text-sm font-bold uppercase mb-2 leading-tight">
                        Solves
                    </h3>
                    <span class="text-xl">
                        {{ stats.latest.solved }} / {{ stats.latest.solved + stats.latest.dnf }}
                    </span>
                </template>
            </Box>
            <Box :loading="stats === null">
                <template v-if="stats">
                    <h3 class="text-sm font-bold uppercase mb-2 leading-tight">
                        Average
                    </h3>
                    <span class="text-xl">
                        {{ Math.round(stats.latest.average * 100) / 100 }}s
                    </span>
                </template>
            </Box>
            <Box :loading="stats === null">
                <template v-if="stats">
                    <h3 class="text-sm font-bold uppercase mb-2 leading-tight">
                        Median
                    </h3>
                    <span class="text-xl">
                        {{ Math.round(stats.latest.median * 100) / 100 }}s
                    </span>
                </template>
            </Box>
            <Box :loading="stats === null">
                <template v-if="stats">
                    <h3 class="text-sm font-bold uppercase mb-2 leading-tight">
                        Std. deviation
                    </h3>
                    <span class="text-xl">
                        {{ Math.round(stats.latest.sd * 100) / 100 }}
                    </span>
                </template>
            </Box>
        </div>

        <h2 class="mt-6 text-2xl font-bold">
            My average solve time history
        </h2>

        <div class="mt-4">
            <Box v-if="stats" class="h-[400px]">
                <Line 
                    :chart-data="solveTimeData"
                    :chart-options="chartOptions"
                ></Line>
            </Box>
        </div>

        <h2 class="mt-6 text-2xl font-bold">
            Number of solves per session
        </h2>

        <div class="mt-4">
            <Box v-if="stats" class="h-[400px]">
                <Line 
                    :chart-data="solvesData"
                    :chart-options="chartOptions"
                ></Line>
            </Box>
        </div>
    </div>
</template>