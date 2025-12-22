@extends('backend.admin_master')
@section('admin')

<div class="page-content">
  <div class="container-fluid">
    <div class="row">
        <div class="col-12">
          <div class="page-title-box d-sm-flex align-items-center justify-content-between pb-2">
            <h4 class="mb-sm-0">Menüler</h4>
          </div>
            <a href="{{ route('add.menus')}}" class="btn btn-info mb-3 btn-sm">Menü Ekle</a>
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
                  <th>Menu Adı</th>
                  <th>Menu Route</th>
                  <th>Menu Statü</th>
                  <th>Menu Sira</th>
                  <th data-priority="1" style="min-width: 50px;width: 50px;max-width: 50px;"></th>
                </tr>
              </thead>

              <tbody>
                @php($i = 1)
                @foreach($menus as $item)
                <tr>
                  <td>{{$i++}}</td>
                  <td>{{$item->name}}</td>
                  <td>{{$item->link}}</td>
                  <td>{{$item->status}}</td>
                  <td>{{$item->sira}}</td>
                  <td>
                    <a href="{{ route('edit.menus', $item->id) }}" class="btn btn-info btn-sm"><i class="fas fa-edit"></i></a>
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
