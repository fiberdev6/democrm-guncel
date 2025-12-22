<section id="references" class="pricing-area references-area container-xxl">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-6">
        <div class="section-header">
          <h2>Referanslarımız</h2>
        </div>
      </div>
    </div> <!-- row -->

    <div class="row justify-content-center">
      @foreach($references as $reference)
        <div class="col-lg-3 col-md-6 col-sm-6">
          <div class="single-pricing mt-20">
            <div class="pricing-header text-center">
              <span class="price"><img src="{{asset($reference->logo)}}"> <span></span></span>
            </div>
          </div>
        </div>
      @endforeach         
    </div>
  </div>
</section>