@extends('backend.admin_master')
@section('admin')

<div class="page-content">
  <div class="container-fluid">
    <div class="row">
        <div class="col-12">
          <div class="page-title-box d-sm-flex align-items-center justify-content-between pb-2">
            <h4 class="mb-0">Slider</h4>
          </div>
           <a href="{{ route('add.slide')}}" class="btn btn-info btn-sm mb-3">Slide Ekle</a>
        </div>
      </div>

    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-body">
            <table id="datatable" class="table table-bordered dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
              <thead>
                <tr>
                  <th style="width: 10px">ID</th>
                  <th data-priority="2">Başlık</th>
                  <th>Resim</th>
                  <th data-priority="1" style="min-width: 50px;width: 50px;max-width: 50px;"></th>
                </tr>
              </thead>

              <tbody>
                @foreach($homeimage as $item)
                <tr>
                  <td>{{$item->id}}</td>
                  <td>{{$item->title}}</td>
                  <td>
                  <img class="img-thumbnail" id="showImage" width="100" height="100" src="{{ asset($item->home_image) }}" data-holder-rendered="true">
                  </td>
                 
                  <td>
                    <a href="{{ route('edit.slide', $item->id)}}" class="btn btn-info btn-sm"><i class="fas fa-edit"></i></a>
                    <a href="{{ route('delete.slide', $item->id)}}" class="btn btn-danger btn-sm" id="delete"><i class="fas fa-trash-alt"></i></a>
                  </td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      </div> <!-- end col -->
    </div> <!-- end row -->

  </div>
</div>

@endsection
