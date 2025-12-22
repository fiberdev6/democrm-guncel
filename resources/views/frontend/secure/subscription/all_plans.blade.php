@extends('frontend.secure.user_master')
@section('user')
  <script src="https://cdn.tailwindcss.com"></script>  
  <div class="page-content" id="subscriptionPlansPage">
    <div class="container-fluid">
          <h2 class="text-2xl font-bold text-gray-800">Ödeme</h2>

      <div class="row ">

        <div class="col-md-12">
          <section class="py-2">
              <div class="">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 md:items-start">
                  @foreach($plans as $i => $plan)
                    @php
                      // 2. kartı "önerilen" yap
                      $isPopular = ($i === 1);
                    @endphp

                    <div class="relative bg-white rounded-2xl shadow-lg hover:shadow-2xl transition transform hover:-translate-y-2 p-8 flex flex-col text-center pricing-card">                                     
                      @if($isPopular)
                        <span class="absolute -top-3 left-1/2 -translate-x-1/2 bg-gradient-to-r from-orange-500 to-[#f9b233] text-white text-xs font-semibold px-3 py-1 rounded-full shadow">
                          Önerilen
                        </span>
                      @endif

                      <div class="flex justify-center mb-6">
                        <i class="{{$plan->icon}} text-5xl text-[#f9b233]"></i>
                      </div>
                      <h3 class="text-xl font-bold text-gray-800 mb-2">{{ $plan->name }}</h3>
                      <p class="text-gray-500 font-normal mb-2" style="font-size: 14px;">Teknik servis süreçlerinizi dijitalleştirin, müşteri memnuniyetini artırın.</p>

                      <div class="text-4xl font-extrabold text-gray-900 mb-4">
                        ₺ {{ number_format($plan->price) }}
                        <span class="text-base text-gray-500 font-normal">
                          / {{ $plan->getBillingCycleText() }}
                        </span>
                      </div>

                      <div class="flex justify-between items-center my-4 text-sm text-gray-600">
                        <!-- Sol Taraf: Responsive İkonları -->
                        <div class="flex items-center space-x-2">
                          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                          </svg>
                          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 1v22m-4-18h8a2 2 0 012 2v14a2 2 0 01-2 2h-8a2 2 0 01-2-2V5a2 2 0 012-2z" />
                          </svg>
                          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                          </svg>
                        </div>
                        <!-- Sağ Taraf: Kullanıcı Sayısı -->
                        <div class="flex items-center">
                          <svg xmlns="http://www.w3.org/2000/svg" class=" mr-1 text-gray-400" style="width: 1rem;height: 0.9rem;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                          </svg>
                          <span class="font-medium">
                            {{ $plan?->limits['users'] == -1 ? 'Sınırsız Kullanıcı' : $plan?->limits['users'].' Kullanıcı' }}
                          </span>
                        </div>
                      </div>

                      <a href="{{ route('subscription.subscribe', [$tenant->id,$plan->id]) }}"
                        class="inline-block w-full py-2 rounded-full bg-gradient-to-r from-orange-500 to-[#f9b233] text-white font-semibold transition hover:opacity-90">
                        Planı Satın Al
                      </a>

                      <hr class="mt-4" style="--tw-border-opacity: 1;border-color: rgb(132 145 173);">

                      <button class="toggle-btn mb-2 mt-3 flex w-full items-center justify-center py-2 rounded-full text-gray-700 transition hover:bg-gray-100">
                        <span class="mr-2">Özellikler</span>
                        <svg class="icon-down h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                          <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                        </svg>
                        <svg class="icon-up hidden h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                          <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd"/>
                        </svg>
                      </button>

                      <div class="price-description hidden text-gray-600 mb-6 text-center">
                        {!! $plan->description !!}
                      </div>
                    </div>
                  @endforeach
                </div>
              </div>
          </section>
        </div>
      </div>
    </div>
  </div>
  

  <script>
    document.addEventListener("DOMContentLoaded", function () {
      const allToggleButtons = document.querySelectorAll(".toggle-btn");

      allToggleButtons.forEach(button => {
        button.addEventListener("click", function () {
          const clickedCard = this.closest(".pricing-card");
          const description = clickedCard.querySelector(".price-description");
          const isHidden = description.classList.contains("hidden");

          document.querySelectorAll(".pricing-card").forEach(card => {
            card.querySelector(".price-description").classList.add("hidden");
            card.querySelector(".icon-down").classList.remove("hidden");
            card.querySelector(".icon-up").classList.add("hidden");
          });

          if (isHidden) {
            description.classList.remove("hidden");
            clickedCard.querySelector(".icon-down").classList.add("hidden");
            clickedCard.querySelector(".icon-up").classList.remove("hidden");
          }
        });
      });
    });
  </script>
@endsection