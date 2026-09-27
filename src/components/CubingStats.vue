<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { Line } from 'vue-chartjs'
import Box from './Box.vue';
import 'chart.js/auto';

const stats = ref(null);
const error = ref(false);

// The `dark` class on <html> is the source of truth: it's set from localStorage or the OS preference,
// and flipped by the dark mode toggle.
const readIsDark = () => document.documentElement.classList.contains('dark');
const isDark = ref(readIsDark());
const themeObserver = new MutationObserver(() => (isDark.value = readIsDark()));

onUnmounted(() => themeObserver.disconnect());

onMounted(async () => {
    themeObserver.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });

    fetch(import.meta.env.PUBLIC_CUBE_API_URL)
        .then((response) => {
            if (! response.ok) {
                throw new Error(`Cubing API responded with ${response.status}`);
            }

            return response.json();
        })
        .then((data) => (stats.value = data))
        .catch((e) => {
            console.error(e);
            error.value = true;
        });
});

const seconds = (ms) => Math.round(ms / 10) / 100;

const solveTimeData = computed(() => {
    return {
        labels: stats.value.average_history.map(session => session.date),
        datasets: [
            {
                label: 'Average solve time (sec)', 
                data: stats.value.average_history.map(session => session.average_ms / 1000),
                tension: .5,
                borderColor: isDark.value ? 'rgb(109 40 217)' : 'rgb(55 65 81)'
            },
        ]
    }
})

const solvesData = computed(() => {
    return {
        labels: stats.value.solve_count_history.map(session => session.date),
        datasets: [
            {
                label: 'Solves during the session', 
                data: stats.value.solve_count_history.map(session => session.solves),
                tension: .5,
                borderColor: isDark.value ? 'rgb(109 40 217)' : 'rgb(55 65 81)'
            },
        ]
    }
})

const chartOptions = computed(() => ({
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
}));
</script>

<template>
    <div class="mt-6">
        <h2 class="text-2xl font-bold">
            My recent session {{ stats ? stats.recent_session.date : '' }}
        </h2>

        <Box v-if="error" class="mt-4">
            Couldn't load stats.
        </Box>

        <template v-else>
            <div class="mt-4 grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <Box :loading="stats === null">
                    <template v-if="stats">
                        <h3 class="text-sm font-bold uppercase mb-2 leading-tight">
                            Solves
                        </h3>
                        <span class="text-xl">
                            {{ stats.recent_session.solves }}
                        </span>
                    </template>
                </Box>
                <Box :loading="stats === null">
                    <template v-if="stats">
                        <h3 class="text-sm font-bold uppercase mb-2 leading-tight">
                            Average
                        </h3>
                        <span class="text-xl">
                            {{ seconds(stats.recent_session.average_ms) }}s
                        </span>
                    </template>
                </Box>
                <Box :loading="stats === null">
                    <template v-if="stats">
                        <h3 class="text-sm font-bold uppercase mb-2 leading-tight">
                            Median
                        </h3>
                        <span class="text-xl">
                            {{ seconds(stats.recent_session.median_ms) }}s
                        </span>
                    </template>
                </Box>
                <Box :loading="stats === null">
                    <template v-if="stats">
                        <h3 class="text-sm font-bold uppercase mb-2 leading-tight">
                            Std. deviation
                        </h3>
                        <span class="text-xl">
                            {{ seconds(stats.recent_session.std_dev_ms) }}s
                        </span>
                    </template>
                </Box>
            </div>

            <h2 class="mt-6 text-2xl font-bold">
                My average solve time history
            </h2>

            <div class="mt-4">
                <Box :loading="stats === null" class="h-[400px]">
                    <Line 
                        v-if="stats"
                        :data="solveTimeData"
                        :options="chartOptions"
                    ></Line>
                </Box>
            </div>

            <h2 class="mt-6 text-2xl font-bold">
                Number of solves per session
            </h2>

            <div class="mt-4">
                <Box :loading="stats === null" class="h-[400px]">
                    <Line 
                        v-if="stats"
                        :data="solvesData"
                        :options="chartOptions"
                    ></Line>
                </Box>
            </div>
        </template>
    </div>
</template>
