<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\ConfigurationRequest;
use App\Models\Configuration;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Prologue\Alerts\Facades\Alert;

/**
 * Class ConfigurationCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class ConfigurationCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     *
     * @return void
     */
    public function setup()
    {
        if(!backpack_user()->hasPermissionTo('Read Config')){
            $this->crud->denyAccess(['list', 'show']);
        }
        if(!backpack_user()->hasPermissionTo('Update Config')){
            $this->crud->denyAccess(['update']);
        }
        CRUD::setModel(\App\Models\Configuration::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/configuration');
        CRUD::setEntityNameStrings('configuration', 'configurations');
    }

    /**
     * Define what happens when the List operation is loaded.
     *
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        CRUD::column('description');
        CRUD::column('value');
        CRUD::column('key');
        CRUD::column('created_at');
        CRUD::column('updated_at');

        /**
         * Columns can be defined using the fluent syntax or array syntax:
         * - CRUD::column('price')->type('number');
         * - CRUD::addColumn(['name' => 'price', 'type' => 'number']);
         */
    }

    /**
     * Define what happens when the Create operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        CRUD::setValidation(ConfigurationRequest::class);

        CRUD::addField([
            'name' => 'key',
            'label' => 'Key',
            'type' => 'text',
            'attributes' => [
                'placeholder' => 'Key',
                'readonly' => 'readonly',
            ],
        ]);
        CRUD::field('value');
        CRUD::field('description');

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

    public function update(ConfigurationRequest $request)
    {
        $this->crud->hasAccessOrFail('update');

        $request->validate([
            'key' => 'required|unique:configurations,key,' . $request->id,
        ]);
        $requests = request()->all();
        if($requests['key'] == 'activation_payment_expiration' || $requests['key'] == 'activation_payment_expiration'){
            $request->validate([
                'value' => 'numeric',
            ]);
        }
        DB::beginTransaction();
        try {
            if($requests['key'] == 'date_start_komisi_lsi' || $requests['key'] == 'date_start_komisi_pm'){
                $requests['value'] = Carbon::parse($requests['value'])->startOfMonth()->format('Y-m-d');
            }
            $config = Configuration::find($requests['id']);
            $config->update($requests);
            DB::commit();
            Alert::success('Configuration updated!')->flash();
            return redirect($this->crud->route);
        } catch (\Exception $e) {
            DB::rollBack();
            Alert::error('Error ', $e->getMessage())->flash();
            return redirect()->back()->withInput();
        }
    }

    public function edit($id)
    {
        $this->crud->hasAccessOrFail('update');

        $this->crud->setOperationSetting('fields', $this->crud->getUpdateFields());
        $this->data['entry'] = $this->crud->getEntry($id);
        $this->data['crud'] = $this->crud;
        $this->data['fields'] = $this->crud->getUpdateFields($id);
        $this->data['saveAction'] = $this->crud->getSaveAction();
        $this->data['id'] = $id;
        if ($this->data['entry']->key == 'activation_payment_expiration') {
            $this->crud->modifyField('value', [
                'suffix' => 'Year',
                'type' => 'number',
            ]);
        }
        if ($this->data['entry']->key == 'activation_payment_amount') {
            $this->crud->modifyField('value', [
                'prefix' => 'Rp .',
                'type' => 'number_format',
            ]);
        }
        if ($this->data['entry']->key == 'bonus_tax_percentage_npwp' || $this->data['entry']->key == 'bonus_tax_percentage_non_npwp' || $this->data['entry']->key == 'transaction_demokit_discount_percentage') {
            $this->crud->modifyField('value', [
                'suffix' => '%',
                'type' => 'number',
                'attributes' => [
                    'min' => 0,
                    'max' => 100,
                ],
            ]);
        }

        if ($this->data['entry']->key == 'date_start_komisi_pm' || $this->data['entry']->key == 'date_start_komisi_lsi') {

            $this->crud->modifyField('value', [
                'type' => 'date_picker',
                'date_picker_options' => [
                    'todayBtn' => true,
                    'format' => 'yyyy-mm-dd',
                    'language' => 'en'
                ],
            ]);
        }
        return view('crud::edit', $this->data);
    }
}
