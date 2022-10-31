<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\UserRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Illuminate\Console\View\Components\Alert;
use Illuminate\Http\Request;

/**
 * Class UserCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class UserCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     * 
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\App\Models\User::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/user');
        CRUD::setEntityNameStrings('user', 'users');

    }

    /**
     * Define what happens when the List operation is loaded.
     * 
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        CRUD::column('name');
        CRUD::column('email');
        CRUD::column('password');

        $this->crud->addButtonFromModelFunction('line', 'register_member', 'registerMember', 'beginning');
        // $this->crud->addButtonFromView('line', 'register_member', 'register_member', 'end');

    }

    /**
     * Define what happens when the Create operation is loaded.
     * 
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        CRUD::setValidation([
            'name' => 'required|min:2',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6|confirmed',
            'password_confirmation' => 'required|min:6',
        ]);

        CRUD::field('name');
        CRUD::field('email');
        CRUD::field('password');
        CRUD::addField([
            'name' => 'password_confirmation',
            'type' => 'password',
            'label' => 'Confirm Password',
        ]);

        /**
         * Fields can be defined using the fluent syntax or array syntax:
         * - CRUD::field('price')->type('number');
         * - CRUD::addField(['name' => 'price', 'type' => 'number'])); 
         */
    }

    /**
     * Define what happens when the Update operation is loaded.
     * 
     * @see https://backpackforlaravel.com/docs/crud-operation-update
     * @return void
     */
    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
        $this->crud->addField([
            'label' => 'Current Password',
            'name' => 'current_password',
            'type' => 'password',
            'value' => 'current_password',
        ])->afterField('email');
        $this->crud->setValidation([
            // validation Passwrod Old 
            'current_password' => ['required', 'min:6', function ($attribute, $value, $fail) {
                $current_password = \App\Models\User::find($this->crud->getCurrentEntryId())->password;
                if (!\Hash::check($value, $current_password)) {
                    return $fail('The current password is incorrect.');
                }
            }],
        ]);
    }

    /**
     * Define what happens when the Show operation is loaded.
     * 
     * @see https://backpackforlaravel.com/docs/crud-operation-show
     * @return void
     */
    protected function setupShowOperation() {
        $this->setupListOperation();        
    }

    protected function store(Request $request) {
        $this->crud->setRequest($this->crud->validateRequest());
        //bcypt password
        $password = bcrypt($request->password);
        $request->merge(['password' => $password]);
        
        $user = $this->crud->create($request->all());
        return redirect()->route('user.index');
    }

    protected function update(Request $request) {
        // validate request data except email
        $this->crud->setValidation([
            'name' => 'required|min:2',
            'email' => ['required', 'email', \Illuminate\Validation\Rule::unique('users')->ignore($request->id)],
            'password' => 'required|min:6|confirmed',
            'password_confirmation' => 'required|min:6',
        ]);
        $this->crud->setRequest($this->crud->validateRequest());
        //bcypt password
        $password = bcrypt($request->password);
        $request->merge(['password' => $password]);
        
        $user = $this->crud->update($request->id, $request->all());
        return redirect()->route('user.index');
    }
}
