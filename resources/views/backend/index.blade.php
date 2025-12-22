@extends('backend.admin_master')
@section('admin')

@php 
$contact = App\Models\Contact::get();
@endphp

@php 
$rooms = App\Models\Room::get();
@endphp

@php 
$slide = App\Models\HomeSlide::get();
@endphp

@php 
$documents = App\Models\OurDocument::get();
@endphp

<div class="page-content">
  <div class="container-fluid">

    <div class="row">
      <div class="col-xl-3 col-md-6">
        <div class="card">
          <a href="{{ route('contact.message')}}" class="card-body">
            <div class="d-flex">
              <div class="flex-grow-1">
                <p class="text-truncate font-size-14 text-body mb-2">Mesajlar</p>
                <h4 class="mb-0">{{count($contact)}}</h4>
              </div>
              <div class="avatar-sm mt-1">
                <span class="avatar-title bg-light text-primary rounded-3">
                <i class="ri-mail-open-line font-size-24"></i>
                </span>
              </div>
            </div>
          </a>
        </div>
      </div>
      <div class="col-xl-3 col-md-6">
        <div class="card">
          <a href="{{route('all.room')}}" class="card-body">
            <div class="d-flex">
              <div class="flex-grow-1">
                <p class="text-truncate font-size-14 text-body mb-2">Ürünler</p>
                <h4 class="mb-0">{{count($rooms)}}</h4>
              </div>
              <div class="avatar-sm mt-1">
                <span class="avatar-title bg-light text-primary rounded-3">
                <i class="ri-team-line font-size-24"></i>
                </span>
              </div>
            </div>
          </a>
        </div>
      </div>
      <div class="col-xl-3 col-md-6">
        <div class="card">
          <a href="{{ route('home.image')}}" class="card-body">
            <div class="d-flex">
              <div class="flex-grow-1">
                <p class="text-truncate font-size-14 text-body mb-2">Slider</p>
                <h4 class="mb-0">{{count($slide)}}</h4>
              </div>
              <div class="avatar-sm mt-1">
                <span class="avatar-title bg-light text-primary rounded-3">
                <i class="ri-pencil-line font-size-24"></i>
                </span>
              </div>
            </div>
          </a>
        </div>
      </div>
      <div class="col-xl-3 col-md-6">
        <div class="card">
          <a href="{{route('all.documents')}}" class="card-body">
            <div class="d-flex">
              <div class="flex-grow-1">
                <p class="text-truncate font-size-14 text-body mb-2">Kataloglar</p>
                <h4 class="mb-0">{{count($documents)}}</h4>
              </div>
              <div class="avatar-sm mt-1">
                <span class="avatar-title bg-light text-primary rounded-3">
                <i class="ri-message-3-line font-size-24"></i>
                </span>
              </div>
            </div>
          </a>
        </div>
      </div>
    </div>
  </div>
</div>

@php 
$contacts = App\Models\Contact::get();
@endphp


  <!-- <div class="container-fluid">
    <div class="row">
        <div class="col-12">
          <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">Mesajlar</h4>
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
                  <th>İsim</th>
                  <th>Email</th>
                  <th>Mesaj</th>
                  <th data-priority="1" style="min-width: 10px;width: 10px;max-width: 10px;"></th>
                </tr>
              </thead>

              <tbody>
                @php($i = 1)
                @foreach($contacts as $item)
                <tr>
                  <td>{{$item->name}}</td>
                  <td>{{$item->email}}</td>
                  <td>{{ Str::limit($item->message,150)}}</td>
                  <td>
                    <a href="{{ route('delete.message', $item->id) }}" class="btn btn-danger btn-sm" id="delete"><i class="fas fa-trash-alt"></i></a>
                  </td>
                </tr>
                @endforeach
              </tbody>
            </table>

          </div>
        </div>
      </div> <!-- end col -->
    </div> <!-- end row -->
  </div> -->


@endsection
