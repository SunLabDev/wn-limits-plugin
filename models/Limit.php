<?php namespace SunLab\Limits\Models;

use Backend\Models\User;
use Backend\Models\UserRole;
use Model;

/**
 * Limit Model
 */
class Limit extends Model
{
    use \October\Rain\Database\Traits\Validation;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'sunlab_limits_limits';

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];

    /**
     * @var array Validation rules for attributes
     */
    public $rules = [
        'description' => 'required',
        'code' => 'required',
    ];

    /**
     * @var array Attributes to be cast to Argon (Carbon) instances
     */
    protected $dates = [
        'created_at',
        'updated_at'
    ];

    /**
     * @var array Relations
     */
    public $morphedByMany = [
        'users' => [
            User::class,
            'name' => 'limitable',
            'table' => 'sunlab_limits_limitables',
            'pivot' => ['maximum']
        ],
        'roles' => [
            UserRole::class,
            'name' => 'limitable',
            'table' => 'sunlab_limits_limitables',
            'pivot' => ['maximum']
        ]
    ];

    public function getLimitableIdOptions($value, $data)
    {
        $dataSource = $data->limitable_type ?? $this->limitable_type;

        if ($dataSource === 'UserRole') {
            return UserRole::all()->pluck('name', 'id');
        }

        if ($dataSource === 'User') {
            return User::where('is_superuser', false)->get()->pluck('login', 'id');
        }

        return [];
    }
}
