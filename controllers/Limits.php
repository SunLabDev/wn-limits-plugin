<?php namespace SunLab\Limits\Controllers;

use Backend\Classes\Controller;
use Backend\Facades\BackendMenu;
use Dotenv\Exception\ValidationException;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Response;
use October\Rain\Support\Facades\Flash;
use October\Rain\Support\Facades\Validator;
use SunLab\Limits\Models\Limit;
use System\Classes\SettingsManager;

/**
 * Limits Back-end Controller
 */
class Limits extends Controller
{
    /**
     * @var array Behaviors that are implemented by this controller.
     */
    public $implement = [
        'Backend.Behaviors.FormController',
        'Backend.Behaviors.ListController'
    ];

    /**
     * @var string Configuration file for the `FormController` behavior.
     */
    public $formConfig = 'config_form.yaml';

    /**
     * @var string Configuration file for the `ListController` behavior.
     */
    public $listConfig = 'config_list.yaml';

    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('October.System', 'system', 'settings');
        SettingsManager::setContext('SunLab.Limits', 'limits');
    }

    public function formBeforeCreate($model)
    {
        $validator = Validator::make(
            post('Limit'),
            [
                'description' => 'required',
                'code' => 'required',
                '_applied_to' => 'required',
                '_applied_to.*.limitable_type' => 'required',
                '_applied_to.*.limitable_id' => 'required',
                '_applied_to.*.maximum' => 'required'
            ]
        );

        if ($validator->fails()) {
            Flash::error($validator->messages()->first());
            abort(406, $validator->messages()->first());
        }
    }

    public function formAfterSave($model)
    {
        // Create as many limit as typed in form (using createMany or looping on related_models)
        $users = [];
        $roles = [];
        foreach (post('Limit._applied_to') as $limitableModel) {
            if ($limitableModel['limitable_type'] === 'User') {
                $users[$limitableModel['limitable_id']] = ['maximum' =>  $limitableModel['maximum']];
            } elseif ($limitableModel['limitable_type'] === 'UserRole') {
                $roles[$limitableModel['limitable_id']] = ['maximum' => $limitableModel['maximum']];
            }
        }

        $model->users()->sync($users);
        $model->roles()->sync($roles);
    }

    /** Pre-populate the repeater fields and values before render on update action
     * @param $formWidget
     * @param $fields
     */
    public function formExtendFields($formWidget, $fields)
    {
        if ($this->action === 'update') {
            $formWidget->model->load('users', 'roles');
            $relatedModels = $formWidget->model->users->merge($formWidget->model->roles);

            $formWidget->data->_applied_to =
                $relatedModels->map(function ($model) {
                    return [
                        'limitable_id' => $model->id,
                        'limitable_type' => class_basename($model),
                        'maximum' => $model->pivot->maximum
                    ];
                })->toArray();
        }
    }
}
