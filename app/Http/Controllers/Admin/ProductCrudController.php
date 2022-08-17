<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\ProductRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class ProductCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class ProductCrudController extends CrudController
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
        CRUD::setRoute(config('backpack.base.route_prefix') . '/product');
        CRUD::setEntityNameStrings('Product', 'Products');
        $this->crud->products = [
            [
                'name' => 'Han Waters Satu',
                'price' => 10000000,
                'bp' => 2000000,
                'bs_member' => 500000,
                'bs_l1' => 500000,
                'or_l1' => 500000,
                'bs_l2' => 1000000,
                'or_l2' => 500000,
                'bsi_l3' => 1000000,
                'bsii_l3' => 500000,
                'or_l3' => 1500000,
                'bonus_kepemimpinan' => 2.5,
                'photo_product' => 'waters.jpeg'
            ],
            [
                'name' => 'Han Waters Dua',
                'price' => 9000000,
                'bp' => 1800000,
                'bs_member' => 400000,
                'bs_l1' => 400000,
                'or_l1' => 400000,
                'bs_l2' => 800000,
                'or_l2' => 400000,
                'bsi_l3' => 800000,
                'bsii_l3' => 400000,
                'or_l3' => 1300000,
                'bonus_kepemimpinan' => 2.25,
                'photo_product' => 'waters.jpeg'
            ],
            [
                'name' => 'Han Waters Tiga',
                'price' => 8000000,
                'bp' => 1500000,
                'bs_member' => 300000,
                'bs_l1' => 300000,
                'or_l1' => 300000,
                'bs_l2' => 700000,
                'or_l2' => 300000,
                'bsi_l3' => 700000,
                'bsii_l3' => 300000,
                'or_l3' => 1000000,
                'bonus_kepemimpinan' => 2,
                'photo_product' => 'waters.jpeg'
            ],
            [
                'name' => 'Han Waters Empat',
                'price' => 6000000,
                'bp' => 1000000,
                'bs_member' => 200000,
                'bs_l1' => 200000,
                'or_l1' => 200000,
                'bs_l2' => 500000,
                'or_l2' => 200000,
                'bsi_l3' => 500000,
                'bsii_l3' => 200000,
                'or_l3' => 800000,
                'bonus_kepemimpinan' => 1.5,
                'photo_product' => 'waters.jpeg'
            ]
        ];
    }

    public function getColumns(){
        CRUD::column('name')->orderable(false)->searchLogic(false)->label('Name')
        ->value(function($entry){
            return optional($this->crud->products[$entry->id - 1] ?? null)['name'] ?? null;
        });
        CRUD::column('price')->label('Price')
        ->value(function($entry){
            return 'Rp '. formatNumber(optional($this->crud->products[$entry->id - 1] ?? null)['price'] ?? null);
        });
        CRUD::column('bp')->label('B. Pribadi')
        ->value(function($entry){
            return 'Rp '. formatNumber(optional($this->crud->products[$entry->id - 1] ?? null)['bp'] ?? null);
        });
        CRUD::column('bs_member')->label('B. Sponsor (Member)')
        ->value(function($entry){
            return 'Rp '. formatNumber(optional($this->crud->products[$entry->id - 1] ?? null)['bs_member'] ?? null);
        });
        CRUD::column('bs_l1')->label('B. Sponsor (L1)')  ->value(function($entry){
            return 'Rp '. formatNumber(optional($this->crud->products[$entry->id - 1] ?? null)['bs_l1'] ?? null);
        });
        CRUD::column('or_l1')->label('Overriding (L1)')
        ->value(function($entry){
            return 'Rp '. formatNumber(optional($this->crud->products[$entry->id - 1] ?? null)['or_l1'] ?? null);
        });
        CRUD::column('bs_l2')->label('B. Sponsor (L2)')
        ->value(function($entry){
            return 'Rp '. formatNumber(optional($this->crud->products[$entry->id - 1] ?? null)['bs_l2'] ?? null);
        });
        CRUD::column('or_l2')->label('Overriding (L2)')  ->value(function($entry){
            return 'Rp '. formatNumber(optional($this->crud->products[$entry->id - 1] ?? null)['or_l2'] ?? null);
        });
        CRUD::column('bsi_l3')->label('B. Sponsor I (L3)')
        ->value(function($entry){
            return 'Rp '. formatNumber(optional($this->crud->products[$entry->id - 1] ?? null)['bsi_l3'] ?? null);
        });
        CRUD::column('bsii_l3')->label('B. Sponsor II (L3)')
        ->value(function($entry){
            return 'Rp '. formatNumber(optional($this->crud->products[$entry->id - 1] ?? null)['bsii_l3'] ?? null);
        });
        CRUD::column('or_l3')->label('Overriding (L3)')
        ->value(function($entry){
            return 'Rp '. formatNumber(optional($this->crud->products[$entry->id - 1] ?? null)['or_l3'] ?? null);
        });
        CRUD::column('bonus_kepemimpinan')->label('Bonus Kepemimpinan')
        ->value(function($entry){
            return formatNumber(optional($this->crud->products[$entry->id - 1] ?? null)['bonus_kepemimpinan'] ?? null) . ' %';
        });
        CRUD::column('photo_product')->label('Photo Product')->type('image_preview')
        ->searchLogic(false)->orderable(false)->limit(1000)->url(function($entry){
            return url('images/waters.jpeg');
        });
    }

    /**
     * Define what happens when the List operation is loaded.
     * 
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        $this->crud->viewAfterContent = ['image_preview_helper'];
        $this->crud->firstCellNonFlex = true;
        $this->getColumns();

        /**
         * Columns can be defined using the fluent syntax or array syntax:
         * - CRUD::column('price')->type('number');
         * - CRUD::addColumn(['name' => 'price', 'type' => 'number']); 
         */
    }

    protected function setupShowOperation(){
        $this->crud->viewAfterContent = ['image_preview_helper'];
        $this->crud->set('show.setFromDb', false);
        $this->getColumns();
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
        CRUD::setValidation(ProductRequest::class);

        CRUD::field('name')->label('Name');
        CRUD::field('price')->label('Price')->type('number_format')->prefix('Rp');
        CRUD::field('bp')->label('B. Pribadi')->type('number_format')->prefix('Rp');
        CRUD::field('bs_member')->label('B. Sponsor (Member)')->type('number_format')->prefix('Rp');
        CRUD::field('bs_l1')->label('B. Sponsor (L1)')->type('number_format')->prefix('Rp');
        CRUD::field('or_l1')->label('Overriding (L1)')->type('number_format')->prefix('Rp');
        CRUD::field('bs_l2')->label('B. Sponsor (L2)')->type('number_format')->prefix('Rp');
        CRUD::field('or_l2')->label('Overriding (L2)')->type('number_format')->prefix('Rp');
        CRUD::field('bsi_l3')->label('B. Sponsor I (L3)')->type('number_format')->prefix('Rp');
        CRUD::field('bsii_l3')->label('B. Sponsor II (L3)')->type('number_format')->prefix('Rp');
        CRUD::field('or_l3')->label('Overriding (L3)')->type('number_format')->prefix('Rp');
        CRUD::field('bonus_kepemimpinan')->label('Bonus Kepemimpinan')->type('number_format')->suffix('%')->decimal(3);
        CRUD::field('photo_product')->label('Photo Product')->type('image_fix')->crop(true)->aspect_ratio(0.67)
        ->max_file_size(5000000)->url(function($entry){
            if(isset($entry)){
                return url('images/waters.jpeg');
            }
        });

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

        $fields = ['name', 'price', 'bp', 'bs_member', 'bs_l1', 'or_l1', 'bs_l2', 'or_l2', 'bsi_l3', 'bsii_l3', 'or_l3', 'bonus_kepemimpinan'
        ];

        foreach($fields as $field){
            $this->crud->modifyField($field, ['value' => optional($this->crud->products[$this->data['entry']->id - 1] ?? null)[$field] ?? null]);
        }
        $this->crud->modifyField('photo_product', ['value' => 'waters.jpg']);

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
