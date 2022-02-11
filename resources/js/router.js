import Vue from 'vue'
import Router from 'vue-router'
import Dashboard from './views/Dashboard.vue'

Vue.use(Router);

const routes = [
    {
        path: '/',
        name: 'dashboard',
        component: Dashboard
    },
    {
        path: '/participants',
        name: 'participants',
        component: () => import('./views/Participants.vue')
    },
    {
        path: '/inputs',
        name: 'inputs',
        component: () => import('./views/Inputs.vue')
    },
    {
        path: '/approval',
        name: 'approval',
        component: () => import('./views/Approval.vue')
    },
    {
        path: '/inprogress',
        name: 'inprogress',
        component: () => import('./views/InProgress.vue')
    },
    {
        path: '/clarification',
        name: 'clarification',
        component: () => import('./views/Clarification.vue')
    },
    {
        path: '/approved',
        name: 'approved',
        component: () => import('./views/Approved.vue')
    },
    {
        path: '/invalid',
        name: 'invalid',
        component: () => import('./views/Invalid.vue')
    },
    {
        path: '/rejected',
        name: 'rejected',
        component: () => import('./views/Rejected.vue')
    },
    {
        path: '/categories',
        name: 'categories',
        component: () => import('./views/Categories.vue')
    },
];

const router = new Router({
    routes:routes,
    linkActiveClass: 'active'
});

export default router;