@extends('backend.admin_master')
@section('admin')

<div class="page-content">
  <div class="container-fluid">
    <div class="row">
        <div class="col-12">
          <div class="page-title-box d-sm-flex align-items-center justify-content-between pb-2">
            <h4 class="mb-0">Ürünlerimiz</h4>
          </div>
          <a href="{{ route('add.room')}}" class="btn btn-info btn-sm mb-3">Ürün Ekle</a>          
          <a href="{{ route('all.categories')}}" class="btn btn-success btn-sm mb-3">Kategoriler</a>          
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
                  <th>Kategori</th>
                  <th data-priority="1" style="min-width: 50px;width: 50px;max-width: 50px;"></th>
                </tr>
              </thead>

              <tbody>
                @foreach($rooms as $item)
                <tr>
                  <td>{{$item->id}}</td>
                  <td>{{$item->title}}</td>
                  <td>{{$item->categori->title}}</td>
                  <td>
                    <a href="{{ route('edit.room', $item->id)}}" class="btn btn-info btn-sm" title="Düzenle"><i class="fas fa-edit"></i></a>
                    <a href="{{ route('delete.room', $item->id)}}" class="btn btn-danger btn-sm" id="delete" title="Sil"><i class="fas fa-trash-alt"></i></a>
                    <a href="{{ route('all.room.image', $item->id)}}" class="btn btn-primary btn-sm" title="Proje resimleri"><i class="fas fa-image"></i></a>

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
