@extends(backpack_view('layouts.top_left'))

@php
  $defaultBreadcrumbs = [
    trans('backpack::crud.admin') => backpack_url('dashboard'),
    $crud->entity_name_plural => url($crud->route),
    'Moderate' => false,
  ];

  // if breadcrumbs aren't defined in the CrudController, use the default breadcrumbs
  $breadcrumbs = $breadcrumbs ?? $defaultBreadcrumbs;
@endphp

@section('header')
  <section class="container-fluid ">
    <a href="javascript: window.print();" class="btn float-right"><i class="la la-print"></i></a>
    <h2>
        <span class="text-capitalize">{!! $crud->getHeading() ?? $crud->entity_name_plural !!}</span>
        <small class="d-print-none">{!! $crud->getSubheading() ?? 'Add '.$crud->entity_name !!}.</small>

        @if ($crud->hasAccess('list'))
          <small><a href="{{ url($crud->route) }}" class="hidden-print font-sm d-print-none"><i class="fa fa-angle-double-left"></i> {{ trans('backpack::crud.back_to_all') }} <span>{{ $crud->entity_name_plural }}</span></a></small>
        @endif
    </h2>
  </section>
@endsection

@section('content')
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header" style="background: #f9fbfd; font-weight:bold;">
                Header
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <tbody>
                        <tr>
                            <th>Invoice Code</th>
                            <td>{{ $entry->code }}</td>
                        </tr>
                        <tr>
                            <th>Transaction Date</th>
                            <td>{{ $entry->transaction_date }}</td>
                        </tr>
                        <tr>
                            <th>Shipping Address</th>
                            <td>{{ $entry->shipping_address ?? '-'  }}</td>
                        </tr>
                        <tr>
                            <th>Unique Member</th>
                            <td>{{ $entry->member_numb }}</td>
                        </tr>
                        <tr>
                            <th>Member Name</th>
                            <td>{{ $entry->member_name }}</td>
                        </tr>
                        <tr>
                            <th>Level</th>
                            <td>{{ $entry->level->name }}</td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td>
                                @if ($entry->status_paid)
                                    <span class="badge badge-success">Lunas</span>
                                @else
                                    <span class="badge badge-danger">Belum Lunas</span>
                                @endif
                            </td>
                        </tr>
                        @if ($entry->nsi)
                        <tr>
                            <th>NSI</th>
                            <td>
                                {{ App\Models\Member::select(['name'])->find($entry->nsi)->name }}
                            </td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header" style="background: #f9fbfd; font-weight:bold;">
                Customer
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <tbody>
                        <tr>
                            <th>Name</th>
                            <td>{{ $entry->customer->name }}</td>
                        </tr>
                        <tr>
                            <th>Address</th>
                            <td>{{ $entry->customer->address }}</td>
                        </tr>
                        <tr>
                            <th>City</th>
                            <td>{{ $entry->customer->city ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Phone</th>
                            <td>{{ $entry->customer->phone }}</td>
                        </tr>
                        <tr>
                            <th>Notes</th>
                            <td>{{ $entry->shipping_notes ?? '-' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-12">
        <div class="card">
            <div class="card-header" style="background: #f9fbfd; font-weight:bold;">
                Products
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <tbody>
                        <tr>
                            <th>Branch</th>
                            <td>{{ $entry->stock_from }}</td>
                        </tr>
                    </tbody>
                </table>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Qty</th>
                            <th>Name</th>
                            <th>Model</th>
                            <th>Capacity</th>
                            <th>Price</th>
                            @if ($entry->type == 'Demokit' || $entry->type == 'Display' || $entry->type == 'Bebas Putus')
                                <th>Discount</th>
                            @endif
                            <th>Subtotal</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($products as $item)
                            <tr>
                                <td>{{ $item->quantity }}</td>
                                <td>{{ $item->name }}</td>
                                <td>{{ $item->model ?? '-' }}</td>
                                <td>{{ $item->capacity ?? '-' }}</td>
                                <td>Rp. {{ number_format($item->price, 2, ',', '.') }}</td>
                                @if ($entry->type == 'Demokit' || $entry->type == 'Display')
                                    <td>{{ $item->discount_percentage }} %</td>
                                    <td>Rp. {{ number_format($item->price * $item->quantity - ($item->price * $item->quantity * $item->discount_percentage / 100), 2, ',', '.') }}</td>
                                @elseif($entry->type == 'Bebas Putus')
                                    @if ($item->discount_percentage > 0)
                                        <td>{{ $item->discount_percentage }} %</td>
                                        <td>Rp. {{ number_format($item->price * $item->quantity - ($item->price * $item->quantity * $item->discount_percentage / 100), 2, ',', '.') }}</td>
                                    @else
                                        <td>Rp. {{ number_format($item->discount_amount, 2, ',', '.') }}</td>
                                        <td>Rp. {{ number_format($item->price * $item->quantity - $item->discount_amount, 2, ',', '.') }}</td>
                                    @endif
                                @else
                                    <td>Rp. {{ number_format($item->price * $item->quantity, 2, ',', '.') }}</td>
                                @endif
                                <td>{{ $item->product_notes ?? '-'}}</td>
                            </tr>
                        @endforeach
                        <tr>
                            <td colspan="@if($entry->type == 'Demokit' || $entry->type == 'Display' || $entry->type == 'Bebas Putus') 6 @else 5 @endif" style="text-align: right; font-weight: bold;">Total</td>
                            <td>Rp. {{ number_format($entry->total_price, 2, ',', '.') }}</td>

                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-12">
        <div class="card">
            <div class="card-header" style="background: #f9fbfd; font-weight:bold;">
                Payments
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Date</th>
                            <th>Payment Type</th>
                            <th>Payment Method</th>
                            <th>Account Number</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($entry->transactionPayments as $item)
                            <tr>
                                <td>
                                    <a href="{{ url('transaction-payment/' . $item->id . "/show") }}" target="_blank">{{ $item->code }}</a>
                                </td>
                                <td>{{ $item->payment_date }}</td>
                                <td>{{ $item->type }}</td>
                                <td>{{ $item->payment_name }}</td>
                                <td>{{ $item->payment_account_number }}</td>
                                <td>Rp. {{ number_format($item->amount, 2, ',', '.') }}</td>
                            </tr>
                        @endforeach
                        <tr>
                            <td colspan="5" style="text-align: right; font-weight: bold;">Total</td>
                            <td>Rp. {{ number_format($entry->transactionPayments->sum('amount'), 2, ',', '.') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-12 d-print-none">
        <div class="card">
            <div class="card-body">
                <table class="table table-bordered">
                    <tbody>
                        @if ($crud->buttons()->where('stack', 'line')->count())
                            <tr>
                                <td><strong>{{ trans('backpack::crud.actions') }}</strong></td>
                                <td colspan="5">
                                    @include('crud::inc.button_stack', ['stack' => 'line'])
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection
