@extends('frontend.secure.user_master')
@section('user')
  <div class="page-content" id="allTenantsPage">
    <div class="container-fluid staff-header-top">
      <div class="row pageDetail">
        <div class="col-12">
          <div class="card card-tenants">
            <div class="card-header card-tenants-header sayfaBaslik d-flex justify-content-between align-items-center">
              <div>
                Müşteriler
              </div>

            </div>
            <div class="card-body card-tenants-body">
              <table id="datatableTenants" class="table table-bordered dt-responsive nowrap"
                style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                <div class="searchWrap float-end">
                  <div class="btn-group ">
                    <button class="btn btn-dark btn-sm dropdown-toggle filtrele" type="button" data-bs-toggle="dropdown"
                      aria-expanded="false">
                      Filtrele <i class="mdi mdi-chevron-down"></i>
                    </button>
                    <div class="dropdown-menu">
                      <div class="item">
                        <div class="row">
                          <label class="col-sm-3 col-3 custom-p-r-m custom-p-m-m">Durum</label>
                          <div class="col-sm-9 col-9">
                            <select name="tenantStatus" id="tenantStatus" class="form-select">
                              <option value="">Hepsi</option>
                              <option value="1">Aktif</option>
                              <option value="0">Pasif</option>
                            </select>
                          </div>
                        </div>
                      </div>

                      <div class="item">
                        <div class="row">
                          <label class="col-sm-3 col-3 custom-p-r-m custom-p-m-m">İl</label>
                          <div class="col-sm-9 col-9">
                            <select name="il" id="countrySelect" class="form-control form-select"
                              style="width:100%!important;">
                              <option value="">Hepsi</option>
                              @foreach($countries as $item)
                                <option value="{{ $item->id }}">{{ $item->name}}</option>
                              @endforeach
                            </select>
                          </div>
                        </div>
                      </div>

                      <div class="item">
                        <div class="row">
                          <label class="col-sm-3 col-3 custom-p-r-m custom-p-m-m">İlçe</label>
                          <div class="col-sm-9 col-9">
                            <select name="ilce" id="citySelect" class="form-control form-select"
                              style="width:100%!important;">
                              <option value="">Hepsi</option>
                            </select>
                          </div>
                        </div>
                      </div>

                    </div>
                  </div><!-- /btn-group -->
                </div>

                <thead class="title">
                  <tr>
                    <th style="width: 10px">ID</th>
                    <th data-priority="2">Müşteri Adı</th>
                    <th>Abonelik Planı</th>
                    <th>Abonelik Plan Bitiş Tarihi</th>
                    <th class="text-center" style="width: 50px;">Durum</th>
                    <th data-priority="1" style="width: 100px;">İşlemler</th>
                  </tr>
                </thead>
                <tbody>

                </tbody>
              </table>
            </div>
          </div>
        </div> <!-- end col -->
      </div> <!-- end row -->
    </div>
  </div>

  <!-- edit modal content -->
  <div class="modal fade" id="editTenantModal" tabindex="-1" aria-labelledby="tenantDetailLabel" aria-hidden="true"
    style="background: rgba(0,0,0,0.5);">
    <div class="modal-dialog modal-lg">
      <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
        <div class="modal-body" style="padding: 0;">
          Yükleniyor...
        </div>
      </div>
    </div>
  </div>

  <!-- Kullanıcıları Görüntüleme Modal -->
  <div class="modal fade" id="tenantUsersModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg ">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">
            <span id="modalTenantName" style="text-transform: uppercase;">Firma</span> Kullanıcıları
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body" id="tenantUsersContent">
          <div class="text-center p-4">
            <div class="spinner-border text-primary" role="status">
              <span class="visually-hidden">Yükleniyor...</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- Impersonation Onay Modal -->
  <div class="modal fade" id="impersonationModal" tabindex="-1" aria-hidden="true"
    style="background: rgba(0, 0, 0, 0.7); padding-top:100px;">
    <div class="modal-dialog ">
      <div class="modal-content">
        <div class="modal-header bg-warning text-dark">
          <h5 class="modal-title">
            <i class="fas fa-user-secret me-2"></i>Kullanıcı Kimliği Değiştir
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>Dikkat!</strong> Bu işlem kayıt altına alınacaktır.
          </div>
          <p>
            <strong><span id="targetCompanyName"></span></strong> firmasında
            <strong><span id="targetUserName"></span></strong> olarak giriş yapmak üzeresiniz.
          </p>
          <div class="mb-3">
            <label for="impersonationReason" class="form-label">Sebep <span class="text-danger">*</span></label>
            <textarea class="form-control" id="impersonationReason" rows="2"
              placeholder="Bu işlemi neden yapıyorsunuz? (zorunlu alan)" required></textarea>
            <div class="invalid-feedback" id="reasonError">
              Sebep alanı zorunludur.
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
            <button type="button" class="btn btn-warning" id="confirmImpersonationBtn">
              <i class="fas fa-user-secret me-1"></i>Giriş Yap
            </button>
          </div>
        </div>
      </div>
    </div>


    <script type="text/javascript">
      $(document).ready(function () {
        // CSRF token'ı global olarak ayarlayın
        $.ajaxSetup({
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          }
        });

        // Super Admin için farklı endpoint kullan
        $('#datatableTenants').on('click', '.editTenant', function (e) {
          e.preventDefault(); // Prevent default link behavior
          var id = $(this).attr("data-bs-id");
          $.ajax({
            url: "/super-admin/tenant/" + id + "/edit"
          }).done(function (data) {
            if ($.trim(data) === "-1") {
              window.location.reload(true);
            } else {
              $('#editTenantModal').modal('show');
              $('#editTenantModal .modal-body').html(data);
            }
          });
        });

        $("#editTenantModal").on("hidden.bs.modal", function () {
          setTimeout(function () {
            if (!$('.modal.show').length) {
              $('#editTenantModal .modal-body').html("");
            }
          });
        });
      });
    </script>

    <script>
      $(document).ready(function () {
        var table = $('#datatableTenants').DataTable({
          responsive: true,
          processing: true,
          serverSide: true,
          language: {
            paginate: {
              previous: "<i class='mdi mdi-chevron-left'>",
              next: "<i class='mdi mdi-chevron-right'>"
            }
          },
          ajax: {
            url: "{{ route('super.admin.tenants') }}", // Super Admin endpoint
            data: function (data) {
              //data.search = $('input[type="search"]').val();
              data.status = $('#tenantStatus').val();
              data.il = $('#countrySelect').val();
              data.ilce = $('#citySelect').val();
            }
          },
          'columns': [
            { data: 'id', name: 'id' },
            { data: 'name', name: 'firma_adi' },
            { data: 'plan', name: 'plan', orderable: false },
            { data: 'plan_end_date', name: 'plan_end_date', orderable: false },
            { data: 'durum', name: 'status', className: 'text-center' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
          ],
          drawCallback: function () {
            $(".dataTables_paginate > .pagination").addClass("pagination-rounded");
          },
          "order": [[0, 'desc']],
          "columnDefs": [{
            "targets": 0,
            "className": "gizli"
          }],
          "oLanguage": {
            "sDecimal": ",",
            "sEmptyTable": "Tabloda herhangi bir veri mevcut değil",
            "sInfo": "Müşteri Sayısı: _TOTAL_",
            "sInfoEmpty": "Kayıt yok",
            "sInfoFiltered": "",
            "sInfoPostFix": "",
            "sInfoThousands": ".",
            "sLengthMenu": "_MENU_",
            "sLoadingRecords": "Yükleniyor...",
            "sProcessing": "İşleniyor...",
            "sSearch": "",
            "sZeroRecords": "Eşleşen kayıt bulunamadı",
            "oPaginate": {
              "sFirst": "İlk",
              "sLast": "Son",
              "sNext": '<i class="fas fa-angle-double-right"></i>',
              "sPrevious": '<i class="fas fa-angle-double-left"></i>'
            },
            "oAria": {
              "sSortAscending": ": artan sütun sıralamasını aktifleştir",
              "sSortDescending": ": azalan sütun sıralamasını aktifleştir"
            },
            "select": {
              "rows": {
                "_": "%d kayıt seçildi",
                "0": "",
                "1": "1 kayıt seçildi"
              }
            }
          },
          dom: '<"top"f>rt<"bottom"i<"float-end"lp>><"clear">',
          "lengthMenu": [[25, 50, 100, -1], [25, 50, 100, "Tümü"]],
          "initComplete": function (settings, json) {
            var searchContainer = $('#datatableTenants_filter');
            var searchInput = searchContainer.find('input');
            searchInput.attr('placeholder', 'Müşteri Ara...');
            if (window.matchMedia("(max-width: 991.98px)").matches) {
              var mobileFilterWrapper = $('.searchWrap');
              searchContainer.append(mobileFilterWrapper);
              searchContainer.addClass('input-group');
              mobileFilterWrapper.find('.btn').css({
                'border-top-left-radius': '0',
                'border-bottom-left-radius': '0'
              });
            }

            // İşlem bittikten sonra filtreleme butonunu görünür yap
            $('.searchWrap').css({ visibility: 'visible', opacity: 1 });
          }
        });


        $('#tenantStatus').change(function () {
          table.draw();
        });

        $('#countrySelect').change(function () {
          table.draw();
        });

        $('#citySelect').change(function () {
          table.draw();
        });

      });
    </script>

    <script>
      $(document).ready(function () {
        $("#countrySelect").change(function () {
          var selectedCountryId = $(this).val();
          if (selectedCountryId) {
            loadCities(selectedCountryId);
          }
        });

        // Şehirleri yüklemek için kullanılan fonksiyon
        function loadCities(countryId) {
          var citySelect = $("#citySelect");
          citySelect.empty();
          citySelect.append(new Option("Yükleniyor...", ""));

          $.get("/get-states/" + countryId, function (data) {
            citySelect.empty();
            citySelect.append(new Option("-Seçiniz-", ""));
            $.each(data, function (index, city) {
              citySelect.append(new Option(city.ilceName, city.id));
            });
          }).fail(function () {
            citySelect.empty();
            citySelect.append(new Option("Unable to load cities", ""));
          });
        }
      });
    </script>

    <script>
      $(document).ready(function () {
        // Firma kullanıcılarını görüntüle
        $(document).on('click', '.view-tenant-users', function () {
          var tenantId = $(this).data('tenant-id');
          loadTenantUsers(tenantId);
        });

        // Firma patronu olarak impersonate
        $(document).on('click', '.impersonate-tenant-owner', function () {
          var tenantId = $(this).data('tenant-id');
          var ownerId = $(this).data('owner-id');
          var ownerName = $(this).data('owner-name');
          var companyName = $(this).data('company-name');

          $('#targetUserName').text(ownerName);
          $('#targetCompanyName').text(companyName);
          $('#impersonationModal').modal('show');

          $('#confirmImpersonationBtn').off('click').on('click', function () {
            var reason = $('#impersonationReason').val().trim();

            // Sebep kontrolü
            if (!reason) {
              $('#impersonationReason').addClass('is-invalid');
              $('#reasonError').show();
              return;
            }

            $('#impersonationReason').removeClass('is-invalid');
            $('#reasonError').hide();

            startImpersonation(ownerId, reason);
          });
        });

        // Kullanıcı olarak impersonate (modal içinden)
        $(document).on('click', '.impersonate-user-btn', function () {
          var userId = $(this).data('user-id');
          var userName = $(this).data('user-name');
          var companyName = $(this).data('company-name');

          $('#targetUserName').text(userName);
          $('#targetCompanyName').text(companyName);
          $('#impersonationModal').modal('show');

          $('#confirmImpersonationBtn').off('click').on('click', function () {
            var reason = $('#impersonationReason').val().trim();

            // Sebep kontrolü
            if (!reason) {
              $('#impersonationReason').addClass('is-invalid');
              $('#reasonError').show();
              return;
            }

            $('#impersonationReason').removeClass('is-invalid');
            $('#reasonError').hide();

            startImpersonation(userId, reason);
          });
        });

        // Textarea'da yazı yazdığında hata mesajını kaldır
        $(document).on('input', '#impersonationReason', function () {
          var reason = $(this).val().trim();
          if (reason) {
            $(this).removeClass('is-invalid');
            $('#reasonError').hide();
          }
        });

        function loadTenantUsers(tenantId) {
          $('#tenantUsersContent').html(`
                <div class="text-center p-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Yükleniyor...</span>
                    </div>
                    <p class="mt-2">Kullanıcılar yükleniyor...</p>
                </div>
            `);
          $('#tenantUsersModal').modal('show');


          $.get(`/impersonation/users/${tenantId}`)
            .done(function (response) {
              if (response.success) {
                $('#modalTenantName').text(response.tenant?.firma_adi || 'Firma');
                renderTenantUsers(response.users, tenantId);
              }
            })
            .fail(function (xhr) {
              $('#tenantUsersContent').html(`
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            ${xhr.responseJSON?.message || 'Kullanıcılar yüklenemedi.'}
                        </div>
                    `);
            });
        }

        function renderTenantUsers(users, tenantId) {
          if (users.length === 0) {
            $('#tenantUsersContent').html(`
                    <div class="text-center p-4">
                        <i class="fas fa-users text-muted" style="font-size: 48px;"></i>
                        <p class="text-muted mt-3">Bu firmada kullanıcı bulunmuyor.</p>
                    </div>
                `);
            return;
          }

          var html = `
                <div class="table-responsive" >
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Kullanıcı</th>
                                <th>Roller</th>
                                <th>Durum</th>
                                <th style="width: 120px;">İşlemler</th>
                            </tr>
                        </thead>
                        <tbody>
            `;

          users.forEach(function (user) {
            var statusBadge = user.is_active ?
              '<span class="badge" style="background-color: #28a745; color: white; padding: 4px 8px; font-size: 11px;"><i class="mdi mdi-check-circle me-1"></i>Aktif</span>' :
              '<span class="badge bg-danger" style="color: white; padding: 4px 8px; font-size: 11px;"><i class="mdi mdi-pause-circle me-1"></i>Pasif</span>';

            var rolesBadges = user.roles.map(role =>
              `<span class="badge bg-primary me-1" style="color: white; padding: 4px 8px; font-size: 11px;">${role}</span>`
            ).join('');

            var actions = '';
            if (user.can_be_impersonated && user.is_active) {
              actions = `<button class="btn btn-outline-danger btn-sm impersonate-user-btn" 
                                      data-user-id="${user.user_id}" 
                                      data-user-name="${user.name}"
                                      data-company-name="${$('#modalTenantName').text()}"
                                      title="Bu kullanıcı olarak giriş yap">
                                  <i class="fas fa-user-secret"></i>
                              </button>`;
            } else {
              actions = '<span class="text-muted small">İmpersonate edilemez</span>';
            }

            var userInfo = user.name;
            if (user.ayrilma_tarihi) {
              userInfo += '<br><small class="text-danger">Çıkış: ' + user.ayrilma_tarihi + '</small>';
            }

            html += `<tr>
                    <td>
                        <div>
                            <strong>${userInfo}</strong>
                            <br><small class="text-muted">${user.username || '-'}</small>
                        </div>
                    </td>
                    <td>${rolesBadges || '<span class="text-muted">Rol yok</span>'}</td>
                    <td>${statusBadge}</td>
                    <td>${actions}</td>
                </tr>`;
          });

          html += '</tbody></table></div>';

          var activeCount = users.filter(u => u.is_active).length;
          var impersonatableCount = users.filter(u => u.can_be_impersonated && u.is_active).length;

          html += `<div class="mt-3 p-3 bg-light rounded">
                <div class="row text-center">
                    <div class="col-4">
                        <div class="fw-bold">${users.length}</div>
                        <small class="text-muted">Toplam Kullanıcı</small>
                    </div>
                    <div class="col-4">
                        <div class="fw-bold">${activeCount}</div>
                        <small class="text-muted">Aktif Kullanıcı</small>
                    </div>
                    <div class="col-4">
                        <div class="fw-bold">${impersonatableCount}</div>
                        <small class="text-muted">Aktif Kimliğe Bürünme</small>
                    </div>
                </div>
            </div>`;

          $('#tenantUsersContent').html(html);
        }

        function startImpersonation(userId, reason = '') {
          $.ajax({
            url: `/impersonation/start/${userId}`,
            method: 'POST',
            headers: {
              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
              'Accept': 'application/json'
            },
            data: {
              reason: reason
            }
          })
            .done(function (response) {
              if (response.success) {
                showNotification(response.message, 'success');

                // Belirli bir süre bekle ve ardından yönlendir
                setTimeout(() => {
                  window.location.href = response.redirect_url;
                }, 1500);
              }
            })
            .fail(function (xhr) {
              var error = xhr.responseJSON?.message || 'Impersonation başlatılamadı';
              showNotification(error, 'danger');
            });
        }

        function showNotification(message, type) {
          var alertClass = type === 'success' ? 'alert-success' :
            type === 'danger' ? 'alert-danger' : 'alert-info';

          var notification = `
                <div class="alert ${alertClass} alert-dismissible fade show position-fixed" 
                     style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
                    <i class="fas fa-${type === 'success' ? 'check' : type === 'danger' ? 'exclamation-triangle' : 'info'} me-2"></i>
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;

          $('body').append(notification);

          setTimeout(() => {
            $('.alert').fadeOut(function () {
              $(this).remove();
            });
          }, 5000);
        }

        // Modal temizleme
        $('#impersonationModal').on('hidden.bs.modal', function () {
          $('#impersonationReason').val('');
          $('#targetUserName').text('');
          $('#targetCompanyName').text('');
          $('#confirmImpersonationBtn').prop('disabled', false).html('<i class="fas fa-user-secret me-1"></i>Giriş Yap');
        });

        $('#tenantUsersModal').on('hidden.bs.modal', function () {
          $('#tenantUsersContent').html(`
                <div class="text-center p-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Yükleniyor...</span>
                    </div>
                </div>
            `);
        });
      });
    </script>
<script>
    $(document).ready(function () {
        var dropdownContainer = $('.searchWrap .btn-group');
        var filterButton = dropdownContainer.find('.filtrele');
        dropdownContainer.on('show.bs.dropdown', function () {
            filterButton.html('Kapat <i class="mdi mdi-chevron-down"></i>');
        });
        dropdownContainer.on('hide.bs.dropdown', function () {
            filterButton.html('Filtrele <i class="mdi mdi-chevron-down"></i>');
        });
    });
</script>
@endsection