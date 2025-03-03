<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import Map from '../../components/Map.vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Ships',
        href: '/',
    },
];

defineProps<{
    ships?: Array<any>;
}>();
</script>

<template>
    <Head title="Ships" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-4 p-4">
            <div class="grid auto-rows-min gap-4 md:grid-cols-3">
                <div class="aspect-video rounded-xl bg-muted/50" />
                <div class="aspect-video rounded-xl bg-muted/50" />
                <div class="aspect-video rounded-xl bg-muted/50" />
            </div>
            <div class="min-h-[100vh] flex-1 rounded-xl bg-muted/50 md:min-h-min"><Map :ships="ships" /></div>
        </div>
    </AppLayout>
</template>
