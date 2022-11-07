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
  <section class="container-fluid">
    <h2>
        <span class="text-capitalize">{!! $crud->getHeading() ?? $crud->entity_name_plural !!}</span>
        <small>{!! $crud->getSubheading() ?? 'Add '.$crud->entity_name !!}.</small>

        @if ($crud->hasAccess('list'))
          <small><a href="{{ url($crud->route) }}" class="hidden-print font-sm"><i class="fa fa-angle-double-left"></i> {{ trans('backpack::crud.back_to_all') }} <span>{{ $crud->entity_name_plural }}</span></a></small>
        @endif
    </h2>
  </section>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8 col-md-offset-2">
      @if ($errors->any())
        <div class="alert alert-danger pb-0">
            <ul class="list-unstyled">
                @foreach ($errors->all() as $error)
                    <li><i class="la la-info-circle"></i> {{ $error }}</li>
                @endforeach
            </ul>
        </div>
      @endif
      <form method="post" action="" enctype="multipart/form-data">
        @csrf
          <div class="card">
            <div class="card-body row">
              <input type="hidden" value="{{$user->id}}" name="user_id" >
              <input type="hidden" value="{{$level->id}}" name="level_id" >
              <input type="hidden" value="{{$user->email}}" name="email" >
              <div class="form-group col-md-12">
                <label >Upline</label>
                <select name="upline_id" id="upline_id" class="form-control">
                  @foreach ($uplines as $upline)
                    <option value="{{$upline->id}}">{{$upline->member_numb}} | {{$upline->name}}</option>
                  @endforeach
                </select>
              </div>
              <div class="form-group col-md-12">
                <label >No. Member</label>
                <input type="text" class="form-control" name="member_numb" value="{{ $upline->member_numb }}" readonly>  
              </div>
              <div class="form-group col-md-12">
                <label>Level</label>
                <input type="text" class="form-control" name="level_name" value="{{ $level->code }} - {{ $level->name }}" readonly>
              </div>
              <div class="form-group col-md-12">
                <label for="name">Name</label>
                <input type="text" class="form-control  @error('name') is-invalid @enderror" name="name" value="@if(old('name')) {{ old('name') }} @else {{ $user->name }} @endif" required>
                  @error('name')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                  @enderror
              </div>
              <div class="form-group col-md-12">
                <label >ID. Card</label>
                <input type="number" class="form-control @error('id_card') is-invalid @enderror" name="id_card" value="{{ old('id_card') }}">
                  @error('id_card')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                  @enderror
              </div>
              <div class="form-group col-md-12">
                <label>Gender</label>
                <select name="gender" id="gender" class="form-control @error('gender') is-invalid @enderror">
                  <option value="">-</option>
                  <option value="M" @if(old('gender')) selected @endif>Male</option> 
                  <option value="F" @if(old('gender')) selected @endif>Female</option>
                </select>
                  @error('gender')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                  @enderror
              </div>
              <div class="form-group col-md-12">
                <label>Phone</label>
                <input type="number" class="form-control @error('phone') is-invalid @enderror" name="phone" value="{{ old('phone') }}">
                  @error('phone')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                  @enderror
              </div>
              <div class="form-group col-md-12">
                <label>Address</label>
                <textarea name="address" class="form-control @error('address') is-invalid @enderror">{{ old('address') }}</textarea>
                  @error('address')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                  @enderror
              </div>
              {{-- <div class="form-group col-md-12">
                <label>Photo Member</label>
                <input type="file" class="form-control" name="photo_url" >
                  @error('photo_member')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                  @enderror
              </div> --}}

            </div><!-- /.card-body -->
          </div><!-- /.card -->
          <div class="d-none" id="parentLoadedAssets">[]</div>
          <div id="saveActions" class="form-group">
              <input type="hidden" name="_save_action" value="save_member">
              <button type="submit" class="btn btn-success">
                  <span class="la la-save" role="presentation" aria-hidden="true"></span> &nbsp;
                  <span data-value="save_member">Save</span>
              </button>
              <div class="btn-group" role="group">
              </div>
              <a href="{{ url($crud->route) }}" class="btn btn-default"><span class="la la-ban"></span>
                  &nbsp;Cancel</a>
          </div>
      </form>
    </div>
</div>
@endsection

@push('after_styles')
<link href="http://127.0.0.1:8001/packages/cropperjs/dist/cropper.min.css" rel="stylesheet" type="text/css" />                
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="{{ asset('packages/select2-bootstrap-theme/dist/select2-bootstrap.min.css') }}" />
@endpush

@push('after_scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
  $(document).ready(function() {
    $('#upline_id').select2({
      theme: "bootstrap",
    });
  });
</script>
@endpush
