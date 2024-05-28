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
    meta: { title: 'Students', bootstrapIcon: 'mortarboard-fill', noCache: true },
    children: [
      {
        path: 'index',
        component: () => import('@/views/students/StudentList.vue'),
        name: 'List',
        meta: { title: 'List',bootstrapIcon: 'mortarboard', icon: 'people', noCache: true },
      },
      {
        path: 'character-certificate',
        component: () => import('@/views/students/StudentCharacterCertificate.vue'),
        name: 'Character Certificate',
        meta: { title: 'Character Certificate',bootstrapIcon: 'mortarboard', icon: 'people', noCache: true },
      },
    ],
  },
  {
    path: '/classes',
    component: Layout,
    redirect: '/classes/list',
    meta: { title: 'Class/Subject', bootstrapIcon: 'person-workspace', noCache: true },
    children: [
      {
        path: 'list',
        component: () => import('@/views/stdclasses/classlist.vue'),
        name: 'Classes',
        meta: { title: 'Classes', bootstrapIcon: 'clipboard-minus-fill', noCache: true },
      },
      {
        path: 'subjects',
        component: () => import('@/views/stdclasses/subjects.vue'),
        name: 'Subjects',
        meta: { title: 'Subjects', bootstrapIcon: 'book-half', noCache: true },
      },
      {
        path: 'subject_class',
        component: () => import('@/views/stdclasses/subjectToClass.vue'),
        name: 'Assign Subject',
        meta: { title: 'Assign Subject', bootstrapIcon: 'book', noCache: true },
      },
    ],
  },
  {
    path: '/parents',
    component: Layout,
    redirect: '/parents/list',
    children: [
      {
        path: 'list',
        component: () => import('@/views/parents/parentlist.vue'),
        name: 'Parents',
        meta: { title: 'Parents', bootstrapIcon: 'person-vcard-fill', noCache: true },
      },
    ],
  },
  {
    path: '/fee',
    component: Layout,
    redirect: '/fee/paidlist',
    meta: { title: 'Fee', bootstrapIcon: 'currency-dollar', noCache: true },
    children: [
      {
        path: 'paidlist',
        component: () => import('@/views/fee/paidlist.vue'),
        name: 'Paid',
        meta: { title: 'Paid', bootstrapIcon: 'diamond-fill', noCache: true },
      },
      {
        path: 'pendinglist',
        component: () => import('@/views/fee/pendinglist.vue'),
        name: 'Pending',
        meta: { title: 'Pending', bootstrapIcon: 'diamond-half', noCache: true },
      },
      {
        path: 'feetypes',
        component: () => import('@/views/fee/feetypes.vue'),
        name: 'Fee Types',
        meta: { title: 'Fee Types', bootstrapIcon: 'currency-exchange', noCache: true },
      },
    ],
  },
  
  {
    path: '/teacher',
    component: Layout,
    redirect: '/teacher/list',
    meta: { title: 'Teachers', bootstrapIcon: 'person-gear', noCache: true },
    children: [
      {
        path: 'list',
        component: () => import('@/views/teachers/TeacherList.vue'),
        name: 'Teachers',
        meta: { title: 'Teachers', bootstrapIcon: 'person-lines-fill', noCache: true },
      },
      {
        path: 'teacher-pay',
        component: () => import('@/views/teachers/TeacherPay.vue'),
        name: 'Teacher Pay',
        meta: { title: 'Pay', bootstrapIcon: 'currency-exchange', noCache: true },
      }
    ],
  },
  {
    path: '/attendance',
    component: Layout,
    redirect: '/attendance/add',
    meta: { title: 'Attendance', bootstrapIcon: 'calendar-check-fill', noCache: true },
    children: [
      {
        path: 'add',
        component: () => import('@/views/attendance/StudentAttendance.vue'),
        name: 'Mark Students Attendance',
        meta: { title: 'Mark Students Attendance', bootstrapIcon: 'calendar-plus', noCache: true },
      },
      {
        path: 'attendance',
        component: () => import('@/views/teachers/AddAttendance.vue'),
        name: 'Mark Teacher Attendance',
        meta: { title: 'Mark Teacher Attendance', bootstrapIcon: 'calendar3', noCache: true },
      },
      {
        path: 'teacher-att-report',
        component: () => import('@/views/teachers/TeacherAttReport.vue'),
        name: 'Attendance Report',
        meta: { title: 'Att Report', bootstrapIcon: 'calendar-check-fill', noCache: true },
      },
      {
        path: 'student-report',
        component: () => import('@/views/attendance/StudentAttReport.vue'),
        name: 'Monthly/Classwise',
        meta: { title: 'Monthly/Classwise', bootstrapIcon: 'calendar3', noCache: true },
      },
      {
        path: 'daily-classwise',
        component: () => import('@/views/attendance/AttReportDailyClasswise.vue'),
        name: 'Daily/Classwise',
        meta: { title: 'Daily/Classwise', bootstrapIcon: 'calendar3-range', noCache: true },
      },
      {
        path: 'student-yearly',
        component: () => import('@/views/attendance/AttReportStudentYearly.vue'),
        name: 'Student Monthly',
        meta: { title: 'Student Attendance Per Month', bootstrapIcon: 'calendar3-range', noCache: true },
      },
    ],
  },
  {
    path: '/exam',
    component: Layout,
    redirect: '/exam/test',
    meta: {title: 'Assessments', bootstrapIcon: 'journal-text', noCache: true},
    children: [
      {
        path: 'exam',
        component: () => import('@/views/exam/ExamList.vue'),
        name: 'Add Exam',
        meta: {title: 'Exam', bootstrapIcon: 'journal-text', noCache: true},
      },
      {
        path: 'test',
        component: () => import('@/views/exam/TestList.vue'),
        name: 'Add Test',
        meta: {title: 'Test', bootstrapIcon: 'journal-text', noCache: true},
      },
    ],
  },
  {
    path: '/accounts',
    component: Layout,
    redirect: '/accounts/index',
    name: 'Accounts',
    alwaysShow: true,
    meta: {
      title: 'Accounts',
      bootstrapIcon: 'people-fill',
      permissions: ['view menu accounts'],
    },
    children: [
      {
        path: 'index',
        component: () => import('@/views/accounts/Main.vue'),
        name: 'Customer',
        meta: { title: 'Accounts', bootstrapIcon: 'people', noCache: true },
      },
      {
        path: 'transactions',
        component: () => import('@/views/accounts/transactions.vue'),
        name: 'Transactions',
        meta: { title: 'Transactions', bootstrapIcon: 'wallet2', noCache: true },
      },
    ],
  },
  {
    path: '/settings',
    component: Layout,
    redirect: '/settings/Setting',
    children: [
      {
        path: 'settings',
        component: () => import('@/views/settings/Setting.vue'),
        name: 'Setting',
        meta: { title: 'Setting', bootstrapIcon: 'gear-fill', noCache: true },
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
