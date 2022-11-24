<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\ActivationPaymentsRequest;
use App\Models\ActivationPayments;
use App\Models\Configuration;
use App\Models\Member;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Prologue\Alerts\Facades\Alert;

/**
 * Class ActivationPaymentsCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class ActivationPaymentsCrudController extends CrudController
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
        $this->crud->setModel(\App\Models\ActivationPayments::class);
        $this->crud->setRoute(config('backpack.base.route_prefix') . '/activation-payments');
        $this->crud->setEntityNameStrings('activation payments', 'activation payments');
    }

    /**
     * Define what happens when the List operation is loaded.
     * 
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        $this->crud->column('member_id');
        $this->crud->column('code');
        $this->crud->column('total')->type('number_format')->prefix('Rp. ');
        $this->crud->column('payment_date');

    }

    /**
     * Define what happens when the Create operation is loaded.
     * 
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        $this->crud->setValidation([
            'payment_date' => 'required',
            'total' => 'required|numeric',
            'code' => 'required|unique:activation_payments,code',
            'member_id' => 'required|exists:members,id',
        ]);
        
        $this->crud->addField([
            'name' => 'payment_date',
            'type' => 'datetime_picker',
            'label' => 'Date',
            'date_picker_option' => [
                'todayBtn' => 'linked',
                'format'   => 'Y-m-d H:i:s',
                'language' => 'id'
            ],
        ]);
        $this->crud->addField([
            'name' => 'member_id',
            'type' => 'select2_from_ajax',
            'entity' => 'member',
            'attribute' => 'text',
            'data_source' => url('members/not-activated'),
            'placeholder' => 'Select a member',
        ]);
        $paymentAmount = Configuration::where('key', 'activation_payment_amount')->first()->value;
        $this->crud->addField([
            'name' => 'total',
            'type' => 'number_format',
            'label' => 'Total',
            'attributes' => [
                'readonly' => 'readonly',
            ],
            'prefix' => 'Rp. ',
            'default' => $paymentAmount,
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
        $this->setupCreateOperation();
        $this->crud->setValidation([
            'code' => '',
            'member_id' => '',
        ]);
        $this->crud->removeFields(['code', 'member_id']);
    }

    public function create(){
        $this->crud->hasAccessOrFail('create');
        
        $this->data['crud'] = $this->crud;
        $this->data['fields'] = $this->crud->getCreateFields();
        $this->data['saveAction'] = $this->crud->getSaveAction();
        $this->crud->modifyField('payment_date', [
            'value' => date('Y-m-d H:i:s'),
        ]);
        return view('crud::create', $this->data);
    }

    public function store(Request $request)
    {
        $code = $this->generateCode();
        $request->merge(['code' => $code]);

        DB::beginTransaction();
        try { 
            // Add To Activation Payments
            $activationPayment = ActivationPayments::create($request->all());
            // Update Member Expired at + 2 Years
            $member = $activationPayment->member;
            $config = Configuration::where('key', 'activation_payment_expiration')->first();
            if($member->expired_at){
                if($member->expired_at < date('Y-m-d H:i:s')){
                    if($member->expired_at > $activationPayment->payment_date){
                        $member->expired_at = date('Y-m-d H:i:s', strtotime($member->expired_at . ' + ' . $config->value . ' years'));
                    }else{
                        $member->expired_at = date('Y-m-d H:i:s', strtotime($activationPayment->payment_date . ' + ' . $config->value . ' years'));
                    }
                }else{
                    $member->expired_at = date('Y-m-d H:i:s', strtotime($member->expired_at.' +'.$config->value.' years'));
                }
            } else {
                $member->expired_at = date('Y-m-d H:i:s', strtotime($request->payment_date . ' + '. $config->value .'years'));
            }
            $member->save();
            Alert::success('Success', 'Activation Payment Created');
            DB::commit();
            return redirect($this->crud->route);
        } catch(\Exception $e) {
            DB::rollBack();
            Alert::error('Error', 'Something went wrong' . $e->getMessage())->flash();
            return redirect()->back()->withErrors($e->getMessage())->withInput();
        }
    }

    protected function generateCode() 
    {
        $lastTransaction = ActivationPayments::orderBy('id', 'desc')->first();
        $lastTransactionCode = $lastTransaction->code ?? 'PYM-000000-0000';
        $transactionCode = explode('-', $lastTransactionCode)[2] + 1;
        $transactionCode = 'REG-' . date('ymd') . '-' . str_pad($transactionCode, 4, '0', STR_PAD_LEFT);
        return $transactionCode;
    }

    public function destroy($id)
    {
        $this->crud->hasAccessOrFail('delete');
        DB::beginTransaction();
        try {
            $activationPayment = ActivationPayments::where('id', $id)->first();
            $member = Member::find($activationPayment->member_id);
            $config = Configuration::where('key', 'activation_payment_expiration')->first();
            $expired_at = date('Y-m-d', strtotime($member->expired_at . ' - '. $config->value .' years'));
            if($expired_at < now()){
                $expired_at = null;
            }
            $member->expired_at = $expired_at;
            $member->save();
            Alert::success('Success', 'Activation Payment Deleted');
            DB::commit();
            return $this->crud->delete($id);
        } catch (\Exception $e) {
            DB::rollBack();
            Alert::error('Error', 'Something went wrong' . $e->getMessage())->flash();
            return redirect()->route('activation-payments')->withErrors($e->getMessage())->withInput();
        }
    }

}
