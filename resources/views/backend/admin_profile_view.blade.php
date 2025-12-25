@extends('backend.admin_master')
@section('admin')

<div class="page-content">
  <div class="container-fluid">

    <div class="row">
      <div class="col-lg-6">
        <div class="card">
        <img class="img-thumbnail" width="200" src="{{ (!empty($adminData->profile_image))? url('upload/admin_images/'.$adminData->profile_image): url('upload/no_image.jpg') }}" data-holder-rendered="true">
          <div class="card-body">
            <h4 class="card-title">Name : {{ $adminData ->name }}</h4>

            <h4 class="card-title">User Name : {{ $adminData ->username }}</h4>
            <hr>

            <a href="{{ route('edit.profile')}}" class="btn btn-info btn-rounded waves-effect waves-light">Edit Profile</a>
          </div>
        </div>
      </div>

    </div>



  </div>
</div>

@endsection
