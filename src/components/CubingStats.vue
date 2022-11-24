<script setup>
import { computed, onMounted, ref } from 'vue';
import { Bar, Line } from 'vue-chartjs'
import Box from './Box.vue';
import 'chart.js/auto';

const stats = ref(null);
const isDark = computed(() =>localStorage.theme === 'dark');

onMounted(async () => {
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