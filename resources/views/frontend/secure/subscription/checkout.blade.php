@extends('frontend.secure.user_master')
@section('user')

<div class="page-content" id="aboneadim2">
  <div class="container-fluid">
              <div class="card-header card-header-custom2 sayfaBaslik">Fatura Bilgileri</div>


    <form action="{{ route('subscription.process', [$tenant_id, $planid]) }}" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-2">
      @csrf

      <!-- Sol taraf (Paket Bilgileri) -->
      <div class="bg-white rounded-2xl shadow-lg p-6">
        <h3 class="text-lg font-semibold text-gray-700 mb-1">Paket Bilgileri</h3>
        <div class="space-y-3 text-gray-600">
          <p><strong>Paket Adı:</strong> {{ $plan->name }}</p>
          <p><strong>Fiyat:</strong> {{ $plan->getFormattedPrice() }} / {{ $plan->getBillingCycleText() }}</p>
          <p><strong>Açıklama:</strong> {!! $plan->description !!}</p>
        </div>
      </div>

      <!-- Sağ taraf (Fatura Bilgileri) -->
      <div class="bg-white rounded-2xl shadow-lg p-6">
        <h3 class="text-lg font-semibold text-gray-700 mb-4">Fatura Bilgileri</h3>

<!-- Form alanları -->
<div class="space-y-3">
  <div class="flex gap-2 items-center">
    <label class="text-sm font-medium text-gray-600 w-32">Adınız</label>
    <input type="text" name="first_name" class="flex-1 border rounded-lg p-2 focus:ring-2 focus:ring-orange-400" value="{{ old('first_name', $tenant->name ?? '') }}" required>
  </div>
  
  <!-- Kurumsal Alanları -->
  <div class="space-y-3">
    <div class="flex gap-2 items-center">
      <label class="text-sm font-medium text-gray-600 w-32">Vergi Dairesi</label>
      <input type="text" name="tax_office" class="flex-1 border rounded-lg p-2 focus:ring-2 focus:ring-orange-400" value="{{ old('tax_office', $tenant->vergiDairesi ?? '') }}">
    </div>

    <div class="flex gap-2 items-center">
      <label class="text-sm font-medium text-gray-600 w-32">Vergi Numarası</label>
      <input type="text" name="tax_number" class="flex-1 border rounded-lg p-2 focus:ring-2 focus:ring-orange-400" value="{{ old('tax_number', $tenant->vergiNo ?? '') }}">
    </div>
  </div>

  <div class="flex gap-2 items-center">
    <label class="text-sm font-medium text-gray-600 w-32">E-Posta</label>
    <input type="email" name="email" class="flex-1 border rounded-lg p-2 focus:ring-2 focus:ring-orange-400" value="{{ old('email', $tenant->eposta ?? '') }}" required>
  </div>

  <div class="flex gap-2 items-center">
    <label class="text-sm font-medium text-gray-600 w-32">Telefon</label>
    <input type="text" name="phone" class="flex-1 border rounded-lg p-2 focus:ring-2 focus:ring-orange-400" value="{{ old('phone', $tenant->tel1 ?? '') }}" required>
  </div>

  <div class="flex gap-2 items-center">
    <label class="text-sm font-medium text-gray-600 w-32">İl / İlçe</label>
    <div class="flex-1 grid grid-cols-2 gap-2">
      <select name="il" id="sehirSelect" class="border rounded-lg p-2 focus:ring-2 focus:ring-orange-400" required>
        <option value="">İl seç</option>
        @foreach($countries as $item)
          <option value="{{ $item->id }}" {{ $tenant->il == $item->id ? 'selected' : '' }}>{{ $item->name }}</option>
        @endforeach
      </select>
      <select name="ilce" id="ilceSelect" class="border rounded-lg p-2 focus:ring-2 focus:ring-orange-400" required>
        <option value="">-Seçiniz-</option>
      </select>
    </div>
  </div>

  <div class="flex gap-2 items-start">
    <label class="text-sm font-medium text-gray-600 w-32 pt-2">Adres</label>
    <textarea name="address" rows="3" class="flex-1 border rounded-lg p-2 focus:ring-2 focus:ring-orange-400">{{ old('address', $tenant->adres ?? '') }}</textarea>
  </div>
</div>
      <!-- Hidden input: Backend'e kurumsal olduğunu bildirmek için -->
        <input type="hidden" name="billing_type" value="kurumsal">
      <!-- Butonlar -->
      <div class="col-span-2 flex justify-end space-x-3 mt-3">
        <a href="{{ route('subscription.plans', $tenant->id) }}" class="px-4 py-2 rounded-lg bg-gray-200 hover:bg-gray-300 text-gray-700">Geri</a>
        <button type="submit" class="px-6 py-2 rounded-lg bg-green-500 text-white font-semibold hover:opacity-90">Ödeme Sayfasına Geç</button>
      </div>
    </form>
  </div>
</div>
<script src="https://cdn.tailwindcss.com"></script>


<script>
$(document).ready(function() {
    var selectedCountryId = {{ $tenant->il == '' ? '0' : $tenant->il }};
    if(selectedCountryId){
        $.get("/get-states/" + selectedCountryId, function(data) {
            $.each(data, function(index, city) {
                $('#ilceSelect').append(new Option(city.ilceName, city.id));
                if(city.id == {{ $tenant->ilce == '' ? '0' : $tenant->ilce}}){
                    $("#ilceSelect").val(city.id).change();
                } 
            });
        });
    }
    
    // Ülke seçildiğinde
    $("#sehirSelect").change(function() {
        var selectedCountryId = $(this).val();
        // Şehirleri getir ve ikinci select'i güncelle
        $.get("/get-states/" + selectedCountryId, function(data) {
            var citySelect = $("#ilceSelect");
            citySelect.empty(); // Önceki seçenekleri temizle
            $.each(data, function(index, city) {
                citySelect.append(new Option(city.ilceName, city.id));
            });
        });
    });
});
</script>
@endsection