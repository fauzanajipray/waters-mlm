@extends(backpack_view('blank'))

@php
  $defaultBreadcrumbs = [
    trans('backpack::crud.admin') => url(config('backpack.base.route_prefix'), 'dashboard'),
    $crud->entity_name_plural => url($crud->route),
    trans('backpack::crud.list') => false,
  ];

  // if breadcrumbs aren't defined in the CrudController, use the default breadcrumbs
  $breadcrumbs = $breadcrumbs ?? $defaultBreadcrumbs;
@endphp

@section('header')
  <div class="container-fluid">
    <h2>
      <span class="text-capitalize">{!! $crud->getHeading() ?? $crud->entity_name_plural !!}</span>
      <small id="datatable_info_stack">{!! $crud->getSubheading() ?? '' !!}</small>
    </h2>
  </div>
@endsection

@section('content')
  {{-- Default box --}}
  <div class="row">
    {{-- Error Session --}}
    @if (session()->has('error'))
      <div class="col-md-12">
        <div class="alert alert-danger">
          {{ session()->get('error') }}
        </div>
      </div>
    @endif
      {{-- THE ACTUAL CONTENT --}}
      <div class="col-md-12">
        <div class="card">
            <div class="card-header" style="background: #f9fbfd; font-weight:bold;">
                Summary
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <tbody>
                        <tr>
                          <th>Period</th>
                          <td id="period-name"></td>
                        </tr>
                        <tr>
                            <th>Branch</th>
                            <td>{{ $crud->stock->branch->name }}</td>
                        </tr>
                        <tr>
                            <th>Product</th>
                            <td>{{ $crud->stock->product->name }} - {{ $crud->stock->product->model }}</td>
                        </tr>
                        <tr>
                          <th>Owner</th>
                          <td>{{ $crud->stock->branch->member->name ?? '-' }}</td>
                        </tr>
                        <tr>
                          <th>Stock Awal</th>
                          <td id="stock-awal"></td>
                        </tr>
                        <tr>
                          <th>Stock Akhir</th>
                          <td id="stock-akhir"></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
      </div>
    <div class="{{ $crud->getListContentClass() }}">
        @if (isset($crud->viewBeforeContent) && is_array($crud->viewBeforeContent))
            @foreach ($crud->viewBeforeContent as $name)
                @include($name)
            @endforeach
        @endif
        <div class="row mb-0">
          <div class="col-sm-6">
            @if ( $crud->buttons()->where('stack', 'top')->count() ||  $crud->exportButtons())
              <div class="d-print-none {{ $crud->hasAccess('create')?'with-border':'' }}">

                @include('crud::inc.button_stack', ['stack' => 'top'])

              </div>
            @endif
          </div>
          <div class="col-sm-6">
            <div id="datatable_search_stack" class="mt-sm-0 mt-2 d-print-none"></div>
          </div>
        </div>

        {{-- Backpack List Filters --}}
        @if ($crud->filtersEnabled())
          @include('crud::inc.filters_navbar')
        @endif

        <table
          id="crudTable"
          class="bg-white table table-striped table-hover nowrap rounded shadow-xs border-xs mt-2"
          data-responsive-table="{{ (int) $crud->getOperationSetting('responsiveTable') }}"
          data-has-details-row="{{ (int) $crud->getOperationSetting('detailsRow') }}"
          data-has-bulk-actions="{{ (int) $crud->getOperationSetting('bulkActions') }}"
          cellspacing="0">
            <thead>
              <tr>
                {{-- Table columns --}}
                @foreach ($crud->columns() as $column)
                  <th
                    data-orderable="{{ var_export($column['orderable'], true) }}"
                    data-priority="{{ $column['priority'] }}"
                    data-column-name="{{ $column['name'] }}"
                    {{--
                    data-visible-in-table => if developer forced field in table with 'visibleInTable => true'
                    data-visible => regular visibility of the field
                    data-can-be-visible-in-table => prevents the column to be loaded into the table (export-only)
                    data-visible-in-modal => if column apears on responsive modal
                    data-visible-in-export => if this field is exportable
                    data-force-export => force export even if field are hidden
                    --}}

                    {{-- If it is an export field only, we are done. --}}
                    @if(isset($column['exportOnlyField']) && $column['exportOnlyField'] === true)
                      data-visible="false"
                      data-visible-in-table="false"
                      data-can-be-visible-in-table="false"
                      data-visible-in-modal="false"
                      data-visible-in-export="true"
                      data-force-export="true"
                    @else
                      data-visible-in-table="{{var_export($column['visibleInTable'] ?? false)}}"
                      data-visible="{{var_export($column['visibleInTable'] ?? true)}}"
                      data-can-be-visible-in-table="true"
                      data-visible-in-modal="{{var_export($column['visibleInModal'] ?? true)}}"
                      @if(isset($column['visibleInExport']))
                         @if($column['visibleInExport'] === false)
                           data-visible-in-export="false"
                           data-force-export="false"
                         @else
                           data-visible-in-export="true"
                           data-force-export="true"
                         @endif
                       @else
                         data-visible-in-export="true"
                         data-force-export="false"
                       @endif
                    @endif
                  >
                    {{-- Bulk checkbox --}}
                    @if($loop->first && $crud->getOperationSetting('bulkActions'))
                      {!! View::make('crud::columns.inc.bulk_actions_checkbox')->render() !!} ss
                    @endif
                    {!! $column['label'] !!}
                  </th>
                @endforeach

                @if ( $crud->buttons()->where('stack', 'line')->count() )
                  <th data-orderable="false"
                      data-priority="{{ $crud->getActionsColumnPriority() }}"
                      data-visible-in-export="false"
                      >{{ trans('backpack::crud.actions') }}</th>
                @endif
              </tr>
            </thead>
            <tbody id="table-data">
            </tbody>
            <tfoot>
              <tr>
                {{-- Table columns --}}
                @foreach ($crud->columns() as $column)
                  <th>
                    {{-- Bulk checkbox --}}
                    @if($loop->first && $crud->getOperationSetting('bulkActions'))
                      {!! View::make('crud::columns.inc.bulk_actions_checkbox')->render() !!}
                    @endif
                    {!! $column['label'] !!} 
                  </th>
                @endforeach

                @if ( $crud->buttons()->where('stack', 'line')->count() )
                  <th>{{ trans('backpack::crud.actions') }}</th>
                @endif
              </tr>
            </tfoot>
          </table>
          @if ( $crud->buttons()->where('stack', 'bottom')->count() )
          <div id="bottom_buttons" class="d-print-none text-center text-sm-left">
            @include('crud::inc.button_stack', ['stack' => 'bottom'])

            <div id="datatable_button_stack" class="float-right text-right hidden-xs"></div>
          </div>
          @endif
          @if (isset($crud->viewAfterContent) && is_array($crud->viewAfterContent))
                @foreach ($crud->viewAfterContent as $name)
                    @include($name) 
                @endforeach
            @endif
    </div>
  </div>

@endsection

@section('after_styles')
  {{-- DATA TABLES --}}
  <link rel="stylesheet" type="text/css" href="{{ asset('packages/datatables.net-bs4/css/dataTables.bootstrap4.min.css') }}">
  <link rel="stylesheet" type="text/css" href="{{ asset('packages/datatables.net-fixedheader-bs4/css/fixedHeader.bootstrap4.min.css') }}">
  <link rel="stylesheet" type="text/css" href="{{ asset('packages/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css') }}">

  {{-- CRUD LIST CONTENT - crud_list_styles stack --}}
  @stack('crud_list_styles')
  @if (isset($crud->firstCellNonFlex) && $crud->firstCellNonFlex)
      <style>
        #crudTable_wrapper #crudTable tr td:first-child, #crudTable_wrapper #crudTable tr th:first-child, #crudTable_wrapper table.dataTable tr td:first-child, #crudTable_wrapper table.dataTable tr th:first-child{
            display: table-cell
        }
      </style>
  @endif
@endsection

@section('after_scripts')
  <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  @include('crud::inc.datatables_logic')

  {{-- CRUD LIST CONTENT - crud_list_scripts stack --}}
  @stack('crud_list_scripts')

  <script>
    $('#crudTable').on('xhr.dt', function ( e, settings, json, xhr ) {
      console.log('xhr.dt');
      if(xhr.status == 200){
        if(json.period_name != null && json.period_name.length != 0){
          $('#period-name').text(json.period_name);
        }
        else{
          $('#period-name').text('-');
        }
  
        if(json.initial_stock != null && json.initial_stock.length != 0){
          $('#stock-awal').text(json.initial_stock);
        }
        else{
          $('#stock-awal').text('-');
        }
  
        if(json.final_stock != null && json.final_stock.length != 0){
          $('#stock-akhir').text(json.final_stock);
        }
        else{
          $('#stock-akhir').text('-');
        }
      }
    });
  </script>
@endsection
