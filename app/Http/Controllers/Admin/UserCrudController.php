<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\UserRequest;
use App\Http\Requests\UserRequestUpdate;
use App\Models\ModelHasRole;
use App\Models\Role;
use App\Models\User;
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
       $this->crud->setModel(\App\Models\User::class);
       $this->crud->setRoute(config('backpack.base.route_prefix') . '/user');
       $this->crud->setEntityNameStrings('user', 'users');
    }

    /**
     * Define what happens when the List operation is loaded.
     * 
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        $this->crud->column('id')->label('ID');
        $this->crud->column('name');
        $this->crud->column('email');
        $this->crud->addColumn([
            'name' => 'role_id',
            'label' => "Role", // Table column heading
            'type' => 'relationship',
            'entity' => 'role', // the method that defines the relationship in your Model
            'attribute' => 'name'
        ]);
        
        $this->crud->addFilter(
            [
                'name'  => 'role',
                'type'  => 'dropdown',
                'label' => 'Role',
            ],
            Role::all()->pluck('name', 'id')->toArray(),
            function ($value) { // if the filter is active
                $this->crud->addClause('whereHas', 'roles', function ($query) use ($value) {
                    $query->where('role_id', '=', $value);
                });
            }
        );
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
        $this->crud->setValidation(UserRequest::class);
    
        $this->crud->field('name');
        $this->crud->field('email');
        $this->crud->field('password');
        $this->crud->addField([
             'name' => 'password_confirmation',
             'type' => 'password',
             'label' => 'Confirm Password',
        ]); 
        $this->crud->addField([
            'name'        => 'role_id', // the name of the db column
            'label'       => 'Role', // the input label
            'type'        => 'relationship',
            'entity'      => 'role', // the method that defines the relationship in your Model
            'attribute'   => 'name', // foreign key attribute that is shown to user
            'model'       => "App\Models\Role", // foreign key model
            'pivot'       => true, // on create&update, do you need to add/delete pivot table entries?
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
        $this->crud->field('name');
        $this->crud->field('email');
        $this->crud->field('password');
        $this->crud->addField([
             'name' => 'password_confirmation',
             'type' => 'password',
             'label' => 'Confirm Password',
         ]);
         $this->crud->addField([
             'name'        => 'role_id', // the name of the db column
             'label'       => 'Role', // the input label
             'type'        => 'relationship',
             'entity'      => 'role', // the method that defines the relationship in your Model
             'attribute'   => 'name', // foreign key attribute that is shown to user
             'model'       => "App\Models\Role", // foreign key model
             'pivot'       => true, // on create&update, do you need to add/delete pivot table entries?
         ]);
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
        $this->crud->column('updated_at');
        $this->crud->column('created_at');
    }

    protected function store(Request $request) {
        $this->crud->setRequest($this->crud->validateRequest());
        $password = bcrypt($request->password);
        $request->merge(['password' => $password]); 
        $user = $this->crud->create($request->all());
        if ($this->crud->getRequest()->role_id) {
            $role_id = $this->crud->getRequest()->role_id;
            $user_id = $user->id;
            
            ModelHasRole::where('model_id', $user_id)->delete();
            ModelHasRole::create([
                'role_id' => $role_id,
                'model_type' => 'App\Models\User',
                'model_id' => $user_id
            ]);
        }
        Alert::success('User has been created successfully')->flash();
        return redirect()->route('user.index');
    }

    protected function update(Request $request) {
        $this->crud->setValidation(UserRequestUpdate::class);
        $this->crud->setRequest($this->crud->validateRequest());
        if($request->password) {
            $password = bcrypt($request->password);
            $request->merge(['password' => $password]);
        } else {
            $request->request->remove('password');
            $request->request->remove('password_confirmation');
        }
        $user = $this->crud->update($request->id, $request->all());
        
        if ($this->crud->getRequest()->role_id) {
            $role_id = $this->crud->getRequest()->role_id;
            $user_id = $user->id;
            
            ModelHasRole::where('model_id', $user_id)->delete();
            ModelHasRole::create([
                'role_id' => $role_id,
                'model_type' => 'App\Models\User',
                'model_id' => $user_id
            ]);
        }
        Alert::success('User has been updated successfully')->flash();
        return redirect()->route('user.index');
    }

    public function create()
    {
        $this->crud->hasAccessOrFail('create');

        $this->data['crud'] = $this->crud;
        $this->data['fields'] = $this->crud->getCreateFields();
        $this->data['saveAction'] = $this->crud->getSaveAction();
        $this->crud->modifyField('role_id', [ 'value' => 2 ]);
        return view('crud::create', $this->data);
    }
}
