<?php

namespace Database\Seeders;

class SecurityTranslationSeeder extends SystemTranslationSeeder
{
    public function run(): void
    {
        $this->seedEntries($this->securityEntries());
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function securityEntries(): array
    {
        $entries = [
            'security.users.title' => 'User management',
            'security.users.description' => 'Manage local staff accounts, branch access, roles, and direct privileges.',
            'security.users.create_title' => 'Create user',
            'security.users.edit_title' => 'Edit user',
            'security.users.blocks.profile.title' => 'Profile information',
            'security.users.blocks.profile.description' => 'Update account identity, email, and local access state.',
            'security.users.blocks.password.title' => 'Password',
            'security.users.blocks.password.description' => 'Use a long password that satisfies the local security policy.',
            'security.users.blocks.roles.title' => 'Roles and branches',
            'security.users.blocks.roles.description' => 'Assign roles and local branch access for this user.',
            'security.users.blocks.permissions.title' => 'Direct permissions',
            'security.users.blocks.permissions.description' => 'Grant only explicit exceptions not already covered by roles.',
            'security.users.fields.name' => 'Name',
            'security.users.fields.email' => 'Email',
            'security.users.fields.password' => 'Password',
            'security.users.fields.roles' => 'Roles',
            'security.users.fields.branches' => 'Branches',
            'security.users.fields.is_active' => 'Active',
            'security.users.fields.security_lock_reason' => 'Lock reason',
            'security.users.placeholders.keep_password' => 'Leave empty to keep the current password',
            'security.users.placeholders.new_password' => 'Enter a new password',
            'security.users.help.roles' => 'Select the local access roles for this account.',
            'security.users.help.branches' => 'Restrict this account to specific school branches when needed.',
            'security.users.help.password_policy' => 'Use at least 12 characters with uppercase, lowercase, number, and symbol.',
            'security.users.actions.add' => 'Add user',
            'security.users.actions.impersonate' => 'Impersonate user',
            'security.users.actions.remove' => 'Remove user',
            'security.users.actions.save' => 'Save user',
            'security.users.actions.edit' => 'Edit',
            'security.users.actions.delete' => 'Delete',
            'security.users.confirm_impersonate' => 'You can revert to your original account by logging out.',
            'security.users.confirm_delete' => 'This will delete the local account. The last active Superadmin cannot be deleted.',
            'security.users.messages.saved' => 'User was saved.',
            'security.users.messages.removed' => 'User was removed.',
            'security.users.messages.impersonating' => 'You are now impersonating this user.',
            'security.users.columns.name' => 'Name',
            'security.users.columns.email' => 'Email',
            'security.users.columns.created' => 'Created',
            'security.users.columns.updated' => 'Last edit',
            'security.users.columns.actions' => 'Actions',
            'security.roles.title' => 'Role management',
            'security.roles.description' => 'Manage local roles and permissions for the driving school team.',
            'security.roles.create_title' => 'Create role',
            'security.roles.edit_title' => 'Edit role',
            'security.roles.blocks.role.title' => 'Role',
            'security.roles.blocks.role.description' => 'Define a local role name and stable system slug.',
            'security.roles.blocks.permissions.title' => 'Permissions',
            'security.roles.blocks.permissions.description' => 'Choose the privileges included in this role.',
            'security.roles.fields.name' => 'Name',
            'security.roles.fields.slug' => 'Slug',
            'security.roles.help.name' => 'Visible role name for administrators.',
            'security.roles.help.slug' => 'Stable system identifier for the role.',
            'security.roles.actions.add' => 'Add role',
            'security.roles.actions.save' => 'Save role',
            'security.roles.actions.remove' => 'Remove role',
            'security.roles.messages.saved' => 'Role was saved.',
            'security.roles.messages.removed' => 'Role was removed.',
            'security.roles.columns.name' => 'Name',
            'security.roles.columns.slug' => 'Slug',
            'security.roles.columns.created' => 'Created',
            'security.roles.columns.updated' => 'Last edit',
            'security.filters.roles' => 'Roles',
            'security.profile.fields.current_password' => 'Current password',
            'security.profile.fields.new_password' => 'New password',
            'security.profile.fields.confirm_password' => 'Confirm new password',
            'security.profile.placeholders.current_password' => 'Enter the current password',
            'security.profile.placeholders.new_password' => 'Enter the new password',
            'security.profile.help.current_password' => 'This confirms the password currently used for your account.',
            'security.profile.help.password_policy' => 'Use at least 12 characters with uppercase, lowercase, number, and symbol.',
            'security.profile.messages.updated' => 'Profile updated.',
            'security.profile.messages.password_changed' => 'Password changed.',
            'security.presenter.users' => 'Users',
            'security.presenter.regular_user' => 'Regular user',
            'security.validation.password_policy' => 'The password must be at least 12 characters and include uppercase, lowercase, number, and symbol.',
            'security.validation.last_superadmin' => 'The last active Superadmin account cannot be removed, locked, deactivated, or stripped of the Superadmin role.',
            'security.validation.superadmin_role_protected' => 'The Superadmin role is protected and cannot be renamed, deleted, or weakened.',
            'security.validation.branch_access_invalid' => 'One or more selected branches are invalid.',
            'security.validation.two_factor_not_available' => 'Two-factor setup is not enabled yet, so no secret was stored.',
            'security.validation.account_locked' => 'This account is inactive or locked.',
            'security.validation.user_name_required' => 'Enter the user name.',
            'security.validation.user_email_required' => 'Enter the user email.',
            'security.validation.user_email_invalid' => 'Enter a valid user email.',
            'security.validation.user_email_unique' => 'This email is already used by another account.',
            'security.validation.user_password_required' => 'Enter a password for this account.',
            'security.validation.role_invalid' => 'One or more selected roles are invalid.',
            'security.validation.role_name_required' => 'Enter the role name.',
            'security.validation.role_slug_required' => 'Enter the role slug.',
            'security.validation.role_slug_unique' => 'This role slug is already used.',
            'security.validation.current_password_required' => 'Enter your current password.',
            'security.validation.current_password_invalid' => 'The current password is incorrect.',
            'security.validation.password_confirmed' => 'Confirm the new password.',
            'security.validation.password_different' => 'The new password must be different from the current password.',
            'validation.attributes.security.user_name' => 'user name',
            'validation.attributes.security.user_email' => 'user email',
            'validation.attributes.security.user_password' => 'password',
            'validation.attributes.security.user_roles' => 'roles',
            'validation.attributes.security.user_branches' => 'branches',
            'validation.attributes.security.user_active' => 'active state',
            'validation.attributes.security.lock_reason' => 'lock reason',
            'validation.attributes.security.role_name' => 'role name',
            'validation.attributes.security.role_slug' => 'role slug',
            'validation.attributes.security.permissions' => 'permissions',
        ];

        return collect($entries)
            ->map(fn (string $value, string $key): array => $this->entry('security', $key, $this->labels($value)))
            ->values()
            ->all();
    }

    /**
     * @return array<string, string>
     */
    private function labels(string $english): array
    {
        return [
            'ru' => $english,
            'en' => $english,
            'lt' => $english,
            'pl' => $english,
        ];
    }
}
