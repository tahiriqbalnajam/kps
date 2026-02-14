import { createRouter, createWebHashHistory } from 'vue-router'

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
    path: '/take-test/:chapterid/:question/:lang',
    component: () => import('@/views/exam/TakeTest.vue'),
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

]

export const asyncRoutes = [
  { path: '/:pathMatch(.*)*', name: 'NotFound', redirect: '/404', hidden: true },
  {
    path: '/students',
    component: Layout,
    redirect: 'students/list',
    meta: { title: 'Students', bootstrapIcon: 'mortarboard-fill', noCache: true, permissions: ['view menu students'] },
    children: [
      {
        path: 'list',
        component: () => import('@/views/students/StudentList.vue'),
        name: 'Students List',
        meta: { title: 'Students', bootstrapIcon: 'person-lines-fill', icon: 'person-lines-fill', noCache: true, permissions: ['manage students'] },
      },
      {
        path: 'report/:id',
        hidden: true,
        component: () => import('@/views/students/StudentReport.vue'),
        name: 'Student Report',
        meta: { title: 'Student Report', icon: 'people', noCache: true },
      },
      {
        path: 'observations',
        name: 'student-observations',
        hidden: true,
        component: () => import('@/views/students/components/StudentObservations.vue'),
        meta: { title: 'Student Observations', icon: 'people', noCache: true }
      },
      {
        path: 'observations/create',
        name: 'create-student-observation',
        hidden: true,
        component: () => import('@/views/students/components/CreateStudentObservation.vue'),
        meta: { title: 'Create Student Observation', icon: 'people', noCache: true }
      },
      {
        path: 'observations/:id/edit',
        name: 'edit-student-observation',
        hidden: true,
        component: () => import('@/views/students/components/EditStudentObservation.vue'),
        meta: { title: 'Edit Student Observation', icon: 'people', noCache: true }
      },
      {
        path: 'observations/:id',
        name: 'view-student-observation',
        hidden: true,
        component: () => import('@/views/students/components/ViewStudentObservation.vue'),
        meta: { title: 'View Student Observation', icon: 'people', noCache: true }
      },
    ],
  },
  {
    path: '/parents',
    component: Layout,
    redirect: 'parents/list',
    children: [
      {
        path: 'list',
        component: () => import('@/views/parents/parentlist.vue'),
        name: 'Parents',
        meta: { title: 'Parents', bootstrapIcon: 'person-vcard-fill', noCache: true, permissions: ['view menu parents'] },
      },
    ],
  },
  {
    path: '/teacher',
    component: Layout,
    redirect: 'teacher/list',
    meta: { title: 'Teachers', bootstrapIcon: 'person-gear', noCache: true, permissions: ['view menu teachers'] },
    children: [
      {
        path: 'list',
        component: () => import('@/views/teachers/TeacherList.vue'),
        name: 'Teachers',
        meta: { title: 'Teachers', bootstrapIcon: 'people', noCache: true },
      },
      {
        path: 'teacher-pay',
        component: () => import('@/views/teachers/TeacherPay.vue'),
        name: 'Teacher Pay',
        meta: { title: 'Pay', bootstrapIcon: 'currency-exchange', noCache: true },
      },
      {
        path: 'class-observation',
        component: () => import('@/views/teachers/TeacherObservation.vue'),
        name: 'Teacher Observation',
        meta: { title: 'Obsercation', bootstrapIcon: 'file-spreadsheet', noCache: true },
      },
      {
        path: 'profile/:id',
        hidden: true,
        component: () => import('@/views/teachers/TeacherProfile.vue'),
        name: 'Teacher Profile',
        meta: { title: 'Profile', bootstrapIcon: 'currency-exchange', noCache: true },
      },
    ],
  },
  {
    path: '/academics',
    component: Layout,
    redirect: 'academics/list',
    meta: { title: 'Academics', bootstrapIcon: 'mortarboard-fill', noCache: true, permissions: ['view menu classes'] },
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
      {
        path: 'syllabus',
        meta: { title: 'syllabus', bootstrapIcon: 'person-workspace', noCache: true, permissions: ['view menu classes'] },
        children: [
          {
            path: 'repository',
            name: 'SyllabusRepository',
            component: () => import('@/views/syllabus/SyllabusRepository.vue'),
            meta: { title: 'Syllabus Repository', icon: 'el-icon-folder' }
          },
          {
            path: 'tracking',
            name: 'SyllabusTrackingEntry',
            component: () => import('@/views/syllabus/SyllabusTrackingEntry.vue'),
            meta: { title: 'Syllabus Tracking', icon: 'el-icon-timer' }
          },
          {
            path: 'completion',
            name: 'SyllabusCompletionTracker',
            component: () => import('@/views/syllabus/SyllabusCompletionTracker.vue'),
            meta: { title: 'Syllabus Completion', icon: 'el-icon-check' }
          }
        ]
      },
    ],
  },
  {
    path: '/attendance',
    component: Layout,
    redirect: 'attendance/mark/add',
    meta: { title: 'Attendance', bootstrapIcon: 'calendar-check-fill', noCache: true, permissions: ['view menu attendance'] },
    children: [
      {
        path: 'mark',
        redirect: 'mark/add',
        meta: { title: 'Mark', bootstrapIcon: 'calendar-plus', noCache: true },
        children: [
          {
            path: 'add',
            component: () => import('@/views/attendance/StudentAttendance.vue'),
            name: 'Students',
            meta: { title: 'Students', bootstrapIcon: 'calendar-plus', noCache: true },
          },
          {
            path: 'attendance',
            component: () => import('@/views/teachers/AddAttendance.vue'),
            name: 'Teacher',
            meta: { title: 'Teacher', bootstrapIcon: 'calendar3', noCache: true },
          },
        ],
      },
      {
        path: 'reports',
        redirect: 'reports/report',
        meta: { title: 'Reports', bootstrapIcon: 'calendar-plus', noCache: true },
        children: [
          {
            path: 'report',
            component: () => import('@/views/attendance/AttendanceReport.vue'),
            name: 'AttendanceReport',
            meta: {
              title: 'Grand Report',
              bootstrapIcon: 'graph-up',
              permissions: ['view menu attendance']
            }
          },
          {
            path: 'students-monthly-report',
            component: () => import('@/views/attendance/StudentsAttMonthlyReport.vue'),
            name: 'Monthly/Classwise',
            meta: { title: 'Monthly/Classwise', bootstrapIcon: 'calendar3', noCache: true },
          },
          {
            path: 'daily-classwise',
            component: () => import('@/views/attendance/AttReportDailyClasswise.vue'),
            name: 'Daily/Classwise',
            meta: { title: 'Daily/Classwise', bootstrapIcon: 'calendar-date-fill', noCache: true },
          },
          {
            path: 'student-yearly',
            component: () => import('@/views/attendance/AttReportStudentYearly.vue'),
            name: 'Any Student',
            meta: { title: 'Any Student', bootstrapIcon: 'calendar3-range', noCache: true },
          },
          {
            path: 'attendance-graph',
            component: () => import('@/views/attendance/DailyAttendanceGraph.vue'),
            name: 'AttendanceGraph',
            meta: {
              title: 'Daily Graph',
              bootstrapIcon: 'bar-chart-line-fill',
              permissions: ['view menu attendance']
            }
          },
          {
            path: 'absent-foreach-class',
            component: () => import('@/views/attendance/AbsentForeachClass.vue'),
            name: 'Absent per class',
            meta: { title: 'Absent Per Class', bootstrapIcon: 'calendar-minus-fill', noCache: true },
          },
          {
            path: 'teacher-att-report',
            component: () => import('@/views/teachers/TeacherMonthlyAttReport.vue'),
            name: 'Teacher Monthly',
            meta: { title: 'Teachers Monthly', bootstrapIcon: 'calendar-check-fill', noCache: true },
          },
        ],
      },
      {
        path: 'online-attendance',
        component: () => import('@/views/teachers/QrcodeAtt.vue'),
        name: 'Online Attendance',
        meta: { title: 'Online Attendance', bootstrapIcon: 'camera-fill', noCache: true },
      },
      {
        path: 'holidays',
        component: () => import('@/views/attendance/Holidays.vue'),
        name: 'Manage Holidays',
        meta: { title: 'Manage Holidays', bootstrapIcon: 'calendar3-range', noCache: true },
      },
    ],
  },
  {
    path: '/finance',
    component: Layout,
    redirect: '/finance/accounting/dashboard',
    meta: { title: 'Finanace', bootstrapIcon: 'credit-card-2-back-fill', noCache: true, permissions: ['view menu timetabel'] },
    children: [
      {
        path: 'fee',
        redirect: 'fee/paidlist',
        meta: { title: 'Fee', bootstrapIcon: 'currency-dollar', noCache: true, permissions: ['view menu fee'] },
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
          {
            path: 'feevoucher',
            component: () => import('@/views/fee/FeeVoucher.vue'),
            name: 'Generate',
            meta: { title: 'Generate', bootstrapIcon: 'file-earmark-text', noCache: true },
          },
          {
            path: 'manage',
            component: () => import('@/views/fee/FeeManage.vue'),
            name: 'Manage',
            meta: { title: 'Manage', bootstrapIcon: 'list-check', noCache: true },
          },
        ],
      },
      {
        path: 'accounting',
        redirect: 'accounting/dashboard',
        meta: { title: 'Accounting', bootstrapIcon: 'calculator', noCache: true, permissions: ['view menu accounting'] },
        children: [
          {
            path: 'dashboard',
            component: () => import('@/views/accounting/Dashboard.vue'),
            name: 'Accounting Dashboard',
            meta: { title: 'Dashboard', bootstrapIcon: 'speedometer2', noCache: true },
          },
          {
            path: 'transactions',
            component: () => import('@/views/accounting/TransactionList.vue'),
            name: 'Transactions1',
            meta: { title: 'Transactions', bootstrapIcon: 'list-ul', noCache: true },
          },
          {
            path: 'income',
            component: () => import('@/views/accounting/IncomeForm.vue'),
            name: 'Add Income',
            meta: { title: 'Add Income', bootstrapIcon: 'plus-circle', noCache: true },
          },
          {
            path: 'expense',
            component: () => import('@/views/accounting/ExpenseForm.vue'),
            name: 'Add Expense',
            meta: { title: 'Add Expense', bootstrapIcon: 'dash-circle', noCache: true },
          },
        ],
      },
      {
        path: 'accounts',
        redirect: 'accounts/index',
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
    ],
  },
  {
    path: '/exam',
    component: Layout,
    redirect: 'exam/test',
    meta: { title: 'Assessments', bootstrapIcon: 'journal-text', noCache: true, permissions: ['view menu exam'], },
    children: [
      {
        path: 'exam',
        component: () => import('@/views/exam/ExamList.vue'),
        name: 'Add Exam',
        meta: { title: 'Exam', bootstrapIcon: 'clipboard2-pulse', noCache: true },
      },
      {
        path: 'test',
        component: () => import('@/views/exam/TestList.vue'),
        name: 'Add Test',
        meta: { title: 'Test', bootstrapIcon: 'clipboard2-data', noCache: true },
      },
      {
        path: 'index',
        component: () => import('@/views/exam/Chapters.vue'),
        name: 'Generate Test',
        meta: { title: 'Chapters', bootstrapIcon: 'book', noCache: true },
      },
      {
        path: 'chapter_options/:id',
        hidden: true,
        component: () => import('@/views/exam/ChapterQuestions.vue'),
        name: 'Options',
        meta: { title: 'options', bootstrapIcon: 'journal-text', noCache: true },
      },
      {
        path: 'low-performance',
        component: () => import('@/views/exam/LowPerformance.vue'),
        name: 'Low Performance',
        meta: { title: 'Low Performance', bootstrapIcon: 'graph-down-arrow', noCache: true },
      },
    ],
  },
  {
    path: '/communication',
    component: Layout,
    redirect: 'communication/queue',
    meta: { title: 'communication', bootstrapIcon: 'chat-right-text-fill', noCache: true, permissions: ['view menu timetabel'] },
    children: [
      {
        path: 'queue',
        component: () => import('@/views/sms/Queue.vue'),
        name: 'Whatsapp Queue',
        meta: { title: 'Whatsapp Queue', bootstrapIcon: 'whatsapp', noCache: true },
      },
      {
        path: 'complaints',
        component: () => import('@/views/complaint/ComplaintManagement.vue'),
        name: 'Complaints',
        meta: { title: 'Complaints', bootstrapIcon: 'exclamation-triangle-fill', noCache: true },
      },
    ],
  },
  {
    path: '/timetable',
    component: Layout,
    redirect: 'timetable/create',
    meta: { title: 'TimeTable', bootstrapIcon: 'clock-fill', noCache: true, permissions: ['view menu timetabel'] },
    children: [
      {
        path: 'generator',
        component: () => import('@/views/timetable/TimeTable.vue'),
        name: 'Generat',
        meta: { title: 'Generate Timetable', bootstrapIcon: 'calendar3', noCache: true },
      },
      {
        path: 'periods',
        component: () => import('@/views/timetable/Periods.vue'),
        name: 'Periods',
        meta: { title: 'Add/Edit Periods', bootstrapIcon: 'watch', noCache: true },
      },
    ],
  },
  {
    path: '/profile',
    component: Layout,
    redirect: 'profile/edit',
    children: [
      {
        path: 'edit',
        component: () => import('@/views/users/SelfProfile.vue'),
        name: 'SelfProfile',
        meta: { title: 'userProfile', bootstrapIcon: 'person-circle', noCache: true },
      },
    ],
  },
  adminRoutes,
  errorRoutes,
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
