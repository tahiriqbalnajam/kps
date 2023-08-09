import {createRouter, createWebHashHistory} from 'vue-router'

/* Layout */
import Layout from '@/layout/Layout.vue'

/* Router for modules */
import chartsRoutes from './modules/charts'
import adminRoutes from './modules/admin'
import errorRoutes from './modules/error'

export const constantRoutes = [
  {
    path: '/login',
    component: () => import('@/views/login/index.vue'),
    hidden: true,
  },
  {
    path: '/auth-redirect',
    component: () => import('@/views/login/AuthRedirect.vue'),
    hidden: true,
  },
  {
    path: '/404',
    redirect: { name: 'Page404' },
    component: () => import('@/views/error-page/404.vue'),
    hidden: true,
  },
  {
    path: '/401',
    component: () => import('@/views/error-page/401.vue'),
    hidden: true,
  },
  {
    path: '/',
    component: Layout,
    redirect: '/dashboard',
    children: [
      {
        path: 'dashboard',
        component: () => import('@/views/dashboard/index.vue'),
        name: 'Dashboard',
        meta: { title: 'dashboard', bootstrapIcon: 'house-fill', noCache: false },
      },
    ],
  },
  {
    path: '/students',
    component: Layout,
    redirect: '/students/index',
    children: [
      {
        path: 'index',
        component: () => import('@/views/students/StudentList.vue'),
        name: 'Students',
        meta: { title: 'Students',bootstrapIcon: 'person-bounding-box', icon: 'people', noCache: true },
      },
    ],
  },
  {
    path: '/classes',
    component: Layout,
    redirect: '/classes/list',
    meta: { title: 'Class/Subject', icon: 'money', noCache: true },
    children: [
      {
        path: 'list',
        component: () => import('@/views/stdclasses/classlist.vue'),
        name: 'Classes',
        meta: { title: 'Classes', icon: 'education', noCache: true },
      },
      {
        path: 'subjects',
        component: () => import('@/views/stdclasses/subjects.vue'),
        name: 'Subjects',
        meta: { title: 'Subjects', icon: 'education', noCache: true },
      },
      {
        path: 'subject_class',
        component: () => import('@/views/stdclasses/subjectToClass.vue'),
        name: 'Assign Subject',
        meta: { title: 'Assign Subject', icon: 'education', noCache: true },
      },
    ],
  },
  {
    path: '/profile',
    component: Layout,
    redirect: '/profile/edit',
    children: [
      {
        path: 'edit',
        component: () => import('@/views/users/SelfProfile.vue'),
        name: 'SelfProfile',
        meta: {title: 'userProfile', bootstrapIcon: 'person-circle', noCache: true},
      },
    ],
  },
  {
    path: '/waqar',
    component: Layout,
    redirect: '/profile/edit',
    children: [
      {
        path: 'edit',
        component: () => import('@/views/users/Waqar.vue'),
        name: 'Waqar',
        meta: {title: 'userProfile', bootstrapIcon: 'person-circle', noCache: true},
      },
    ],
  },
]

export const asyncRoutes = [
  chartsRoutes,
  adminRoutes,
  errorRoutes,
  { path: '/:pathMatch(.*)*', name: 'NotFound', redirect: '/404', hidden: true }
]

const router = createRouter({
  routes: constantRoutes,
  scrollBehavior: () => ({ top: 0 }),
  history: createWebHashHistory(),
})

export function resetRouter() {
  const asyncRouterNameArr = asyncRoutes.map((mItem) => mItem.name)
  asyncRouterNameArr.forEach((name) => {
    if (router.hasRoute(name)) {
      router.removeRoute(name)
    }
  })
}

export default router
