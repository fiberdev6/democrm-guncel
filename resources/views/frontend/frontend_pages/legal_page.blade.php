@extends('frontend.main_master')
@section('main')
@section('title', $page->content['title'])

<style>
.legal-page {
    padding: 80px 0;
    background: #f8f9fa;
}

.legal-content {
    background: white;
    padding: 50px;
    border-radius: 10px;
    box-shadow: 0 0 20px rgba(0,0,0,0.05);
}

.legal-content h1 {
    color: var(--primary-blue);
    margin-bottom: 30px;
    font-size: 2.5rem;
    font-weight: 700;
}

.legal-content h2 {
    color: var(--dark);
    margin-top: 30px;
    margin-bottom: 15px;
    font-size: 1.8rem;
    font-weight: 600;
}

.legal-content h3 {
    color: var(--dark);
    margin-top: 20px;
    margin-bottom: 10px;
    font-size: 1.4rem;
    font-weight: 600;
}

.legal-content p {
    color: var(--gray);
    line-height: 1.8;
    margin-bottom: 15px;
}

.legal-content ul, .legal-content ol {
    color: var(--gray);
    line-height: 1.8;
    margin-bottom: 15px;
    padding-left: 30px;
}

.legal-content li {
    margin-bottom: 8px;
}

.legal-content a {
    color: var(--primary-blue);
    text-decoration: underline;
}

.legal-content a:hover {
    color: var(--orange);
}

.legal-content strong {
    color: var(--dark);
    font-weight: 600;
}

.legal-meta {
    color: var(--gray);
    font-size: 0.9rem;
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 1px solid #eee;
}

@media (max-width: 768px) {
    .legal-content {
        padding: 30px 20px;
    }
    
    .legal-content h1 {
        font-size: 2rem;
    }
}
</style>

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