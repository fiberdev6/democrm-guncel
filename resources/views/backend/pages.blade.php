@extends('backend.admin_master')
@section('admin')

<div class="page-content">
  <div class="container-fluid">
    <div class="row">
        <div class="col-12">
          <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-0">Sayfalar</h4>
          </div>
        </div>
      </div>

    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-body">
            <table id="datatable" class="table table-bordered dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
              <thead>
                <tr>
                  <th>Sayfa Adı</th>
                  <th style="min-width: 10px;width: 10px;max-width: 10px;"></th>
                </tr>
              </thead>

              <tbody>
                <tr>
                  <td>
                    <a href="{{ route('pages.home')}}"><strong>Anasayfa</strong></a>
                  </td>
                  <td class="d-flex justify-content-end">
                  <a href="{{ route('pages.home')}}" class="btn btn-info btn-sm"><i class="fas fa-edit"></i></a>
                  </td>
                </tr>

                <tr>
                  <td>
                    <a href="{{ route('pages.about')}}"><strong>Hakkımızda</strong></a>
                  </td>
                  <td class="d-flex justify-content-end">
                  <a href="{{ route('pages.about')}}" class="btn btn-info btn-sm"><i class="fas fa-edit"></i></a>
                  </td>
                </tr>

                <tr>
                  <td>
                    <a href="{{ route('pages.rooms')}}"><strong>Ürünlerimiz</strong></a>
                  </td>
                  <td class="d-flex justify-content-end">
                  <a href="{{ route('pages.rooms')}}" class="btn btn-info btn-sm"><i class="fas fa-edit"></i></a>
                  </td>
                </tr>

                <tr>
                  <td>
                    <a href="{{ route('pages.products')}}"><strong>Kataloglar</strong></a>
                  </td>
                  <td class="d-flex justify-content-end">
                  <a href="{{ route('pages.products')}}" class="btn btn-info btn-sm"><i class="fas fa-edit"></i></a>
                  </td>
                </tr>

                <tr>
                  <td>
                    <a href="{{ route('pages.contact')}}"><strong>İletişim</strong></a>
                  </td>
                  <td class="d-flex justify-content-end">
                  <a href="{{ route('pages.contact')}}" class="btn btn-info btn-sm"><i class="fas fa-edit"></i></a>
                  </td>
                </tr>
              </tbody>
            </table>

          </div>
        </div>
      </div> <!-- end col -->
    </div> <!-- end row -->



  </div>
</div>


@endsection
