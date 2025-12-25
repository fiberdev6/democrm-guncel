
<div id="serviceReportAccordion">
  <div class="accordion-item">
    <h2 class="accordion-header" id="heading1">
      <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" 
        data-bs-target="#collapse1" aria-expanded="false" 
        aria-controls="collapse1">
        <strong>Yapılan Anketler</strong>
      </button>
    </h2>
    <div id="collapse1" class="accordion-collapse collapse" 
      aria-labelledby="heading1" data-bs-parent="#serviceReportAccordion">
      <div class="accordion-body">
        <form  id="yapilanAnketler">
         <div class="row form-group">
                        <div class="col-lg-5 rw1"><label>Anketi Yapılan Personel</label></div>
                        <div class="col-lg-7 rw2">
                            <select name="anketi_yapilan_personel" class="form-control personeller">
                                <option value="0">Tüm Personeller</option>
                                @foreach ($personeller as $personel)
                                    <option value="{{$personel->user_id}}">{{$personel->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row form-group">
                        <div class="col-lg-5 rw1"><label>Anketi Ekleyen Personel</label></div>
                        <div class="col-lg-7 rw2">
                            <select name="anketi_yapan_personel" class="form-control personeller">
                                <option value="0">Tüm Personeller</option>
                                @foreach ($personeller as $personel)
                                    <option value="{{$personel->user_id}}">{{$personel->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
          <div class="row form-group">
            <div class="col-lg-5 rw1"><label>Bayi</label></div>
            <div class="col-lg-7 rw2">
              <select name="bayiler" class="form-control bayiler">
                <option value="0">Tüm Bayiler</option>    
                @foreach ($bayiler as $bayi)
                    <option value="{{$bayi->user_id}}">{{$bayi->name}}</option>
                @endforeach                
              </select>
            </div>
          </div>

          <div class="row form-group">
            <div class="col-lg-5 rw1"><label>Tarih Aralığı</label></div>
            <div class="col-lg-7 d-flex gap-2 rw2">
              <input type="date" name="yapilananket_tarih1" class="form-control tarih1 "  value="{{date('Y-m-d')}}" style="background:#fff;margin-bottom: 3px;">
              <input type="date" name="yapilananket_tarih2" class="form-control tarih2 "  value="{{date('Y-m-d')}}"  style="background:#fff;margin-bottom: 2px;">
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
    <h2 class="accordion-header" id="heading2">
      <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" 
        data-bs-target="#collapse2" aria-expanded="false" 
        aria-controls="collapse2">
        <strong>Yapılmayan Anketler</strong>
      </button>
    </h2>
    <div id="collapse2" class="accordion-collapse collapse" 
      aria-labelledby="heading2" data-bs-parent="#serviceReportAccordion">
      <div class="accordion-body">
        <form id="yapilmayanAnketler">
          <div class="row form-group">
            <div class="col-lg-5 rw1"><label>Personel</label></div>
            <div class="col-lg-7 rw2">
              <select name="yapilmayan_personel" class="form-control personeller">
                <option value="0">Tüm Personeller</option>
                @foreach ($personeller as $personel)
                    <option value="{{$personel->user_id}}">{{$personel->name}}</option>
                @endforeach
              </select>
            </div>
          </div>
          <div class="row form-group">
            <div class="col-lg-5 rw1"><label>Bayi</label></div>
            <div class="col-lg-7 rw2">
              <select name="bayiler" class="form-control bayiler">
                <option value="0">Tüm Bayiler</option>    
                @foreach ($bayiler as $bayi)
                    <option value="{{$bayi->user_id}}">{{$bayi->name}}</option>
                @endforeach                
              </select>
            </div>
          </div>
         <div class="row form-group">
            <div class="col-lg-5 rw1"><label>Tarih Aralığı</label></div>
            <div class="col-lg-7 d-flex gap-2 rw2">
              <input type="date" name="yapilmayananket_tarih1" class="form-control tarih1 "  value="{{date('Y-m-d')}}" style="background:#fff;margin-bottom: 3px;">
              <input type="date" name="yapilmayananket_tarih2" class="form-control tarih2 "  value="{{date('Y-m-d')}}"  style="background:#fff;margin-bottom: 2px;">
            </div>
          </div>

          <div class="row">
            <div class="col-lg-7 offset-lg-5">
              <button type="submit" class="btn-full btn btn-primary btn-sm inBtn btn-block btnFilter ">ARA</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>