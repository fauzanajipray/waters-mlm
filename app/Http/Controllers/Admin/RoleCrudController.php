<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\RoleRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class RoleCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class RoleCrudController extends CrudController
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
        CRUD::setRoute(config('backpack.base.route_prefix') . '/role');
        CRUD::setEntityNameStrings('Role', 'Roles');
    }

    public function getColumns($forShow = false){
        CRUD::column('name')->orderable(false)->searchLogic(false)->value(function($entry){
            if($entry->id == 1){
                return 'Super Admin';
            }
            else if($entry->id == 2){
                return 'Admin';
            }
            else if($entry->id == 3){
                return 'Member';
            }
            else if($entry->id == 4){
                return 'Guest';
            }
        });
        if($forShow){
            CRUD::column('permissions')->type('table')->columns(['no' => 'No', 'name' => 'Name'])->value(function($entry){
                $permissions = [
                    [
                        'no' => 1,
                        'name' => 'Read Transaction'
                    ],
                    [
                        'no' => 2,
                        'name' => 'Create Transaction'
                    ],
                    [
                        'no' => 3,
                        'name' => 'Update Transaction'
                    ],
                    [
                        'no' => 4,
                        'name' => 'Delete Transaction'
                    ]
                ];
                if($entry->id == 1){
                    return $permissions;
                }
                else if($entry->id == 2){
                    array_splice($permissions, -1, 1);
                    return $permissions;
                }
                else if($entry->id == 3){
                    array_splice($permissions, -2, 2);
                    return $permissions;
                }
                else if($entry->id == 4){
                    array_splice($permissions, - 3, 3);
                    return $permissions;
                }
            });
        }
    }

    /**
     * Define what happens when the List operation is loaded.
     * 
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        $this->getColumns();
    }


    protected function setupShowOperation(){
        $this->crud->set('show.setFromDb', false);
        $this->getColumns(true);
        $this->crud->column($this->crud->model->getCreatedAtColumn())->type('datetime');
        $this->crud->column($this->crud->model->getUpdatedAtColumn())->type('datetime');
    }

    /**
     * Define what happens when the Create operation is loaded.
     * 
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        CRUD::setValidation(RoleRequest::class);

        
        CRUD::field('name');

        CRUD::field('permissions')->type('select2_from_array')->allows_multiple(true)->options([
            1 => 'Read Transaction',
            2 => 'Create Transaction',
            3 => 'Update Transaction',
            4 => 'Delete Transaction',
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
    }

    public function store()
    {
        // show a success message
        \Alert::success(trans('backpack::crud.insert_success'))->flash();

        return redirect($this->crud->route);
    }

    public function edit($id)
    {
        $this->crud->hasAccessOrFail('update');
        // get entry ID from Request (makes sure its the last ID for nested resources)
        $id = $this->crud->getCurrentEntryId() ?? $id;
        // get the info for that entry

        $this->data['entry'] = $this->crud->getEntryWithLocale($id);
        $this->crud->setOperationSetting('fields', $this->crud->getUpdateFields());

        $this->data['crud'] = $this->crud;
        $this->data['saveAction'] = $this->crud->getSaveAction();
        $this->data['title'] = $this->crud->getTitle() ?? trans('backpack::crud.edit').' '.$this->crud->entity_name;
        $this->data['id'] = $id;

        $names = ['Super Admin', 'Admin', 'Member', 'Guest'];
        $this->crud->modifyField('name', ['value' => $names[$this->data['entry']->id - ($this->data['entry']->id > 0 ? 1 : 0)] ?? '']);

        $permissions = [1, 2, 3, 4];
        if($this->data['entry']->id == 2){
            array_splice($permissions, -1, 1);
        }
        else if($this->data['entry']->id == 3){
            array_splice($permissions, -2, 2);
        }
        else if($this->data['entry']->id == 4){
            array_splice($permissions, - 3, 3);
        }
        $this->crud->modifyField('permissions', ['value' => $permissions]);

        // load the view from /resources/views/vendor/backpack/crud/ if it exists, otherwise load the one in the package
        return view($this->crud->getEditView(), $this->data);
    }

    public function update()
    {
        // show a success message
        \Alert::success(trans('backpack::crud.update_success'))->flash();

        return redirect($this->crud->route);
    }

    public function destroy($id)
    {
        return 1;
    }
}
