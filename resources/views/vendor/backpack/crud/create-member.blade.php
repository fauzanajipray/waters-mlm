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
                <select name="upline_id" id="" class="form-control">
                  @foreach ($uplines as $upline)
                    <option value="{{$upline->id}}">{{$upline->member_numb}} | {{$upline->name}}</option>
                  @endforeach
                </select>
              </div>
              <div class="form-group col-md-12">
                <label >No. Member</label>
                <input type="text" class="form-control" name="upline" value="{{ $upline->member_numb }}" readonly>  
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
                <select name="gender" class="form-control @error('gender') is-invalid @enderror">
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
              <div class="form-group col-md-12">
                <label>Photo Member</label>
                {{-- <div class="row">
                  <div class="col-sm-6" data-handle="previewArea" style="margin-bottom: 20px;">
                      <img data-handle="mainImage" src="">
                  </div>
                </div>
                <div class="btn-group">
                  <div class="btn btn-light btn-sm btn-file">
                      Choose file <input type="file" accept="image/*" data-handle="uploadImage"  class="form-control">
                      <input type="hidden" data-handle="hiddenImage" name="image" data-value-prefix="" value="">
                  </div>
                  <button class="btn btn-light btn-sm" data-handle="remove" type="button"><i class="la la-trash"></i></button>
                </div> --}}
                <input type="file" class="form-control" name="photo_url" >
                  @error('photo_member')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                  @enderror
              </div>

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
{{-- <style>
  .image .btn-group {
      margin-top: 10px;
  }
  img {
      max-width: 100%; /
  }
  .img-container, .img-preview {
      width: 100%;
      text-align: center;
  }
  .img-preview {
      float: left;
      margin-right: 10px;
      margin-bottom: 10px;
      overflow: hidden;
  }
  .preview-lg {
      width: 263px;
      height: 148px;
  }

  .btn-file {
      position: relative;
      overflow: hidden;
  }
  .btn-file input[type=file] {
      position: absolute;
      top: 0;
      right: 0;
      min-width: 100%;
      min-height: 100%;
      font-size: 100px;
      text-align: right;
      filter: alpha(opacity=0);
      opacity: 0;
      outline: none;
      background: white;
      cursor: inherit;
      display: block;
  }
</style> --}}
@endpush

@push('after_styles')

@endpush

@push('before_scripts')

@endpush

@push('after_scripts')

@endpush
