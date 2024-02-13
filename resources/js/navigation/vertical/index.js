export default [
  {
    title: 'Home',
    icon: { icon: 'mdi-home' },
    to: 'index',
    action: 'view',
    subject: 'User',
  },
  {
    title: 'Access Management',
    icon: { icon: 'mdi-account-badge-outline' },
    children: [
      {
        title: 'Users',
        to: { name: 'access-control-user-list' },
        action: 'view',
        subject: 'User',
      },
      {
        title: 'Roles',
        to: { name: 'access-control-role-list' },
        action: 'view',
        subject: 'Role',
      },
      {
        title: 'Permissions',
        to: { name: 'access-control-permission-list' },
        action: 'view',
        subject: 'Permission',
      },
      {
        title: 'Audit Logs',
        to: { name: 'audit-audit-list' },
        action: 'view',
        subject: 'Audit Log',
      },
    ],
  },
]
