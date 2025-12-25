@extends('backend.admin_master')
@section('admin')
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">Site Ayarları</h4>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <form method="post" action="{{ route('update.site.settings') }}" enctype="multipart/form-data"
                                class="needs-validation" novalidate>

                                @csrf

                                <input type="hidden" name="id" value="{{ $site_settings_all->id }}">

                                <div class="row mb-3">
                                    <label class="col-sm-2 col-form-label">Site Adı:</label>
                                    <div class="col-sm-10">
                                        <input class="form-control" name="site_name" type="text"
                                            value="{{ $site_settings_all->site_name }}" required>
                                    </div>
                                </div>
                                <!-- end row -->

                                <div class="row mb-3">
                                    <label class="col-sm-2 col-form-label">Site Url:</label>
                                    <div class="col-sm-10">
                                        <input class="form-control" name="site_url" type="text"
                                            value="{{ $site_settings_all->site_url }}" required>
                                    </div>
                                </div>
                                <!-- end row -->

                                <div class="row mb-3">
                                    <label class="col-sm-2 col-form-label">Site Description:</label>
                                    <div class="col-sm-10">
                                        <input class="form-control" name="site_description" type="text"
                                            value="{{ $site_settings_all->site_description }}">
                                    </div>
                                </div>
                                <!-- end row -->

                                <div class="row mb-3">
                                    <label class="col-sm-2 col-form-label">Site Keywords:</label>
                                    <div class="col-sm-10">
                                        <input class="form-control" name="site_keywords" type="text"
                                            value="{{ $site_settings_all->site_keywords }}">
                                    </div>
                                </div>
                                <!-- end row -->

                                <div class="row mb-3">
                                    <label class="col-sm-2 col-form-label">Copyright:</label>
                                    <div class="col-sm-10">
                                        <input class="form-control" name="copyright" type="text"
                                            value="{{ $site_settings_all->copyright }}" required>
                                    </div>
                                </div>
                                <!-- end row -->

                                <div class="row mb-3">
                                    <label class="col-sm-2 col-form-label">Site Logo:</label>
                                    <div class="col-sm-10">
                                        <input class="form-control" name="site_logo" type="file" id="image">
                                        @if($errors->has('site_logo'))
                                            <div class="error">{{ $errors->first('site_logo') }}</div>
                                        @endif
                                                                                <label class=" col-form-label">Not: Maksimum resim boyutu 2MB'tan fazla olmamalıdır.</label>
                                    </div>
                                </div>
                                <!-- end row -->

                                <div class="row mb-3">
                                    <label class="col-sm-2 col-form-label"></label>
                                    <div class="col-sm-10">
                                        <img class="img-thumbnail" id="showImage" width="200"
                                            src="{{ !empty($site_settings_all->site_logo) ? url($site_settings_all->site_logo) : url('upload/no_image.jpg') }}"
                                            data-holder-rendered="true">
                                    </div>
                                </div>
                                <!-- end row -->

                                <div class="row mb-3">
                                    <label class="col-sm-2 col-form-label">Favicon:</label>
                                    <div class="col-sm-10">
                                        <input class="form-control" name="favicon" type="file" id="image1">
                                        @if($errors->has('favicon'))
                                            <div class="error">{{ $errors->first('favicon') }}</div>
                                        @endif
                                                                                <label class=" col-form-label">Not: Maksimum resim boyutu 2MB'tan fazla olmamalıdır.</label>
                                    </div>
                                </div>
                                <!-- end row -->

                                <div class="row mb-3">
                                    <label class="col-sm-2 col-form-label"></label>
                                    <div class="col-sm-10">
                                        <img class="img-thumbnail" id="showImage1" width="200"
                                            src="{{ !empty($site_settings_all->favicon) ? url($site_settings_all->favicon) : url('upload/no_image.jpg') }}"
                                            data-holder-rendered="true">
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

    <!-- burada javascript ile seçilen resmi görüntüledik -->
    <script type="text/javascript">
        $(document).ready(function() {
            $('#image').change(function(e) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    $('#showImage').attr('src', e.target.result);
                }
                reader.readAsDataURL(e.target.files['0']);
            });
        });
    </script>

    <script type="text/javascript">
        $(document).ready(function() {
            $('#image1').change(function(e) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    $('#showImage1').attr('src', e.target.result);
                }
                reader.readAsDataURL(e.target.files['0']);
            });
        });
    </script>
@endsection
