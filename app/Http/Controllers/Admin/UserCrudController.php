<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\UserRequest;
use App\Http\Requests\UserRequestUpdate;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Illuminate\Http\Request;
use Prologue\Alerts\Facades\Alert;

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

        // TODO: Add Register Member Button
        // $this->crud->addButtonFromModelFunction('line', 'register_member', 'registerMember', 'beginning');
    }

    /**
     * Define what happens when the Create operation is loaded.
     * 
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        CRUD::setValidation(UserRequest::class);

        CRUD::field('name');
        CRUD::field('email');
        CRUD::field('password');
        CRUD::addField([
            'name' => 'password_confirmation',
            'type' => 'password',
            'label' => 'Confirm Password',
        ]);
    }

    /**
     * Define what happens when the Update operation is loaded.
     * 
     * @see https://backpackforlaravel.com/docs/crud-operation-update
     * @return void
     */
    protected function setupUpdateOperation()
    {
        $this->crud->setValidation(UserRequestUpdate::class);
        $this->setupCreateOperation();
        $this->crud->setValidation([      
            'email' => ['required', 'email', \Illuminate\Validation\Rule::unique('users')->ignore(request()->id)],
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
        $password = bcrypt($request->password);
        $request->merge(['password' => $password]);        
        $this->crud->create($request->all());
        Alert::success('User has been created successfully')->flash();
        return redirect()->route('user.index');
    }

    protected function update(Request $request) {
        $this->crud->setValidation(UserRequestUpdate::class);
        $this->crud->setRequest($this->crud->validateRequest());
        $password = bcrypt($request->password);
        $request->merge(['password' => $password]);
        $this->crud->update($request->id, $request->all());
        Alert::success('User has been updated successfully')->flash();
        return redirect()->route('user.index');
    }
}
