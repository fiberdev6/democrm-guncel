@extends('backend.admin_master')
@section('admin')

<div class="page-content">
  <div class="container-fluid">
    <div class="row">
      <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
          <h4 class="mb-sm-0">Gizlilik Politikası</h4>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-body">
            <form method="post" action="{{ route('update.privacy') }}" enctype="multipart/form-data" class="needs-validation" novalidate>
              @csrf
              <input type="hidden" name="id" value="{{$all_privacy->id }}">
              
                <!-- end row -->
                <div class="row mb-3">
                <label for="example-text-input" class="col-sm-2 col-form-label">Project Description:</label>
                <div class="col-sm-10">
                <textarea id="elm1" name="description" type="text" aria-hidden="true" style="display:;">{{$all_privacy->description}}</textarea>
                </div>
              </div>
              <!-- end row -->
          
              <div class="row">
                <label class="col-sm-2 col-form-label"></label>
                <div class="col-sm-10">
                  <input type="submit" class="btn btn-info waves-effect waves-light" value="Gönder">
                </div>
              </div>
            </form>
          </div>
        </div>
      </div> <!-- end col -->
    </div>
  </div>
</div>


@endsection
