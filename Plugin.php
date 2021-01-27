<?php namespace SunLab\Limits;

use Backend;
use Backend\Models\User;
use Backend\Models\UserRole;
use October\Rain\Database\Relations\Relation;
use SunLab\Limits\Models\Limit;
use System\Classes\PluginBase;
use System\Classes\SettingsManager;

/**
 * Limits Plugin Information File
 */
class Plugin extends PluginBase
{
    /**
     * Returns information about this plugin.
     *
     * @return array
     */
    public function pluginDetails()
    {
        return [
            'code'        => 'Limits',
            'description' => 'No description provided yet...',
            'author'      => 'SunLab',
            'icon'        => 'icon-leaf'
        ];
    }

    /**
     * Boot method, called right before the request route.
     *
     * @return array
     */
    public function boot()
    {
        Relation::morphMap([
            'User' => User::class,
            'UserRole' => UserRole::class,
        ]);

        UserRole::extend(static function ($role) {
            $role->morphToMany['limits'] = [Limit::class, 'name' => 'limitable', 'table' => 'sunlab_limits_limitables'];
        });

        User::extend(static function ($user) {
            $user->morphToMany['limits'] = [Limit::class, 'name' => 'limitable', 'table' => 'sunlab_limits_limitables'];

            $user->addDynamicMethod('hasReachedLimit', function ($code, $amount) use ($user) {
                // If it's a super user, sure it's false
                if ($user->isSuperUser()) {
                    return false;
                }

                // Check for a user limit
                if ($user->whereHas('limits', function ($query) use ($code, $amount) {
                    return $query->where([
                        ['code', $code],
                        ['maximum', '>', $amount]
                    ]);
                })->exists()) {
                    return false;
                }

                // Check for the user's role limit
                if ($user->role()->whereHas('limits', function ($query) use ($code, $amount) {
                    return $query->where([
                        ['code', $code],
                        ['maximum', '>', $amount]
                    ]);
                })->exists()) {
                    return false;
                }

                return true;
            });
        });
    }

    /**
     * Registers any back-end permissions used by this plugin.
     *
     * @return array
     */
    public function registerPermissions()
    {
        return [
            'sunlab.limits.manage_limits' => [
                'tab' => 'SunLab Limits',
                'label' => 'Can manage limits'
            ],
        ];
    }

    public function registerSettings()
    {
        return [
            'limits' => [
                'label'       => 'Limits',
                'description' => 'Manage users and roles limits.',
                'category'    => SettingsManager::CATEGORY_SYSTEM,
                'icon'        => 'icon-signal',
                'url'         => Backend::url('sunlab/limits/limits'),
                'order'       => 400,
                'keywords'    => 'admin user role permission limit',
                'permissions' => ['sunlab.limits.manage_limits']
            ]
        ];
    }
}
