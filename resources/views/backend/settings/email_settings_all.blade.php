@extends('backend.admin_master')
@section('admin')

<div class="page-content">
  <div class="container-fluid">
    <div class="row">
      <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
          <h4 class="mb-sm-0">Email Ayarları</h4>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-body">

            <form method="post" action="{{ route('update.email.settings')}}" class="needs-validation" novalidate>

              @csrf

                <input type="hidden" name="id" value="{{ $email_settings_all->id }}">

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Mail Server:</label>
                <div class="col-sm-10">
                  <input class="form-control" name="mail_server" type="text" value="{{$email_settings_all->mail_server}}" required>
                </div>
              </div>
              <!-- end row -->

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Mail Port:</label>
                <div class="col-sm-10">
                  <input class="form-control" name="mail_port" type="text" value="{{ $email_settings_all->mail_port}}" required>
                </div>
              </div>
              <!-- end row -->

              <div class="row mb-3">
              <label class="col-sm-2 col-form-label">Mail Protokol: </label>

                <div class="col-sm-10">
                  <select name="protokol" class="form-select" required>
                    <option value="ssl" {{ $email_settings_all->protokol == "ssl" ? 'selected' : ''}}>SSL</option>
                    <option value="tsl" {{ $email_settings_all->protokol == "tsl" ? 'selected' : ''}}>TLS</option>
                  </select>
                </div>
              </div>
              <!-- end row -->


              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Email:</label>
                <div class="col-sm-10">
                  <input class="form-control" name="email" type="email" value="{{ $email_settings_all->email}}" required>
                </div>
              </div>
              <!-- end row -->

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Mail Şifre:</label>
                <div class="col-sm-10">
                  <input class="form-control" name="mail_sifre" type="text" value="{{ $email_settings_all->mail_sifre}}" required>
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
