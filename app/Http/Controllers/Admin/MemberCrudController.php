<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Http\Requests\MemberRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Barryvdh\DomPDF\Facade\Pdf as PDF;

/**
 * Class MemberCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class MemberCrudController extends CrudController
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
        CRUD::setRoute(config('backpack.base.route_prefix') . '/member');
        CRUD::setEntityNameStrings('member', 'members');

        $this->crud->members = [
            [
                'no_member' => '5000-2000-1000',
                'ktp_sim' => 1234,
                'level' => 'L3',
                'role' => 'Super Admin',
                'jenis_kelamin' => 'Male',
                'mobile_phone' => '0811234',
                'alamat' => 'Jalan Beruang No 1',
                'upline' => null,
                'expired' => '1 Jun 2024'
            ],
            [
                'no_member' => '5000-2001-2000',
                'ktp_sim' => 5678,
                'level' => 'L2',
                'role' => 'Admin',
                'jenis_kelamin' => 'Male',
                'mobile_phone' => '0815678',
                'alamat' => 'Jalan Hasanuddin No 43',
                'upline' => User::where('id', 1)->first()->name ?? null,
                'expired' => '14 Jan 2024'
            ],
            [
                'no_member' => '5000-2002-2000',
                'ktp_sim' => 9101112,
                'level' => 'L1',
                'role' => 'Admin',
                'jenis_kelamin' => 'Male',
                'mobile_phone' => '0819101112',
                'alamat' => 'Jalan Kemerdekaan No 45',
                'upline' => User::where('id', 1)->first()->name ?? null,
                'expired' => '19 Oct 2025'
            ],
            [
                'no_member' => '5000-2001-2000',
                'ktp_sim' => 13141516,
                'level' => 'Member',
                'role' => 'Member',
                'jenis_kelamin' => 'Male',
                'mobile_phone' => '08113141516',
                'alamat' => 'Jalan Mangga No 17',
                'upline' => User::where('id', 1)->first()->name ?? null,
                'expired' => '23 Mar 2025'
            ],
        ];
    }

    public function getColumns(){
        CRUD::column('no_member')->label('No. Member')->value(function($entry){
            return optional($this->crud->members[$entry->id - 1] ?? null)['no_member'] ?? null;
        });
        CRUD::column('ktp_sim')->label('KTP / SIM')
        ->value(function($entry){
            return optional($this->crud->members[$entry->id - 1] ?? null)['ktp_sim'] ?? null;
        });
        CRUD::column('name')->orderable(false)->searchLogic(false)->label('Name');
        CRUD::column('level')->label('Level')
        ->value(function($entry){
            return optional($this->crud->members[$entry->id - 1] ?? null)['level'] ?? null;
        });
        CRUD::column('role')->label('Role')
        ->value(function($entry){
            return optional($this->crud->members[$entry->id - 1] ?? null)['role'] ?? null;
        });
        CRUD::column('jenis_kelamin')->label('Gender')
        ->value(function($entry){
            return optional($this->crud->members[$entry->id - 1] ?? null)['jenis_kelamin'] ?? null;
        });
        CRUD::column('mobile_phone')->label('Mobile Phone')->value(function($entry){
            return optional($this->crud->members[$entry->id - 1] ?? null)['mobile_phone'] ?? null;
        });
        CRUD::column('email')->orderable(false)->searchLogic(false)->label('Email');
        CRUD::column('alamat')->label('Address')->value(function($entry){
            return optional($this->crud->members[$entry->id - 1] ?? null)['alamat'] ?? null;
        });
        CRUD::column('upline')->label('Upline')->value(function($entry){
            return optional($this->crud->members[$entry->id - 1] ?? null)['upline'] ?? null;
        });
        CRUD::column('expired')->label('Expired')->value(function($entry){
            return optional($this->crud->members[$entry->id - 1] ?? null)['expired'] ?? null;
        });
        CRUD::column('photo_member')->label('Photo Member')->type('image_preview')
        ->searchLogic(false)->orderable(false)->limit(1000)->url(function($entry){
            return url('images/profile.jpg');
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
        $this->crud->addButtonFromModelFunction('line', 'cardMember', 'cardMember', 'beginning');
        $this->crud->addButtonFromModelFunction('line', 'reportMember', 'reportMember', 'beginning');
    }

    protected function setupShowOperation(){
        $this->crud->set('show.setFromDb', false);
        $this->crud->viewAfterContent = ['image_preview_helper'];
        $this->getColumns(true);
        $this->crud->column($this->crud->model->getCreatedAtColumn())->type('datetime');
        $this->crud->column($this->crud->model->getUpdatedAtColumn())->type('datetime');

        $this->crud->addButtonFromModelFunction('line', 'cardMember', 'cardMember', 'beginning');
        $this->crud->addButtonFromModelFunction('line', 'reportMember', 'reportMember', 'beginning');
    }

    /**
     * Define what happens when the Create operation is loaded.
     * 
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        CRUD::setValidation(MemberRequest::class);

        CRUD::field('ktp_sim')->label('NIK / SIM');
        CRUD::field('name')->label('Name');
        CRUD::field('password')->label('Password')->type('password');
        CRUD::field('confirmation_password')->label('Password Confirmation')->type('password');
        CRUD::field('role')->label('Role')
        ->type('select2_from_array')->options([
            'Super Admin' => 'Super Admin',
            'Admin' => 'Admin',
            'Member' => 'Member',
            'Guest' => 'Guest'
        ]);
        CRUD::field('jenis_kelamin')->label('Gender')
        ->type('select2_from_array')->options(['Male' => 'Male', 'Female' => 'Female']);
        CRUD::field('mobile_phone')->label('Mobile Phone');
        CRUD::field('email')->label('Email');
        CRUD::field('alamat')->label('Address');
        CRUD::field('upline')->label('Upline')
        ->type('select2_from_array')->options(User::select('name')->get()->mapWithKeys(function($item) {
            return [$item['name'] => $item['name']];
        }));

        CRUD::field('photo_member')->label('Photo Member')->type('image_fix')->crop(true)->aspect_ratio(0.67)
        ->max_file_size(5000000)->url(function($entry){
            if(isset($entry)){
                return url('images/profile.jpg');
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

        $fields = ['ktp_sim', 
        'role', 'jenis_kelamin', 'mobile_phone', 'alamat', 'upline'
        ];

        foreach($fields as $field){
            $this->crud->modifyField($field, ['value' => optional($this->crud->members[$this->data['entry']->id - 1] ?? null)[$field] ?? null]);
        }
        $this->crud->modifyField('photo_member', ['value' => 'profile.jpg']);

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

    public function downloadCardMember($id) {
        $user = User::where('id', $id)->firstOrFail();

        $noMember = optional($this->crud->members[$user->id - 1] ?? null)['no_member'] ?? '-';
        $title = "Card Member ({$noMember} - {$user->name})";
        $pdf = PDF::loadView('member.card_member_pdf', ['user' => $user, 'title' => $title, 'member' => optional($this->crud->members[$user->id - 1] ?? null)]);
        return $pdf->download($title . ".pdf");
    }

    public function reportMember($id){
        $user = User::where('id', $id)->firstOrFail();

        $noMember = optional($this->crud->members[$user->id - 1] ?? null)['no_member'] ?? '-';
        return view('member.report_member', ['title' => "Report Member ({$noMember} - {$user->name})", 'user' => $user]);
    }
}
