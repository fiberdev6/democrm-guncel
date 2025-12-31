@extends('frontend.main_master')
@section('main')
@section('title', $page->content['title'])



<section class="legal-page">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="legal-content">
                    <h1>{{ $page->content['title'] }}</h1>
                    
                    <div class="legal-meta">
                        <i class="fas fa-calendar-alt me-2"></i> 
                        Son Güncelleme: {{ $page->updated_at->format('d.m.Y') }}
                    </div>
                    
                    <div class="content-body">
                        {!! $page->content['content'] !!}
                    </div>
                    

                </div>
            </div>
        </div>
    </div>
</section>

@endsection