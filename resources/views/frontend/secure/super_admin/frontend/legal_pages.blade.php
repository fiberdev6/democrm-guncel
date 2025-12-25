@extends('frontend.secure.user_master')
@section('user')
<div class="page-content">
    <div class="container-fluid">
        
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Yasal Sayfalar</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('super.admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Yasal Sayfalar</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Sayfa</th>
                                        <th>Başlık</th>
                                        <th>Güncelleme</th>
                                        <th width="150">İşlemler</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $legalSlugs = [
                                            'gizlilik' => 'Gizlilik Politikası',
                                            'kullanim-kosullari' => 'Kullanım Şartları',
                                            'kvkk' => 'KVKK Aydınlatma Metni'
                                        ];
                                    @endphp
                                    
                                    @foreach($legalSlugs as $slug => $defaultTitle)
                                        @php
                                            $page = $pages->firstWhere('section', $slug);
                                        @endphp
                                        <tr>
                                            <td><strong>{{ $defaultTitle }}</strong></td>
                                            <td>{{ $page->content['title'] ?? '-' }}</td>
                                            <td>{{ $page ? $page->updated_at->format('d.m.Y H:i') : '-' }}</td>
                                            <td>
                                                @if($page)
                                                    <a href="{{ route('super.admin.frontend.legal-pages.edit', $slug) }}" class="btn btn-sm btn-primary">
                                                        <i class="fas fa-edit"></i> Düzenle
                                                    </a>
                                                @else
                                                    <a href="{{ route('super.admin.frontend.legal-pages.edit', $slug) }}" class="btn btn-sm btn-success">
                                                        <i class="fas fa-plus"></i> Oluştur
                                                    </a>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection