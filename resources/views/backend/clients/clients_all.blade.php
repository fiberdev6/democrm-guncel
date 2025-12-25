@extends('backend.admin_master')
@section('admin')

<div class="page-content">
  <div class="container-fluid">
    <div class="row">
      <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between pb-2">
          <h4 class="mb-sm-0">Sık Sorulan Sorular</h4>
        </div>
        <a href="{{ route('add.client')}}" class="btn btn-info mb-3 btn-sm">Soru-Cevap Ekle</a>
      </div>
    </div>

    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-body">
            <table id="datatable" class="table table-bordered dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
              <thead>
                <tr>
                  <th>Soru</th>
                  <th>Cevap</th>
                  <th data-priority="1" style="min-width: 50px;width: 50px;max-width: 50px;"></th>
                </tr>
              </thead>

              <tbody>
                @php($i = 1)
                @foreach($clients as $item)
                <tr>
                  <td>{{$item->name}}</td>
                  <td>{!! Str::limit($item->message, 100) !!}</td>
                  <td>
                    <a href="{{ route('edit.client', $item->id)}}" class="btn btn-info btn-sm"><i class="fas fa-edit"></i></a>
                    <a href="{{ route('delete.client', $item->id)}}" class="btn btn-danger btn-sm" id="delete"><i class="fas fa-trash-alt"></i></a>
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