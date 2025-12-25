<div class="row pageDetail" id="statisticsMenu">
  <div class="col-12">
    <div class="row">
      <div class="col-12">
        <div class="statistics-menu">
          <a href="{{ route('statistics', $tenant_id) }}" class="btn statistic-btn btn-servis">
            Servis İstatistikleri
          </a>
          <a href="{{ route('technician.statistics', $tenant_id) }}" class="btn  statistic-btn btn-teknisyen">
            Teknisyen İstatistikleri
          </a>
          <a href="{{ route('operator.statistics', $tenant_id) }}" class="btn  statistic-btn btn-operator">
            Operatör İstatistikleri
          </a>
          <a href="{{ route('state.statistics', $tenant_id) }}" class="btn statistic-btn btn-durum">
            Durum İstatistikleri
          </a>
          <a href="{{ route('stage.statistics', $tenant_id) }}" class="btn  statistic-btn btn-asama">
            Aşama İstatistikleri
          </a>
          <a href="{{ route('stock.statistics', $tenant_id) }}" class="btn statistic-btn btn-depo">
            Depo İstatistikleri
          </a>
          <a href="{{ route('ilce.statistics', $tenant_id) }}" class="btn statistic-btn btn-ilce">
            İlçe İstatistikleri
          </a>
          <a href="{{ route('survey.statistics', $tenant_id) }}" class="btn statistic-btn btn-anket">
            Anket İstatistikleri
          </a>
          <a href="{{ route('cash.statistics', $tenant_id) }}" class="btn  statistic-btn btn-kasa">
            Kasa İstatistikleri
          </a>
        </div>
      </div>
    </div>
  </div>
</div>

