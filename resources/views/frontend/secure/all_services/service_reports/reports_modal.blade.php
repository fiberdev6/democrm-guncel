
<div id="serviceReportAccordion">
  <div class="accordion-item">
    <h2 class="accordion-header" id="heading1">
      <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" 
        data-bs-target="#collapse1" aria-expanded="false" 
        aria-controls="collapse1">
        <strong>Operatör Arama</strong>
      </button>
    </h2>
    <div id="collapse1" class="accordion-collapse collapse" 
      aria-labelledby="heading1" data-bs-parent="#serviceReportAccordion">
      <div class="accordion-body">
        <form  id="operatorArama">
          <div class="row form-group">
            <div class="col-lg-5 rw1"><label>Personel</label></div>
            <div class="col-lg-7 rw2">
              <select name="operator_pers" class="form-control personeller">
                <option value="0">Tüm Personeller</option>  
                @foreach ($operators as $item)
                    <option value="{{$item->user_id}}">{{$item->name}}</option>
                @endforeach                         
              </select>
            </div>
          </div>

          <div class="row form-group">
  <div class="col-lg-5 rw1"><label>Tarih Aralığı</label></div>
  <div class="col-lg-7 rw2 d-flex gap-2"> 
    <input type="date" name="operator_tarih1" class="form-control tarih1 datepicker" value="{{date('Y-m-d')}}" style="background:#fff;">
    <input type="date" name="operator_tarih2" class="form-control tarih2 datepicker" value="{{date('Y-m-d')}}" style="background:#fff;">
  </div>
</div>

          <div class="row">
            <div class="col-lg-7 offset-lg-5">
              <button type="submit" class="btn-full btn-full btn btn-primary btn-sm inBtn btn-block btnFilter">ARA</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div class="accordion-item">
    <h2 class="accordion-header" id="heading2">
      <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" 
        data-bs-target="#collapse2" aria-expanded="false" 
        aria-controls="collapse2">
        <strong>Teknisyen Arama</strong>
      </button>
    </h2>
    <div id="collapse2" class="accordion-collapse collapse" 
      aria-labelledby="heading2" data-bs-parent="#serviceReportAccordion">
      <div class="accordion-body">
        <form id="teknisyenArama">
          <div class="row form-group">
            <div class="col-lg-5 rw1"><label>Teknisyen</label></div>
            <div class="col-lg-7 rw2">
              <select name="teknisyen" class="form-control personeller">
                <option value="0">Tüm Personeller</option>
                @foreach ($teknisyen as $item)
                    <option value="{{$item->user_id}}">{{$item->name}}</option>
                @endforeach
              </select>
            </div>
          </div>
          
          <div class="row form-group">
            <div class="col-lg-5 rw1"><label>Araç</label></div>
            <div class="col-lg-7 rw2">
              <select name="tekArac" class="form-control tekArac">
                <option value="0">Tüm Araçlar</option>    
                @foreach ($cars as $item)
                    <option value="{{$item->id}}">{{$item->arac}}</option>
                @endforeach                
              </select>
            </div>
          </div>

          <div class="row form-group">
            <div class="col-lg-5 rw1"><label>Tarih</label></div>
            <div class="col-lg-7 rw2">
              <input type="date" name="tekTarih" class="form-control tarih1 datepicker" value="{{date('Y-m-d')}}" style="background:#fff;">
            </div>
          </div>

          <div class="row">
            <div class="col-lg-7 offset-lg-5">
              <button type="submit" class="btn-full btn btn-primary btn-sm inBtn btn-block btnFilter teknisyenAramaBtn">ARA</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div class="accordion-item">
    <h2 class="accordion-header" id="heading3">
      <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" 
        data-bs-target="#collapse3" aria-expanded="false" 
        aria-controls="collapse3">
        <strong>Cihaz Satışı Yapıldı</strong>
      </button>
    </h2>
    <div id="collapse3" class="accordion-collapse collapse" 
      aria-labelledby="heading3" data-bs-parent="#serviceReportAccordion">
      <div class="accordion-body">
        <form id="urunSatisArama">
          
          <div class="row form-group">
            <div class="col-lg-5 rw1"><label>Tarih Aralığı</label></div>
            <div class="col-lg-7 d-flex gap-2 rw2">
              <input type="date" name="satis_tarih1" class="form-control satis_tarih1 datepicker" value="{{ date('Y-m-d') }}">
              <input type="date" name="satis_tarih2" class="form-control satis_tarih2 datepicker" value="{{ date('Y-m-d') }}">
            </div>
          </div>

          <div class="row">
            <div class="col-lg-7 offset-lg-5">
              <button type="submit" class="btn-full btn btn-primary btn-sm inBtn btn-block btnFilter">ARA</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div class="accordion-item">
    <h2 class="accordion-header" id="heading4">
      <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" 
        data-bs-target="#collapse4" aria-expanded="false" 
        aria-controls="collapse4">
        <strong>Bayi Servisleri</strong>
      </button>
    </h2>
    <div id="collapse4" class="accordion-collapse collapse" 
      aria-labelledby="heading4" data-bs-parent="#serviceReportAccordion">
      <div class="accordion-body">
        <form id="bayiArama">
          
          <div class="row form-group">
            <div class="col-lg-5 rw1"><label>Tarih Aralığı</label></div>
            <div class="col-lg-7 rw2 d-flex gap-2">
              <input type="date" name="bayi_tarih1" class="form-control bayi_tarih1 datepicker" value="{{ date('Y-m-d') }}">
              <input type="date" name="bayi_tarih2" class="form-control bayi_tarih2 datepicker" value="{{ date('Y-m-d') }}">
            </div>
          </div>

          <div class="row">
            <div class="col-lg-7 offset-lg-5">
              <button type="submit" class="btn-full btn btn-primary btn-sm inBtn btn-block btnFilter">ARA</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div class="accordion-item">
    <h2 class="accordion-header" id="heading5">
      <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" 
        data-bs-target="#collapse5" aria-expanded="false" 
        aria-controls="collapse5">
        <strong>Acil Servisler</strong>
      </button>
    </h2>
    <div id="collapse5" class="accordion-collapse collapse" 
      aria-labelledby="heading5" data-bs-parent="#serviceReportAccordion">
      <div class="accordion-body">
        <form id="acilArama">
          
          <div class="row form-group">
            <div class="col-lg-5 rw1"><label>Tarih Aralığı</label></div>
            <div class="col-lg-7 rw2 d-flex gap-2">
              <input type="date" name="acil_tarih1" class="form-control acil_tarih1 datepicker" value="{{ date('Y-m-d') }}">
              <input type="date" name="acil_tarih2" class="form-control acil_tarih2 datepicker" value="{{ date('Y-m-d') }}">
            </div>
          </div>

          <div class="row">
            <div class="col-lg-7 offset-lg-5">
              <button type="submit" class="btn-full btn btn-primary btn-sm inBtn btn-block btnFilter">ARA</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div class="accordion-item">
    <h2 class="accordion-header" id="heading6">
      <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" 
        data-bs-target="#collapse6" aria-expanded="false" 
        aria-controls="collapse6">
        <strong>Gelen Çağrılar</strong>
      </button>
    </h2>
    <div id="collapse6" class="accordion-collapse collapse" 
      aria-labelledby="heading6" data-bs-parent="#serviceReportAccordion">
      <div class="accordion-body">
        <form id="gelenCagriArama">

          <div class="row form-group">
            <div class="col-lg-5 rw1"><label>Personel</label></div>
            <div class="col-lg-7 rw2">
              <select name="cagri_pers" class="form-control personeller">
                <option value="0">Tüm Personeller</option>  
                @foreach ($operators as $item)
                    <option value="{{$item->user_id}}">{{$item->name}}</option>
                @endforeach                         
              </select>
            </div>
          </div>

          <div class="row form-group">
            <div class="col-lg-5 rw1"><label>Marka</label></div>
            <div class="col-lg-7 rw2">
              <select name="cagri_marka" class="form-control">
                <option value="0">Hepsi</option>  
                @foreach ($marka as $item)
                    <option value="{{$item->id}}">{{$item->marka}}</option>
                @endforeach                         
              </select>
            </div>
          </div>

          <div class="row form-group">
            <div class="col-lg-5 rw1"><label>Servis Kaynağı</label></div>
            <div class="col-lg-7 rw2">
              <select name="cagri_kaynak" class="form-control ">
                <option value="0">Hepsi</option>  
                @foreach ($servisKaynak as $item)
                    <option value="{{$item->id}}">{{$item->kaynak}}</option>
                @endforeach                         
              </select>
            </div>
          </div>
          
          <div class="row form-group">
            <div class="col-lg-5 rw1"><label>Tarih Aralığı</label></div>
            <div class="col-lg-7 rw2 d-flex gap-2">
              <input type="date" name="cagri_tarih1" class="form-control cagri_tarih1 datepicker" value="{{ date('Y-m-d') }}">
              <input type="date" name="cagri_tarih2" class="form-control cagri_tarih2 datepicker" value="{{ date('Y-m-d') }}">
            </div>
          </div>

          <div class="row">
            <div class="col-lg-7 offset-lg-5">
              <button type="submit" class="btn-full btn btn-primary btn-sm inBtn btn-block btnFilter">ARA</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>

</div>